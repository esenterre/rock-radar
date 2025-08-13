<html>
  <head>
    <meta charset="utf-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel= "stylesheet" type="text/css" href="style.css" />
    <script src="functions.js"></script>
    <title>RockRadar</title>
  </head>

<body>

<div class='page_title'>
    <img src="images/rockradar.png" /><br />
    Survey Scanner Analyser
</div>
<br />

<?php

require 'functions.php';

$survey = $_POST["survey"];
$session = $_POST["session"];
$ip = $_SERVER['REMOTE_ADDR'];

if ($session == "") { $session = base66_encode(time()); }

$result = saveSurvey($session, $survey);

if ($result == true) {
  $newurl = "/$session";
	echo "<center>Data saved to file successfully.<br/>";
	echo "Redirecting to main page.<br/><br/>";
	echo "<a href='$newurl'>Click here</a> if not redirected</center>";
	echo "<script>";
	echo "const newURL = '$newurl'; ";
	echo "window.location.href = newURL; ";
	echo "</script>";
} else {

}
?>

<br/><br/>

<center>&copy; <a href="https://evewho.com/character/2120640566">Harkayn</a></center>
