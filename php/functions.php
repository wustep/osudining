<?php
	if ('functions.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
		header("Location: ../");
		die('Error.');
	}

	function db_connect() {
		static $connection;
		if(!isset($connection)) {
			$config = parse_ini_file(__DIR__.'/../config.ini',true); 
			$connection = mysqli_connect($config['mysql']['host'],$config['mysql']['user'],$config['mysql']['pass'],$config['mysql']['db']);
		}

		if ($connection === false) {
			return false; 
		}
		return $connection;
	}

	if (db_connect() == false) {
		die("Something went wrong!");
	}

	function searchForKeyword($keyword) {
		$db = db_connect();
		$stmt = $db->prepare("SELECT name FROM places WHERE name LIKE ?");

		$keyword = $keyword . '%';
		$stmt->bind_param('s', $keyword);

		$results = array();
		$stmt->execute();
		$stmt->bind_result($result);

		$out = array();
		while($stmt->fetch()) {
			array_push($out,$result);
		}
	

		$db->close();
		return $out;
	}
	
	function explodeTags($taglist, $href) {
		$tags = "";
		$taglist = array_filter(preg_split('/[,\s]+/', $taglist));
		$a = 0;
		foreach ($taglist as $tag) {
			if ($a != 0) $tags = $tags . ', ';
				$tags = $tags . '<a class="tag" href="'.$href.'?id='.$tag.'">'.$tag.'</a>';
				$a++;
		}
		return $tags; 		
	}
	
	function getPlaceInfo($get=0, $name=0) { // $get = 0 means ALL places, otherwise get the ID of $get, set $name = 1 to use name instead of id
		$db = db_connect();
		if ($name == 1) $sql = "WHERE name=?"; // show by name 
		else if ($get == 0) $sql = "ORDER BY name ASC";  // show all
		else $sql = "WHERE places.id=?"; // show by id
		if ($stmt = $db->prepare("SELECT places.id,name,mon,tue,wed,thu,fri,sat,sun,notes,website,menu,phone,location,type,tags FROM places JOIN hours ON places.id = hours.id JOIN info ON places.id = info.id $sql")) { // get seasonid, put into $sid)
			if ($name == 0 && $get != 0) $stmt->bind_param("i",$get);
			else if ($name == 1) {
				$get = str_replace('_',' ',$get);
				$stmt->bind_param("s",$get);
			} 
			$stmt->execute();
			$meta = $stmt->result_metadata(); 
			while ($field = $meta->fetch_field()) { 
				$params[] = &$row[$field->name]; 
			} 
			call_user_func_array(array($stmt, 'bind_result'), $params); 

			$i = 0;
			$result = array();
			while ($stmt->fetch()) { 
				foreach($row as $key => $val) 
				{ 
					$c[$key] = $val; 
				} 
				$result[] = $c; 
				$i++;
			}
			$stmt->close();
			if (count($result) >= 1) {
				if ($name == 1) $get = 1; // quick fix for $get being 0 on ?name request, fix later
				$day = jddayofweek(cal_to_jd(CAL_GREGORIAN, date("m"),date("d"), date("Y")), 0);
				$days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
				for ($i = 0; $i < count($result); $i++) {
					//$title = $get != 0 ? $result[$i]["name"] : "<a class='title' href='place?id=".$result[$i]["id"]."'>".$result[$i]["name"]."</a>";
					$url_part = str_replace(' ','_',$result[$i]["name"]);
					$title = $get != 0 ? $result[$i]["name"] : "<a class='title' href='place?name=$url_part'>".$result[$i]["name"]."</a>";					
					$location = $result[$i]["location"];
					echo
"		<h3><b>$title</b></h3>
		<b>Type:</b> ".explodeTags($result[$i]["type"],"type")."<br>
		<b>Address:</b> <a href=\"https://www.google.com/maps?q=".str_replace(" ","+",$location)."\">$location</a><br>
		<b>Hours:</b>
";
					if ($get != 0) { // Messy function but we'll fix it some day, maybe :)
						for ($j = 0; $j < 7; $j++) {
							$output = "&nbsp;&nbsp;$days[$j]: " . $result[$i][strtolower($days[$j])];
							if ($j == $day) {
								$output = "<b>$output</b>";
							}
							echo 
"		<br>$output
";
						}
						if (strlen($result[$i]['notes']) > 0) {
							echo 
"		<br>&nbsp;&nbsp;Note: ".$result[$i]['notes']."
";
						}
					} else {
						echo
"		<br>&nbsp;&nbsp;<b>$days[$day]:</b> ".$result[$i][strtolower($days[$day])]."
";
					}
					echo 
"		<br><b>Tags:</b> ".explodeTags($result[$i]["tags"],"tags")."
";				
					if ($get != 0) {
						if (strlen($result[$i]['website']) > 0) {
							echo 
"		<br><b>Website:</b> ".$result[$i]['website']."
";
						}
						if (strlen($result[$i]['menu']) > 0) {
							echo 
"		<br><b>Menu:</b> ".$result[$i]['menu']."
";
						}
						if (strlen($result[$i]['phone']) > 0) {
							echo 
"		<br><b>Phone:</b> ".$result[$i]['phone']."
";
						}
					}
				}
			}
		}		
	}
?>