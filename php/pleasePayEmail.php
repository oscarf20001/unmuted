<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendPleasePayEmail(
    $email,
    $vorname,
    $ticketCount,
    $price,
    $day,
    $mailHost,
    $mailUsername,
    $mailPassword,
    $mailPort
) {
    $mail = new PHPMailer(true);

    $vornameSafe = htmlspecialchars($vorname, ENT_QUOTES, 'UTF-8');
    $emailSafe   = str_replace("@","_at_", htmlspecialchars($email, ENT_QUOTES, 'UTF-8'));
    $priceSafe   = htmlspecialchars($price, ENT_QUOTES, 'UTF-8');
    $ticketSafe  = htmlspecialchars($ticketCount, ENT_QUOTES, 'UTF-8');

    $daySafe     = htmlspecialchars($day, ENT_QUOTES, 'UTF-8');    
    $dateObj     = DateTime::createFromFormat('d-m-Y H:i:s', $daySafe);
    $dayDate     = $dateObj->format('d.m.Y');
    $dayTime     = $dateObj->format('H:i');


    try {
    $nachricht = <<<HTML
        <!DOCTYPE html>
        <html lang="de">
        <head>
        <meta charset="UTF-8">
        <title>Ihre Reservierung – SK Musical "Unmuted"</title>
        </head>

        <body style="
        margin:0;
        padding:0;
        font-family: Inter, Montserrat, Arial, sans-serif;
        background: linear-gradient(to bottom, #7a0c16 0%, #2b0a0e 70%);
        color:#e6e7eb;
        ">

        <div style="max-width:600px;margin:0 auto;padding:2rem 1.5rem;">

            <div style="text-align:center;margin-bottom:2rem;">
            <h1 style="
                margin:0;
                font-size:3rem;
                font-weight:800;
                font-style:italic;
                color:#ffffff;
                text-shadow:0 4px 12px rgba(0,0,0,0.5);
            ">
                UNMUTED
            </h1>
            <h3 style="
                margin-top:0.75rem;
                font-size:1.1rem;
                font-weight:400;
                letter-spacing:0.12em;
                color:rgba(255,255,255,0.85);
            ">
                ZEIG, WER DU BIST
            </h3>
            </div>

            <div style="
            background:rgba(0,0,0,0.25);
            border-radius:14px;
            padding:2rem;
            color:#ffffff;
            ">

            <p style="font-size:1.05rem;">
                Hallo <strong>{$vornameSafe}</strong>,
            </p>

            <p style="line-height:1.6;color:#e6e7eb;">
                vielen Dank für Ihre Reservierung für unser <strong>SK Musical</strong> –
                wir freuen uns sehr, dass Sie am {$dayDate} um {$dayTime} Uhr dabei sind! 🎭
            </p>

            <p style="line-height:1.6;color:#b5b8c2;">
                Um Ihre Tickets verbindlich zu sichern, überweisen Sie bitte den Betrag von
                <strong style="color:#ffffff;">{$priceSafe} €</strong>
                über PayPal an den auf der Website gezeigten Account. Besuchen Sie dazu folgende Seite:<br>
                <a href="https://www.curiegymnasium.de/payment/index.php?email={$emailSafe}&tickets={$ticketSafe}">Zahlungsinformationen</a>
            </p>

            <p style="line-height:1.6;color:#b5b8c2;">
                Sobald die Zahlung bei uns eingegangen ist, erhalten Sie in den nächsten 48h eine
                Bestätigung per E-Mail.
            </p>

            <p style="line-height:1.6;color:#b5b8c2;">
                Bei Fragen melden Sie sich jederzeit gerne bei uns.
            </p>

            <p style="margin-top:2rem;font-weight:500;color:#ffffff;">
                Wir freuen uns auf Ihren Besuch!<br><br>
                <span style="color:#c8a96a;">Ihr SK-Musical-Team</span>
            </p>

            </div>

            <p style="
            margin-top:2rem;
            text-align:center;
            font-size:0.8rem;
            color:#7e828f;
            ">
            Marie-Curie-Gymnasium · SK Musical
            </p>

        </div>

        </body>
        </html>
        HTML;

    $mail->isSMTP();
    $mail->Host       = $mailHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailUsername;
    $mail->Password   = $mailPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $mailPort;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($mailUsername, 'Marie-Curie-Gymnasium – SK Musical');
    $mail->addReplyTo($mailUsername, 'SK Musical Team');
    $mail->addAddress($email, $vorname);
    $mail->addCustomHeader(
    'List-Unsubscribe',
    '<mailto:' . $mailUsername . '>'
    );

    $mail->isHTML(true);
    $mail->Subject = 'Ihre Reservierung für das SK Musical "Unmuted" – Zahlungsinformationen';
    $mail->Body    = $nachricht;
    $mail->AltBody = "
    Hallo {$vornameSafe},

    vielen Dank für Ihre Reservierung für unser SK Musical.

    Bitte überweisen Sie {$priceSafe} € über PayPal an den auf der Website angegebenen Account:
    https://www.curiegymnasium.de/payment/index.php?email={$emailSafe}&tickets={$ticketSafe}

    Nach Zahlungseingang erhalten Sie innerhalb von 48 Stunden eine Bestätigung.

    Ihr SK-Musical-Team
    Marie-Curie-Gymnasium
    ";

    $mail->send();
    return true;

} catch (Exception $e) {
    return false;
}
 catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
