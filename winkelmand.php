<?php
session_start();

// Winkelmand initialiseren als die nog niet bestaat
if (!isset($_SESSION['winkelmand'])) {
    $_SESSION['winkelmand'] = [];
}

// Verwijderen van domein uit winkelmand (via GET parameter)
if (isset($_GET['verwijder'])) {
    $verwijderDomain = $_GET['verwijder'];
    foreach ($_SESSION['winkelmand'] as $key => $item) {
        if ($item['domain'] === $verwijderDomain) {
            unset($_SESSION['winkelmand'][$key]);
            // Herindexeren van array keys
            $_SESSION['winkelmand'] = array_values($_SESSION['winkelmand']);
            break;
        }
    }
}

// Toevoegen van domein aan winkelmand (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = $_POST['domain'] ?? '';
    $price = floatval($_POST['price'] ?? 0);

    // Check dat domein nog niet in winkelmand zit
    $bestaatAl = false;
    foreach ($_SESSION['winkelmand'] as $item) {
        if ($item['domain'] === $domain) {
            $bestaatAl = true;
            break;
        }
    }

    if (!$bestaatAl && $domain !== '' && $price > 0) {
        $_SESSION['winkelmand'][] = [
            'domain' => $domain,
            'price' => $price
        ];
    }

    // Na toevoegen terug naar winkelmand pagina om dubbel submitten te voorkomen
    header('Location: winkelmand.php');
    exit;
}

// Bereken subtotaal en btw (21%)
$subtotaal = 0;
foreach ($_SESSION['winkelmand'] as $item) {
    $subtotaal += $item['price'];
}
$btw = $subtotaal * 0.21;
$totaal = $subtotaal + $btw;
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <title>Winkelmand</title>
</head>
<body>
    <h1>Winkelmand</h1>

    <?php if (empty($_SESSION['winkelmand'])): ?>
        <p>Je winkelmand is leeg.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Domein</th>
                <th>Prijs (EUR)</th>
                <th>Actie</th>
            </tr>
            <?php foreach ($_SESSION['winkelmand'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['domain']) ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td>
                    <a href="winkelmand.php?verwijder=<?= urlencode($item['domain']) ?>" onclick="return confirm('Weet je zeker dat je dit domein wilt verwijderen?');">Verwijder</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p>Subtotaal: €<?= number_format($subtotaal, 2) ?></p>
        <p>BTW (21%): €<?= number_format($btw, 2) ?></p>
        <p><strong>Totaal: €<?= number_format($totaal, 2) ?></strong></p>

        <form method="post" action="bestelling_toevoegen.php">
            <button type="submit">Bestelling afronden</button>
        </form>
    <?php endif; ?>

    <p><a href="index.php">Verder zoeken</a></p>
</body>
</html>
