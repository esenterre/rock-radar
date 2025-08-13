<?

// Set UTC timezone, the EVE Online Timezone to use
date_default_timezone_set('UTC');

// Set to show all errors
ini_set('display_errors', 'On');
error_reporting(E_ERROR);


/**
 * Génère le HTML pour le tableau des minéraux sans tooltip sur les colonnes de revenu.
 *
 * @param array $orePrice Tableau associatif des prix des minéraux.
 * @param array $totals Tableau des totaux par type de minéral.
 * @param array $itemIDByName Tableau associatif des identifiants d'objets par nom.
 * @param int $lastIndex Index du dernier sondage.
 * @return string HTML du tableau.
 */
function generateOreTable(array $orePrice, array $totals, array $itemIDByName, int $lastIndex): string
{
    $compressionRatios = [];
    $hourlyIncomeCompressed = [];

    foreach ($orePrice as $name => $value) {
        if (strpos($name, "Compressed ") === 0) {
            $stripName = str_replace("Compressed ", "", $name);
            $compressionRatios[$stripName] = (($orePrice[$stripName]["single"] / $orePrice[$stripName]["volume"]) / ($orePrice[$name]["single"] / $orePrice[$name]["volume"]));
            $hourlyIncomeCompressed[$stripName] = $orePrice[$name]["volume"] / $compressionRatios[$stripName];
        }
    }


    $tableHTML = "<table id='myTable'><thead>\n";
    $tableHTML .= "<tr style='white-space: nowrap;'>
                    <th style='text-align:left;' onclick='sortTable(0)'><img src='images/sort.png' style='height:16px; margin-right:4px;' /> Ore Type</th>
                    <th style='text-align:center;' onclick='sortNumericTable(1)'><img src='images/sort.png' style='height:16px; margin-right:4px;' /> Units left</th>
                    <th style='text-align:center;' onclick='sortNumericTable(2)'><img src='images/sort.png' style='height:16px; margin-right:4px;' /> Volume left (m³)</th>
                    <th style='text-align:center;' onclick='sortNumericTable(3)'><img src='images/sort.png' style='height:16px; margin-right:4px;' /> Price (ISK/m³)</th>
                    <th style='text-align:center;' onclick='sortNumericTable(4)'><img src='images/sort.png' style='height:16px; margin-right:4px;' /> Price Cmp (ISK/m³)</th>
                    <th style='text-align:center;'> Income (ISK/h)</th>
                    <th style='text-align:center;'> Income Cmp (ISK/h)</th>
                </tr></thead>\n";
    $tableHTML .= "<tbody>\n";

    foreach ($orePrice as $name => $value) {
        if (strpos($name, "Compressed ") === 0) {
            $stripName = str_replace("Compressed ", "", $name);
            $itemID = $itemIDByName[$stripName];
    
            $tableHTML .= "<tr>";
            $tableHTML .= "<td style='text-align:left; white-space: nowrap;'><img src='https://images.evetech.net/types/$itemID/icon?size=32' alt='$stripName' title='$stripName' style='vertical-align: middle;' /> $stripName</td>";
            $tableHTML .= "<td style='text-align:center;'>".number_format($totals[$lastIndex][$stripName]["rocks"], 0, ".", " ")."</td>";
            $tableHTML .= "<td style='text-align:center;'>".number_format($totals[$lastIndex][$stripName]["volume"], 0, ".", " ")."</td>";
            $tableHTML .= "<td style='text-align:center;'><div class='tooltip'>".number_format($orePrice[$stripName]["volume"], 0, ".", " ");
            if ($orePrice[$stripName]["volume"] != 0) {
                $tableHTML .= "<span class='tooltiptext'><b>$stripName</b><br/>Unit price: " . $orePrice[$stripName]["single"] . " ISK<br/>";
                $tableHTML .= "Unit volume: " . ($orePrice[$stripName]["single"] / $orePrice[$stripName]["volume"]) . " m³<br />";
                $tableHTML .= "Price/m³: " . number_format($orePrice[$stripName]["volume"], 2, ".", " ") . " ISK/m³</span></div></td>\n";
            } else {
                $tableHTML .= "<span class='tooltiptext'><b>$stripName</b><br/>Unit price: " . $orePrice[$stripName]["single"] . " ISK<br/>";
                $tableHTML .= "Unit volume: N/A<br />";
                $tableHTML .= "Price/m³: N/A</span></div></td>\n";
            }
    
            $tableHTML .= "<td style='text-align:center;'><div class='tooltip'>" . number_format($orePrice[$name]["volume"], 0, ".", " ");
            if ($orePrice[$name]["volume"] != 0) {
                $tableHTML .= "<span class='tooltiptext'><b>$name</b><br/>Unit price: " . $orePrice[$name]["single"] . " ISK<br/>";
                $tableHTML .= "Unit volume: " . ($orePrice[$name]["single"] / $orePrice[$name]["volume"]) . " m³<br />";
                $tableHTML .= "Price/m³: " . number_format($orePrice[$name]["volume"], 2, ".", "") . " ISK/m³</span></div></td>\n";
            } else {
                $tableHTML .= "<span class='tooltiptext'><b>$name</b><br/>Unit price: " . $orePrice[$name]["single"] . " ISK<br/>";
                $tableHTML .= "Unit volume: N/A<br />";
                $tableHTML .= "Price/m³: N/A</span></div></td>\n";
            }

            $tableHTML .= "<td style='text-align:center;' data-price='" . $orePrice[$stripName]["volume"] . "' class='calc-income'></td>\n";
            $tableHTML .= "<td style='text-align:center;' data-price='" . $hourlyIncomeCompressed[$stripName] . "' class='calc-income'></td>\n";
            $tableHTML .= "</tr>\n";
        }
    }
    

    // Calcul des moyennes pondérées
    $totalVolumeLeft = 0;
    $totalWeightedPrice = 0;
	$totalWeightedCompressedIncome = 0;
	$totalWeightedCompressedPrice = 0;

    foreach ($orePrice as $name => $value) {
        if (strpos($name, "Compressed ") === 0) {
            $stripName = str_replace("Compressed ", "", $name);
            $volumeLeft = $totals[$lastIndex][$stripName]["volume"];
            
            $totalVolumeLeft += $volumeLeft;
            $totalWeightedPrice += $volumeLeft * $orePrice[$stripName]["volume"];
			// Calculate the compressed hourly income using the ratio already calculated
			$totalWeightedCompressedIncome += $volumeLeft * $hourlyIncomeCompressed[$stripName];
			// Calculate the total of compressed prices
            $totalWeightedCompressedPrice += $volumeLeft * $orePrice[$name]["volume"];

        }
    }

    // Calcul des prix moyens pondérés
    $averagePricePerM3 = ($totalVolumeLeft > 0) ? $totalWeightedPrice / $totalVolumeLeft : 0;
	// Calculate average compressed income by dividing with the total volume
    $averageCompressedIncome = ($totalVolumeLeft > 0) ? $totalWeightedCompressedIncome / $totalVolumeLeft : 0;
	// Calculate the average of compressed prices
    $averageCompressedPricePerM3 = ($totalVolumeLeft > 0) ? $totalWeightedCompressedPrice / $totalVolumeLeft : 0;

     // Ajout de la ligne "Averages" à la fin du tableau
    $tableHTML .= "<tr style='font-weight: bold;'>";
    $tableHTML .= "<td style='text-align:left;'><span style='display:inline-block; width: 32px; height:32px; vertical-align: middle;'></span>Averages</td>";
    $tableHTML .= "<td style='text-align:center;'>-</td>";
    $tableHTML .= "<td style='text-align:center;'>" . number_format($totalVolumeLeft, 0, ".", " ") . "</td>";
    $tableHTML .= "<td style='text-align:center;'>" . number_format($averagePricePerM3, 0, ".", " ") . "</td>";
    $tableHTML .= "<td style='text-align:center;'>" . number_format($averageCompressedPricePerM3, 0, ".", " ") . "</td>";
    $tableHTML .= "<td style='text-align:center;' data-price='" . number_format($averagePricePerM3, 0, ".", "") . "' class='calc-income'></td>";
    $tableHTML .= "<td style='text-align:center;' data-price='" . number_format($averageCompressedIncome, 0, ".", "") . "' class='calc-income'></td>";
    $tableHTML .= "</tr>";

    $tableHTML .= "</tbody></table>\n";

    return $tableHTML;
}



