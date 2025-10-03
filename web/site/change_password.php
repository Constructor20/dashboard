<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php'; // connexion PDO
include 'auth.php';        // vérifie la session

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
            // Vérifier l'ancien mot de passe
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($currentPassword, $user['password'])) {
                // Hachage sécurisé du nouveau mot de passe
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
    <title>Changer le mot de passe</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            margin: 80px auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
        }
        button {
            background: #ffd700;
            color: black;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #ffcc00;
        }
        .msg {
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
        }
        .success { background: rgba(0,128,0,0.7); }
        .error { background: rgba(128,0,0,0.7); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>Changer mon mot de passe</h1>

        <?php if (isset($success)): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="current_password">Ancien mot de passe :</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">Nouveau mot de passe :</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Changer le mot de passe</button>
        </form>

        <p style="margin-top:20px;">
            <a href="profile.php" style="color:#ffd700;">⬅ Retour au profil</a>
        </p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
