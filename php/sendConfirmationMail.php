<?php

require_once 'config.php'; // hier deine mysqli Verbindung $mysqli
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getNeccessaryData($conn, $id){

    $stmt = $conn->prepare(
        'SELECT vorname, nachname, ticketCount, email, day FROM tickets WHERE id = ?'
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'message' => 'Kein Datensatz gefunden'
        ];
    }

    $row = $result->fetch_assoc();

    return [
        'success'     => true,
        'vorname'     => (string)$row['vorname'],
        'nachname'     => (string)$row['nachname'],
        'ticketCount' => (int)$row['ticketCount'],
        'email'       => (string)$row['email'],
        'day'         => (string)$row['day']
    ];
}

/**
 * ======================================
 * SEND CONFIRMATION EMAIL
 * ======================================
 */
function sendConfirmationEmail(
    $email,
    $vorname,
    $nachname,
    $ticketCount,
    $day,
    $mailHost,
    $mailUsername,
    $mailPassword,
    $mailPort
){

    $mail = new PHPMailer(true);

    try {

        // ====== Datum formatieren ======
        $dateObj = DateTime::createFromFormat('d-m-Y H:i:s', $day);
        if (!$dateObj) {
            throw new Exception('Ungültiges Datum: ' . $day);
        }
        $dayDate = $dateObj->format('d.m.Y');
        $dayTime = $dateObj->format('H:i');

        // ====== HTML Escaping ======
        $vornameSafe  = htmlspecialchars($vorname, ENT_QUOTES, 'UTF-8');
        $emailSafe    = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $ticketSafe   = (int)$ticketCount;
        $nachnameSafe = htmlspecialchars($nachname, ENT_QUOTES, 'UTF-8');
        $daySafe      = htmlspecialchars($day, ENT_QUOTES, 'UTF-8');

        // ====== Mail Body (UNVERÄNDERT) ======
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
                    vielen Dank! Ihre Zahlung ist bei uns eingegangen. ✅
                </p>

                <p style="line-height:1.6;color:#b5b8c2;">
                    Ihre Tickets für das SK Musical sind damit verbindlich bestätigt. 🎉<br>
                    Reservierung: {$ticketSafe} Tickets auf den Namen "{$vorname} {$nachname}" am {$dayDate} um {$dayTime} Uhr
                </p>

                <p style="line-height:1.6;color:#b5b8c2;">
                    Alle weiteren Informationen zur Veranstaltung erhalten Sie rechtzeitig vorab per E-Mail.
                </p>

                <p style="line-height:1.6;color:#b5b8c2;">
                    Wir freuen uns sehr, Sie bei unserer Aufführung begrüßen zu dürfen!<br>
                    Bei Fragen melden Sie sich jederzeit gerne bei uns.
                </p>

                <p style="margin-top:2rem;font-weight:500;color:#ffffff;">
                    Herzliche Grüße!<br><br>
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

        // ====== SMTP Setup ======
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
        $mail->addAddress($email, $vornameSafe);
        $mail->addCustomHeader('List-Unsubscribe','<mailto:' . $mailUsername . '>');

        $mail->isHTML(true);
        $mail->Subject = 'Viel Spaß bei unserem Musical 🥳 | SK Musical';
        $mail->Body    = $nachricht;

        $mail->AltBody = "
        Hallo {$vornameSafe},

        vielen Dank! Ihre Zahlung ist bei uns eingegangen. ✅

        Ihre Tickets für das SK Musical sind damit verbindlich bestätigt. 🎉
        Reservierung: [Anzahl] Tickets auf den Namen {$vornameSafe}

        Nach Zahlungseingang erhalten Sie innerhalb von 48 Stunden eine Bestätigung.

        Ihr SK-Musical-Team
        Marie-Curie-Gymnasium
        ";

        $mail->send();

        return [
            'success' => true,
            'message' => 'Transaktion und Mail erfolgreich durchgeführt!'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Mailer-Fehler: ' . $e->getMessage()
        ];
    }
}

function setConfirmationTimestamp($conn, $id){
    $setConfirmationTimestampStatement = $conn->prepare("UPDATE tickets SET confirmed = CURRENT_TIMESTAMP() WHERE id = ?");
    $setConfirmationTimestampStatement->bind_param('i', $id);
    $setConfirmationTimestampStatement->execute();
}