/**
 * Load survey data from a JSON file for a specific session.
 *
 * This function takes a session identifier as input and attempts to load
 * survey data from a corresponding JSON file. It performs the following steps:
 * 1. Checks if the file exists.
 * 2. Reads the JSON data from the file.
 * 3. Decodes the JSON data into an associative array.
 * 4. Handles errors if the file does not exist or JSON decoding fails.
 *
 * @param string $session The session identifier for which to load survey data.
 * @return array|false An associative array of survey data if successful, false on error.
 */
function loadSurveys($session) {
    $file_path = "surveys/$session.json";

    // Step 1: Check if the file exists
    if (file_exists($file_path)) {
        // Step 2: Read the JSON data from the file
        $json_data = file_get_contents($file_path);

        // Step 3: Decode the JSON data and check if decoding is successful
        $decoded_data = json_decode($json_data, true); // Set the second parameter to true for associative array output

        if ($decoded_data === null) {
            // JSON decoding failed, handle the error here
            echo "Error: Invalid JSON data in the file.";
            return false;
        } else {
            // JSON decoding was successful, you can now work with the data
            // For example, print the decoded data
            return $decoded_data;
        }
    } else {
        // The file does not exist, handle the error here
        echo "Error: The file $session does not exist.";
        return false;
    }

}



