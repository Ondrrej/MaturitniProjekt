<?php 
session_start();
header('Content-Type: text/html; charset=utf-8');

// Připojení
require_once 'db_connect.php';

$error = "";

try {
    // Zpracování loginu  a hesla
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formUsername = $_POST['username'] ?? '';
        $formPassword = $_POST['password'] ?? '';

        // Najít uživatele podle username
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $formUsername);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // když ho najdu
            $stmt->bind_result($userId, $dbUsername, $dbPasswordHash);
            $stmt->fetch();

            // Ověření hesla
            if (password_verify($formPassword, $dbPasswordHash)) {
                // Přihlášení úspěšné
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $dbUsername;
                
                // Přesměrování do administrace
                header("Location: admin.php");
                exit;
            } else {
                // Špatné heslo
                $error = "Neplatné přihlašovací údaje.";
            }
        } else {
            // Uživatel neexistuje
            $error = "Uživatelské jméno nebylo nalezeno.";
        }
        $stmt->close();
    }
} catch (Exception $e) {
    $error = "Výjimka: " . htmlspecialchars($e->getMessage());
}

// Uzavření připojení
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Přihlášení do administrace</title>
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
            max-width: 400px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
            color: #2D3436;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 15px;
            text-align: center;
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

        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Přihlášení do administrace</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="username">Uživatelské jméno:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Heslo:</label>
        <input type="password" name="password" id="password" required>

        <div class="center">
            <button type="submit">Přihlásit se</button>
        </div>
    </form>
</body>
</html>