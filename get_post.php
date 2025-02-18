<?php
header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['id'])) {
    echo '<p>Chyba: Nebylo zadáno ID příspěvku.</p>';
    exit;
}

$id = intval($_GET['id']);

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

    $stmt = $conn->prepare("SELECT nazev, obsah, datum_vytvoreni, cesta_k_obrazku FROM blog_posts WHERE id = ? AND is_published = 1");
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $stmt->bind_result($nazev, $obsah, $datum_vytvoreni, $cesta_k_obrazku);

    if ($stmt->fetch()) {
        $datum = date('d.m.Y', strtotime($datum_vytvoreni));

        echo '<h2>' . htmlspecialchars($nazev) . '</h2>';
        echo '<div class="post-meta">Publikováno: ' . $datum . '</div>';

        if (!empty($cesta_k_obrazku)) {
            echo '<img src="' . htmlspecialchars($cesta_k_obrazku) . '" alt="' . htmlspecialchars($nazev) . '" style="max-width:100%; margin:15px 0;">';
        }

        echo '<div class="post-content">' . $obsah . '</div>';
    } else {
        echo '<p>Příspěvek nebyl nalezen.</p>';
    }

    $stmt->close();

} catch (Exception $e) {
    echo '<p>Chyba při načítání příspěvku: ' . htmlspecialchars($e->getMessage()) . '</p>';
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>