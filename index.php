<html>

<?php

/**
 * RockRadar - Survey Scanner Analyser
 *
 * This script analyzes survey scanner data from EVE Online to provide insights
 * into resource harvesting progress, including estimated time of completion (ETA)
 * and market value estimations.
 *
 * Author: Eric Senterre (eric@senterre.com)
 * Date: 2024-12-14
 */

  $RRVersion = "1.241214"; ?>

  <head>
    <meta charset="utf-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel="stylesheet" type="text/css" href="style.css?v=<?= $RRVersion ?>" />
    <script src="functions.js?v=<?= $RRVersion ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <title>RockRadar</title>
    <!-- RockRadar v<?= $RRVersion ?> by Éric Senterre aka Harkayn ( eric at senterre.com ) -->
  </head>

<body onload="checkDevSite();">

<div class='page_title'>
    <img src="images/rockradar.png" /><br />
    Survey Scanner Analyser
</div>
<br />
<?php

require 'functions.php';

$session = $_GET['session'];

$showForm = false;

$surveys = [];
$itemNameByID = [];
$itemIDByName = [];

if ($session == "") {
    $showForm = true;
} else {
    $surveys = loadSurveys($session);
    if ($surveys[0]["ip"] == $_SERVER['REMOTE_ADDR']) {
        $showForm = true;
    }
}

    if ($showForm == true) {
?>
        <script>
        document.addEventListener('paste', async (event) => {
                const clipboardText = event.clipboardData.getData('text/plain');
                if (clipboardText) {
                    try {
                        const clipboardDataInput = document.getElementById('survey');
                        clipboardDataInput.value = clipboardText;
                        const clipboardForm = document.getElementById('clipboardForm');
                        clipboardForm.submit();
                    } catch (error) {
                        console.error('Error submitting form :', error);
                    }
                }
            });
    </script>

<?php
    echo "<center><div class='formdiv'>\n";
    echo "<form action='ss.php' method='POST' id='clipboardForm' >";
    echo "<input type='hidden' id='survey' name='survey'>";
    if ($session) {  echo "<input type='hidden' name='session' value='$session' />";  }
    echo "</form>";
    echo "Paste your new <b>Survey Scanner</b> results right on this page<br />";
	echo "<div style='text-align:left; margin:auto; width:570px; '>";
    echo "<font size=-0.5><ul><li>Don't forget to expand all ore types and select ALL these results before pasting here</li>";
	echo "<li>Please wait at least 5 minutes between each paste operation to get better results.</li></ul></font>";
    echo "</div></div></center>\n";
}

// If there's no surveys yet, stop here.
if (count($surveys) == 0 ) { exit; }

$nb_surveys = 0;
$nb_roids = 0;
$data = [];
$totals = [];

// Parse surveys and make some arrays
parseSurveys($surveys); 

?>
<div id="charts">
    <center>
    <div id="chart">
    </div>

    <div id="side_chart">
    </div>
    </center>
</div>


