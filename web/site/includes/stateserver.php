<?php
include 'db.php'; // connexion MySQL (PDO $pdo)

// Récupérer les serveurs
$stmt = $pdo->query("SELECT id FROM servers");
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// URL de l’API Python
$API_URL = "http://192.168.1.22:8080";

foreach ($servers as $srv) {
    $server_id = $srv['id'];
    $url = $API_URL . $server_id;

    // Appel vers API Python
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        // si l'API ne répond pas, on met offline
        $update = $pdo->prepare("UPDATE servers SET online = 0, current_players = 0, last_checked = NOW() WHERE id = ?");
        $update->execute([$server_id]);
        continue;
    }

    $data = json_decode($response, true);

    if (isset($data["running"])) {
        $online = $data["running"] ? 1 : 0;

        // "0/20" → séparer online/max
        $players_str = $data["players"] ?? "0/0";
        list($current_players, $max_players) = explode("/", $players_str);

        $update = $pdo->prepare("UPDATE servers 
            SET online = ?, current_players = ?, max_players = ?, last_checked = NOW()
            WHERE id = ?");
        $update->execute([$online, (int)$current_players, (int)$max_players, $server_id]);
    }
}
