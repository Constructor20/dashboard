<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendResetEmail($to, $resetLink, $resetLinkwithouttoken, $expires) {
    $config = require 'config_smtp.php';

    $mail = new PHPMailer(true);

    try {
        // Configuration encodage
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Serveur SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port       = $config['port'];

        // Expéditeur
        $mail->setFrom($config['from_email'], 'Minecraft Panel');
        $mail->addAddress($to);

        // Contenu HTML
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de votre mot de passe';
        $mail->Body = "
            <html lang='fr'>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        background: radial-gradient(circle at top left, #0f172a, #1e293b);
                        color: #f8fafc;
                        margin: 0;
                        padding: 40px;
                        text-align: center;
                    }

                    .email-container {
                        background: radial-gradient(circle at top left, #1e293b, #0f172a);
                        border: 1px solid #334155;
                        border-radius: 16px;
                        padding: 30px;
                        max-width: 600px;
                        margin: auto;
                        box-shadow: 0 0 25px rgba(15, 23, 42, 0.8);
                    }

                    h2 {
                        color: #38bdf8;
                        text-shadow: 0 0 10px rgba(56,189,248,0.4);
                    }

                    p {
                        color: #cbd5e1;
                        line-height: 1.6;
                        font-size: 16px;
                    }

                    a.button {
                        display: inline-block;
                        margin-top: 20px;
                        background: linear-gradient(90deg, #3b82f6, #0ea5e9);
                        color: white;
                        text-decoration: none;
                        padding: 12px 24px;
                        border-radius: 10px;
                        font-weight: bold;
                        transition: all 0.3s ease;
                    }

                    a.button:hover {
                        background: linear-gradient(90deg, #38bdf8, #2563eb);
                        box-shadow: 0 0 15px rgba(56,189,248,0.5);
                        transform: translateY(-2px);
                    }

                    .footer {
                        margin-top: 30px;
                        font-size: 13px;
                        color: #94a3b8;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <h2>🔐 Réinitialisation de mot de passe</h2>
                    <p>Vous avez demandé à réinitialiser votre mot de passe.</p>
                    <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                    <p>
                        <a href='$resetLink' class='button'>Réinitialiser mon mot de passe</a>
                    </p>
                    <p>Ce lien expirera dans une heure.</p>
                    <div class='footer'>
                        Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet e-mail.
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->AltBody = "Réinitialisation de mot de passe :\n\n"
                    . "Cliquez sur le lien suivant pour définir un nouveau mot de passe : $resetLink\n\n"
                    . "Ce lien expirera dans une heure.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de l'e-mail : " . $mail->ErrorInfo);
        return false;
    }
}
