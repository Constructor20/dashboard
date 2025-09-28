<?php
session_start();
require 'includes/db.php';

// Si déjà connecté, on redirige directement
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // On vérifie que username + email correspondent à un même utilisateur
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

        .login-container {
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

        .login-container label {
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

        .login-container .primary-button {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .login-container .primary-button:hover {
            background: #1e3c72;
        }

        .login-container .secondary-button {
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

        .login-container .secondary-button:hover {
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
  <div class="login-container">
    <form method="POST">
        <h2>Minecraft Panel</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

        <label for="username">Nom d'utilisateur</label>
        <input type="text" name="username" id="username" class="input-field" required>

        <label for="email">Adresse e-mail</label>
        <input type="email" name="email" id="email" class="input-field" required>

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" class="input-field" required>

        <button type="submit" class="primary-button">Se connecter</button>
    </form>



    <!-- Liens supplémentaires (autres actions) -->
    <form action="forgot.php" method="GET">
        <button type="submit" class="secondary-button">Mot de passe oublié ?</button>
    </form>
    <form action="register.php" method="GET">
        <button type="submit" class="secondary-button">S'inscrire</button>
    </form>
  </div>
</body>
</html>


