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
        }
    </style>
</head>  
<body>
    <?php include 'navbar.php'; ?>

<div class="container">
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['username']); ?> !</h1>
    <p>Ton adresse e-mail : <strong><?= htmlspecialchars($_SESSION['email']); ?></strong></p>

    <!-- Section de gestion du compte -->
    <div style="margin: 20px 0; padding: 15px; background: rgba(255,255,255,0.15); border-radius: 8px;">
        <h2>Mon compte</h2>
        <ul>
            <li><a href="edit_profile.php" style="color: #ffd700; font-weight: bold;">Modifier mes informations</a></li>
            <!-- tu peux rajouter d'autres liens ici -->
        </ul>
    </div>

    <hr style="margin: 30px 0;">

    <h2>Mes projets Minecraft</h2>
    <p>Ici tu peux afficher des infos, stats ou liens vers des actions.</p>
    <ul>
        <li>Projet 1 : Serveur Minecraft survie</li>
        <li>Projet 2 : Mod personnalisé SpotiMod</li>
        <li>Projet 3 : Panel d’administration</li>
    </ul>
</div>


    <?php include 'footer.php'; ?>
</body>
</html>
