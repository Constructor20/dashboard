<?php
include '../auth.php';
include 'db.php'; // PDO $pdo

header('Content-Type: application/json');

// --- Fonctions utilitaires --- //
function ping_pc($ip, $timeout = 2) {
    if (stripos(PHP_OS, 'WIN') === 0) {
        $output = shell_exec("ping -n 1 -w " . ($timeout*1000) . " " . escapeshellarg($ip));
        return (strpos($output, "TTL=") !== false);
    } else {
        $output = shell_exec("ping -c 1 -W $timeout " . escapeshellarg($ip));
        return (strpos($output, "ttl=") !== false);
    }
}

function send_wol($mac, $broadcast = "255.255.255.255", $port = 9) {
    $mac = str_replace([':', '-'], '', $mac);
    if (strlen($mac) != 12) return false;

    $packet = str_repeat(chr(0xFF), 6);
    for ($i = 0; $i < 16; $i++) {
        $packet .= pack('H12', $mac);
    }

    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock === false) return false;

    socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);
    $result = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, $port);
    socket_close($sock);

    return $result !== false;
}

// --- Lecture données JSON envoyées --- //
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['server_id'], $data['action'])) {
    echo json_encode(["status"=>"error","message"=>"Paramètres manquants"]);
    exit;
}

$server_id = intval($data['server_id']);
$action = strtolower($data['action']);
$user_id = $_SESSION['user_id'];

// --- Vérifier serveur et permissions --- //
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

// --- Vérifier action autorisée --- //
if ($action === 'start' && !$can_start) {
    echo json_encode(["status"=>"error","message"=>"Permission refusée pour démarrer"]);
    exit;
}
if ($action === 'stop' && !$can_stop) {
    echo json_encode(["status"=>"error","message"=>"Permission refusée pour arrêter"]);
    exit;
}

// --- Variables PC cible --- //
$pc_ip = $server['pc_ip'] ?? "192.168.1.22";   
$pc_mac = $server['pc_mac'] ?? "2c:f0:5d:7f:e3:2b"; 
$ssh_user = $server['ssh_user'] ?? "pi";       // ajouter colonne SSH user
$ssh_key  = $server['ssh_key_path'] ?? "/root/.ssh/id_rsa"; // chemin clé privée dans container

// --- Si start : vérifier si PC est ON, sinon envoyer WOL --- //
if ($action === 'start') {
    if (!ping_pc($pc_ip)) {
        send_wol($pc_mac, "192.168.1.255");
        $max_wait = 60; // attendre jusqu'à 60s
        $elapsed = 0;
        while ($elapsed < $max_wait && !ping_pc($pc_ip)) {
            sleep(5);
            $elapsed += 5;
        }
        if ($elapsed >= $max_wait) {
            echo json_encode(["status"=>"error","message"=>"Le PC n'a pas démarré après WOL"]);
            exit;
        }
    }
}

// --- Lancer le script Python via SSH --- //
$remote_script = $action === 'start' ? '/home/pi/start_server.py' : '/home/pi/stop_server.py';
$ssh_cmd = "ssh -i " . escapeshellarg($ssh_key) . " -o StrictHostKeyChecking=no $ssh_user@$pc_ip 'python3 $remote_script'";

exec($ssh_cmd, $output, $status);

if ($status !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Impossible d'exécuter le script Python via SSH",
        "output" => $output
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Action '$action' exécutée avec succès",
    "output" => $output
]);