/**
 * Parses a survey and extracts data into global arrays.
 *
 * This function takes a survey as input and extracts relevant data such as
 * name, quantity, volume, and distance from each line of the survey. It then
 * stores this data in global arrays ($data and $totals) for further processing.
 *
 * @data contains only raw data from the all surveys 
 * @totals contains data are summed by survey and ore type
 * 
 * @param array $survey The survey data to be parsed.
 */
function parseSurvey($survey) {

        global $nb_roids;
        global $data;
        global $totals;
        global $nb_surveys;

        // Loop thru all line of one survey
        $lines = explode("\n", $survey["survey"]);
    
        // Re-init counter for each survey
        $nb_roids=0;

        // Loop thru every line of one survey
        foreach($lines as $line) {
            // Split variables with the TAB 
            list($name, $quantity, $volume, $distance) = explode("\t", $line);

            /* // Convert to UTF-8 encoding
            iconv(mb_detect_encoding($volume, mb_detect_order(), true), "UTF-8", $volume);
            iconv(mb_detect_encoding($quantity, mb_detect_order(), true), "UTF-8", $quantity);

            $volume = preg_replace('/m3/', '', $volume);
            $quantity = preg_replace('/m3/', '', $volume);

            $volume = preg_replace('/[^\d]+/', '', $volume);
            $quantity = preg_replace('/[^\d]+/', '', $volume);

            $volume = preg_replace('/ /', '', $volume);
            $quantity = preg_replace('/ /', '', $quantity);

            // Convert string in integer (be sure to remove nbsp in string)
            $volume = intval(preg_replace('/\xc2\xa0/', '', $volume));
            $quantity = intval(preg_replace('/\xc2\xa0/', '', $quantity)); */

            $volume = cleanStringToIntegers($volume);
            $quantity = cleanStringToIntegers($quantity);

            if ($volume>0) {
                //store in $data array
                $data[$nb_surveys][$nb_roids]["name"] = $name;
                $data[$nb_surveys][$nb_roids]["quantity"] = $quantity;
                $data[$nb_surveys][$nb_roids]["volume"] = $volume;
                $data[$nb_surveys]["time"] = $survey["time"];
    
                // adjust $totals array
                // By type of ORE
                $totals[$nb_surveys][$name]["quantity"] += $quantity;
                $totals[$nb_surveys][$name]["volume"] += $volume;
                $totals[$nb_surveys][$name]["rocks"]++;
    
                // For the whole survey
                $totals[$nb_surveys]["quantity"] += $quantity;
                $totals[$nb_surveys]["volume"] += $volume;
                $totals[$nb_surveys]["rocks"]++;
    
                $nb_roids++;
            }
        }
        $nb_surveys++;

}

