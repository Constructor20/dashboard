<?php
require_once 'includes/db.php';
require_once 'send_mail.php';
session_start();

$message = '';
$cooldown = 300; // 5 minutes
$email = '';
$now = time();

$pdo->exec("DELETE FROM password_resets WHERE expires_at < NOW()");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_link') {
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
                $resetLinkwithouttoken = $protocol . $host . $path . "/reset_password.php";

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
        /* 🌌 Thème global */
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top left, #0f172a, #1e293b);
            color: #f8fafc;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        h1 {
            color: #38bdf8;
            text-align: center;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
        }

        .container {
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            border-radius: 16px;
            border: 1px solid #334155;
            box-shadow: 0 0 25px rgba(15, 23, 42, 0.8);
            padding: 30px 40px;
            max-width: 480px;
            width: 90%;
            transition: all 0.3s ease;
        }

        .container:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 35px rgba(59,130,246,0.4);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #cbd5e1;
        }

        input[type="email"] {
            width: 95%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #334155;
            background: rgba(30, 41, 59, 0.9);
            color: #f1f5f9;
            font-size: 15px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56,189,248,0.5);
            outline: none;
        }

        button {
            width: 100%;
            background: linear-gradient(90deg, #3b82f6, #0ea5e9);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            font-size: 16px;
            margin-top: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(90deg, #38bdf8, #2563eb);
            box-shadow: 0 0 20px rgba(56,189,248,0.5);
            transform: translateY(-2px);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #334155;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background: rgba(16,185,129,0.15);
            border: 1px solid #10b981;
            color: #a7f3d0;
        }

        .message.error {
            background: rgba(239,68,68,0.15);
            border: 1px solid #ef4444;
            color: #fecaca;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            color: #60a5fa;
            text-shadow: 0 0 8px rgba(96,165,250,0.6);
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Mot de passe oublié</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'attendre') !== false ? 'error' : 'success' ?>" 
                 id="countdown-message" data-remaining="<?= $remaining ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="email">Adresse e-mail :</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="action" value="send_link">

            <?php
                $last = $_SESSION['reset_last_sent'][$email] ?? 0;
                $remaining = $cooldown - ($now - $last);
                if ($last === 0 || $remaining <= 0):
            ?>
                <button type="submit" id="reset-btn">📧 Envoyer le lien de réinitialisation</button>
            <?php else: ?>
                <button type="submit" id="reset-btn" disabled data-remaining="<?= $remaining ?>">
                    Veuillez patienter (<?= $remaining ?>s)
                </button>
            <?php endif; ?>
        </form>

        <div class="back-link">
            <a href="index.php">⬅ Retour à la connexion</a>
        </div>
    </div>

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
                    btn.textContent = "📧 Envoyer le lien de réinitialisation";
                    btn.removeAttribute("data-remaining");
                }
                if (msg) {
                    msg.classList.remove("error");
                    msg.classList.add("success");
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
</body>
</html>
