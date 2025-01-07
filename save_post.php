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
    // připojení k databázi
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        throw new Exception("Chyba připojení: " . $conn->connect_error);
    }

    // získání dat z formuláře, ošetření vstupů
    $nazev = $_POST['nazev'];
    $obsah = $_POST['obsah'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Zpracování nahraného souboru 
    $uploadedFilePath = null;
    if (isset($_FILES['obrazek']) && $_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
        // Cesta ke SLOŽCE
        $targetDirectory = __DIR__ . '/obrazky/';
        
        // definitivní cesta k souboru
        $targetFile = $targetDirectory . basename($_FILES['obrazek']['name']);
        
        // cesta do databáze k vlání
        $uploadedFilePath = 'obrazky/' . basename($_FILES['obrazek']['name']);

        // Přesun obrázku do složky 
        if (!move_uploaded_file($_FILES['obrazek']['tmp_name'], $targetFile)) {
            throw new Exception("Nahrání souboru se nezdařilo.");
        }
    }

    // Pokud je v id číslo je to uprava
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        // Změna cesty k obrázku
        if ($uploadedFilePath !== null) {
            $stmt = $conn->prepare("UPDATE blog_posts SET nazev = ?, obsah = ?, cesta_k_obrazku = ?, is_published = ? WHERE id = ?");
            $stmt->bind_param("sssii", $nazev, $obsah, $uploadedFilePath, $is_published, $id);
        } else {
            // nic neměním když nenahrál nic novýho
            $stmt = $conn->prepare("UPDATE blog_posts SET nazev = ?, obsah = ?, is_published = ? WHERE id = ?");
            $stmt->bind_param("ssii", $nazev, $obsah, $is_published, $id);
        }

        if ($stmt->execute()) {
            header('Location: admin.php');
            exit;
        } else {
            throw new Exception("Chyba při aktualizaci článku: " . $stmt->error);
        }

    } else {
        // vložení novýho článku
        $stmt = $conn->prepare("INSERT INTO blog_posts (nazev, obsah, cesta_k_obrazku, is_published) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nazev, $obsah, $uploadedFilePath, $is_published);

        if ($stmt->execute()) {
            header('Location: admin.php');
            exit;
        } else {
            throw new Exception("Chyba při ukládání článku: " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo '<p>Chyba: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>