function cleanStringToIntegers($input) {
    // Convert encoding to UTF-8
    $converted = iconv(mb_detect_encoding($input, mb_detect_order(), true), "UTF-8", $input);
    // Remove non-breaking spaces, spaces, and "m3" text
    $cleaned = preg_replace(['/\xc2\xa0/', '/ /', '/m3/'], '', $converted);
    // Remove non-numeric characters and convert to an integer
    return intval(preg_replace('/[^\d]+/', '', $cleaned));
}


/**
 * Parses multiple surveys and aggregates their data.
 *
 * This function takes an array of surveys as input and iterates through each
 * survey using the `parseSurvey` function. It aggregates the data from all
 * surveys and stores it in global arrays ($data and $totals).
 *
 * @param array $surveys An array of surveys to be parsed.
 */
function parseSurveys($surveys) {

    global $nb_surveys;
    global $nb_roids;
    global $data;
    global $totals;

    // Init counters
    $nb_surveys = 0;
    $nb_roids = 0;

    // Loop thru all the surveys
    foreach($surveys as $survey) {
        parseSurvey($survey);
    }
}

/**
 * Save survey data to a JSON file for a specific session.
 *
 * This function takes a session identifier and survey data as input and attempts to save
 * the survey data to a corresponding JSON file. It performs the following steps:
 * 1. Checks if the session file exists and loads existing survey data if available.
 * 2. Verifies the survey data format.
 * 3. Adds the current survey data to the session data.
 * 4. Saves the updated data to the JSON file.
 *
 * @param string $session The session identifier for which to save survey data.
 * @param string $survey The survey data to be saved.
 * @return bool True if the data is successfully saved, false on error.
 */
function saveSurvey($session, $survey) {

    // Check totalVolume on the submitted data
    $lines = explode("\n", $survey);
    $totalVolumeSubmitted = 0;
    
    // Loop thru every line of one survey
    foreach($lines as $line) {
        // Split variables with the TAB 
        list($name, $quantity, $volume, $distance) = explode("\t", $line);
        $totalVolumeSubmitted += $volume;
    }

    $file_path = "surveys/$session.json";

    // If session file exists, load it
    $surveys = loadSurveys($session);
    $totalVolumeSoFar = 0;

    // If an error while loading set empty array
    if ($surveys == false) {
        $surveys = [];
    } else {
        //******* Check, if theres MORE volume than before, then
        // Suggests to start a new session or going back to the current one
        
        global $nb_surveys;
        global $nb_roids;
        global $data;
        global $totals;

        // Parse surveys and make some arrays
        parseSurveys($surveys); 

        // Keep the total volume in one variable
        #echo "<pre>";
        #echo var_dump($totals);
        #echo "</pre>";

        $totalVolumeSoFar = end($totals)["volume"];
    }

    //echo "Submitted:$totalVolumeSubmitted  vs  SoFar:$totalVolumeSoFar<br/>";

    // Check if volume pasted is higher than the volume so far
    // Display an error message if this is the case.
    if (($totalVolumeSoFar <= $totalVolumeSubmitted) && ($totalVolumeSoFar>0)) {
        echo "<center>";
        echo "This pasted data have the same or more volume than the previous one.<br/>";
        echo "Maybe you've pasted a brand new ore belt or you did expand some ore types from your Survey Scanner<br />";
        echo "Either way you can <a href='/'>Start a new session</a> or <a href='/$session'>Return and retry</a> to paste your data<br />";
        echo "</center>";
        exit;
    }

    // Check if the survey looks like a Survey Scanner results
    // Best way to check it, if there's at least one "m3" in it
    /*if (strpos($survey, "m3") === false) {
        echo "<center>This pasted data doesn't look like a Survey Scanner<br/>";
        echo "<a href='/$session'>Click here</a> to return to main page.</center>";
        exit;
    } */

	if (!validateSurveyData($survey)) {
        echo "<center>This pasted data doesn't look like a Survey Scanner<br/>";
        echo "<a href='/$session'>Click here</a> to return to main page.</center>";
        exit;
	}

    // Check if the array is empty
    if (empty($surveys)) {
        // If the array is empty, set the first index to 0 for the new value
        $newIndex = 0;
    } else {
        // Create/Add survey data
        // Get all the keys of the first index
        $keys = array_keys($surveys);

        // Get the last value of the first index
        $lastIndexValue = end($keys);

        // Increment the index for the next value you want to add
        $newIndex = $lastIndexValue + 1;    
    }

    // Add current data to the rest of the session
    $surveys[$newIndex]["time"] = time();
    $surveys[$newIndex]["ip"] = $_SERVER['REMOTE_ADDR'];
    $surveys[$newIndex]["survey"] = $survey;

    // Save data to file

    // Convert the array to JSON format
    $json_data = json_encode($surveys);
    
    // Write the JSON data to the file
    if (file_put_contents($file_path, $json_data) !== false) {
        return true;
    } else {
        echo "Error saving data to file.";
        return false;
    }    

}

