<?php
header('Content-Type: text/html; charset=utf-8');

// Parametry pro připojení k databázi
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
} catch (Exception $e) {
    echo '<p>Chyba při připojení k databázi: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
?>