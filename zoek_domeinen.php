<?php
function zoekDomeinen($name, $extensions = ['com', 'nl', 'net', 'org', 'eu', 'info', 'biz', 'io', 'dev', 'shop']) {
    $url = "https://dev.api.mintycloud.nl/api/v2.1/domains/search?with_price=true";
    $apiKey = "072dee999ac1a7931c205814c97cb1f4d1261559c0f6cd15f2a7b27701954b8d";

    $data = [];
    foreach ($extensions as $ext) {
        $data[] = [
            "name" => $name,
            "extension" => $ext
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        return null;
    }

    curl_close($ch);
    return json_decode($response, true);
}

// Voorbeeld gebruik:
$result = zoekDomeinen("example");
echo "<pre>";
print_r($result);
echo "</pre>";
?>
