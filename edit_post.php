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
    // Získání ID článku z URL
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Získání dat článku z databáze a cesty k obrázku
        $stmt = $conn->prepare("SELECT nazev, obsah, cesta_k_obrazku, is_published FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($nazev, $obsah, $cesta_k_obrazku, $is_published);
        $stmt->fetch();

        if ($stmt->num_rows == 0) {
            throw new Exception("Článek nebyl nalezen.");
        }
        $stmt->close();
    } else {
        throw new Exception("Nebyl zadán ID článku.");
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
    <title>Upravit článek</title>
    <!-- TinyMCE editor -->
    <script src="https://cdn.tiny.cloud/1/f4qnu9avyajk0itp299ux3dd4bex5fagku1d8ba0bsiw661x/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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

        form {
            max-width: 800px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
            color: #2D3436;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="checkbox"] {
            margin-top: 5px;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #388E3C;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        button:hover {
            background-color: #2E7D32;
        }

        .preview-img {
            margin-top: 10px;
            max-width: 300px;
            display: block;
        }
    </style>
    <script>
        // Inicializace TinyMCE
        tinymce.init({
            selector: '#obsah',
            language: 'cs',
            height: 500,
            plugins: 'print preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount charmap quickbars emoticons',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist checklist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview save print | insertfile image media link anchor codesample',
            content_style: 'body { font-family:Arial,sans-serif; font-size:14px }'
        });
    </script>
</head>
<body>
    <h1>Upravit článek</h1>
    <form action="save_post.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <label for="nazev">Název článku:</label>
        <input type="text" name="nazev" id="nazev" 
               value="<?php echo htmlspecialchars($nazev, ENT_QUOTES); ?>" required>

        <label for="obsah">Obsah článku:</label>
        <textarea name="obsah" id="obsah"><?php echo htmlspecialchars($obsah); ?></textarea>

        <label for="obrazek">Nahradit/změnit obrázek (pokud necháte prázdné, zůstane původní):</label>
        <input type="file" name="obrazek" id="obrazek" accept="image/*">

        <?php if(!empty($cesta_k_obrazku)): ?>
            <img src="<?php echo htmlspecialchars($cesta_k_obrazku); ?>" 
                 alt="Nahraný obrázek" class="preview-img">
        <?php endif; ?>

        <label for="is_published">Publikovat:</label>
        <input type="checkbox" name="is_published" id="is_published" value="1" 
               <?php if($is_published) echo 'checked'; ?>>

        <button type="submit">Uložit změny</button>
    </form>
</body>
</html>