<script>
// Show graph
var options = {
    chart: {
      type: 'line',
      toolbar:{
            show: false,
          }
    },
    tooltip: {
        theme: "dark", // Changer le thème du tooltip à "dark" pour des couleurs de texte différentes au survol
        x: {
            show: true,
            formatter: function(val, opts) {
                return new Date(val).toLocaleTimeString(); // Formater l'heure
            }
        },
        y: {
            formatter: function(val, opts) {
                if (opts.seriesIndex === 0) {
                    return val.toLocaleString() + " m³";
                } else if (opts.seriesIndex === 1) {
                    return val.toLocaleString() + " m³/sec";
                }
                return val;
            }
        }

    },
    stroke: {
        width: 4,
        curve: "smooth"
    },
    legend: {
        labels: {
            colors: "#FFFFFF" // Change the color of legend labels here
        }
    },

    colors: ['#DFBB25', '#cc2211'],

    series: [{
      name: 'Volume',
      type: 'bar',
      data: [<?php
            $x=0;
            $previousVolume = 0;
            $previousTime = 0;
            foreach ($totals as $nb_surveys => $info) {
                // Check if the "volume" key exists in the current sub-array
                if (isset($info["volume"])) {
                    if($x>0) { echo ","; }
                    $x++;
                    $volumeValue = $info["volume"];
                    $time = $data[$nb_surveys]["time"]*1000;
                    $speed = ($previousVolume-$volumeValue)/($time-$previousTime);
                    $previousTime = $time;
                    $previousVolume = $volumeValue;
                    // Now you can use $volumeValue for further processing
                    // For example, you could print it:
                    echo "[$time, $volumeValue]";
                } 
            }
            echo ", [$time, $volumeValue]";

      ?>]
    },{
        name: 'Speed',
        type: 'line',
        data: [<?php
            $x=0;
            $previousVolume = 0;
            $previousTime = 0;
            foreach ($totals as $nb_surveys => $info) {
                // Check if the "volume" key exists in the current sub-array
                if (isset($info["volume"])) {
                    if($x>0) { echo ","; }
                    $volumeValue = $info["volume"];
                    $time = $data[$nb_surveys]["time"]*1000;
                    $speed = intval(($previousVolume-$volumeValue)/(($time-$previousTime)/1000));
                    $previousTime = $time;
                    $previousVolume = $volumeValue;
                    // Now you can use $volumeValue for further processing
                    // For example, you could print it:
                    //if ($speed == 0 ) { $speed = null; }
                    if ($speed != 0) {
                        echo "[$time, $speed]";  // ($previousVolume-$volumeValue)/($time-$previousTime)\n";
                        $x++;
                    }
                } 
            }
			/*if ($x>0) {
				echo ", [$time, $speed]";
			}*/

      ?>]
    }
    ],
    xaxis: {
        type: 'datetime',
        labels: {
            style: {
                colors : "#FFFFFF"
            },
            datetimeFormatter: {
                year: 'yyyy',
                month: 'MMM',
                day: 'dd',
                hour: 'HH:mm', // Ajoutez cette ligne pour afficher l'heure
            }
        }
    },
    yaxis: [
        {
            title: {
                text: "Volume (m³)",
                style : {
                    color: "#FFFFFF"
                }
            },
            labels: {
                style : {
                    colors: "#FFFFFF"
                }
            }
        },
        {
            opposite: true,
            title: {
                text: "Speed (m³/sec)",
                style: {
                    color: "#FFFFFF"
                }
            },
            labels: {
                style : {
                    colors : "#FFFFFF"
                }
            }
        }
    ]
  }
  
  var chart = new ApexCharts(document.querySelector("#chart"), options);
  
  chart.render();


  // bar
  var options = {

          colors: ['#DFBB25', '#222222'],
          series: [{
          name: 'Mined',
          data: [<?php
                $lastIndex = count($totals) - 1;
                $x = 0;
                foreach ($totals[0] as $name => $value) {
                    if ($name != "quantity" && $name != "volume" && $name != "rocks") {
                
                        $lastVolume = $totals[$lastIndex][$name]["volume"];
                        $firstVolume = $totals[0][$name]["volume"];
                        $percent = 100-round(($lastVolume / $firstVolume)*100);
                        if ($x>0) { echo ",";}
                        echo "$percent";
                        $x++;
                    }
                }
?>]
        }],
        chart: {
          type: 'bar',
          height: "auto",
          stacked: false,
          stackType: '100%',
          toolbar:{
            show: false,
          },
        },
        plotOptions: {
          bar: {
            horizontal: true,
            columnWidth: '15%'
          },
        },
        stroke: {
          width: 0,
          colors: ['#222']
        },
        dataLabels: {
            formatter: (val) => {
                return val + '%'
            }
        },
        xaxis: {
          categories: [<?php
                $lastIndex = count($totals) - 1;
                $x = 0;
                foreach ($totals[0] as $name => $value) {
                    if ($name != "quantity" && $name != "volume" && $name != "rocks") {
                        if ($x>0) { echo ",";}
                        echo "'$name'";
                        $x++;
                    }
                }?>],
            labels: {
                style: {
                    colors : "#FFFFFF"
                },
                formatter: function(value) {
                    return value + "%";
                }
            }
        },

        yaxis: [

        {
            max:100,
            labels: {
                style : {
                    colors: "#FFFFFF"
                }
            }
        }],

        fill: {
          opacity: 1
        
        },
        legend: {
            show: false
        },
        
        tooltip: {
            theme: "dark", // Changer le thème du tooltip à "dark" pour des couleurs de texte différentes au survol
            y: {
                formatter: function (val) {
                    return val + '%';
                }
            }
            }
        };

        var side_chart = new ApexCharts(document.querySelector("#side_chart"), options);
        side_chart.render();
        
