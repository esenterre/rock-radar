<?php
function base66_encode($number) {

	echo "$number=>";
	$number = ($number % 10 !== 0) ? $number : $number +1 ;
	echo "$number=>";
	$number = strrev($number);
	echo "$number=>";

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

function base66_decode($encoded) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_.~';
    $base = strlen($characters);
    $decoded = 0;

    for ($i = 0; $i < strlen($encoded); $i++) {
        $decoded = $decoded * $base + strpos($characters, $encoded[$i]);
    }

    return strrev($decoded);
}

$ts2 = microtime(true)*10000;

$timestamp = time();
$encodedTimestamp = base66_encode($timestamp);



echo "<pre>";
echo "PHP version              :" . phpversion() . "\n";
echo "Original Timestamp       : $timestamp\n";
echo "Base66 Encoded Timestamp : $encodedTimestamp\n";

$decodedString = base66_decode($encodedTimestamp);

echo "Decoded Timestamp        : $decodedString";


echo "\n\n\n";
$encodedTimestamp = base66_encode($ts2);

echo "\nOriginal Timestamp       : $ts2\n";
echo "Base66 Encoded Timestamp : $encodedTimestamp\n";

$decodedString = base66_decode($encodedTimestamp);

echo "Decoded Timestamp        : $decodedString";


?>