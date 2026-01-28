<style>
/* 🌙 Style global de la navbar */
.navbar {
    background: radial-gradient(circle at top left, #1e293b, #0f172a);
    box-shadow: 0 0 25px rgba(0, 102, 255, 0.4);
    padding: 15px 40px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(6px);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

/* 💠 Logo */
.navbar .logo {
    font-weight: bold;
    font-size: 1.6rem;
    letter-spacing: 0.5px;
    color: #60a5fa;
    text-shadow: 0 0 10px rgba(96,165,250,0.5);
    transition: all 0.3s ease;
}
.navbar .logo:hover {
    text-shadow: 0 0 15px rgba(147,197,253,0.8);
}

/* 🔗 Liens */
.navbar ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 25px;
}

.navbar ul li a {
    color: #e2e8f0;
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    transition: all 0.3s ease;
    position: relative;
}

/* ✨ Effet de glow au survol */
.navbar ul li a:hover {
    color: #93c5fd;
    text-shadow: 0 0 8px rgba(147,197,253,0.7);
}

/* 🌊 Effet d’indicateur (ligne sous le lien actif ou au hover) */
.navbar ul li a::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -4px;
    width: 0%;
    height: 2px;
    background: #3b82f6;
    border-radius: 10px;
    transition: width 0.3s ease;
}
.navbar ul li a:hover::after {
    width: 100%;
}

/* 🟢 Lien actif */
.navbar ul li a.active {
    color: #60a5fa;
}
.navbar ul li a.active::after {
    width: 100%;
    background: #60a5fa;
}

/* 📱 Responsive */
@media (max-width: 800px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
        padding: 15px 25px;
    }
    .navbar ul {
        flex-direction: column;
        gap: 15px;
        width: 100%;
        margin-top: 10px;
    }
}
</style>

<nav class="navbar">
    <div class="logo">Minecraft Panel</div>

    <ul>
        <li><a href="http://chrisdashboard.ddnsfree.com" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="servers.php" class="<?= basename($_SERVER['PHP_SELF']) === 'servers.php' ? 'active' : '' ?>">Serveurs</a></li>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
            <li><a href="admin_servers.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_servers.php' ? 'active' : '' ?>">Admin Serveurs</a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'profile.php'): ?>
            <li><a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">Profil</a></li>
        <?php endif; ?>

        <li><a href="projects.php" class="<?= basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : '' ?>">Projets</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="index.php">Connexion</a></li>
        <?php endif; ?>
    </ul>
</nav>
