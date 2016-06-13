<!DOCTYPE html>
<html>
<head>
<style>
html {
	height: 100%;
	width: 100%;
}
body {
	background: url('images/map.png');
	background-color:rgb(37, 37, 37);
	background-size: contain;
	background-repeat: no-repeat;
	margin: 0;
	padding: 0;
	height: 100%;
	width: 100%;
}

overlay1 {
	height: 100%;
	width: 100%;
	background-size: contain;
	background-repeat: no-repeat;
	position: absolute;
	display:none;
}

overlay2 {
	height: 100%;
	width: 100%;
	background-size: contain;
	background-repeat: no-repeat;
	position: absolute;
	display:none;
}

</style>
<title>Heatmap</title>
<script type='text/javascript' src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script type='text/javascript'>
function reloadBackground() {
	
	var randomId = new Date().getTime();
	$('overlay1').css("background-image", "url(image.php?random=" + randomId + ")");
	$(document).ready(function(){$("overlay1").fadeIn(1000);});
	setTimeout(fadeOutOverlay2, 1000);
	setTimeout(reloadOverlay, 120000);
}

function reloadOverlay() {
	var randomId = new Date().getTime();
	$('overlay2').css("background-image", "url(image.php?random=" + randomId + ")");
	$(document).ready(function(){$("overlay2").fadeIn(1000);});
	setTimeout(fadeOutOverlay1, 1000);
	setTimeout(reloadBackground, 120000);
}

function fadeOutOverlay1() {
	$(document).ready(function(){$("overlay1").fadeOut(1000);});
}

function fadeOutOverlay2() {
	$(document).ready(function(){$("overlay2").fadeOut(1000);});
}

</script>
</head>

<body onload="reloadBackground()">
<overlay1></overlay1><overlay2></overlay2>
</body>
</html>