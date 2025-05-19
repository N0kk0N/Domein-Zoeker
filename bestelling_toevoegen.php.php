<?php
session_start();

// Database connectie (pas aan naar jouw instellingen)
$host = 'localhost';
$db   = 'domein_zoeker';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connectie mislukt: " . $e->getMessage());
}

if (empty($_SESSION['winkelmand'])) {
    die("Winkelmand is leeg.");
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

    echo "Bestelling succesvol toegevoegd! <a href='bestellingen.php'>Bekijk bestellingen</a>";

} catch (Exception $e) {
    $pdo->rollBack();
    die("Fout bij opslaan bestelling: " . $e->getMessage());
}
