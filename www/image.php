<?php
require 'config.php';
require 'functions.php';
header('Content-Type: image/png');

$link = connect_to_database($dbhost, $dbuser, $dbpass, $dbname);

if(isset($_GET['time']))
{
	$values = get_measurements($link, $numsensors, $_GET['time']);
	$time = $_GET['time'];
}
else
{
	$values = get_measurements($link, $numsensors);
	$time = date("Y-m-d H:i:s",time());
}
	

// Load images...
$im     = imagecreatefrompng("images/map.png");
$overlay = imagecreatefrompng("images/overlay.png");

// Draw heatmap on map...
draw_room($im, 400, 314, 183, 180, $values[1]); // PLENUM A
draw_room($im, 585, 314, 183, 180, $values[2]); // PLENUM B
draw_room($im, 769, 314, 180, 180, $values[3]); // PLENUM C
draw_room($im, 1264, 726, 198, 148, $values[4]); // MAXI D
draw_room($im, 557, 725, 129, 150, $values[5]); // MAXI E
draw_room($im, 994, 50, 98, 147, $values[6]); // BISTRO
draw_room($im, 1066, 225, 229, 177, $values[7]); // PEJSESTUE
draw_room($im, 1030, 403, 264, 181, $values[8]); // RESTAURANT
draw_room($im, 184, 660, 198, 371, $values[9]); // KLATRESALEN

// Put overlay on map+heatmap
imagecopy($im, $overlay, 0, 0, 0, 0, 1920, 1080);

// Draw timestamp
$text = $time;
$color = imagecolorallocate($im, 255, 255, 255);
imagettftext($im, 20, 0, 20, 1060, $color, $timestampfont, $text );

// Show image
imagepng($im);

// Delete images from memory
imagedestroy($im);
imagedestroy($overlay);

?>