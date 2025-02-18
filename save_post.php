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
    // získání dat z formuláře
    $nazev = $_POST['nazev'] ?? '';
    $obsah = $_POST['obsah'] ?? '';
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    // Zpracování nahraného souboru
    $uploadedFilePath = null;
    if (isset($_FILES['obrazek']) && $_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
        // Cesta ke složce
        $targetDirectory = __DIR__ . '/obrazky/';
        
        // Konečná cesta k souboru
        $targetFile = $targetDirectory . basename($_FILES['obrazek']['name']);
        
        // Cesta do databáze
        $uploadedFilePath = 'obrazky/' . basename($_FILES['obrazek']['name']);
        
        // Přesun obrázku do složky
        if (!move_uploaded_file($_FILES['obrazek']['tmp_name'], $targetFile)) {
            throw new Exception("Nahrání souboru se nezdařilo.");
        }
    }

    // Pokud je v id číslo, je to úprava
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        
        // Změna cesty k obrázku
        if ($uploadedFilePath !== null) {
            $stmt = $conn->prepare("UPDATE blog_posts SET nazev = ?, obsah = ?, cesta_k_obrazku = ?, is_published = ? WHERE id = ?");
            $stmt->bind_param("sssii", $nazev, $obsah, $uploadedFilePath, $is_published, $id);
        } else {
            // Pokud nebyl nahrán nový obrázek cestu neměním
            $stmt = $conn->prepare("UPDATE blog_posts SET nazev = ?, obsah = ?, is_published = ? WHERE id = ?");
            $stmt->bind_param("ssii", $nazev, $obsah, $is_published, $id);
        }

        if ($stmt->execute()) {
            header('Location: admin.php');
            exit;
        } else {
            throw new Exception("Chyba při aktualizaci článku: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Vložení nového článku
        $stmt = $conn->prepare("INSERT INTO blog_posts (nazev, obsah, cesta_k_obrazku, is_published) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nazev, $obsah, $uploadedFilePath, $is_published);
        
        if ($stmt->execute()) {
            header('Location: admin.php');
            exit;
        } else {
            throw new Exception("Chyba při ukládání článku: " . $stmt->error);
        }
        $stmt->close();
    }

    $conn->close();
} catch (Exception $e) {
    echo '<p>Chyba: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>