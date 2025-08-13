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

$survey = $_GET['survey'];

/*echo "POST:".$_POST." || log:".$log."<br>\n";
echo "POST:".isset($_POST)." || log:".!isset($log)."<br>\n";*/

// If no GET data, show the survey form
// If GET check if survey exists
//    -> if exists
//      -> if IP == survey IP show form
// Else dont show the form

$showSurvey = false;

if ($survey == "") {
    $showSurvey = true;
} else {
    
}

if (isset($survey) || !isset($log)) {
    echo "<div class='formdiv'>\n";
    echo "<form action='savesurvey.php' method='POST' >\n";
    echo "Paste your new <b>Survey Scanner</b> results in this box<br><br>\n";
    echo "<textarea style='resize: none;' name='survey' rows='15' cols='80' >\n";
    echo "</textarea>\n";
    echo "<input type='submit' value='Submit' >\n";
    echo "</form>\n";
    echo "</div>\n";
}



/*$lines = explode("\n", $survey);
$rocks=0;
foreach($lines as $line) {
    list($name, $quantity, $volume, $distance) = explode("\t", $line);
    $volume = intval(preg_replace('/\xc2\xa0/', '', $volume));
    if ($volume>0) {
        $volumes[$name] += $volume;
        $volumes["Total"] += $volume;
        $rocks++;
    }
}

foreach($volumes as $key => $value) {
    echo "Total $key = $value<br>\n";
}
echo "<br><pre>";
$time = time();
$ip = $_SERVER['REMOTE_ADDR'];
$session = md5($time); 

echo "</pre><br>";
echo "Session ID : $session<br>";
echo "Time : $time<br>";
echo "Addr : $ip <br>";*/




/*

Structure ? 

$surveys[0][Total Concentrated Veldspar] = 159637
$surveys[0][Total Scordite] = 66812
$surveys[0][Total Total] = 561663
$surveys[0][Time] = 1689816315
$surveys[0][IP] = "184.161.232.10"
$surveys[1][Total Concentrated Veldspar] = 15963
$surveys[1][Total Scordite] = 6681
$surveys[1][Total Total] = 56166
$surveys[1][Time] = 2689816315
$surveys[1][IP] = "184.161.232.10"

*/


function linearRegression($data) {
    $n = count($data); // Number of observations
    $sumX = 0; // Sum of explanatory variable values
    $sumY = 0; // Sum of dependent variable values
    $sumXY = 0; // Sum of the products of x and y values
    $sumXSquare = 0; // Sum of the squares of x values

    // Calculate the sums
    foreach ($data as $point) {
        $x = $point[0]; // Explanatory variable value
        $y = $point[1]; // Dependent variable value

        $sumX += $x;
        $sumY += $y;
        $sumXY += $x * $y;
        $sumXSquare += $x * $x;
    }

    // Calculate the regression coefficients
    $a = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXSquare - $sumX * $sumX);
    $b = ($sumY - $a * $sumX) / $n;

    return array('a' => $a, 'b' => $b);
}

/* Example usage
$data = array(
    array(0, 2),
    array(1, 4),
    array(2, 6),
    array(3, 8),
    array(4, 10),
    array(5, 12),
    array(6, 14),
    array(7, 16),
    array(8, 18),
    array(9, 20)
);

$result = linearRegression($data);
echo "Equation of the regression line: y = " . $result['a'] . "x + " . $result['b']; */




?>

<br/><br/>

<center>&copy;2023 <a href="https://evewho.com/character/2120640566">Harkayn</a></center>


<!--
ToDo:
-->