/**
 * Valide les données du Survey Scanner ligne par ligne.
 *
 * Cette fonction prend les données du Survey Scanner sous forme de chaîne de caractères
 * et vérifie si chaque ligne est au format correct et contient des valeurs numériques valides.
 *
 * Étapes :
 * 1. Décompose les données en lignes en utilisant les sauts de ligne comme séparateurs.
 * 2. Pour chaque ligne :
 *   - Décompose la ligne en parties en utilisant les tabulations comme séparateurs.
 *   - Vérifie si la ligne a le format attendu (4 parties).
 *   - Supprime les caractères non numériques de la quantité, du volume et de la distance.
 *   - Vérifie si les valeurs restantes sont numériques.
 * 3. Si une ligne valide est trouvée, retourne `true`.
 * 4. Si aucune ligne valide n'est trouvée, retourne `false`.
 *
 * @param string $survey Les données du Survey Scanner sous forme de chaîne de caractères.
 * @return bool `true` si les données sont valides, `false` sinon.
 */
function validateSurveyData($survey) {
  $lines = explode("\n", $survey);

  foreach ($lines as $line) {
    $parts = explode("\t", trim($line));

    // Check if the line has the expected format (4 parts)
    if (count($parts) !== 4) {
      continue; // Skip to the next line if invalid format
    }

    // Remove non-numeric characters from quantity, volume, and distance
    $quantity = preg_replace('/\D/', '', $parts[1]);
    $volume = preg_replace('/\D/', '', $parts[2]);
    $distance = preg_replace('/\D/', '', $parts[3]);

    // Now check if the values are numeric (should always be true after the replacement)
    if (is_numeric($quantity) && is_numeric($volume) && is_numeric($distance)) {
      return true; // Found a valid line, data is valid
    }
  }

  return false; // No valid lines found
}



/**
 * Formatte un volume en m³ en utilisant les préfixes métriques standard.
 *
 * @param float $volume Le volume à formater en mètres cubes (m³).
 * @return string Le volume formaté avec l'unité appropriée.
 */
function formatVolume($volume) {
    $units = array(' m³', 'k m³', 'M m³');
	
	if ($volume > 10000000) {
		$unit = $units[2];
		$volume = round($volume / 1000 / 1000);
	} elseif ($volume > 10000) {
		$unit = $units[1];
		$volume = round($volume / 1000);
	} else {
		$unit = $units[0];
		$volume = round($volume);
	}

	$formatted = "$volume$unit";

	return $formatted;

}


function solveForX($m, $b) {
    if ($m != 0) {
        $x = (-$b) / $m;
        return $x;
    } else {
        return null; // Division by zero, unable to solve for x
    }
}
/* Example usage
$m = 2;
$b = 5;

$x = solveForX($m, $b);
if ($x !== null) {
    echo "The value of x when y = 0 is: " . $x;
} else {
    echo "Unable to solve for x. Division by zero.";
}*/

