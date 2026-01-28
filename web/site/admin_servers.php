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
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: radial-gradient(circle at top left, #0f172a, #1e293b);
                color: #e2e8f0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .message-box {
                background: rgba(255,255,255,0.04);
                padding: 40px;
                border-radius: 18px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.4);
                text-align: center;
                border: 1px solid rgba(255,255,255,0.03);
            }
            h1 { color: #ff6b6b; margin-bottom: 20px; text-shadow: 0 0 8px rgba(255,107,107,0.25); }
            a {
                text-decoration: none;
                background: linear-gradient(90deg,#2563eb,#3b82f6);
                color: white;
                padding: 10px 20px;
                border-radius: 8px;
                transition: 0.3s;
                box-shadow: 0 0 15px rgba(59,130,246,0.25);
            }
            a:hover { background: #005fa3; transform: translateY(-2px); }
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
        $can_stop  = isset($_POST['can_stop'])  ? 1 : 0;
        $can_view = isset($_POST['can_view']) ? 1 : 0;
        $can_console = isset($_POST['can_console']) ? 1 : 0;
        $can_files = isset($_POST['can_files']) ? 1 : 0;

        $stmt = $pdo->prepare("SELECT id FROM permissions WHERE user_id=? AND server_id=?");
        $stmt->execute([$user_id, $server_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE permissions SET can_start=?, can_stop=?, can_view=?, can_console=?, can_files=? WHERE user_id=? AND server_id=?");
            $stmt->execute([$can_start, $can_stop, $can_view, $can_console, $can_files, $user_id, $server_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO permissions (user_id, server_id, can_start, can_stop, can_view, can_console, can_files) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $server_id, $can_start, $can_stop, $can_view, $can_console, $can_files]);
        }
    }
}

$servers = $pdo->query("SELECT * FROM servers")->fetchAll();
$users = $pdo->query("SELECT * FROM users")->fetchAll();

function get_user_permission($pdo, $user_id, $server_id) {
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE user_id=? AND server_id=?");
    $stmt->execute([$user_id, $server_id]);
    $perm = $stmt->fetch();
    return $perm ?: ['can_start'=>0,'can_stop'=>0,'can_view'=>0,'can_console'=>0,'can_files'=>0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Serveurs Minecraft</title>
    <?php include 'navbar.php'; ?>
    <style>
        /* 🌌 Thème général */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b);
            color: #e2e8f0;
            margin: 0;
            padding-bottom: 60px;
        }

        /* ✨ Titres */
        h1 {
            text-align: center;
            margin-top: 80px;
            color: #60a5fa;
            text-shadow: 0 0 20px rgba(96,165,250,0.6);
        }
        h2 {
            color: #93c5fd;
            text-shadow: 0 0 10px rgba(147,197,253,0.4);
        }

        /* 🧱 Conteneur général */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        /* 💠 Cartes */
        .card {
            background: rgba(30,41,59,0.7);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 16px;
            box-shadow: 0 0 25px rgba(59,130,246,0.18);
            padding: 22px 24px;
            margin-bottom: 26px;
            transition: all 0.25s ease;
        }
        .card:hover {
            box-shadow: 0 0 40px rgba(59,130,246,0.28);
            transform: translateY(-2px);
        }

        /* 🧩 Inputs */
        input[type="text"], select, textarea, input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            margin: 8px 0 14px;
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 10px;
            background: rgba(255,255,255,0.03);
            color: #f1f5f9;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 10px rgba(59,130,246,0.4);
            background: rgba(255,255,255,0.05);
        }

        /* 🎛️ Boutons */
        button, .save-btn {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 10px 18px;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 0 14px rgba(59,130,246,0.3);
            transition: all 0.25s ease;
        }
        button:hover, .save-btn:hover {
            box-shadow: 0 0 24px rgba(59,130,246,0.45);
            transform: translateY(-2px);
        }

        /* 🔹 Bouton retour */
        a.back-btn {
            display: inline-block;
            text-decoration: none;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            transition: 0.25s;
            box-shadow: 0 0 20px rgba(59,130,246,0.2);
        }
        a.back-btn:hover { transform: translateY(-3px); }

        /* 📂 Accordéons */
        .accordion {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.03);
            color: #e2e8f0;
            padding: 12px 14px;
            border-radius: 10px;
            cursor: pointer;
            text-align: left;
            margin-top: 10px;
            transition: all 0.2s ease;
            font-size: 15px;
            display:flex;
            align-items:center;
            justify-content:space-between;
        }
        .accordion:hover { background: rgba(59,130,246,0.08); box-shadow: 0 0 10px rgba(59,130,246,0.12); }
        .accordion .acc-title { font-weight:600; color:#e6eefc; }
        .panel {
            display: none;
            background: rgba(255,255,255,0.02);
            border-radius: 10px;
            padding: 14px 16px;
            margin-top: 10px;
            box-shadow: inset 0 0 8px rgba(59,130,246,0.06);
        }

        .server-name { font-weight:600; margin-bottom:10px; color:#cfe9ff; }

        /* 🧮 Groupes de cases */
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 12px;
        }

        /* ======= iOS-like sliding switch ======= */
        .switch-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.02);
            padding: 8px 10px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.02);
        }

        .switch {
            --w: 48px;    /* width */
            --h: 28px;    /* height */
            --knob: 22px; /* knob size */

            display: inline-block;
            width: var(--w);
            height: var(--h);
            position: relative;
            border-radius: calc(var(--h) / 2);
            background: linear-gradient(180deg, #ef4444, #dc2626); /* OFF red */
            box-shadow: 0 4px 10px rgba(0,0,0,0.5), inset 0 -2px 6px rgba(0,0,0,0.25);
            transition: background 0.22s ease, box-shadow 0.22s ease;
        }

        /* visually hide real checkbox but keep it accessible */
        .switch input[type="checkbox"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 100%;
            margin: 0;
            position: absolute;
            inset: 0;
            cursor: pointer;
            opacity: 0;
            z-index: 2;
        }

        /* knob */
        .switch .knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: var(--knob);
            height: var(--knob);
            background: white;
            border-radius: 50%;
            box-shadow: 0 3px 8px rgba(34,34,34,0.6);
            transition: transform 0.22s cubic-bezier(.2,.9,.2,1), box-shadow 0.22s ease;
            z-index: 1;
        }

        /* ON state */
        .switch.on {
            background: linear-gradient(180deg,#22c55e,#16a34a);
            box-shadow: 0 6px 18px rgba(34,197,94,0.18), inset 0 -2px 6px rgba(0,0,0,0.25);
        }
        .switch.on .knob {
            transform: translateX(calc(var(--w) - var(--knob) - 6px));
            box-shadow: 0 4px 12px rgba(34,197,94,0.28);
        }

        /* label text */
        .switch-label {
            color: #e2e8f0;
            font-size: 14px;
            user-select: none;
            display:inline-block;
            min-width:80px;
        }

        /* small helper color when enabled/disabled */
        .state-pill {
            font-size:12px;
            padding:4px 8px;
            border-radius:999px;
            background: rgba(255,255,255,0.03);
            color:#cfe9ff;
            box-shadow: inset 0 -2px 6px rgba(0,0,0,0.2);
        }
        .state-pill.on { background: rgba(34,197,94,0.12); color:#aee6be; }
        .state-pill.off { background: rgba(239,68,68,0.08); color:#f6b8b8; }

        /* responsive */
        @media (max-width: 820px) {
            .checkbox-group { flex-direction: column; align-items: flex-start; }
            .switch-label { min-width: 0; }
        }
    </style>
</head>
<body>
    <h1>⚙️ Administration des Serveurs Minecraft</h1>
    <div class="container">

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
                <button type="submit">💾 Ajouter</button>
            </form>
        </div>

        <div class="card">
            <h2>🖥️ Modifier les serveurs existants</h2>
            <?php foreach ($servers as $srv): ?>
                <button class="accordion">
                    <span class="acc-title">⚙️ <?= htmlspecialchars($srv['name']) ?></span>
                    <span style="opacity:0.8; font-size:13px; color:#bcdfff;">Éditer</span>
                </button>
                <div class="panel">
                    <form method="post">
                        <input type="hidden" name="action" value="update_server">
                        <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                        <label>Nom :</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($srv['name']) ?>">
                        <label>Chemin :</label>
                        <input type="text" name="path" value="<?= htmlspecialchars($srv['path']) ?>">
                        <label>Type :</label>
                        <select name="type">
                            <option value="vanilla" <?= $srv['type']=="vanilla"?"selected":"" ?>>Vanilla</option>
                            <option value="spigot" <?= $srv['type']=="spigot"?"selected":"" ?>>Spigot</option>
                            <option value="paper" <?= $srv['type']=="paper"?"selected":"" ?>>Paper</option>
                            <option value="forge" <?= $srv['type']=="forge"?"selected":"" ?>>Forge</option>
                        </select>
                        <label>Port :</label>
                        <input type="number" name="port" value="<?= $srv['port'] ?>">
                        <label>Max joueurs :</label>
                        <input type="number" name="max_players" value="<?= $srv['max_players'] ?>">
                        <label>Arguments Java :</label>
                        <textarea name="java_args"><?= htmlspecialchars($srv['java_args']) ?></textarea>
                        <button type="submit">💾 Mettre à jour</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>👥 Permissions utilisateurs</h2>
            <?php foreach ($users as $user): ?>
                <button class="accordion">
                    <span class="acc-title">👤 <?= htmlspecialchars($user['username']) ?></span>
                    <span style="opacity:0.8; font-size:13px; color:#bcdfff;">Modifier</span>
                </button>
                <div class="panel">
                    <?php foreach ($servers as $srv): ?>
                        <?php $perm = get_user_permission($pdo, $user['id'], $srv['id']); ?>
                        <form method="post" style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px dashed rgba(255,255,255,0.03);">
                            <div class="server-name"><?= htmlspecialchars($srv['name']) ?></div>
                            <div class="checkbox-group">
                                <!-- Démarrer -->
                                <?php $id = "start_{$user['id']}_{$srv['id']}"; ?>
                                <div class="switch-wrap">
                                    <div class="switch <?= $perm['can_start'] ? 'on' : '' ?>" aria-hidden>
                                        <input type="checkbox" id="<?= $id ?>" name="can_start" <?= $perm['can_start'] ? 'checked' : '' ?>>
                                        <div class="knob"></div>
                                    </div>
                                    <label for="<?= $id ?>" class="switch-label">Démarrer</label>
                                    <div class="state-pill <?= $perm['can_start'] ? 'on' : 'off' ?>"><?= $perm['can_start'] ? 'Actif' : 'Désactivé' ?></div>
                                </div>

                                <!-- Arrêter -->
                                <?php $id2 = "stop_{$user['id']}_{$srv['id']}"; ?>
                                <div class="switch-wrap">
                                    <div class="switch <?= $perm['can_stop'] ? 'on' : '' ?>" aria-hidden>
                                        <input type="checkbox" id="<?= $id2 ?>" name="can_stop" <?= $perm['can_stop'] ? 'checked' : '' ?>>
                                        <div class="knob"></div>
                                    </div>
                                    <label for="<?= $id2 ?>" class="switch-label">Arrêter</label>
                                    <div class="state-pill <?= $perm['can_stop'] ? 'on' : 'off' ?>"><?= $perm['can_stop'] ? 'Actif' : 'Désactivé' ?></div>
                                </div>

                                <!-- Voir -->
                                <?php $id3 = "view_{$user['id']}_{$srv['id']}"; ?>
                                <div class="switch-wrap">
                                    <div class="switch <?= $perm['can_view'] ? 'on' : '' ?>" aria-hidden>
                                        <input type="checkbox" id="<?= $id3 ?>" name="can_view" <?= $perm['can_view'] ? 'checked' : '' ?>>
                                        <div class="knob"></div>
                                    </div>
                                    <label for="<?= $id3 ?>" class="switch-label">Voir</label>
                                    <div class="state-pill <?= $perm['can_view'] ? 'on' : 'off' ?>"><?= $perm['can_view'] ? 'Actif' : 'Désactivé' ?></div>
                                </div>

                                <!-- Console -->
                                <?php $id4 = "console_{$user['id']}_{$srv['id']}"; ?>
                                <div class="switch-wrap">
                                    <div class="switch <?= $perm['can_console'] ? 'on' : '' ?>" aria-hidden>
                                        <input type="checkbox" id="<?= $id4 ?>" name="can_console" <?= $perm['can_console'] ? 'checked' : '' ?>>
                                        <div class="knob"></div>
                                    </div>
                                    <label for="<?= $id4 ?>" class="switch-label">Console</label>
                                    <div class="state-pill <?= $perm['can_console'] ? 'on' : 'off' ?>"><?= $perm['can_console'] ? 'Actif' : 'Désactivé' ?></div>
                                </div>

                                <!-- Fichiers -->
                                <?php $id5 = "files_{$user['id']}_{$srv['id']}"; ?>
                                <div class="switch-wrap">
                                    <div class="switch <?= $perm['can_files'] ? 'on' : '' ?>" aria-hidden>
                                        <input type="checkbox" id="<?= $id5 ?>" name="can_files" <?= $perm['can_files'] ? 'checked' : '' ?>>
                                        <div class="knob"></div>
                                    </div>
                                    <label for="<?= $id5 ?>" class="switch-label">Fichiers</label>
                                    <div class="state-pill <?= $perm['can_files'] ? 'on' : 'off' ?>"><?= $perm['can_files'] ? 'Actif' : 'Désactivé' ?></div>
                                </div>
                            </div>

                            <input type="hidden" name="action" value="update_permissions">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="server_id" value="<?= $srv['id'] ?>">
                            <button type="submit" class="save-btn">💾 Sauvegarder</button>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;">
            <a href="servers.php" class="back-btn">⬅ Retour aux serveurs</a>
        </div>
    </div>

    <script>
        // Accordion toggles
        document.querySelectorAll(".accordion").forEach(btn => {
            btn.addEventListener("click", () => {
                btn.classList.toggle("active");
                let panel = btn.nextElementSibling;
                panel.style.display = (panel.style.display === "block") ? "none" : "block";
            });
        });

        // Make the visual switch follow the actual checkbox state and update classes + state-pill
        document.querySelectorAll('.switch input[type="checkbox"]').forEach(input => {
            const wrapper = input.closest('.switch');
            const statePill = wrapper.parentElement.querySelector('.state-pill');

            // init visual from checkbox
            if (input.checked) wrapper.classList.add('on');
            else wrapper.classList.remove('on');

            // when the real checkbox changes (click or keyboard), toggle classes
            input.addEventListener('change', (e) => {
                if (input.checked) wrapper.classList.add('on'); else wrapper.classList.remove('on');
                if (statePill) {
                    statePill.classList.toggle('on', input.checked);
                    statePill.classList.toggle('off', !input.checked);
                    statePill.textContent = input.checked ? 'Actif' : 'Désactivé';
                }
            });

            // also allow clicking the visual area to toggle (works because input overlay is full-size but just in case)
            wrapper.addEventListener('click', (ev) => {
                // ignore if the click is directly on the input (it will fire change itself)
                if (ev.target.tagName.toLowerCase() === 'input') return;
                input.checked = !input.checked;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    </script>
</body>
</html>
