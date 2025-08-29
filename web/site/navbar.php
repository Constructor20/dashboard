<?php
// navbar.php
session_start(); // Assure-toi que la session est démarrée ou démarre-la ici
?>

<nav style="background:#2a5298; padding: 15px; color: white; display:flex; justify-content:space-between; align-items:center; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="logo" style="font-weight:bold; font-size:1.4rem;">Minecraft Panel</div>
    <ul style="list-style:none; margin:0; padding:0; display:flex; gap:20px;">
        <li><a href="dashboard.php" style="color:white; text-decoration:none;">Dashboard</a></li>
        <li><a href="profile.php" style="color:white; text-decoration:none;">Profil</a></li>
        <li><a href="projects.php" style="color:white; text-decoration:none;">Projets</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php" style="color:white; text-decoration:none;">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="login.php" style="color:white; text-decoration:none;">Connexion</a></li>
        <?php endif; ?>
    </ul>
</nav>
