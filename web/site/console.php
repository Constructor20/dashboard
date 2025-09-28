<?php
include 'auth.php';
include 'includes/db.php';

if (!isset($_GET['server_id'])) {
    die("Serveur non spécifié.");
}

$server_id = intval($_GET['server_id']);

// Récupère les infos du serveur
$stmt = $pdo->prepare("SELECT * FROM servers WHERE id=?");
$stmt->execute([$server_id]);
$server = $stmt->fetch();

if (!$server) {
    die("Serveur introuvable.");
}

// Vérifie les permissions de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM permissions WHERE user_id=? AND server_id=?");
$stmt->execute([$_SESSION['user_id'], $server_id]);
$perm = $stmt->fetch();

if (!$perm || !$perm['can_start']) {
    die("Accès refusé à ce serveur.");
}

// Actions (start, stop, etc.) via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    // Ici tu appelles ton API Python pour démarrer/stop le serveur
    // Exemple: file_get_contents("http://127.0.0.1:8080/start")
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Console - <?= htmlspecialchars($server['name']) ?></title>
    <style>
        body { font-family: Arial,sans-serif; background:#f5f6fa; padding:20px; }
        h1 { text-align:center; color:#2c3e50; }
        .actions button { padding:10px 20px; margin:5px; border:none; border-radius:5px; cursor:pointer; }
        .start { background:#2ecc71; color:white; }
        .stop { background:#e74c3c; color:white; }
    </style>
</head>
<body>
    <h1>Console du serveur: <?= htmlspecialchars($server['name']) ?></h1>
    <div class="actions">
        <?php if ($perm['can_start']): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="start">
                <button class="start" type="submit">Démarrer</button>
            </form>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="stop">
                <button class="stop" type="submit">Arrêter</button>
            </form>
        <?php endif; ?>
        <!-- Tu peux ajouter ici d'autres boutons comme envoyer commande, etc. -->
    </div>
</body>
</html>
