<?php
require 'includes/db.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } elseif (strlen($newPassword) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashedPassword, $_SESSION['user_id']]);
                $success = "Mot de passe changé avec succès ✅";
            } else {
                $error = "L'ancien mot de passe est incorrect.";
            }
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe - Minecraft Panel</title>
    <style>
        /* 🌌 Thème global */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b);
            color: #f8fafc;
            margin: 0;
            min-height: 100vh;
            padding-bottom: 60px;
        }

        h1 {
            text-align: center;
            font-size: 28px;
            color: #38bdf8;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
            margin-top: 80px;
        }

        /* 🧱 Carte principale */
        .container {
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            border-radius: 16px;
            border: 1px solid #334155;
            box-shadow: 0 0 25px rgba(15, 23, 42, 0.8);
            padding: 30px;
            max-width: 500px;
            margin: 40px auto;
            transition: all 0.3s ease;
        }

        .container:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 35px rgba(59,130,246,0.4);
        }

        /* Champs */
        label {
            display: block;
            margin: 10px 0 6px;
            color: #cbd5e1;
            font-weight: 600;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #334155;
            background: rgba(30, 41, 59, 0.9);
            color: #f1f5f9;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input[type="password"]:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56,189,248,0.5);
            outline: none;
        }

        /* Bouton principal */
        button {
            width: 100%;
            background: linear-gradient(90deg, #3b82f6, #0ea5e9);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 0;
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(90deg, #38bdf8, #2563eb);
            box-shadow: 0 0 20px rgba(56,189,248,0.5);
            transform: translateY(-2px);
        }

        /* Messages */
        .msg {
            margin: 15px 0;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            animation: fadeIn 0.4s ease;
        }

        .success {
            background: rgba(16,185,129,0.15);
            border: 1px solid #10b981;
            color: #a7f3d0;
        }

        .error {
            background: rgba(239,68,68,0.15);
            border: 1px solid #ef4444;
            color: #fecaca;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Lien retour */
        .back-link {
            display: block;
            text-align: center;
            color: #38bdf8;
            margin-top: 20px;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: #60a5fa;
            text-shadow: 0 0 10px rgba(96,165,250,0.4);
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1>Changer mon mot de passe</h1>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="current_password">Ancien mot de passe :</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">Nouveau mot de passe :</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">🔒 Changer le mot de passe</button>
        </form>

        <a href="profile.php" class="back-link">⬅ Retour au profil</a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
