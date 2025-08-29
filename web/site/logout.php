<?php
session_start();

// Détruit toutes les données de session
$_SESSION = [];
session_destroy();

// Redirection vers la page de connexion
header('Location: index.php');
exit;
