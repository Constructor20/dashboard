<?php
include 'auth.php';
include 'includes/db.php';

// Vérifie si l'utilisateur est admin
if ($_SESSION['user_id'] != 1) {
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Accès refusé</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f6fa;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .message-box {
                background: #fff;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                text-align: center;
            }
            h1 {
                color: #e74c3c;
                margin-bottom: 20px;
            }
            p {
                color: #333;
                margin-bottom: 30px;
            }
            a {
                text-decoration: none;
                background: #3498db;
                color: #fff;
                padding: 10px 20px;
                border-radius: 5px;
                transition: 0.3s;
            }
            a:hover {
                background: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="message-box">
            <h1>⚠️ Accès refusé</h1>
            <p>Cette page est réservée uniquement à l\'administrateur.</p>
            <a href="servers.php">⬅ Retour aux serveurs</a>
        </div>
    </body>
    </html>';
    exit;
}

// --- Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_server') {
        $stmt = $pdo->prepare("INSERT INTO servers (name, path, type, port, max_players, java_args) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['path'],
            $_POST['type'],
            intval($_POST['port']),
            intval($_POST['max_players']),
            $_POST['java_args']
        ]);
    }

    elseif ($action === 'update_server') {
        $stmt = $pdo->prepare("UPDATE servers SET name=?, path=?, type=?, port=?, max_players=?, java_args=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['path'],
            $_POST['type'],
            intval($_POST['port']),
            intval($_POST['max_players']),
            $_POST['java_args'],
            intval($_POST['server_id'])
        ]);
    }

    elseif ($action === 'update_permissions') {
        $user_id = intval($_POST['user_id']);
        $server_id = intval($_POST['server_id']);
        $can_start = isset($_POST['can_start']) ? 1 : 0;
        $can_view = isset($_POST['can_view']) ? 1 : 0;
        $can_console = isset($_POST['can_console']) ? 1 : 0;
        $can_files = isset($_POST['can_files']) ? 1 : 0;

        $stmt = $pdo->prepare("SELECT id FROM permissions WHERE user_id=? AND server_id=?");
        $stmt->execute([$user_id, $server_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE permissions SET can_start=?, can_view=?, can_console=?, can_files=? WHERE user_id=? AND server_id=?");
            $stmt->execute([$can_start, $can_view, $can_console, $can_files, $user_id, $server_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO permissions (user_id, server_id, can_start, can_view, can_console, can_files) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $server_id, $can_start, $can_view, $can_console, $can_files]);
        }
    }
}

$servers = $pdo->query("SELECT * FROM servers")->fetchAll();
$users = $pdo->query("SELECT * FROM users")->fetchAll();

