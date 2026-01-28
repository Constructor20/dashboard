<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Minecraft Panel</title>

    <style>
        /* 🌌 Arrière-plan général */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b);
            color: #e2e8f0;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* 🌟 Conteneur principal */
        .container {
            background: radial-gradient(circle at top left, rgba(30,41,59,0.95), rgba(15,23,42,0.95));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(148,163,184,0.2);
            padding: 40px 50px;
            border-radius: 20px;
            max-width: 700px;
            margin: 120px auto 60px;
            box-shadow: 0 0 35px rgba(59,130,246,0.25);
            text-align: center;
            transition: all 0.3s ease;
        }

        .container:hover {
            box-shadow: 0 0 45px rgba(96,165,250,0.35);
            transform: translateY(-3px);
        }

        /* ✨ Titres */
        h1 {
            color: #60a5fa;
            font-size: 2rem;
            text-shadow: 0 0 10px rgba(96,165,250,0.5);
        }

        h2 {
            color: #38bdf8;
            text-shadow: 0 0 6px rgba(56,189,248,0.4);
            border-left: 3px solid #3b82f6;
            padding-left: 12px;
            margin-bottom: 15px;
        }

        /* 🧍 Section Mon Compte */
        .account-section {
            background: rgba(148,163,184,0.05);
            border: 1px solid rgba(148,163,184,0.15);
            border-radius: 12px;
            padding: 20px;
            text-align: left;
            margin-top: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        .account-section ul {
            list-style: none;
            padding-left: 10px;
        }

        .account-section li {
            margin: 10px 0;
        }

        .account-section a {
            color: #93c5fd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .account-section a:hover {
            color: #60a5fa;
            text-shadow: 0 0 8px rgba(96,165,250,0.6);
        }

        /* ⚡ Lien serveur */
        .server-links a {
            display: inline-block;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: #fff;
            font-weight: bold;
            border-radius: 12px;
            padding: 14px 26px;
            margin-top: 20px;
            text-decoration: none;
            letter-spacing: 0.4px;
            box-shadow: 0 0 20px rgba(37,99,235,0.4);
            transition: all 0.3s ease;
        }

        .server-links a:hover {
            background: linear-gradient(90deg, #1d4ed8, #2563eb);
            box-shadow: 0 0 30px rgba(96,165,250,0.6);
            transform: translateY(-2px);
        }

        /* 🧭 Barre de séparation */
        hr {
            border: none;
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 35px 0;
        }

        /* 💫 Animation d’apparition douce */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.8s ease forwards;
        }

        /* 📱 Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 25px;
                margin: 80px 15px;
            }
            h1 { font-size: 1.6rem; }
            h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>👋 Bienvenue, <?= htmlspecialchars($_SESSION['username']); ?> !</h1>
        <p>Ton adresse e-mail : <strong style="color:#93c5fd;"><?= htmlspecialchars($_SESSION['email']); ?></strong></p>

        <!-- Section de gestion du compte -->
        <div class="account-section">
            <h2>⚙️ Mon compte</h2>
            <ul>
                <li><a href="edit_profile.php">Modifier mes informations</a></li>
                <li><a href="change_password.php">Changer mon mot de passe</a></li>
            </ul>
        </div>

        <hr>

        <!-- Nouvelle section Accès Serveurs -->
        <h2>🖥️ Accédez à vos serveurs</h2>
        <div class="server-links">
            <a href="servers.php">Gérer mes serveurs</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
