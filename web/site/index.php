<?php
session_start();
require 'includes/db.php';

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header("Location: profile.php");
        exit;
    } else {
        $error = "Nom d'utilisateur, email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Minecraft Panel</title>
    <style>
        /* 🌌 Thème global */
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b, #1e3a8a);
            color: #f8fafc;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* 🪐 Conteneur principal */
        .login-container {
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59,130,246,0.2);
            padding: 40px 35px;
            border-radius: 16px;
            box-shadow: 0 0 25px rgba(37,99,235,0.3);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        h2 {
            color: #38bdf8;
            text-shadow: 0 0 8px rgba(56,189,248,0.6);
            font-size: 28px;
            margin-bottom: 30px;
        }

        /* 🔤 Champs */
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #e2e8f0;
            text-align: left;
        }

        .input-field {
            width: 100%;
            padding: 12px 0px;
            margin-bottom: 18px;
            background: rgba(15,23,42,0.6);
            border: 1px solid rgba(100,116,139,0.3);
            color: #f1f5f9;
            border-radius: 10px;
            font-size: 15px;
            transition: 0.3s;
        }

        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59,130,246,0.5);
            outline: none;
            background: rgba(15,23,42,0.8);
        }

        /* 🔵 Bouton principal */
        .primary-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(59,130,246,0.4);
            transition: all 0.25s ease;
            margin-top: 10px;
        }

        .primary-button:hover {
            background: linear-gradient(90deg, #1e40af, #2563eb);
            box-shadow: 0 0 20px rgba(59,130,246,0.6);
        }

        /* 🟦 Boutons secondaires */
        .secondary-button {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: #60a5fa;
            border: 1px solid rgba(96,165,250,0.5);
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.25s ease;
        }

        .secondary-button:hover {
            background: rgba(96,165,250,0.1);
            box-shadow: 0 0 10px rgba(96,165,250,0.3);
        }

        /* ⚠️ Message d'erreur */
        .error {
            background: rgba(239,68,68,0.15);
            color: #fca5a5;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 10px;
            border: 1px solid rgba(239,68,68,0.3);
            text-shadow: 0 0 4px rgba(239,68,68,0.4);
        }

        /* 📱 Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 0 15px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form method="POST">
            <h2>⚡ Minecraft Panel</h2>
            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="input-field" required>

            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" id="email" class="input-field" required>

            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" class="input-field" required>

            <button type="submit" class="primary-button">Se connecter</button>
        </form>

        <form action="forgot.php" method="GET">
            <button type="submit" class="secondary-button">Mot de passe oublié ?</button>
        </form>
        <form action="register.php" method="GET">
            <button type="submit" class="secondary-button">Créer un compte</button>
        </form>
    </div>
</body>
</html>
