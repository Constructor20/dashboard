<?php
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }
?>
<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Minecraft Panel</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding-bottom: 60px; /* pour ne pas cacher le footer */
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            margin: 80px auto 40px; /* assez d’espace pour navbar */
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center; /* Centrage du contenu */
        }
        .server-links a {
            display: inline-block;
            margin: 10px;
            padding: 12px 20px;
            background: #ffd700;
            color: black;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .server-links a:hover {
            background: #ffcc00;
        }
    </style>
</head>  
    <body>
        <?php include 'navbar.php'; ?>

        <div class="container">
            <h1>Bienvenue, <?= htmlspecialchars($_SESSION['username']); ?> !</h1>
            <p>Ton adresse e-mail : <strong><?= htmlspecialchars($_SESSION['email']); ?></strong></p>

            <!-- Section de gestion du compte -->
            <div style="margin: 20px 0; padding: 15px; background: rgba(255,255,255,0.15); border-radius: 8px; text-align:left;">
                <h2>Mon compte</h2>
                <ul>
                    <li><a href="edit_profile.php" style="color: #ffd700; font-weight: bold;">Modifier mes informations</a></li>
                    <li><a href="change_password.php" style="color: #ffd700; font-weight: bold;">Changer mon mot de passe</a></li>
                </ul>
            </div>

            <hr style="margin: 30px 0;">

            <!-- Nouvelle section Accès Serveurs -->
            <h2>Accédez à vos serveurs</h2>
            <div class="server-links">
                <a href="servers.php">Gérer mes serveurs</a>
            </div>
        </div>

    <?php include 'footer.php'; ?>
    </body>
</html>
