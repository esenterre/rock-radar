<?php

function convert_yaml_to_csv($yaml_file, $csv_file) {
    $handle = fopen($yaml_file, 'r');
    $output = fopen($csv_file, 'w');

    // Écrire la première ligne d'en-tête dans le fichier CSV
    $headers = [];
    fputcsv($output, $headers);

    $line = fgets($handle);

    while ($line !== false) {
        $line = trim($line);

        // Ignorer les lignes vides ou les commentaires
        if (empty($line) || $line[0] == '#') {
            $line = fgets($handle);
            continue;
        }

        // Ignorer les sections nommées "description"
        if (strtolower(substr($line, 0, 11)) == 'description') {
            $line = fgets($handle);
            continue;
        }

        // Convertir la ligne YAML en un tableau associatif
        $data = parse_yaml_line($line);

        // Écrire la ligne dans le fichier CSV
        if (empty($headers)) {
            $headers = array_keys($data);
            fputcsv($output, $headers);
        }
        $csv_line = array_values($data);
        fputcsv($output, $csv_line);

        $line = fgets($handle);
    }

    fclose($handle);
    fclose($output);
}


function parse_yaml_line($line) {
    $line = trim($line);
    $parts = explode(':', $line, 2);
    $key = trim($parts[0]);
    $value = isset($parts[1]) ? trim($parts[1]) : '';

    if (substr($value, 0, 1) == '[') {
        $value = trim($value, '[]');
        $value = explode(',', $value);
        foreach ($value as $k => $v) {
            $value[$k] = trim($v);
        }
    }

    return [$key => $value];
}

$yaml_file = 'typeIDs.yaml';
$csv_file = 'typeIDs.csv';

convert_yaml_to_csv($yaml_file, $csv_file);


?>