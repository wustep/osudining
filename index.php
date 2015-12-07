<?php
	require_once("frames/head.php"); 
?>
	<div class="maindiv">
		<img src="img/eduDine.png" class="logo img-responsive">
		<input id="search" type="text" list="suggestions" class="form-control search_big" placeholder="Where would you like to eat?" data-toggle="dropdown">
		<datalist id="suggestions"></datalist> 
	</div>
	<script type="text/javascript" src='js/autocomplete.js'></script>
	<script typu="text/javascript" src='js/search.js'></script>
	
<?php
  	require_once("frames/footer.php");
?>