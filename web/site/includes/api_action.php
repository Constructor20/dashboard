<?php
include '../auth.php';
include 'db.php'; // PDO $pdo
include 'lib/woltour.php';
include 'lib/sshtour.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['server_id'], $data['action'])) {
    echo json_encode(["status" => "error", "message" => "Paramètres manquants"]);
    exit;
}

$server_id = intval($data['server_id']);
$action = strtolower($data['action']);
$user_id = $_SESSION['user_id'];

// --- Vérification serveur et permissions ---
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
    echo json_encode(["status" => "error", "message" => "Serveur introuvable"]);
    exit;
}

if ($action === 'start' && !$can_start) {
    echo json_encode(["status" => "error", "message" => "Permission refusée pour démarrer"]);
    exit;
}
if ($action === 'stop' && !$can_stop) {
    echo json_encode(["status" => "error", "message" => "Permission refusée pour arrêter"]);
    exit;
}

// --- Variables PC cible ---
$pc_ip   = $server['pc_ip'] ?? "192.168.1.22";
$pc_mac  = $server['pc_mac'] ?? "2c:f0:5d:7f:e3:2b";
$pc_user = $server['pc_user'] ?? "aleix";
$ssh_key = "/var/www/.ssh/id_rsa";

$debug = ["wol" => null, "ssh" => null];

// --- Vérifier si la tour est allumée et envoyer WOL si nécessaire ---
if ($action === 'start' && !ping_pc($pc_ip)) {
    $wol_sent = send_wol($pc_mac, "192.168.1.255");
    $debug["wol"] = $wol_sent ? "WOL envoyé" : "WOL échoué";

    $max_wait = 60;
    $elapsed = 0;
    while ($elapsed < $max_wait && !ping_pc($pc_ip)) {
        sleep(5);
        $elapsed += 5;
    }
    if ($elapsed >= $max_wait) {
        echo json_encode(["status" => "error", "message" => "Le PC n'a pas démarré après WOL", "debug" => $debug]);
        exit;
    }
}

// --- Vérifier si l'API Python est active ---
$api_alive = false;
$check_url = "http://$pc_ip:8080/status/$server_id";
$ch = curl_init($check_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($httpCode === 200 && $response) $api_alive = true;

// --- Si l'API Python inactive et action=start, lancer Python via SSH ---
if (!$api_alive && $action === 'start') {
    
    if (!wait_for_ssh($pc_ip, 22, 120)) {
        echo json_encode(["status" => "error", "message" => "SSH non dispo après 120s", "debug" => $debug]);
        exit;
    }
    
    $result = ssh_start_api();

    if ($result["success"]) {
        $debug["ssh"] = "SSH OK (exit={$result["exitCode"]}) : " . $result["stdout"];
    } else {
        $debug["ssh"] = "SSH FAIL (exit={$result["exitCode"]}) : " . $result["stderr"];
    }

    // Attente que l’API soit prête
    $max_wait = 30; // secondes max
    $elapsed = 0;
    while ($elapsed < $max_wait) {
        $ch = curl_init("http://$pc_ip:8080/status/$server_id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $resp) {
            $api_alive = true;
            break;
        }
        sleep(2);
        $elapsed += 2;
    }
}

if ($api_alive) {
    // --- Envoi de la commande de l'utilisateur à l'API Python ---
    $API_BASE = "http://$pc_ip:8080";
    $endpoint = ($action === "start") ? "/start" : "/stop";
    $API_URL = $API_BASE . $endpoint;
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
        echo json_encode(["status" => "error", "message" => "Impossible de contacter l'API Python", "debug" => $debug]);
        exit;
    }

    $resData = json_decode($response, true);
    if (!$resData) $resData = ["status" => "error", "message" => "Réponse invalide de l'API Python"];

    $resData["debug"] = $debug;
    echo json_encode($resData);
    exit;
} else {
    echo json_encode(["status" => "error", "message" => "API Python pas prête après $max_wait secondes", "debug" => $debug]);
    exit;
}