function get_user_permission($pdo, $user_id, $server_id) {
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE user_id=? AND server_id=?");
    $stmt->execute([$user_id, $server_id]);
    $perm = $stmt->fetch();
    return $perm ?: ['can_start'=>0,'can_view'=>0,'can_console'=>0,'can_files'=>0];
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Admin Serveurs Minecraft</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 20px; }
            h1 { color: #2c3e50; text-align: center; }
            .container { max-width: 1000px; margin: auto; }
            .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
            .card h2 { margin-top: 0; color: #34495e; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
            input[type="text"], input[type="number"], textarea, select {
                width: calc(100% - 20px); /* Largeur uniforme */
                padding: 8px;
                margin: 8px 0;
                border: 1px solid #bdc3c7;
                border-radius: 4px;
                display: block; /* Forcer le block */
                box-sizing: border-box; /* Inclut padding/border dans le calcul */
            }

            button {
                background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px;
            }
            button:hover { background: #2980b9; }
            .accordion { background-color: #3498db; color: white; cursor: pointer; padding: 10px; width: 100%; border: none;
                text-align: left; outline: none; font-size: 16px; transition: 0.3s; margin-top: 5px; border-radius: 5px; }
            .accordion:hover { background-color: #2980b9; }
            .panel { padding: 0 15px; display: none; background-color: #f1f1f1; border-left: 3px solid #3498db;
                border-radius: 0 0 5px 5px; margin-bottom: 10px; }
            .panel form { background: #fff; border: 1px solid #ddd; margin: 8px 0; padding: 8px; border-radius: 5px; }
            .checkbox-label { margin-right: 10px; }
            .server-form { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 6px; background: #fafafa; }
        </style>
    </head>
    <body>
    <div class="container">
        <h1>⚙️ Administration des Serveurs Minecraft</h1>

        <!-- Ajout d'un serveur -->
        <div class="card">
            <h2>➕ Ajouter un serveur</h2>
            <form method="post">
                <input type="hidden" name="action" value="add_server">
                <label>Nom du serveur :</label>
                <input type="text" name="name" required>
                <label>Chemin du serveur :</label>
                <input type="text" name="path" required>
                <label>Type :</label>
                <select name="type">
                    <option value="vanilla">Vanilla</option>
                    <option value="spigot">Spigot</option>
                    <option value="paper">Paper</option>
                    <option value="forge">Forge</option>
                </select>
                <label>Port :</label>
                <input type="number" name="port" value="25565" required>
                <label>Max joueurs :</label>
                <input type="number" name="max_players" value="20" required>
                <label>Arguments Java :</label>
                <textarea name="java_args" rows="3">-Xmx2G -Xms1G</textarea>
                <button type="submit">Ajouter</button>
            </form>
        </div>
    

    <div style="text-align:center; margin:40px 0;">
        <a href="servers.php" style="text-decoration:none; background:#3498db; color:white; padding:10px 20px; border-radius:5px; transition:0.3s;">
            ⬅ Retour aux serveurs
        </a>
    </div>

        <!-- Liste des serveurs (édition) -->
        <div class="card">
            <h2>🖥️ Modifier les serveurs existants</h2>
            <?php foreach ($servers as $srv): ?>
                <button class="accordion">⚙️ <?= htmlspecialchars($srv['name']) ?></button>
                <div class="panel">
                    <form class="server-form" method="post">
                        <input type="hidden" name="action" value="update_server">
                        <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                        
                        <label>Nom :</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($srv['name']) ?>" required>
                        
                        <label>Chemin :</label>
                        <input type="text" name="path" value="<?= htmlspecialchars($srv['path']) ?>" required>
                        
                        <label>Type :</label>
                        <select name="type">
                            <option value="vanilla" <?= $srv['type']=="vanilla" ? "selected":"" ?>>Vanilla</option>
                            <option value="spigot" <?= $srv['type']=="spigot" ? "selected":"" ?>>Spigot</option>
                            <option value="paper" <?= $srv['type']=="paper" ? "selected":"" ?>>Paper</option>
                            <option value="forge" <?= $srv['type']=="forge" ? "selected":"" ?>>Forge</option>
                        </select>
                        
                        <label>Port :</label>
                        <input type="number" name="port" value="<?= $srv['port'] ?>" required>
                        
                        <label>Max joueurs :</label>
                        <input type="number" name="max_players" value="<?= $srv['max_players'] ?>" required>
                        
                        <label>Arguments Java :</label>
                        <textarea name="java_args" rows="2"><?= htmlspecialchars($srv['java_args']) ?></textarea>
                        
                        <button type="submit">💾 Mettre à jour</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Permissions -->
        <div class="card">
            <h2>👥 Permissions utilisateurs</h2>
            <?php foreach ($users as $user): ?>
                <button class="accordion"><?= htmlspecialchars($user['username']) ?></button>
                <div class="panel">
                    <?php foreach ($servers as $srv): ?>
                        <?php $perm = get_user_permission($pdo, $user['id'], $srv['id']); ?>
                        <form method="post">
                            <div><strong><?= htmlspecialchars($srv['name']) ?></strong></div>
                            <div>
                                <label class="checkbox-label"><input type="checkbox" name="can_start" <?= $perm['can_start'] ? 'checked' : '' ?>> Démarrer</label>
                                <label class="checkbox-label"><input type="checkbox" name="can_view" <?= $perm['can_view'] ? 'checked' : '' ?>> Voir</label>
                                <label class="checkbox-label"><input type="checkbox" name="can_console" <?= $perm['can_console'] ? 'checked' : '' ?>> Console</label>
                                <label class="checkbox-label"><input type="checkbox" name="can_files" <?= $perm['can_files'] ? 'checked' : '' ?>> Fichiers</label>
                            </div>
                            <div>
                                <input type="hidden" name="action" value="update_permissions">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                                <button type="submit">💾 Sauvegarder</button>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll(".accordion").forEach(button => {
            button.addEventListener("click", function() {
                this.classList.toggle("active");
                let panel = this.nextElementSibling;
                panel.style.display = (panel.style.display === "block") ? "none" : "block";
            });
        });
    </script>
    </body>
</html>
