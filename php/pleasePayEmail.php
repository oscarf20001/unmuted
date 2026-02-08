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
    $emailSafe   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $priceSafe   = htmlspecialchars($price, ENT_QUOTES, 'UTF-8');
    $ticketSafe  = htmlspecialchars($ticketCount, ENT_QUOTES, 'UTF-8');
    $daySafe     = htmlspecialchars($day, ENT_QUOTES, 'UTF-8');


    try {
        $nachricht = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Unmuted - Zeig, wer du bist!</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        padding: 8px;
                        text-align: left;
                        border: 1px solid #ddd;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    p {
                        margin: 16px 0;
                    }
                </style>
            </head>
            <body>
                <p>Hallo {$vornameSafe},</p>
                <p>
                    vielen Dank für Ihre Reservierung für unser SK Musical – wir freuen uns sehr, dass Sie dabei sind! 🎭<br><br>

                    Um Ihre Tickets verbindlich zu sichern, überweisen Sie bitte den Betrag von {$priceSafe}€ an:<br><br>

                    PayPal: [PayPal-Adresse / Name „Raphael …“]<br>
                    Verwendungszweck: {$emailSafe}+{$ticketSafe}+MusicalUnmuted<br><br>

                    Sobald die Zahlung bei uns eingegangen ist, erhalten Sie eine Bestätigung per E-Mail.<br><br>

                    Bei Fragen melden Sie sich jederzeit gerne bei uns.<br><br>

                    Wir freuen uns auf Ihren Besuch!<br>
                    Ihr SK-Musical Team
                </p>
                <!--<p>
                    Vorname: ".htmlspecialchars($vorname, ENT_QUOTES, 'UTF-8')."<br>
                    Email: ".htmlspecialchars($email, ENT_QUOTES, 'UTF-8')."<br>
                    Anzahl an Tickets: ".htmlspecialchars($ticketCount, ENT_QUOTES, 'UTF-8')."<br>
                    Preis: ".htmlspecialchars($price, ENT_QUOTES, 'UTF-8')."<br>
                    Tag: ".htmlspecialchars($day, ENT_QUOTES, 'UTF-8')."
                </p>-->
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

        $mail->setFrom($mailUsername, 'Marie-Curie Gymnasium');
        $mail->addReplyTo('oscar-streich@t-online.de', 'Oscar');
        $mail->addAddress($email, $vorname);

        $mail->isHTML(true);
        $mail->Subject = 'Vielen Dank für ihre Reservierung | SK Musical';
        $mail->Body    = $nachricht;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
