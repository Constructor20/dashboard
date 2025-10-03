<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php'; // ta connexion PDO
include 'auth.php';        // vérifie la session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newEmail = trim($_POST['email']);

    if (!empty($newUsername) && !empty($newEmail)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$newUsername, $newEmail, $_SESSION['user_id']]);

            // Mets à jour la session
            $_SESSION['username'] = $newUsername;
            $_SESSION['email'] = $newEmail;

            $success = "Profil mis à jour avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier mon profil</title>
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
        input[type="text"], input[type="email"] {
            width: 84%;
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
        <h1>Modifier mon profil</h1>

        <?php if (isset($success)): ?>
            <div class="msg success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" 
                   value="<?= htmlspecialchars($_SESSION['username']); ?>" required>

            <label for="email">Adresse e-mail :</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($_SESSION['email']); ?>" required>

            <button type="submit">Enregistrer les modifications</button>
        </form>
            <a href="change_password.php" style="color:#ffd700;">Changer de mot de passe</a>

        <p style="margin-top:20px;">
            <a href="profile.php" style="color:#ffd700;">⬅ Retour au tableau de bord</a>
        </p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
