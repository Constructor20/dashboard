<?php
require 'includes/db.php';
session_start();

$fatalError = null;
$error = null;
$success = null;

$token = $_GET['token'] ?? null;

if (!$token) {
    $fatalError = "Lien de réinitialisation invalide.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $fatalError = "Lien invalide ou expiré.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$reset['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            $fatalError = "Utilisateur introuvable.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirm'] ?? '';

                if (strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères.";
                } elseif ($password !== $confirm) {
                    $error = "Les mots de passe ne correspondent pas.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update->execute([$hashed, $user['id']]);

                    $delete = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                    $delete->execute([$token]);

                    $success = "Mot de passe mis à jour avec succès.<br><a href='index.php'>Se connecter</a>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe - Minecraft Panel</title>
    <style>
        /* 🌌 Thème global */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b);
            color: #f8fafc;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        h1 {
            color: #38bdf8;
            text-align: center;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
        }

        .container {
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            border-radius: 16px;
            border: 1px solid #334155;
            box-shadow: 0 0 25px rgba(15, 23, 42, 0.8);
            padding: 30px 40px;
            max-width: 480px;
            width: 90%;
            transition: all 0.3s ease;
        }

        .container:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 35px rgba(59,130,246,0.4);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #cbd5e1;
        }

        input[type="password"] {
            width: 95%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #334155;
            background: rgba(30, 41, 59, 0.9);
            color: #f1f5f9;
            font-size: 15px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        input[type="password"]:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56,189,248,0.5);
            outline: none;
        }

        button {
            width: 100%;
            background: linear-gradient(90deg, #3b82f6, #0ea5e9);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            font-size: 16px;
            margin-top: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(90deg, #38bdf8, #2563eb);
            box-shadow: 0 0 20px rgba(56,189,248,0.5);
            transform: translateY(-2px);
        }

        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background: rgba(16,185,129,0.15);
            border: 1px solid #10b981;
            color: #a7f3d0;
        }

        .message.error {
            background: rgba(239,68,68,0.15);
            border: 1px solid #ef4444;
            color: #fecaca;
        }

        .message.fatal {
            background: rgba(239,68,68,0.2);
            border: 2px solid #ef4444;
            color: #fca5a5;
            text-align: center;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            color: #60a5fa;
            text-shadow: 0 0 8px rgba(96,165,250,0.6);
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #0f172a;
            color: #94a3b8;
            text-align: center;
            padding: 10px 0;
            font-size: 0.9rem;
            border-top: 1px solid #1e293b;
        }

        footer span {
            color: #38bdf8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Réinitialiser le mot de passe</h1>

        <?php if ($fatalError): ?>
            <div class="message fatal"><?= htmlspecialchars($fatalError) ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php else: ?>
            <form method="POST">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>

                <label for="confirm">Confirmer le mot de passe</label>
                <input type="password" id="confirm" name="confirm" placeholder="••••••••" required>

                <button type="submit">Réinitialiser</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.php">← Retour à la connexion</a>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y'); ?> <span>Minecraft Panel</span> - Tous droits réservés
    </footer>
</body>
</html>
