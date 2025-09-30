<?php
include 'includes/db.php'; // PDO $pdo

$stmt = $pdo->query("SELECT id, host, port FROM servers");
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($servers as $srv) {
    $online = false;
    $conn = @fsockopen($srv['host'], $srv['port'], $errno, $errstr, 1);
    if ($conn) {
        fclose($conn);
        $online = true;
    }
    $update = $pdo->prepare("UPDATE servers SET online = ?, last_checked = NOW() WHERE id = ?");
    $update->execute([$online ? 1 : 0, $srv['id']]);
}
