<?php
	require_once("../php/functions.php");
	function getAPI($x) {
		$config = parse_ini_file(__DIR__.'/../config.ini',true); 
		return $config['api'][$x];
	}

	function getGeo() { // $get=0 or no $from means ALL places, otherwise check 	$get from $from
		$db = db_connect();
		if ($stmt = $db->prepare("SELECT places.id,location FROM places JOIN info ON places.id = info.id ORDER BY NAME ASC LIMIT 5")) { // get seasonid, put into $sid)
			$stmt->execute();
			$meta = $stmt->result_metadata(); 
			while ($field = $meta->fetch_field()) { 
				$params[] = &$row[$field->name]; 
			} 
			call_user_func_array(array($stmt, 'bind_result'), $params); 
			$result = array();
			while ($stmt->fetch()) {
				foreach($row as $key => $val) 
					$c[$key] = $val; 
				$result[] = $c; 
			}
			$stmt->close();
			if (count($result) > 0) {
				$a = 0;			
				foreach ($result as $res) {
					$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($res['location'])."<br>";
					$f = file_get_contents($url);
					$r = json_decode($f, true);;
					//echo $url;
					foreach ($r['results'] as $r) {					
						echo "UPDATE places SET geo = '".$r['geometry']['location']['lat'].", ".$r['geometry']['location']['lng']."' WHERE id = '".$res['id']."';<br>";
						$a++;
					}
				}
			} else {
				echo "<b>Sorry! No results returned.</b><br>";
			} 
		}		
	}
	getGeo();
?>