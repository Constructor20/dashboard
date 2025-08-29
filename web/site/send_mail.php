<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendResetEmail($to, $resetLink, $expires) {
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
        $mail->Body    = "
            <h2>Réinitialisation de mot de passe</h2>
            <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>Ce lien expirera dans une heure.</p>
        ";

        // Contenu alternatif (au cas où HTML ne s'affiche pas)
        $mail->AltBody = "Réinitialisation de mot de passe :\n\n"
                       . "Cliquez sur ce lien pour réinitialiser votre mot de passe : $resetLink\n"
                       . "Ce lien expirera dans une heure.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de l'e-mail : " . $mail->ErrorInfo);
        return false;
    }
}
