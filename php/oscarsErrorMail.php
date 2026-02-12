<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function informOscar($mailHost, $mailUsername, $mailPassword, $mailPort, $msg, $vorname = null, $nachname = null, $email = null) {
    $mail = new PHPMailer(true);

    $errorType = null;

    switch (true) {
        case str_contains($msg, 'Feld') && str_contains($msg, 'ungültig'):
            $errorType = 'Ungültiges Feld in Formular';
            break;

        case str_contains($msg, 'E-Mail-Adresse'):
            $errorType = 'Invalid E-Mail';
            break;

        case str_contains($msg, '0'):
            $errorType = 'Ticketanzahl unterschreitet 1';
            break;

        case str_contains($msg, 'ungültig'):
            $errorType = 'Ungültiger Preis';
            break;

        case str_contains($msg, 'Vorbereiten'):
            $errorType = 'Prepared Statement Error - DB';
            break;

        case str_contains($msg, 'Binden'):
            $errorType = 'Bind Parameter Error - DB';
            break;

        case str_contains($msg, 'Ausführen'):
            $errorType = 'Execute Command Error - DB';
            break;

        case str_contains($msg, 'eingefügt'):
            $errorType = 'Anderer unbekannter Datenbankfehler';
            break;

        case str_contains($msg, 'Mailversand'):
            $errorType = 'Mailversand fehlgeschlagen';
            break;

        case str_contains($msg, 'existiert'):
            $errorType = 'Already existing ticket';
            break;

        default:
            // fallback
            break;
    }

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
                Metis-System has detected an Error: <br><br>

                Error-Type: {$errorType}<br>
                Vorname: {$vorname}<br>
                Nachname: {$nachname}<br>
                Email: {$email}<br>
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

        $mail->setFrom($mailUsername, 'Metis automated error handling Service');
        $mail->addAddress('oscar-streich@t-online.de', 'Oscar');

        $mail->isHTML(true);
        $mail->Subject = 'Systemfehler: ' . '';
        $mail->Body    = $nachricht;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
