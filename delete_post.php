<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Kontrola přihlášení
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Připojení
require_once 'db_connect.php';

try {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Smazání článku
        $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Přesměrování zpátk\y na admin
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