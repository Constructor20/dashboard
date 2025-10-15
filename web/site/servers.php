<?php
include 'auth.php';
include 'includes/db.php'; // PDO $pdo

$user_id = $_SESSION['user_id'];

// --- Récupérer les serveurs accessibles à l'utilisateur ---
if ($user_id == 1) {
    $stmt = $pdo->query("SELECT * FROM servers");
} else {
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
    <meta charset="UTF-8">
    <title>Gestion Serveurs Minecraft</title>
    <?php include 'navbar.php'; ?>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b);
            color: #f8fafc;
            margin: 0;
            min-height: 100vh;
        }

        h1 {
            margin-top: 80px;
            font-size: 32px;
            color: #38bdf8;
            text-align: center;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
        }

        .server {
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            margin: 20px auto;
            padding: 20px 25px;
            border-radius: 16px;
            max-width: 600px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 18px;
            transition: all 0.25s ease;
            border: 1px solid #334155;
            box-shadow: 0 0 20px rgba(15,23,42,0.6);
            cursor: pointer;
        }

        .server:hover {
            transform: translateY(-3px);
            background: radial-gradient(circle at bottom right, #1e3a8a, #1e293b);
            border-color: #3b82f6;
            box-shadow: 0 0 25px rgba(59,130,246,0.4);
        }

        .status {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .status.green {
            background: linear-gradient(90deg, #22c55e, #16a34a);
            box-shadow: 0 0 10px rgba(34,197,94,0.6);
        }
        .status.red {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            box-shadow: 0 0 10px rgba(239,68,68,0.6);
        }

        .players {
            font-weight: bold;
            color: #e2e8f0;
            font-size: 14px;
            min-width: 70px;
        }

        .server a {
            color: #93c5fd;
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            letter-spacing: 0.3px;
            transition: color 0.2s, text-shadow 0.2s;
        }
        .server a:hover {
            color: #60a5fa;
            text-shadow: 0 0 8px rgba(96,165,250,0.6);
        }

        .no-server {
            text-align: center;
            margin-top: 100px;
            font-size: 18px;
            color: #94a3b8;
            background: rgba(148,163,184,0.05);
            display: inline-block;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid rgba(148,163,184,0.2);
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 600px) {
            .server {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 18px;
            }
            .players {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <h1>Gestion des Serveurs Minecraft</h1>

    <?php if (empty($servers)): ?>
        <div class="no-server">❌ Aucun serveur accessible pour votre compte.</div>
    <?php else: ?>
        <?php foreach ($servers as $srv): ?>
            <div class="server" id="server-<?= $srv['id'] ?>" onclick="window.location.href='console.php?server=<?= $srv['id'] ?>'">
                <span class="status red"></span>
                <span class="players">--/--</span>
                <a href="console.php?server=<?= $srv['id'] ?>"><?= htmlspecialchars($srv["name"]); ?></a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        async function updateServerStatus(serverId) {
            try {
                const response = await fetch(`includes/api_console.php?server=${serverId}`);
                const data = await response.json();

                const serverDiv = document.getElementById(`server-${serverId}`);
                if (!serverDiv) return;

                const statusDot = serverDiv.querySelector(".status");
                const playersSpan = serverDiv.querySelector(".players");

                if (data.status === "ok") {
                    // Pastille verte/rouge selon running
                    if (data.running) {
                        statusDot.classList.remove("red");
                        statusDot.classList.add("green");
                    } else {
                        statusDot.classList.remove("green");
                        statusDot.classList.add("red");
                    }

                    // Joueurs connectés
                    playersSpan.textContent = `${data.current_players}/${data.max_players}`;
                } else {
                    // Si erreur API
                    statusDot.classList.remove("green");
                    statusDot.classList.add("red");
                    playersSpan.textContent = "--/--";
                }
            } catch (e) {
                console.warn(`Erreur de récupération du statut du serveur ${serverId}:`, e);
            }
        }

        // Actualiser tous les serveurs
        function refreshAllServers() {
            const serverDivs = document.querySelectorAll(".server[id^='server-']");
            serverDivs.forEach(div => {
                const id = div.id.replace("server-", "");
                updateServerStatus(id);
            });
        }

        // Lancer au chargement + toutes les 10s
        refreshAllServers();
        setInterval(refreshAllServers, 10000);

    </script>
</body>
</html>
