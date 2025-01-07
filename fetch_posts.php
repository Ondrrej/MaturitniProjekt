<?php
header('Content-Type: text/html; charset=utf-8');

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

    // uložení návštěvy
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $visited_at = date('Y-m-d H:i:s');

    $insert_sql = "INSERT INTO page_visits (ip_address, user_agent, visited_at) VALUES (?, ?, ?)";
    $stmt_visit = $conn->prepare($insert_sql);
    $stmt_visit->bind_param("sss", $ip_address, $user_agent, $visited_at);
    $stmt_visit->execute();
    $stmt_visit->close();

    $sql = "SELECT id, nazev, obsah, datum_vytvoreni, cesta_k_obrazku 
            FROM blog_posts 
            WHERE is_published = 1
            ORDER BY datum_vytvoreni DESC";

    $result = $conn->query($sql);

    $output = '';

    if ($result->num_rows > 0) {
        $output .= '<div class="posts-grid">';

        while($row = $result->fetch_assoc()) {
            $datum = date('d.m.Y', strtotime($row['datum_vytvoreni']));

            $output .= '<article class="post">';
            $output .= '<div class="post-content-wrapper">';

            if (!empty($row['cesta_k_obrazku'])) {
                $output .= '<div class="post-image">';
                $output .= '<img src="' . htmlspecialchars($row['cesta_k_obrazku']) . '" alt="' . htmlspecialchars($row['nazev']) . '" loading="lazy">';
                $output .= '</div>';
            }

            $output .= '<div class="post-details">';
            $output .= '<h2 class="post-title">' . htmlspecialchars($row['nazev']) . '</h2>';
            $output .= '<div class="post-meta">Publikováno: ' . $datum . '</div>';
            $excerpt = mb_substr(strip_tags($row['obsah']), 0, 100) . '...';
            $output .= '<div class="post-excerpt">' . $excerpt . '</div>';
            $output .= '<div class="read-more"><a href="#" data-id="' . $row['id'] . '">Číst více</a></div>';
            $output .= '</div>';

            $output .= '</div>';
            $output .= '</article>';
        }

        $output .= '</div>';
    } else {
        $output = '<p>Žádné příspěvky k zobrazení.</p>';
    }

    echo $output;

} catch (Exception $e) {
    echo '<p>Chyba při načítání příspěvků: ' . htmlspecialchars($e->getMessage()) . '</p>';
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>