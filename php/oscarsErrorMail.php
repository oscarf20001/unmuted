<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function informOscar(
    $mailHost,
    $mailUsername,
    $mailPassword,
    $mailPort
) {
    $mail = new PHPMailer(true);

    try {
        $nachricht = "
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
                
            </body>
            </html>
        ";

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