function strToDateTime($time) {
    return DateTime::createFromFormat("Y.m.d H:i", $time);
}

function format_volume($volume) {
    $units = ['m³', 'km³', 'Mm³', 'Gm³', 'Tm³', 'Pm³', 'Em³', 'Zm³', 'Ym³'];
    $base = 1000;

    $index = 0;
    while ($volume >= $base && $index < count($units) - 1) {
        $volume /= $base;
        $index++;
    }

    // If the volume is greater than or equal to 1000, keep only two numbers
    if ($volume >= 1000) {
        $formatted_volume = number_format($volume, 0);
    } else {
        // Round the volume to one decimal place
        $formatted_volume = number_format($volume, 1);
    }

    return $formatted_volume . $units[$index];
}

function convertToSlug($inputString) {
    $lowercaseString = strtolower($inputString);
    $slug = str_replace(' ', '-', $lowercaseString);
    return $slug;
}

/**
 * base66_encode -  This function takes an integer reverse it and encodes it
 *                  with characters that can be used in an URL. 
 *                  Only caveat, if the integer ends with a zero, function adds 1.
 * 
 *                  Resulting in a looking random and short string.
 *                  Not safe by any means, but useful to obsfuscating an integer and shorten URL length
 *
 * @param $number the integer to encode
 */
function base66_encode($number) {

	$number = ($number % 10 !== 0) ? $number : $number +1 ;
	$number = strrev($number);

	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_.~';
    $base = strlen($characters);
    $encoded = '';

    while ($number > 0) {
        $remainder = $number % $base;
        $encoded = $characters[$remainder] . $encoded;
        $number = floor($number / $base);
    }

    return $encoded;
}

/** 
 * base66_decode -  The counterpart of the base66_encode. 
 *                  Will decode the string $encoded and return an integer
 * 
 * @param $encoded the encoded string
*/
function base66_decode($encoded) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_.~';
    $base = strlen($characters);
    $decoded = 0;

    for ($i = 0; $i < strlen($encoded); $i++) {
        $decoded = $decoded * $base + strpos($characters, $encoded[$i]);
    }

    return strrev($decoded);
}


/**
 * Load item types from a CSV file and store them in global arrays.
 *
 * @return array An array containing type names.
 */
function loadItemsTypes() {
    // Declare global variables to store item names and IDs.
    global $itemNameByID, $itemIDByName;

    // Initialize an empty array to store type names.
    $typeNames = array();

    // Open the 'typeIDs.csv' file for reading.
    $file = fopen('typeIDs.csv', 'r');

    // Read the headers from the CSV file.
    $headers = fgetcsv($file, 0, "\t");

    // Loop through each row in the CSV file.
    while (($data = fgetcsv($file, 0, "\t")) !== FALSE) {
        $typeID = $data[0];
        $key = $data[1];
        $value = $data[2];

        // Check if the key is 'name'.
        if ($key == 'name') {
            // Store the item name by ID and item ID by name.
            $itemNameByID[$typeID] = $value;
            $itemIDByName[$value] = $typeID;
        }
    }

    // Close the CSV file.
    fclose($file);

    // Return the array containing type names.
    return $typeNames;
}


