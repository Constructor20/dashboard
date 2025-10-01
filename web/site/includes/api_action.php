<?php
include '../auth.php';
include 'db.php'; // PDO $pdo

header('Content-Type: application/json');

// Récupérer les données JSON envoyées
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['server_id'], $data['action'])) {
    echo json_encode(["status"=>"error","message"=>"Paramètres manquants"]);
    exit;
}

$server_id = intval($data['server_id']);
$action = strtolower($data['action']);
$user_id = $_SESSION['user_id'];

// --- Vérifier serveur et permissions ---
if ($user_id == 1) {
    $stmt = $pdo->prepare("SELECT * FROM servers WHERE id=?");
    $stmt->execute([$server_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);
    $can_start = $can_stop = true;
} else {
    $stmt = $pdo->prepare("
        SELECT s.*, p.can_start, p.can_stop
        FROM servers s
        INNER JOIN permissions p ON p.server_id = s.id
        WHERE s.id=? AND p.user_id=?
    ");
    $stmt->execute([$server_id, $user_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);
    $can_start = $server['can_start'] ?? false;
    $can_stop  = $server['can_stop'] ?? false;
}

if (!$server) {
    echo json_encode(["status"=>"error","message"=>"Serveur introuvable"]);
    exit;
}

// --- Vérifier action autorisée ---
if ($action === 'start' && !$can_start) {
    echo json_encode(["status"=>"error","message"=>"Permission refusée pour démarrer"]);
    exit;
}
if ($action === 'stop' && !$can_stop) {
    echo json_encode(["status"=>"error","message"=>"Permission refusée pour arrêter"]);
    exit;
}

// --- Envoyer requête à l'API Python ---
$API_URL = "http://192.168.1.22:8080/" . $action;
$postData = json_encode(["server_id" => $server_id]);

$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode(["status"=>"error","message"=>"Impossible de contacter le serveur"]);
    exit;
}

$resData = json_decode($response, true);
if (!$resData) $resData = ["status"=>"error","message"=>"Réponse invalide de l'API"];

echo json_encode($resData);
