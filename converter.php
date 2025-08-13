<?php
$nom_fichier = "typeIDs.yaml";
$nom_output = "typeIDs.csv";
$fichier = fopen($nom_fichier, "r");
$output = fopen($nom_output, "w");

$headers = array('typeID', 'key', 'value');
fputcsv($output, $headers, "\t");

// Lire chaque ligne du fichier
while(!feof($fichier)) {
	$ligne = fgets($fichier);

  	if(!ctype_space(substr($ligne, 0, 1))) {
		$typeID = substr($ligne, 0, strpos($ligne, ':'));
		echo "$typeID.";
	}

	// Vérifier si la ligne commence par 4 espaces
	if (substr($ligne, 0, 8) === "        ") {

		// Extraire les deux valeurs de chaque côté du ":"
		$partie_extraite = trim(substr($ligne, 8));
		$valeurs = explode(":", $partie_extraite);
		$valeur1 = trim($valeurs[0]);
		$valeur2 = trim($valeurs[1]);

		if ($valeur1 == "en") {
			// Vérifier si la deuxième valeur est vide
			if(!empty($valeur2)) {		
				$value = $valeur2;
			}
		}

	} else if (substr($ligne, 0, 4) === "    ") {

		// Extraire les deux valeurs de chaque côté du ":"
		$partie_extraite = trim(substr($ligne, 4));
		$valeurs = explode(":", $partie_extraite);
		$valeur1 = trim($valeurs[0]);
		$valeur2 = trim($valeurs[1]);

		// Vérifier si la deuxième valeur est vide
		if ($valeur1 == "description") {
			$key = "";
		} else if(!empty($valeur2)) {		
			$key = $valeur1;
			$value = $valeur2;
		} else {
			$key = $valeur1;
		}

  	}

	if (!empty($typeID) && !empty($key) && !empty($value)) {
		$data = array($typeID,$key,$value);
		fputcsv($output, $data, "\t");
		$key = "";
		$value ="";
	}
	
}

// Fermer le fichier
fclose($output);
fclose($fichier);

?>
