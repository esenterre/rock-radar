<?php

// New API URL for creating an appraisal
$newApiUrl = 'https://appraise.imperium.nexus/appraisal.json';

// Define your payload data (e.g., item names)
$itemNames = "Zeolites"; // You can adjust this to include multiple item names if needed

// Set up the headers
$newApiHeaders = [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: RockRadar eric@senterre.com', // Replace with your application name
];

// Prepare the POST data
$postData = "market=jita&raw_textarea=" . urlencode($itemNames) . "&persist=no";

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

// Check if the response contains valid data
if (isset($responseData['appraisal']['items'])) {
    // Extract item data from the response and populate the $orePrice array
    $items = $responseData['appraisal']['items'];
    $orePrice = [];

    foreach ($items as $item) {
        $itemName = $item['name'];
        $buyPrice = $item['prices']['buy']['median'];
        $volume = $item['quantity'] * $item['typeVolume'];

        // Populate the $orePrice array
        $orePrice[$itemName]["single"] = $buyPrice;
        $orePrice[$itemName]["volume"] = $volume;
    }

} else {
    echo "Invalid API response.";
}

echo "<pre>";
echo var_dump($orePrice);
echo "</pre>";



?>