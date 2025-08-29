<?php
// Configuration de la base de données
$host = 'mysql-db';
$dbname = 'minecraft_panel';  // À adapter selon le nom de ta base
$user = 'root';               // Ton utilisateur MySQL
$pass = 'root';                   // Ton mot de passe MySQL (souvent vide en local)

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exceptions en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch par défaut en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utiliser les vrais préparés
];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        $options
    );
} catch (PDOException $e) {
    // Message d'erreur plus clair
    die("Connexion à la base impossible : " . $e->getMessage());
}
