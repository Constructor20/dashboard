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
    /* --- Corps de page --- */
    body { 
        font-family: Arial, sans-serif; 
        background: #f5f6fa; 
        margin: 0; 
        padding: 20px; 
    }
    h1 { 
        color: #2c3e50; 
        text-align: center; 
    }
    .container { 
        max-width: 1000px; 
        margin: auto; 
    }

    /* --- Card --- */
    .card {
        background: #fff; 
        border-radius: 12px; 
        padding: 20px; 
        margin-bottom: 25px; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .card h2 { 
        margin-top: 0; 
        color: #34495e; 
        border-bottom: 2px solid #ecf0f1; 
        padding-bottom: 5px; 
    }

    /* --- Formulaires --- */
    input[type="text"], input[type="number"], textarea, select {
        width: calc(100% - 20px);
        padding: 8px;
        margin: 8px 0;
        border: 1px solid #bdc3c7;
        border-radius: 4px;
        box-sizing: border-box;
    }
    button {
        background: #3498db; 
        color: white; 
        border: none; 
        padding: 10px 15px; 
        border-radius: 4px; 
        cursor: pointer; 
        margin-top: 10px;
    }
    button:hover { background: #2980b9; }
    .server-form, .perm-form { 
        background: #fafafa; 
        border: 1px solid #ddd; 
        padding: 10px; 
        border-radius: 6px; 
        margin: 8px 0; 
    }

    /* --- Accordions --- */
    .accordion {
        background-color: #3498db; 
        color: white; 
        cursor: pointer; 
        padding: 10px; 
        width: 100%; 
        border: none;
        text-align: left; 
        outline: none; 
        font-size: 16px; 
        transition: 0.3s; 
        margin-top: 5px; 
        border-radius: 5px; 
    }
    .accordion:hover { background-color: #2980b9; }
    .panel { 
        padding: 0 15px; 
        display: none; 
        background-color: #f1f1f1; 
        border-left: 3px solid #3498db;
        border-radius: 0 0 5px 5px; 
        margin-bottom: 10px; 
    }

    /* --- Toggle switches --- */
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin: 10px 0;
    }
    .switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: relative;
        width: 50px;
        height: 24px;
        background-color: #ccc;
        border-radius: 24px;
        transition: 0.3s;
    }
    .slider:before {
        content: "";
        position: absolute;
        height: 20px;
        width: 20px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }
    .switch input:checked + .slider {
        background-color: #4caf50;
    }
    .switch input:checked + .slider:before {
        transform: translateX(26px);
    }
    .server-name { 
        margin-top: 15px; 
        margin-bottom: 5px; 
        font-weight: bold; 
    }
    .form-actions { margin-top: 10px; }
    .save-btn {
        background-color: #007acc;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.2s;
    }
    .save-btn:hover { background-color: #005fa3; }

    /* --- Bouton retour --- */
    .back-btn {
        text-decoration:none; 
        background:#3498db; 
        color:white; 
        padding:10px 20px; 
        border-radius:5px; 
        transition:0.3s;
        display: inline-block;
        margin: 40px 0;
    }
    .back-btn:hover { background:#2980b9; }
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
                        <form method="post" class="perm-form">
                            <div class="server-name"><strong><?= htmlspecialchars($srv['name']) ?></strong></div>
                            <div class="checkbox-group">
                                <label class="switch">
                                    <input type="checkbox" name="can_start" <?= $perm['can_start'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                    Démarrer
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="can_view" <?= $perm['can_view'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                    Voir
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="can_console" <?= $perm['can_console'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                    Console
                                </label>
                                <label class="switch">
                                    <input type="checkbox" name="can_files" <?= $perm['can_files'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                    Fichiers
                                </label>
                            </div>
                            <div class="form-actions">
                                <input type="hidden" name="action" value="update_permissions">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                                <button type="submit" class="save-btn">💾 Sauvegarder</button>
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
