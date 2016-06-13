<?php
function connect_to_database($dbhost, $dbuser, $dbpass, $dbname) {
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}

function write_measurement($link, $sensor, $value) {
	$query = "INSERT INTO `measurements` (`sensor`, `value`) VALUES ('$sensor', '$value')";
	return mysqli_query($link, $query);
}

function get_last_id($link, $sensor) {
	$query = "SELECT `ID` FROM `measurements` WHERE `sensor`=$sensor ORDER BY `ID` DESC LIMIT 0,1";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	return $row['ID'];
}

function getval($mysqli, $sql) {
    $result = $mysqli->query($sql);
    $value = $result->fetch_array(MYSQLI_NUM);
    return is_array($value) ? $value[0] : "";
}

function get_measurements($link, $n, $time = null) {
	if(!isset($time))
		$time = date("Y-m-d H:i:s",time());
	
	$retarr = [];
	$minarr = [];
	$maxarr = [];
	
	for($i = 1; $i <= $n; $i++)
	{
		$query = "SELECT `value` FROM `measurements` WHERE `sensor`=$i AND `time`<='$time' ORDER BY `ID` DESC LIMIT 0,1";
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);
		$retarr[$i] = $row['value'];
	}
	
	for($i = 1; $i <= $n; $i++)
	{
		$query = "SELECT `value` FROM `measurements` WHERE `sensor`=$i ORDER BY `value` DESC LIMIT 0,1";
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);
		$maxarr[$i] = $row['value'];
	}
	
	for($i = 1; $i <= $n; $i++)
	{
		$query = "SELECT `value` FROM `measurements` WHERE `sensor`=$i ORDER BY `value` ASC LIMIT 0,1";
		$result = mysqli_query($link, $query);
		$row = mysqli_fetch_assoc($result);
		$minarr[$i] = $row['value'];
	}
	
	for($i = 1; $i <= $n; $i++)
	{
	$retarr[$i] = ($retarr[$i] - $minarr[$i]) * (100 / ($maxarr[$i] - $minarr[$i]));
	if(is_nan($retarr[$i]))
		$retarr[$i] = 0;
	}
	
	return $retarr;
}

function get_color($value) {
	if($value>0)
		$value = min(sqrt($value)*10, 100);
	if($value<50)
	{
		$green = 50 + $value;
		$red = $value * 2;
	}
	else
	{
		$green = 100 - (2 * ($value-50));
		$red = 100;
	}
		
	$red = min($red,100);
	$red = max($red,0);
	
	$green = min($green,100);
	$green = max($green,0);
		
	return array(round($red*2.55), round($green*2.55), 0);
}

function draw_room(& $im, $x, $y, $w, $h, $value) {
	$rgbcolors = get_color($value);
	$color = imagecolorallocate($im, $rgbcolors[0], $rgbcolors[1], $rgbcolors[2]);
	imagefilledrectangle($im, $x, $y, $x+$w, $y+$h, $color);
}
?>
