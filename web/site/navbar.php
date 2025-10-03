<nav style="background:#2a5298; padding: 15px; color: white; display:flex; justify-content:space-between; align-items:center; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="logo" style="font-weight:bold; font-size:1.4rem;">Minecraft Panel</div>
    <ul style="list-style:none; margin:0; padding:0; display:flex; gap:20px;">
        <li><a href="http://chrisdashboard.ddnsfree.com" style="color:white; text-decoration:none;">Dashboard</a></li>
        <li><a href="servers.php" style="color:white; text-decoration:none;">Serveurs</a></li>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
            <li><a href="admin_servers.php" style="color:white; text-decoration:none;">Admin Serveurs</a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'profile.php'): ?>
            <li><a href="profile.php" style="color:white; text-decoration:none;">Profile</a></li>
        <?php endif; ?>

        <li><a href="projects.php" style="color:white; text-decoration:none;">Projets</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php" style="color:white; text-decoration:none;">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="index.php" style="color:white; text-decoration:none;">Connexion</a></li>
        <?php endif; ?>
    </ul>
</nav>
