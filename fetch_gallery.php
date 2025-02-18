<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'db_connect.php';

try {
    // Výběr obrázků z tabulky fotogalerie – řazeno podle ID sestupně
    $sql = "SELECT id, nazev, cesta_k_obrazku FROM fotogalerie ORDER BY id DESC";
    $result = $conn->query($sql);

    $output = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Obrázek obalen do divu s třídou "gallery-item" a obsahuje data-title a data-description 
            $output .= '<div class="gallery-item" data-title="' . htmlspecialchars($row['nazev']) . '" data-description="Popis obrázku: ' . htmlspecialchars($row['nazev']) . '">';
            $output .= '<img src="' . htmlspecialchars($row['cesta_k_obrazku']) . '" alt="' . htmlspecialchars($row['nazev']) . '">';
            $output .= '</div>';
        }
    } else {
        $output = '<div class="gallery-item"><p>Žádné obrázky k zobrazení.</p></div>';
    }
    echo $output;
} catch (Exception $e) {
    echo '<div class="gallery-item"><p>Chyba při načítání galerie: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>