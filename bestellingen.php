<?php
// Database connectie (zoals hierboven)
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

// Bestellingen ophalen met domeinen
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <title>Bestellingen</title>
</head>
<body>
    <h1>Bestellingen</h1>
    <?php if (empty($orders)): ?>
        <p>Er zijn nog geen bestellingen.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <h2>Bestelling #<?= $order['id'] ?> (<?= $order['created_at'] ?>)</h2>
            <p>Subtotaal: €<?= number_format($order['subtotal'], 2) ?></p>
            <p>BTW (21%): €<?= number_format($order['vat'], 2) ?></p>

            <h3>Domeinen:</h3>
            <ul>
            <?php
                $stmtItems = $pdo->prepare("SELECT * FROM order_domains WHERE order_id = ?");
                $stmtItems->execute([$order['id']]);
                $items = $stmtItems->fetchAll();
                foreach ($items as $item): ?>
                <li><?= htmlspecialchars($item['domain']) ?> - €<?= number_format($item['price'], 2) ?></li>
            <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="index.php">Terug naar zoeken</a></p>
</body>
</html>
