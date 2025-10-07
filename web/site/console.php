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
    if (!$server || !$server['can_view']) die("❌ Accès refusé à ce serveur.");
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
body { font-family: "Segoe UI", sans-serif; background:#1e293b; color:white; margin:0; padding:0; }
h1 { margin:20px; font-size:28px; color:#facc15; }
.status { margin:15px; font-weight:bold; }
.status span { padding:5px 10px; border-radius:8px; }
.green { background:#22c55e; } .red { background:#ef4444; }
.players { margin:10px; }
.actions { margin:20px; }
button { background:#2563eb; color:white; border:none; padding:10px 15px; border-radius:8px; margin:5px; cursor:pointer; }
button:hover { background:#1d4ed8; }
.console-box { background:black; padding:10px; margin:20px; border-radius:10px; height:300px; overflow-y:scroll; font-family:monospace; white-space:pre; }
.command-input { display:flex; gap:10px; margin:20px; }
.command-input input { flex:1; padding:8px; border-radius:6px; border:none; }
.command-input button { background:#16a34a; }
#action-status { margin:15px; font-weight:bold; }
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
            if(box) {
                const safeLogs = data.logs.map(l => l.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'));
                box.innerHTML = safeLogs.join("\n");
                box.scrollTop = box.scrollHeight;
            }
        });
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