function shortenURL($urlToShorten) {
    // Vérifie d'abord si la réponse est en cache
    $cacheFileName = md5($urlToShorten); // Utilisez une clé unique basée sur l'URL
    $cacheDirectory = 'cache/'; // Remplacez par votre propre chemin

    // Vérifie si le cache existe et n'a pas expiré
    if (file_exists($cacheDirectory . $cacheFileName) && (time() - filemtime($cacheDirectory . $cacheFileName)) < 30 * 24 * 60 * 60) {
        // Si le cache est valide, renvoie la réponse du cache
        return file_get_contents($cacheDirectory . $cacheFileName);
    } else {
        // Sinon, effectue la requête HTTP comme vous le faites actuellement
        $apiUrl = 'https://urlbae.com/api/url/add';
        $apiKey = '49a6dc886bd1036d822903415d180df5';

        // Les données à envoyer sous forme de tableau associatif
        $data = array(
            'url' => $urlToShorten
        );

        // Les options de la requête HTTP
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n" .
                             "Authorization: Bearer $apiKey\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );

        // Crée un contexte pour la requête HTTP
        $context  = stream_context_create($options);

        // Effectue la requête HTTP
        $response = file_get_contents($apiUrl, false, $context);

        if ($response === FALSE) {
            return "Erreur lors de la requête HTTP";
        } else {
            // Convertit la réponse JSON en tableau associatif
            $responseData = json_decode($response, true);

            if (isset($responseData['error']) && $responseData['error'] === 0) {
                // Enregistre la réponse en cache
                file_put_contents($cacheDirectory . $cacheFileName, $responseData['shorturl']);
                // Renvoie le lien raccourci
                return $responseData['shorturl'];
            } else {
                return "Erreur lors du raccourcissement de l'URL";
            }
        }
    }
}

// Retrieve Price for EVEPraisal API
function retrievePrices($itemsList) {
    // New API URL for creating an appraisal
    $newApiUrl = 'https://appraise.imperium.nexus/appraisal.json';
    $newApiUrl = 'https://appraise.gnf.lt/appraisal.json';

    // Define your payload data (e.g., item names)
    $itemNames = $itemsList; // You can adjust this to include multiple item names if needed

    // Set up the headers
    $newApiHeaders = [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: RockRadar - eric@senterre.com', // Replace with your application name
    ];

    // Prepare the POST data
    $postData = "market=jita&raw_textarea=" . urlencode($itemNames) . "&persist=no";

    // Define the cache file path
    $cacheFile = 'cache/' . md5($postData);

    // Check if the cache file exists and is within the cache lifetime
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 900)) { // 900 seconds = 15 minutes
        // Load the response from the cache
        $response = file_get_contents($cacheFile);
		$updateTime = filemtime($cacheFile);

    } else {
        // Initialize cURL
        $ch = curl_init($newApiUrl);

        // Configure cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $newApiHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            // Save the response to the cache file
            file_put_contents($cacheFile, $response);
			$updateTime = time();
        }

        // Close cURL session
        curl_close($ch);
    }

    // Process the API response
    $responseData = json_decode($response, true);

    // Check if the response contains valid data
    if (isset($responseData['appraisal']['items'])) {
        // Extract item data from the response and populate the $orePrice array
        $items = $responseData['appraisal']['items'];
        $orePrice = [];

        foreach ($items as $item) {
            $itemName = $item['name'];
            $buyPrice = $item['prices']['buy']['max'];
            $volume = $buyPrice / $item['typeVolume'];

            // Populate the $orePrice array
            $orePrice[$itemName]["single"] = $buyPrice;
            $orePrice[$itemName]["volume"] = $volume;
        }

		$orePrice['updateTime'] = $updateTime;

        // Use the populated $orePrice array as needed
        return $orePrice;
    } else {
        echo "Invalid API response.";
		return array(); // return empty array to prevent warnings
	}

}

function retrieveAppraisal($survey) {
    // New API URL for creating an appraisal
    $newApiUrl = 'https://appraise.imperium.nexus/appraisal.json';
    $newApiUrl = 'https://appraise.gnf.lt/appraisal.json';

    // Define your payload data (e.g., item names)
    $itemNames = $survey; // You can adjust this to include multiple item names if needed

    // Set up the headers
    $newApiHeaders = [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: RockRadar - eric@senterre.com', // Replace with your application name
    ];

    // Prepare the POST data
    $postData = "market=jita&raw_textarea=" . urlencode($itemNames) . "&persist=yes";

    // Define the cache file path
    $cacheFile = 'cache/' . md5($postData);

    // Check if the cache file exists and is within the cache lifetime
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 21600)) { // 21600 seconds = 6 hours
        // Load the response from the cache
        $response = file_get_contents($cacheFile);
    } else {
        // Initialize cURL
        $ch = curl_init($newApiUrl);

        // Configure cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $newApiHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } 
        else {
            // Save the response to the cache file
            file_put_contents($cacheFile, $response);
        }

        // Close cURL session
        curl_close($ch);
    }

    // Process the API response
    $responseData = json_decode($response, true);

    // Check if the response contains valid data
    if (isset($responseData['appraisal']['id'])) {
        $urlAppraisal = "https://appraise.imperium.nexus/a/".$responseData['appraisal']['id'];
        $urlAppraisal = "https://appraise.gnf.lt/a/".$responseData['appraisal']['id'];
        return $urlAppraisal;
    } else {
        echo "Invalid API response.";
    }

}

