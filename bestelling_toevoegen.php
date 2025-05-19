<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bestelling verwerken</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php

session_start();

// Database connectie
$host = 'localhost';
$port = 8889;
$db   = 'domein_zoeker';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("<p>Database connectie mislukt: " . $e->getMessage() . "</p>");
}

if (empty($_SESSION['winkelmand'])) {
    die("<p>Winkelmand is leeg.</p>");
}

// Bereken subtotaal en btw
$subtotaal = 0;
foreach ($_SESSION['winkelmand'] as $item) {
    $subtotaal += $item['price'];
}
$btw = $subtotaal * 0.21; // 21% btw

// Begin transactie
$pdo->beginTransaction();

try {
    // Insert bestelling in orders tabel
    $stmt = $pdo->prepare("INSERT INTO orders (subtotal, vat) VALUES (?, ?)");
    $stmt->execute([$subtotaal, $btw]);
    $order_id = $pdo->lastInsertId();

    // Insert domeinen in order_domains tabel
    $stmtItem = $pdo->prepare("INSERT INTO order_domains (order_id, domain, price) VALUES (?, ?, ?)");
    foreach ($_SESSION['winkelmand'] as $item) {
        $stmtItem->execute([$order_id, $item['domain'], $item['price']]);
    }

    $pdo->commit();

    // Winkelmand leegmaken
    $_SESSION['winkelmand'] = [];

    echo "<p>Bestelling succesvol toegevoegd!</p>";
    echo "<p><a href='bestellingen.php'>Bekijk bestellingen</a></p>";

} catch (Exception $e) {
    $pdo->rollBack();
    die("<p>Fout bij opslaan bestelling: " . $e->getMessage() . "</p>");
}
?>
</body>
</html>
