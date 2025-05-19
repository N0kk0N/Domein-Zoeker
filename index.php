<?php
session_start(); // ➤ Sessie starten voor winkelmand

// Functie om domeinen te zoeken
function zoekDomeinen($name, $extensions = ['com','nl','net','org','eu','info','biz','io','dev','shop']) {
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

$results = null;
$zoekterm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zoekterm = trim($_POST['domeinnaam'] ?? '');
    if ($zoekterm !== '') {
        $results = zoekDomeinen($zoekterm);
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Domein Zoeker</title>
</head>
<body>
    <h1>Domein Zoeker</h1>

    <!-- Link naar winkelmand zonder teller -->
    <p>
        <p>
    <a href="winkelmand.php">🛒 Winkelmand</a> | 
    <a href="bestellingen.php">📄 Bestellingen</a>
</p>

    </p>

    <form method="post" action="">
        <input type="text" name="domeinnaam" placeholder="Vul domeinnaam in" value="<?= htmlspecialchars($zoekterm) ?>" required>
        <button type="submit">Zoek</button>
    </form>

    <?php if ($results !== null): ?>
        <h2>Resultaten voor "<?= htmlspecialchars($zoekterm) ?>"</h2>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Domein</th>
                <th>Status</th>
                <th>Prijs (EUR)</th>
                <th>Toevoegen</th>
            </tr>
            <?php foreach ($results as $domain): 
                $status = $domain['status'];
                $price = $domain['price']['product']['price'] ?? 'N/A';
                $available = ($status === 'free');
            ?>
            <tr>
                <td><?= htmlspecialchars($domain['domain']) ?></td>
                <td><?= $available ? 'Beschikbaar' : 'Niet beschikbaar' ?></td>
                <td><?= number_format($price, 2) ?></td>
                <td>
                    <?php if ($available): ?>
                        <form method="post" action="winkelmand.php" style="margin:0;">
                            <input type="hidden" name="domain" value="<?= htmlspecialchars($domain['domain']) ?>">
                            <input type="hidden" name="price" value="<?= $price ?>">
                            <button type="submit">Toevoegen</button>
                        </form>
                    <?php else: ?>
                        <button disabled>Niet beschikbaar</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
