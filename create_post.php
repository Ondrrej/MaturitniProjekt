<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Kontrola přihlášení
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Vytvořit nový článek</title>
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
        input[type="text"], input[type="file"] {
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
    </style>
    <script>
        // Inicializace TinyMCE
        tinymce.init({
            selector: '#obsah',
            language: 'cs',
            height: 500,
            plugins: 'print preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount charmap quickbars emoticons',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist checklist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media link anchor codesample',
            content_style: 'body { font-family:Arial,sans-serif; font-size:14px }'
        });
    </script>
</head>
<body>
    <h1>Vytvořit nový článek</h1>
    <form action="save_post.php" method="post" enctype="multipart/form-data">
        <label for="nazev">Název článku:</label>
        <input type="text" name="nazev" id="nazev" required>

        <label for="obsah">Obsah článku:</label>
        <textarea name="obsah" id="obsah"></textarea>

        <label for="obrazek">Vyberte obrázek k nahrání:</label>
        <input type="file" name="obrazek" id="obrazek" accept="image/*">

        <label for="is_published">Publikovat:</label>
        <input type="checkbox" name="is_published" id="is_published" value="1">

        <button type="submit">Uložit</button>
    </form>
</body>
</html>