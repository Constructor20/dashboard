<?php
require_once 'includes/db.php';
require_once 'send_mail.php';
session_start();

$message = '';
$cooldown = 300; // 5 minutes

$email = '';
$now = time();

// Supprimer les tokens expirés à chaque chargement
$pdo->exec("DELETE FROM password_resets WHERE expires_at < NOW()");

// Gestion POST uniquement si bouton cliqué explicitement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_link') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse e-mail invalide.";
    } else {
        $lastSent = $_SESSION['reset_last_sent'][$email] ?? 0;

        if ($now - $lastSent < $cooldown) {
            $remaining = $cooldown - ($now - $lastSent);
            $message = "Vous devez attendre encore " . ceil($remaining) . " secondes avant de pouvoir renvoyer un lien.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userId = $user['id'];

                $stmt = $pdo->prepare("SELECT token, expires_at FROM password_resets WHERE user_id = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$userId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $token = $existing['token'];
                    $expires = $existing['expires_at'];
                } else {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$userId, $token, $expires]);
                }

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'];
                $path = dirname($_SERVER['PHP_SELF']);

                $resetLink = $protocol . $host . $path . "reset_password.php?token=$token";
                $resetLinkwithouttoken = $protocol . $host . $path . "reset_password.php";

                if (sendResetEmail($email, $resetLink, $resetLinkwithouttoken, $expires)) {
                    $_SESSION['reset_last_sent'][$email] = $now;
                    $message = "Un lien a été envoyé à votre adresse e-mail.";
                } else {
                    $message = "Erreur lors de l'envoi de l'e-mail.";
                }
            } else {
                $message = "Aucun compte trouvé avec cet e-mail.";
            }
        }
    }
}

// Gestion cooldown affiché (même hors POST)
$email = $_POST['email'] ?? $email;
$last = $_SESSION['reset_last_sent'][$email] ?? 0;
$remaining = max(0, $cooldown - ($now - $last));
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - Minecraft Panel</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f2f4f8;
        }

        header {
            background-color: #2a5298;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2a5298;
            text-align: center;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
            color: #333;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2a5298;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1e3c72;
        }

        .message {
            background-color: #eef4ff;
            color: #2a5298;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #cdddfb;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #2a5298;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h2>Minecraft Panel</h2>
    </header>

    <div class="container">
        <h1>Mot de passe oublié</h1>

    <?php if ($message): ?>
        <?php if ($last !== 0 && $remaining > 0): ?>
            <div class="message" id="countdown-message" data-remaining="<?= $remaining ?>">
                Vous devez attendre encore <?= ceil($remaining) ?> secondes avant de pouvoir renvoyer un lien.
            </div>
        <?php else: ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
    <?php endif; ?>


        <form method="POST">
            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="action" value="send_link">

        <?php
            $last = $_SESSION['reset_last_sent'][$email] ?? 0;
            $remaining = $cooldown - ($now - $last);
            if ($last === 0 || $remaining <= 0):
        ?>
            <button type="submit" id="reset-btn">Envoyer le lien de réinitialisation</button>
        <?php else: ?>
            <button type="submit" id="reset-btn" disabled data-remaining="<?= $remaining ?>">Veuillez patienter (<?= $remaining ?>s)</button>
        <?php endif; ?>

        </form>


        <div class="back-link">
            <a href="index.php">← Retour à la connexion</a>
        </div>
    </div>
</body>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("reset-btn");
    const msg = document.getElementById("countdown-message");

    let remaining = null;

    if (btn && btn.hasAttribute("data-remaining")) {
        remaining = parseInt(btn.getAttribute("data-remaining"));
    } else if (msg && msg.hasAttribute("data-remaining")) {
        remaining = parseInt(msg.getAttribute("data-remaining"));
    }

    if (remaining === null || isNaN(remaining)) return;

    const interval = setInterval(() => {
        remaining--;

        if (remaining <= 0) {
            clearInterval(interval);
            if (btn) {
                btn.disabled = false;
                btn.textContent = "Envoyer le lien de réinitialisation";
                btn.removeAttribute("data-remaining");
            }
            if (msg) {
                msg.textContent = "Vous pouvez à nouveau demander un lien de réinitialisation.";
                msg.removeAttribute("data-remaining");
            }
        } else {
            if (btn) btn.textContent = `Veuillez patienter (${remaining}s)`;
            if (msg) msg.textContent = `Vous devez attendre encore ${remaining} secondes avant de pouvoir renvoyer un lien.`;
        }
    }, 1000);
});
</script>


</html>
