<?php
// logout.php
session_start();

// Zruším všechny session proměnné
$_SESSION = [];

// Zničím session
session_destroy();

// Přesměruji zpět na login
header("Location: login.php");
exit;
?>