<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Kontrola přihlášení
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blog_posts";

try {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Připojení k databázi
        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8mb4");

        if ($conn->connect_error) {
            throw new Exception("Chyba připojení: " . $conn->connect_error);
        }

        // Smazání článku
        $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Přesměrování zpět na admin
            header('Location: admin.php');
            exit;
        } else {
            throw new Exception("Chyba při mazání článku: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();

    } else {
        throw new Exception("Chyba při mazání.");
    }

} catch (Exception $e) {
    echo '<p>Chyba: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>