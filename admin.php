<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Kontrola, jestli je uživatel přihlášen
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Pokud není přihlášen, přesměruju na login
    header("Location: login.php");
    exit;
}

// Připojení k databázi
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blog_posts";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        throw new Exception("Chyba připojení: " . $conn->connect_error);
    }

    // Zjistíme, zda chceme zobrazit články (výchozí) nebo statistiky návštěv
    $section = isset($_GET['section']) ? $_GET['section'] : 'posts';

    // Předpřipravíme proměnné pro články i statistiky
    $articlesResult = null;
    $stats = [];

    if ($section === 'posts') {
        // Získání seznamu článků
        $sqlPosts = "SELECT id, nazev, datum_vytvoreni, is_published FROM blog_posts ORDER BY datum_vytvoreni DESC";
        $articlesResult = $conn->query($sqlPosts);
    } elseif ($section === 'stats') {
        // Načtení základních statistik z tabulky page_visits
        // Počet všech záznamů (počet návštěv)
        $sqlCount = "SELECT COUNT(*) AS total_visits FROM page_visits";
        $countResult = $conn->query($sqlCount);
        $countRow = $countResult->fetch_assoc();
        $stats['total_visits'] = $countRow['total_visits'];

        // Unikátní IP adresy
        $sqlUnique = "SELECT COUNT(DISTINCT ip_address) AS unique_ips FROM page_visits";
        $uniqueResult = $conn->query($sqlUnique);
        $uniqueRow = $uniqueResult->fetch_assoc();
        $stats['unique_ips'] = $uniqueRow['unique_ips'];

        // Poslední návštěvy (limit 10 pro ukázku)
        $sqlLastVisits = "SELECT ip_address, user_agent, visited_at FROM page_visits ORDER BY visited_at DESC LIMIT 10";
        $stats['last_visits'] = $conn->query($sqlLastVisits);
    }

} catch (Exception $e) {
    echo '<p>Chyba: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Administrace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #FFFFFF;
        }
        h1 {
            text-align: center;
            color: #2D3436;
        }
        .logout-wrapper {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .logout-button {
            padding: 10px 15px;
            background-color: #d12e2e;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            border-radius: 4px;
        }
        .logout-button:hover {
            background-color: #af1e1e;
        }
        .nav-bar {
            text-align: center;
            margin-bottom: 20px;
        }
        .nav-bar a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 15px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .nav-bar a:hover {
            background-color: #2980b9;
        }
        .content-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .article-list, .stats-list {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #F0F0F0;
        }
        a.button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #388E3C;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            border-radius: 4px;
        }
        a.button:hover {
            background-color: #2E7D32;
        }
        .actions a {
            margin-right: 10px;
        }
        .actions a.delete {
            color: #E53935;
        }
    </style>
</head>
<body>

    <h1>Administrace</h1>

    <div class="logout-wrapper">
        <a href="logout.php" class="logout-button">Odhlásit se</a>
    </div>

    <div class="nav-bar">
        <a href="admin.php?section=posts">Články</a>
        <a href="admin.php?section=stats">Statistiky návštěvnosti</a>
    </div>

    <div class="content-container">
        <?php if ($section === 'posts'): ?>
            <!-- Sekce pro správu článků -->
            <div class="article-list">
                <a href="create_post.php" class="button">Vytvořit nový článek</a>

                <table>
                    <thead>
                        <tr>
                            <th>Název</th>
                            <th>Datum vytvoření</th>
                            <th>Publikováno</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($articlesResult) && $articlesResult && $articlesResult->num_rows > 0): ?>
                            <?php while($row = $articlesResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nazev']); ?></td>
                                    <td><?php echo htmlspecialchars($row['datum_vytvoreni']); ?></td>
                                    <td><?php echo $row['is_published'] ? 'Ano' : 'Ne'; ?></td>
                                    <td class="actions">
                                        <a href="edit_post.php?id=<?php echo $row['id']; ?>">Upravit</a>
                                        <a href="delete_post.php?id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Opravdu chcete smazat tento článek?');">Smazat</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Žádné články nebyly nalezeny.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($section === 'stats'): ?>
            <!-- Sekce pro statistiky -->
            <div class="stats-list">
                <h2>Statistiky návštěvnosti</h2>
                <p><strong>Celkový počet návštěv:</strong> <?php echo (int)$stats['total_visits']; ?></p>
                <p><strong>Počet unikátních IP adres:</strong> <?php echo (int)$stats['unique_ips']; ?></p>

                <h3>Poslední návštěvy (max. 10)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>IP adresa</th>
                            <th>User-Agent</th>
                            <th>Čas návštěvy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stats['last_visits'] && $stats['last_visits']->num_rows > 0): ?>
                            <?php while ($visit = $stats['last_visits']->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($visit['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars($visit['user_agent']); ?></td>
                                    <td><?php echo htmlspecialchars($visit['visited_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Žádné záznamy nenalezeny.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>