// Janice API key : GlnomGWP8qw7EuIvVDBT1LrXtHzI8xVw
function retrievePrices_Janice($itemsList) {
    // New API URL for creating an appraisal
    $newApiUrl = 'https://janice.e-351.com/api/rest/v2/appraisal?market=2&persist=true&compactize=true&pricePercentage=1';

    // Replace 'YOUR_API_KEY' with your actual API key
    $apiKey = 'GlnomGWP8qw7EuIvVDBT1LrXtHzI8xVw';

    // Set up the headers
    $newApiHeaders = [
        'Content-Type: text/plain',
        'X-ApiKey: ' . $apiKey,
    ];

    // Prepare the POST data
    $postData = $itemsList;

    // Define the cache file path
    $cacheFile = 'cache/' . md5($postData);

    // Check if the cache file exists and is within the cache lifetime
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 900)) { // 900 seconds = 15 minutes
        // Load the response from the cache
        $response = file_get_contents($cacheFile);
    } else {
        // Initialize cURL
        $ch = curl_init($newApiUrl);

        // Configure cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $newApiHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            // Save the response to the cache file
            file_put_contents($cacheFile, $response);
        }

        // Close cURL session
        curl_close($ch);
    }

    // Process the API response
    $responseData = json_decode($response, true);

   /* echo "<pre>";
    echo $response;
    echo "</pre>";*/

    // Check if the response contains valid data
    if (isset($responseData['items'])) {
        // Extract item data from the response and populate the $orePrice array
        $items = $responseData['items'];
        $orePrice = [];

        foreach ($items as $item) {
            $itemName = $item['itemType']['name'];
            $buyPrice = $item['effectivePrices']['buyPrice'];
            $volume = $item['totalVolume'];

            // Populate the $orePrice array
            $orePrice[$itemName]["single"] = $buyPrice;
            $orePrice[$itemName]["volume"] = $volume;
        }

        // Use the populated $orePrice array as needed
        return $orePrice;
    } else {
        echo "Invalid API response.";
    }
}


/**
 * Calcule l'ETA (Estimated Time of Annihilation) en utilisant les données des deux derniers sondages.
 *
 * @param array $totals Tableau contenant les totaux de tous les sondages.
 * @param array $data Tableau contenant les données brutes de tous les sondages.
 * @return array Tableau contenant le temps de fin estimé et la vitesse actuelle.
 */
function calculateEtaFromLastTwoSurveys($totals, $data)
{
    $lastIndex = count($totals) - 1;
    $previousIndex = $lastIndex - 1;
    if ($previousIndex < 0) {
        $previousIndex = 0;
    }

    $lastVolume = $totals[$lastIndex]["volume"];
    $previousVolume = $totals[$previousIndex]["volume"];

    $lastTime = $data[$lastIndex]["time"];
    $previousTime = $data[$previousIndex]["time"];

    $timeSinceLastUpdate = $lastTime - $previousTime;
    $lastVolumeHarvested = $previousVolume - $lastVolume;

    if ($timeSinceLastUpdate > 0 && $lastVolumeHarvested > 0) {
        $currentSpeed = $lastVolumeHarvested / $timeSinceLastUpdate;
        $remainingTime = $lastVolume / $currentSpeed;
        $endTime = $lastTime + $remainingTime;
    } else {
        $currentSpeed = 0;
        $endTime = 0;
    }

    return [
        'endTime' => $endTime,
        'currentSpeed' => $currentSpeed
    ];
}

?>