# heatmap
Heatmap install-instructions:

1.	Import database.sql on your webserver i.e. using phpMyAdmin.

2a.	Edit www/config.php to suit your needs.

2b. Upload contents of www-directory to your webserver.

2c.	Change permissions of 'timelapse' directory to 777!!!

3a.	Edit HeatmapSensor.ino to suit your needs. Each sensor should have a unique ID.
	Do not leave gaps in the numbering! It's important to point at the right server
	and to set-up your WiFi-connection.

3b.	Place jumper on GPIO12 and GPIO14 to enable sensor-calibration-mode.
	
3c.	Compile and upload the sketch to your ESP-12 (or compatible device).

3d.	HeatmapSensor is now in calibration-mode. Adjust the screw on your sound-sensor
	to get the flashing led to represent environmental-noise.

3e.	Move jumper from GPIO12-GPIO14 to GPIO12-GPIO13.


If you did everything according to these instructions, your Heatmap should work. If
you need an image instead of a refreshing webpage, just point to 
wherever-you-located-your-heatmap/image.php

Files to edit when customizing:

config.php: number of sensors
image.php: map rooms
images/map.png: background for your heatmap
images/overlay.png: this will be placed on top of your map+heatmap	
