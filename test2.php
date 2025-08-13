<?php
function shorthenURL($urlToShorten, $apiKey) {
    // L'URL de l'API de urlbae pour raccourcir un lien
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

    // Vérifie si la réponse est valide
    if ($response === FALSE) {
        return "Erreur lors de la requête HTTP";
    } else {
        // Convertit la réponse JSON en tableau associatif
        $responseData = json_decode($response, true);

        // Vérifie s'il y a eu une erreur
        if (isset($responseData['error']) && $responseData['error'] === 0) {
            // Renvoie le lien raccourci
            return $responseData['shorturl'];
        } else {
            // Renvoie un message d'erreur
            return "Erreur lors du raccourcissement de l'URL";
        }
    }
}

// Exemple d'utilisation de la fonction
$urlToShorten = 'https://www.senterre.com';
$shortenedUrl = shortenURL($urlToShorten, $apiKey);

if ($shortenedUrl !== null) {
    echo "Lien raccourci : $shortenedUrl";
} else {
    echo "Une erreur s'est produite lors du raccourcissement de l'URL.";
}
?>