</script>

<?php

// Show TOTAL progression 

$lastIndex = count($totals) - 1;
$previousIndex = $lastIndex - 1;
if ($previousIndex < 0)  { $previousIndex = 0; }

$lastVolume = $totals[$lastIndex]["volume"];
$firstVolume = $totals[0]["volume"];
$previousVolume = $totals[$previousIndex]["volume"];

$percent = 100-round(($lastVolume / $firstVolume)*100,0);

$lastTime = $data[$lastIndex]["time"];
$firstTime = $data[0]["time"];
$previousTime = $data[$previousIndex]["time"];
$totalTime = $lastTime - $firstTime;

?>
<br /><br />
<center>

<?php 

// load itemType reference variable
loadItemsTypes();

// Create an array with all the Ore Types
$oreTypesValue = [];
foreach ($totals[0] as $name => $value) {
    if ($name != "quantity" && $name != "volume" && $name != "rocks") {
        $oreTypesValue[$name] = 0;
        $numberOfOreTypes++;
    }
}

// Find current URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$fullURL = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

// Shorten that full URL with URLbea API
// $shortURL = shortenURL($fullURL);  // URLBea have bad reputation... I cancelled that
$shortURL = $fullURL;


// Retrieve lives Prices
$itemsList = "";
foreach ($oreTypesValue as $name=>$value) {
    $itemsList .= "$name\nCompressed $name\n";
}

$orePrice = retrievePrices($itemsList);
$updateTime = $orePrice['updateTime'];

// **** FIRST APPRAISAL CALL **** // 
// If it's the 1st Survey Scanner, request a full Appraisal with link
// And store the URL in the array
if (isset($surveys[0]) && is_array($surveys[0]) && array_key_exists("survey", $surveys[0])) {
    // echo "1st survey exists";
    if (isset($surveys[1]) && is_array($surveys[1]) && !array_key_exists("survey",$surveys[1])) {
        // echo "2nd survey exists";
    } else {
        # Now we know that we're at the 1st survey only. We'll fetch a new appraisal link
        $urlAppraisal = retrieveAppraisal($surveys[0]["survey"]);
        $surveys[0]["appraisal"] = $urlAppraisal;

		/*** TO DO : Save this new data in the JSON file ***/
    }
}


// Fonction de comparaison pour trier en fonction de la valeur "volume"
function compareVolume($a, $b) {
    return $b["volume"] - $a["volume"];
}

// Tri de l'array en utilisant la fonction de comparaison et en conservant les clés
uasort($orePrice, "compareVolume");

$oreOrder = "";

foreach ($orePrice as $name => $value) {
    // if (str_starts_with($name,"Compressed ")) {
    if (strpos($name, "Compressed ") === 0) {
        $stripName = str_replace("Compressed ", "", $name);
        $cmpName = preg_replace('/(\b[aeiouAEIOU])|([aeiouAEIOU])/', '$1', $stripName);

        if (strlen($oreOrder>0)) { $oreOrder .=" > "; }
        $oreOrder .= $cmpName;
    }
}
$oreOrder = "Price order: ".$oreOrder;



