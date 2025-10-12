<?php
include 'auth.php';
include 'includes/db.php'; // PDO $pdo

if (!isset($_GET['server'])) die("❌ Serveur non spécifié.");
$server_id = intval($_GET['server']);
$user_id = $_SESSION['user_id'];

// --- Vérification droits ---
if ($user_id == 1) {
    $stmt = $pdo->prepare("SELECT * FROM servers WHERE id = ?");
    $stmt->execute([$server_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);
    $permissions = ["can_view"=>1,"can_start"=>1,"can_stop"=>1,"can_console"=>1];
} else {
    $stmt = $pdo->prepare("
        SELECT s.*, p.can_view, p.can_start, p.can_stop, p.can_console
        FROM servers s
        INNER JOIN permissions p ON p.server_id = s.id
        WHERE s.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$server_id, $user_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$server || !$server['can_view']) {
    echo '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Accès refusé</title>
        <style>
            body {
                background: radial-gradient(circle at top left, #0f172a, #1e293b);
                font-family: "Segoe UI", sans-serif;
                color: #f8fafc;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .error-box {
                background: rgba(15, 23, 42, 0.9);
                border: 2px solid #ef4444;
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
                border-radius: 12px;
                padding: 40px 60px;
                text-align: center;
                animation: fadeIn 0.6s ease-out;
            }
            h1 {
                font-size: 2em;
                color: #ef4444;
                text-shadow: 0 0 10px rgba(239,68,68,0.6);
                margin-bottom: 10px;
            }
            p {
                color: #cbd5e1;
                font-size: 1rem;
                margin-bottom: 25px;
            }
            a {
                display: inline-block;
                background: linear-gradient(90deg, #2563eb, #1d4ed8);
                color: white;
                padding: 10px 25px;
                border-radius: 9999px;
                text-decoration: none;
                font-weight: 600;
                box-shadow: 0 0 10px rgba(37,99,235,0.4);
                transition: 0.3s ease-in-out;
            }
            a:hover {
                transform: translateY(-2px);
                background: linear-gradient(90deg, #1e40af, #1d4ed8);
                box-shadow: 0 0 20px rgba(59,130,246,0.5);
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: scale(0.95); }
                to { opacity: 1; transform: scale(1); }
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>🚫 Accès refusé</h1>
            <p>Tu n\'as pas la permission d\'accéder à ce serveur Minecraft.</p>
            <a href="servers.php">⬅ Retour aux serveurs</a>
        </div>
    </body>
    </html>';
    exit;
}

    $permissions = [
        "can_view"=>$server["can_view"],
        "can_start"=>$server["can_start"],
        "can_stop"=>$server["can_stop"],
        "can_console"=>$server["can_console"]
    ];
}

if (!$server) die("❌ Serveur introuvable.");

$online = $server['online']==1;
$players = $online ? (isset($server['current_players'])?"{$server['current_players']}/{$server['max_players']}":"0/{$server['max_players']}") : "0/{$server['max_players']}";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Console - <?= htmlspecialchars($server['name']) ?></title>
<?php include 'navbar.php'; ?>
<style>
    body {
    font-family: "Segoe UI", sans-serif;
    background: radial-gradient(circle at top left, #0f172a, #1e293b);
    color: #f8fafc;
    margin: 0;
    padding: 0;
    }
    h1 {
    margin: 20px;
    font-size: 28px;
    color: #38bdf8;
    text-shadow: 0 0 8px rgba(56, 189, 248, 0.4);
    }
    .status {
    margin: 15px;
    font-weight: bold;
    font-size: 16px;
    }
    .status span {
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: bold;
    box-shadow: 0 0 6px rgba(0,0,0,0.3);
    }
    .green {
    background: linear-gradient(90deg, #22c55e, #16a34a);
    box-shadow: 0 0 12px rgba(34,197,94,0.5);
    }
    .red {
    background: linear-gradient(90deg, #ef4444, #dc2626);
    box-shadow: 0 0 12px rgba(239,68,68,0.5);
    }
    .players {
    margin: 10px 20px;
    font-size: 15px;
    color: #cbd5e1;
    }
    .actions {
    margin: 20px;
    }
    button {
    background: linear-gradient(90deg, #2563eb, #1d4ed8);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 9999px;
    margin: 5px;
    cursor: pointer;
    transition: 0.2s ease-in-out;
    font-weight: 600;
    box-shadow: 0 0 10px rgba(37,99,235,0.3);
    }
    button:hover {
    transform: translateY(-1px);
    background: linear-gradient(90deg, #1e40af, #1d4ed8);
    box-shadow: 0 0 15px rgba(59,130,246,0.5);
    }
    .console-box {
    background: #0f172a;
    background: radial-gradient(circle at top left, #0f172a 0%, #1e293b 100%);
    padding: 15px;
    margin: 20px;
    border-radius: 12px;
    height: 350px;
    overflow-y: auto;
    font-family: "Consolas", "Courier New", monospace;
    white-space: pre-wrap;
    border: 2px solid #334155;
    color: #d1d5db;
    box-shadow: inset 0 0 15px rgba(0,0,0,0.4);
    transition: 0.3s;
    }
    .console-box::-webkit-scrollbar {
    width: 8px;
    }
    .console-box::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
    }
    .console-box::-webkit-scrollbar-thumb:hover {
    background: #64748b;
    }
    .console-box:focus-within {
    border-color: #22d3ee;
    box-shadow: 0 0 10px rgba(34,211,238,0.3);
    }
    .command-input {
    display: flex;
    gap: 10px;
    margin: 20px;
    }
    .command-input input {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: 2px solid #334155;
    background: #1e293b;
    color: #f8fafc;
    outline: none;
    font-family: "Consolas", monospace;
    transition: border 0.2s, box-shadow 0.2s;
    }
    .command-input input:focus {
    border-color: #22c55e;
    box-shadow: 0 0 10px rgba(34,197,94,0.4);
    }
    .command-input button {
    background: linear-gradient(90deg, #16a34a, #22c55e);
    box-shadow: 0 0 10px rgba(34,197,94,0.4);
    }
    .command-input button:hover {
    background: linear-gradient(90deg, #15803d, #22c55e);
    }
    #action-status {
    margin: 15px 20px;
    font-weight: bold;
    font-size: 14px;
    color: #f87171;
    background: rgba(239,68,68,0.1);
    border-left: 4px solid #ef4444;
    padding: 8px 12px;
    border-radius: 6px;
    display: inline-block;
    }
</style>

</head>
<body>
<h1>Console - <?= htmlspecialchars($server['name']) ?></h1>
<div class="status">Statut: <span class="<?= $online?'green':'red' ?>"><?= $online?"En ligne":"Hors ligne" ?></span></div>
<div class="players">Joueurs connectés: <?= $players ?></div>
<div class="actions">
<?php if($permissions["can_start"]): ?><button onclick="sendAction('start')">Démarrer</button><?php endif; ?>
<?php if($permissions["can_stop"]): ?><button onclick="sendAction('stop')">Arrêter</button><?php endif; ?>
</div>
<div id="action-status"></div>

<?php if($permissions["can_console"]): ?>
<div class="console-box" id="console"></div>
<div class="command-input">
<input type="text" id="command" placeholder="Commande..." />
<button onclick="sendCommand()">Envoyer</button>
</div>
<?php endif; ?>

<script>
const serverId = <?= $server_id ?>;

// --- Récupération console ligne par ligne ---
function fetchConsole() {
    fetch(`includes/api_console.php?server=${serverId}`)
        .then(res => res.json())
        .then(data => {
            const box = document.getElementById("console");
            const statusSpan = document.querySelector(".status span");
            const playersDiv = document.querySelector(".players");

            // --- Afficher les logs ---
            if (box && data.logs) {
                const safeLogs = data.logs.map(l =>
                    l.replace(/&/g,'&amp;')
                     .replace(/</g,'&lt;')
                     .replace(/>/g,'&gt;')
                );
                box.innerHTML = safeLogs.join("\n");
                box.scrollTop = box.scrollHeight;
            }

            // --- Mettre à jour le statut ---
            if (statusSpan) {
                statusSpan.className = data.online ? "green" : "red";
                statusSpan.textContent = data.online ? "En ligne" : "Hors ligne";
            }

            // --- Mettre à jour le nombre de joueurs ---
            if (playersDiv && data.players) {
                playersDiv.textContent = "Joueurs connectés: " + data.players;
            }
        })
        .catch(err => console.error("Erreur console:", err));
}


// --- Envoyer commande ---
function sendCommand() {
    const cmd = document.getElementById("command").value.trim();
    if(!cmd) return;
    fetch("includes/api_command.php", {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({server_id:serverId, command:cmd})
    }).then(res=>res.json()).then(data=>{
        console.log(data);
        document.getElementById("command").value = "";
    });
}

// --- Actions start/stop ---
function sendAction(action){
    fetch("includes/api_action.php", {
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({server_id:serverId, action:action})
    })
    .then(res => res.text()) // <- texte brut
    .then(text => {
        const statusDiv = document.getElementById("action-status");
        statusDiv.style.whiteSpace = "pre-wrap"; // garder les retours à la ligne
        statusDiv.style.color = "#ef4444"; // rouge pour les erreurs
        statusDiv.textContent = text;
    })
    .catch(err => {
        const statusDiv = document.getElementById("action-status");
        statusDiv.style.color = "#ef4444";
        statusDiv.textContent = "❌ Erreur fetch : " + err;
    });
}


// --- Rafraîchissement toutes les 1s ---
setInterval(fetchConsole,1000);
</script>
</body>
</html>
