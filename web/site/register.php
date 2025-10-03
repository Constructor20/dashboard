<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Vérifier que l'utilisateur ou l'email n'existe pas déjà
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = "Nom d'utilisateur ou email déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            header("Location: index.php?registered=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Minecraft Panel</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            background: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #2a5298;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        .input-field {
            width: 100%;
            height: 45px;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border 0.3s, box-shadow 0.3s;
        }

        .input-field:focus {
            border-color: #2a5298;
            box-shadow: 0 0 5px rgba(42, 82, 152, 0.4);
            outline: none;
        }

        .primary-button {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .primary-button:hover {
            background: #1e3c72;
        }

        .secondary-button {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: #2a5298;
            border: 1px solid #2a5298;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
            margin-top: 8px;
        }

        .secondary-button:hover {
            background: #2a5298;
            color: white;
        }

        .error {
            background: #ffd2d2;
            color: #a70000;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <form method="POST">
            <h2>Créer un compte</h2>
            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" class="input-field" required>


            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="input-field" required>

            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" class="input-field" required>

            <label for="confirm">Confirmer le mot de passe</label>
            <input type="password" name="confirm" id="confirm" class="input-field" required>

            <button type="submit" class="primary-button">Créer mon compte</button>
        </form>

        <form action="index.php" method="GET">
            <button type="submit" class="secondary-button">Déjà inscrit ? Connexion</button>
        </form>
    </div>
</body>
</html>
