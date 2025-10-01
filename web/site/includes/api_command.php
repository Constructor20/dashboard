<?php
include '../auth.php';
include 'db.php'; // PDO $pdo

header('Content-Type: application/json');

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['server_id'], $input['command'])) {
    echo json_encode(["status"=>"error", "message"=>"Paramètres manquants"]);
    exit;
}

$server_id = intval($input['server_id']);
$command = trim($input['command']);
$user_id = $_SESSION['user_id'];

if ($command === '') {
    echo json_encode(["status"=>"error", "message"=>"Commande vide"]);
    exit;
}

// --- Vérifier les permissions ---
if ($user_id != 1) {
    $stmt = $pdo->prepare("
        SELECT can_console
        FROM permissions
        WHERE server_id = ? AND user_id = ?
    ");
    $stmt->execute([$server_id, $user_id]);
    $perm = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$perm || !$perm['can_console']) {
        echo json_encode(["status"=>"error", "message"=>"Accès refusé"]);
        exit;
    }
}

// --- Vérifier que le serveur existe ---
$stmt = $pdo->prepare("SELECT * FROM servers WHERE id = ?");
$stmt->execute([$server_id]);
$server = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$server) {
    echo json_encode(["status"=>"error", "message"=>"Serveur introuvable"]);
    exit;
}

// --- Envoyer la commande à l'API Python ---
$API_URL = "http://192.168.1.22:8080/command";
$options = [
    "http" => [
        "header" => "Content-Type: application/json\r\n",
        "method" => "POST",
        "content" => json_encode([
            "server_id" => $server_id,
            "command" => $command
        ]),
        "timeout" => 3
    ]
];
$context = stream_context_create($options);
$response = @file_get_contents($API_URL, false, $context);

if ($response === false) {
    echo json_encode(["status"=>"error", "message"=>"Impossible de contacter le serveur"]);
    exit;
}

$data = json_decode($response, true);
if (!$data) {
    echo json_encode(["status"=>"error", "message"=>"Réponse invalide du serveur"]);
    exit;
}

// Retourner le résultat
echo json_encode($data);
