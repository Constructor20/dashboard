<?php
include '../auth.php';
include 'db.php'; // PDO $pdo

header('Content-Type: application/json');

// --- Vérification du paramètre ---
if (!isset($_GET['server'])) {
    echo json_encode(["status" => "error", "message" => "Serveur non spécifié"]);
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
        echo json_encode(["status" => "error", "message" => "Accès refusé"]);
        exit;
    }
}

// --- Vérifier que le serveur existe ---
$stmt = $pdo->prepare("SELECT * FROM servers WHERE id = ?");
$stmt->execute([$server_id]);
$server = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$server) {
    echo json_encode(["status" => "error", "message" => "Serveur introuvable"]);
    exit;
}

// --- Appeler le backend Python ---
$API_URL = "http://192.168.1.22:8080/status/{$server_id}";
$options = [
    "http" => [
        "method" => "GET",
        "timeout" => 5
    ]
];
$context = stream_context_create($options);
$response = @file_get_contents($API_URL, false, $context);

if ($response === false) {
    echo json_encode(["status" => "error", "message" => "Impossible de contacter l’API Python"]);
    exit;
}

$data = json_decode($response, true);

// --- Vérifier la validité de la réponse ---
if (
    !$data ||
    !isset($data['status']) ||
    $data['status'] !== 'ok' ||
    !isset($data['server_id'])
) {
    echo json_encode(["status" => "error", "message" => "Réponse invalide du backend Python"]);
    exit;
}

// --- Retourner les infos du serveur ---
echo json_encode([
    "status" => "ok",
    "server_id" => intval($data["server_id"]),
    "server_name" => $data["server_name"] ?? $server["name"],
    "running" => (bool)$data["running"],
    "online" => (bool)$data["online"],
    "current_players" => intval($data["current_players"] ?? 0),
    "max_players" => intval($data["max_players"] ?? 0),
    "cpu" => floatval($data["cpu"] ?? 0),
    "ram" => floatval($data["ram"] ?? 0),
    "uptime" => $data["uptime"] ?? null,
    "logs" => $data["logs"] ?? []
]);
