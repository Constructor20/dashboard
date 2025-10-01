<?php
include '../auth.php';
include 'db.php'; // PDO $pdo

header('Content-Type: application/json');

if (!isset($_GET['server'])) {
    echo json_encode(["status"=>"error", "message"=>"Serveur non spécifié"]);
    exit;
}

$server_id = intval($_GET['server']);
$user_id = $_SESSION['user_id'];

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

// --- Récupérer les logs depuis l'API Python ---
$API_URL = "http://192.168.1.22:8080/logs/{$server_id}"; // à créer côté Python
$options = [
    "http" => [
        "method" => "GET",
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
if (!$data || !isset($data['logs'])) {
    echo json_encode(["status"=>"error", "message"=>"Réponse invalide du serveur"]);
    exit;
}

// Retourner les logs
echo json_encode([
    "status" => "ok",
    "logs" => $data['logs']
]);
