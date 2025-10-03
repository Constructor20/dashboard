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
    // 1. Vérifier si le token existe dans password_resets
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $fatalError = "Lien invalide ou expiré.";
    } else {
        // 2. Récupérer l'utilisateur correspondant
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$reset['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            $fatalError = "Utilisateur introuvable.";
        } else {
            // 3. Si formulaire soumis
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirm'] ?? '';

                if (strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères.";
                } elseif ($password !== $confirm) {
                    $error = "Les mots de passe ne correspondent pas.";
                } else {
                    // Hasher et mettre à jour
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update->execute([$hashed, $user['id']]);

                    // Supprimer le token après usage
                    $delete = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                    $delete->execute([$token]);

                    $success = "Mot de passe mis à jour. <a href='index.php'>Connecte-toi ici</a>.";
                }
            }
        }
    }
}
?>


<!-- Même HTML qu’avant (formulaire et style) -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html {
            font-size: 110%; /* Augmente tout de 10% */
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 440px; /* 400px + 10% */
            width: 100%;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
            color: #2a5298;
            font-size: 1.6rem;
        }

        .input,
        .btn {
            width: 100%;
            padding: 13.2px; /* 12px + 10% */
            border-radius: 6.6px;
            font-size: 1.1rem;
            display: block;
        }

        .input {
            margin: 11px 0;
            border: 1px solid #ccc;
        }

        .btn {
            background-color: #2a5298;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 11px;
        }

        .btn:hover {
            background-color: #1e3c72;
        }

        .error,
        .success {
            padding: 11px;
            margin: 11px 0;
            border-radius: 6.6px;
            text-align: center;
            font-size: 1rem;
        }

        .error {
            background-color: #ffd2d2;
            color: #a70000;
        }

        .success {
            background-color: #d2ffd2;
            color: #007a00;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Réinitialiser le mot de passe</h2>

        <?php if (!empty($fatalError)): ?>
            <div class="error"><?= $fatalError ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php else: ?>
            <form method="POST">
                <input type="password" name="password" class="input" placeholder="Nouveau mot de passe" required>
                <input type="password" name="confirm" class="input" placeholder="Confirme le mot de passe" required>
                <button type="submit" class="btn">Réinitialiser</button>
            </form>
        <?php endif; ?>

    </div>
</body>
</html>