// Find remaining volume to this belt
$remainingVolume = $firstVolume - $lastVolume;

// Find AVERAGE speed
if ($totalTime > 0) {
    $averageSpeed = abs($remainingVolume / $totalTime);
} else {
    $averageSpeed = 0;
}

// Find CURRENT speed
if ($totalTime > 0) {
    $lastVolumeHarvested = $remainingVolume - $previousVolume;
	$timeSinceLastUpdate = $lastTime - $previousTime;
	$currentSpeed = abs($lastVolumeHarvested / $timeSinceLastUpdate);
} else {
    $currentSpeed = 0;
}

	
// Récupérez les résultats de la fonction calculateEtaFromLastTwoSurveys
$etaData = calculateEtaFromLastTwoSurveys($totals, $data);
$endTime = $etaData['endTime'];
$currentSpeed = $etaData['currentSpeed'];

// Formatez les résultats pour l'affichage
$endTimeFormatted = "Done";
if ($percent < 100) {
    $endTimeFormatted = date('H:i:s', $endTime);
}
if ($remainingVolume <= 0) {
    $endTimeFormatted = "TBD";
}

$currentSpeedFormatted = number_format($currentSpeed, 1, ',', ' ') . " m³/s";


$rocks = $nb_roids;
$maxRocks = $totals[0]["rocks"];

?>
<br/>

<?php 

// Assurez-vous que la valeur est entre 0 et 100.
$percent = max(0, min(100, $percent));

// Lenght of progressBar 
$progressBarLength = 35;

// Format m³ left with Metric suffixes.
$formattedLastVolume = formatVolume($lastVolume);


// Calculez le nombre de caractères à remplir dans la barre de progression
$filledChars = (int)($percent / (100/$progressBarLength)); 

// Construisez la barre de progression.
$progressBar = str_repeat('█', $filledChars) . str_repeat('░', $progressBarLength - $filledChars);

$endTimeFormatted2 = "Done";
if ($percent < 100) { $endTimeFormatted2 = date('H:i', $endTime); }
if ($remainingVolume <= 0) { $endTimeFormatted2 = "TBD"; }
$lastTimeFormatted = date('H:i', $lastTime);
$asciiReport = "/me ✻ <b>ROCK ЯΛDΛR</b> updated at <b>$lastTimeFormatted</b> ✻ \\n";
$asciiReport .= "╔═══ $shortURL ════════════──···\\n";
$asciiReport .= "║ $progressBar <b>$percent%</b>\\n";
$asciiReport .= "╚═══ ETA: <b>$endTimeFormatted2</b> ═══ Left: <b>$rocks units / $formattedLastVolume</b> ═══──···";

$motdLink = "<a href=\'$shortURL\'>Current <b>RⓄCK ЯΛDΛR</b> update</a>";
?>


<div class='name-ore-div'>
<div class='subtitle'>Global Resources Harvesting Progression (<?= $percent ?>%) <?php 
echo "[ $rocks/$maxRocks ";
if ($rocks>0) { echo ($rocks > 1 ? ' asteroids' : ' asteroid'); }
echo " ] ";
if ($showForm == true) {
    echo "<img ".
            "src='images/copy.png' ".
            "alt='Copy progession to clipboard' title='Copy progression to clipboard' ".
            "style='height:18px' ".
            "onclick='animateCopyIcon(this); copyToClipboard(\"$asciiReport\");'/>\n";

    //echo "<img src='images/motd.png' alt='Copy RR Link for MOTD' title='Copy RR Link for MOTD' style='height:15px' onclick=\"copyToClipboard('$motdLink');\"/>\n";
}
?>
</div>
</div>
<!-- >=4.5% start showing 1st scan -->
<!-- <=95.5% stop showing last scan -->
<?php echo"
<style>
    .mainTimelineLeft {
        flex: $percent%;
    }
    .mainTimelineRight {
        flex: ".(100-$percent)."%;
    }
</style>
";
?>

