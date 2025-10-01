<?php
include 'auth.php';
include 'includes/db.php'; // PDO $pdo

// Vérifie que l'utilisateur est connecté
$user_id = $_SESSION['user_id'];

// --- Récupérer les serveurs accessibles à l'utilisateur ---
if ($user_id == 1) {
    // Admin : voir tous les serveurs
    $stmt = $pdo->query("SELECT * FROM servers");
} else {
    // Utilisateur normal : voir uniquement les serveurs pour lesquels il a une permission can_view
    $stmt = $pdo->prepare("
        SELECT s.* 
        FROM servers s
        INNER JOIN permissions p ON p.server_id = s.id
        WHERE p.user_id = ? AND p.can_view = 1
    ");
    $stmt->execute([$user_id]);
}
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include 'navbar.php'; ?>
    <meta charset="UTF-8">
    <title>Gestion Serveurs Minecraft</title>   
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #1e3c72, #2a5298); color: white; min-height: 100vh; margin: 0; text-align: center; padding-bottom: 60px; }
        h1 { margin-top: 80px; font-size: 32px; color: #ffd700; text-shadow: 0 2px 6px rgba(0,0,0,0.4); }
        .server { background: rgba(255,255,255,0.1); margin: 20px auto; padding: 20px 25px; border-radius: 20px; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: flex-start; gap: 15px; transition: transform 0.2s, background 0.3s; }
        .server:hover { transform: translateY(-3px); background: rgba(255,255,255,0.2); cursor: pointer; }
        .status { width: 18px; height: 18px; border-radius: 50%; background: red; }
        .status.green { background: #4ade80; }
        .status.red { background: #f87171; }
        .players { font-weight: bold; color: #fff; min-width: 50px; }
        .server a { text-decoration: none; color: #fff; font-weight: bold; font-size: 18px; }
    </style>
</head>
<body>
    <h1>Gestion des Serveurs Minecraft</h1>

    <?php if (empty($servers)): ?>
        <p>Aucun serveur accessible pour votre compte.</p>
    <?php else: ?>
        <?php foreach ($servers as $srv): 
            $online = $srv['online'] == 1;
            $players = $online 
                ? (isset($srv['current_players']) ? "{$srv['current_players']}/{$srv['max_players']}" : "0/{$srv['max_players']}")
                : "0/{$srv['max_players']}";
            $link = "console.php?server=" . $srv['id'];
        ?>
            <div class="server" onclick="window.location.href='<?= $link ?>'">
                <span class="status <?= $online ? 'green' : 'red'; ?>"></span>
                <span class="players"><?= $players ?></span>
                <a href="<?= $link ?>"><?= htmlspecialchars($srv["name"]); ?></a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
