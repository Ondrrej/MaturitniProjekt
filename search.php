<?php 
// Připojen
require_once 'db_connect.php'; 

// Kontrola parametru 'q' 
$q = isset($_GET['q']) ? $_GET['q'] : ""; 

?> 
<!DOCTYPE html> 
<html lang="cs"> 
<head>
    <meta charset="UTF-8">
    <title>Vyhledávání</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #FFFFFF;
        }

        .search-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-container input[type='text'] {
            width: 300px;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 4px 0 0 4px;
            outline: none;
            font-size: 14px;
        }

        .search-container button {
            padding: 10px 20px;
            border: none;
            background-color: #81C784;
            color: #2D3436;
            font-weight: bold;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #66BB6A;
        }

        .search-container a {
            text-decoration: none;
            color: #2D3436;
            margin-left: 20px;
        }

        .search-container a:hover {
            color: #388E3C;
        }

        .search-result {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 6px;
            background: #F9F9F9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .search-result h2 {
            margin: 0 0 8px 0;
            font-size: 20px;
        }

        .search-result .excerpt {
            margin-bottom: 10px;
        }

        .no-results {
            margin-top: 20px;
            font-weight: bold;
            color: #B71C1C;
        }

        .read-more-link {
            color: #388E3C;
            text-decoration: none;
            font-weight: 600;
            background-color: rgba(168, 230, 207, 0.1);
            padding: 6px 12px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .read-more-link:hover {
            background-color: rgba(168, 230, 207, 0.2);
            color: #2E7D32;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <form method="GET" action="search.php">
            <input type="text" name="q" placeholder="Vyhledat..." 
                   value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>" required>
            <button type="submit">Hledat</button>
        </form>
        <a href="index.html">← Zpět na hlavní stránku</a>
    </div>

<?php
// Zpracování vyhledávacího dotazu pokud je parametr 'q'
if (!$q) {
    echo "</body></html>";
    $conn->close();
    exit;
}

// SQL dotaz s ochranou proti sql injection
$sql = "
    SELECT id, nazev AS title,
    SUBSTRING(obsah, 1, 100) AS excerpt
    FROM blog_posts
    WHERE (nazev LIKE ? OR obsah LIKE ?)
    ORDER BY id DESC
";

// kontrola SQL dotazu
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Chyba při přípravě dotazu: " . $conn->error);
}

// fulltextové vyhledávání
$likeQ = "%".$q."%";
$stmt->bind_param("ss", $likeQ, $likeQ);
$stmt->execute();

// výsledky
$result = $stmt->get_result();

// Zobrazení výsledků
if ($result->num_rows > 0) {
    echo "<h1>Výsledky hledání pro: <em>".htmlspecialchars($q, ENT_QUOTES)."</em></h1>";
    while($row = $result->fetch_assoc()) {
        ?>
        <div class="search-result">
            <h2><?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?></h2>
            <div class="excerpt"><?php echo $row['excerpt']; ?>...</div>
            <a class="read-more-link" href="#" data-id="<?php echo $row['id']; ?>">
                Číst celý příspěvek
            </a>
        </div>
        <?php
    }
} else {
    echo "<div class='no-results'>Nebyly nalezeny žádné výsledky.</div>";
}

$conn->close();
?>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div id="modal-body"></div>
    </div>
</div>

<script>

// Modální okno
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('myModal');
    const modalClose = document.querySelector('.modal-close');
    const modalBody = document.getElementById('modal-body');

    modalClose.onclick = function() {
        modal.style.display = "none";
        modalBody.innerHTML = "";
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            modalBody.innerHTML = "";
        }
    };

    // Načítání článku přes ajax po kliknutí
    const readMoreLinks = document.querySelectorAll('.read-more-link');
    readMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-id');

            // požadavek na get_post
            fetch('get_post.php?id=' + postId)
                .then(response => response.text())
                .then(data => {
                    modalBody.innerHTML = data;
                    modal.style.display = "block";
                })
                .catch(err => {
                    modalBody.innerHTML = "<p>Nepodařilo se načíst příspěvek.</p>";
                    modal.style.display = "block";
                    console.error("Chyba při načítání příspěvku:", err);
                });
        });
    });
});
</script>
</body>
</html>