<div class="mainTimeline">
    <div class="mainTimelineLeft"><?php
    if ($percent>4.5) {
        echo "<div class='tooltip'>".date('H:i', $firstTime)."<span class='tooltiptext'>First scan time<br/>".date('H:i:s', $firstTime)."</span></div>"; 
    }
    if ($percent>50) {
        echo "<div class='tooltip mainTimelineCurrentLeft'>".date('H:i', $lastTime)."<span class='tooltiptext'>Last scan time<br/>".date('H:i:s', $lastTime)."</span></div>";
    }
    ?></div>
    <div class="mainTimelineRight"><?php
    if ($percent<=50) {
        echo "<div class='tooltip mainTimelineCurrentRight'>".date('H:i', $lastTime)."<span class='tooltiptext'>Last scan time<br/>".date('H:i:s', $lastTime)."</span></div>";
    }
    if ($percent<=95.5) {
        echo "<div class='tooltip'>".$endTimeFormatted2."<span class='tooltiptext'>Estimated Time of Annihilation<br/>".date('H:i:s', $endTime)."</span></div>"; 
    }
    ?></div>
</div>

<div class="stats">
    <div class="left">Size: <?php echo number_format($lastVolume,0,"."," ") ?>/<?php echo number_format($firstVolume,0,"."," ") ?> m³</div>
    <!-- <div class="center">Avg Speed: <?php echo $averageSpeedFormatted ?> - Current Speed: <?php echo $currentSpeedFormatted ?></div> -->
    <!-- <div class="center">Average Speed: <?php echo $averageSpeedFormatted ?></div> -->
    <div class="center">Current Speed: <?php echo $currentSpeedFormatted ?></div>
    <div class="right">ETA: <?=$endTimeFormatted?></div>
</div>

<?php 

// Show all ore types image / name / quantity ($last/$first) / buy value / buy value compressed
// Affichage du tableau des minéraux
echo "<br /><br /><br />";
echo "<div class='subtitle'>Informations on Ore Types and Market Prices";
if ($showForm == true) {
    echo "  <img src='images/copy.png' alt='Copy ore price order to clipboard' title='Copy ore price order to clipboard' style='height:18px' onclick='animateCopyIcon(this); copyToClipboard(\"$oreOrder\");'/>\n";
}
echo "</div>";

echo "<div class='totalYieldDiv'>";
echo "<b>*BETA*</b> Enter your <div class='tooltip'>TOTAL YIELD<span class='tooltiptext'>Your mining laser(s) + your drone(s) yield in m³/s</span></div> (in m³/s) :";
echo "<input type='number' id='totalYield' class='totalYield' value='1.0' placeholder='1.0' step='0.1' min='0.1' max='10000' oninput='calcAllIncome();' />";
echo "</div><br />";

// Appel de la nouvelle fonction pour générer le tableau
echo generateOreTable($orePrice, $totals, $itemIDByName, $lastIndex);
echo "<script>calcAllIncome();</script>";


echo "<div style='width:80%; text-align:right; font-size:14px; margin-top:3px;'>Jita's Buy Prices are updated every <div class='tooltip'>15 minutes<span class='tooltiptext'>Prices last update: ".date("Y/m/d H:i:s", $updateTime)."</span></div>.</div>";
echo "<div style='width:80%; text-align:right; font-size:14px; margin-top:3px;'><a href='".$surveys[0]["appraisal"]."' target='_blank'>Initial appraisal</a></span></div></div>";


?>


<br/><br/><br />
© <a href="https://evewho.com/character/2120640566">Harkayn</a><br>
<p class="copyrightNotice">EVE Online and the EVE logo are the registered trademarks of CCP hf. All rights are reserved worldwide. All other trademarks are the property of their respective owners. EVE Online, the EVE logo, EVE and all associated logos and designs are the intellectual property of CCP hf. All artwork, screenshots, characters, vehicles, storylines, world facts or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. CCP is in no way responsible for the content on or functioning of this website, nor can it be liable for any damage arising from the use of this website.</p>


</center>