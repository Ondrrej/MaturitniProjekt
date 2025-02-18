<?php
session_start();

// Vymažu session
$_SESSION = [];

// Zničím session
session_destroy();

// Přesměruji zpět na login
header("Location: login.php");
exit;
?>