<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Berlin');

//require_once __DIR__ . '/../server/php/db_connection.php';
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Person-ID empfangen
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$personId = $data['id'] ?? $_GET['id'] ?? 1;

$email = $data['email'] ?? 'streiosc@curiegym.de';
$vorname = $data['vorname'] ?? 'Teilnehmer';

if (!$email) {
    writeToLog($logHandle, "❌ FEHLER: E-Mail existiert nicht: '{$email}'");
    echo json_encode([
        'status' => 'fail',
        'message' => 'E-Mail-Adresse fehlt!'
    ]);
    return;
}

$logHandle = fopen(__DIR__ . '/ticketMail.log', 'a'); // oder anderer Pfad

// Check for already submitted-Tickets for this person
$stmt = $conn->prepare('SELECT vorname, nachname, send_TicketMail
                        FROM person
                        WHERE id = ?');
$stmt->bind_param('i', $personId);

if(!$stmt->execute()){
    writeToLog($logHandle, "❌ FEHLER: Prepared Statement failed for mail: '{$email}'");
    echo json_encode([
        'status' => 'fail',
        'message' => 'Execution of prepared Statement failed'
    ]);
    return;
    $stmt->close();
}

$stmt->bind_result($vorname, $nachname, $send_TicketMail);

if (!$stmt->fetch()) {
    writeToLog($logHandle, "❌ FEHLER: No person found with given ID. Email: '{$email}'");
    echo json_encode([
        'status' => 'fail',
        'message' => 'No person found with given ID'
    ]);
    $stmt->close();
    return;
}

$stmt->close();

// Start generating the PDF-File, because Mail wasnt send yet
$returnVar = file_get_contents('https://metis-pdfgen.curiegymnasium.de/?person_id='. $personId);

$reponse = json_decode($returnVar, true); // true = associative array

$messageForNetwork = [
    'status' => 'error',
    'message' => 'kein Inhalt zurückgegeben'
];

if ($reponse && $reponse['status'] === 'success') {
    $pdfFile = base64_decode($reponse['pdf']);
    $pdfPfad = __DIR__ . '/gen_pdfs/ticket_person_' . $personId . '.pdf';
    file_put_contents($pdfPfad, $pdfFile);
    $messageForNetwork = [
        'status' => 'success',
        'message' => 'PDF erfolgreich generiert',
        'base64' => base64_encode($pdfFile)
    ];
} else {
    $messageForNetwork = [
        'status' => 'error',
        'message' => 'PDF-Generierung fehlgeschlagen'
    ];
}

// Send the Mail; attached the generated PDF
sendConfirmationMail($conn, $personId, $data['vorname'], $data['email'], $data['nachname'], $logHandle);
function sendConfirmationMail($conn, $id, $vorname, $email, $nachname, $logHandle){
    $nachricht = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Ticketbestätigung Herbstball 2025 MCG-FFR</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #f6f6f6;
                font-family: Arial, sans-serif;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 24px;
                border-radius: 8px;
            }
            h1 {
                font-size: 24px;
                color: #333;
            }
            p {
                font-size: 16px;
                color: #333;
                line-height: 1.6;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 16px;
            }
            th, td {
                padding: 12px;
                border: 1px solid #ddd;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .cta-button {
                display: inline-block;
                margin-bottom: 24px;
                padding: 12px 24px;
                font-size: 16px;
                color: white;
                background-color: #7F63F4;
                text-decoration: none;
                border-radius: 5px;
                color: #ffffff;
            }
            .qr-section {
                text-align: center;
                margin: 32px 0;
            }
            .footer {
                font-size: 12px;
                color: #888;
                text-align: center;
                margin: 40px 0 10px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>
                Hey " . $vorname . ",
            </h1>

            <p>
                Du hast keine offenen Kosten mehr. Wie episch!
            </p>

            <p>
                Wir möchten euch, wo wir gerade schon alle hier sind, noch einige letzte wichtige Infos mitgeben und FAQs beantworten:<br><br>

                Datum: <strong>17.10.2025</strong><br>
                Uhrzeit: <strong>Einlass</strong> ab 18:45 Uhr, Beginn um 20:00 Uhr, Ende: 01:00 Uhr<br>
                Adresse: <strong>Friedrich-Wolf-Straße 31, Oranienburg</strong><br><br>

                🦺 <strong>\"gibts irgendwie Security Menschen, die den Einlass kontrollieren oder macht ihr das einfach?\"<br></strong>
                <strong>Ja</strong>, es wird beim Betreten des Geländes eine Personalausweis-, als auch Taschenkontrolle, sowie Bodychecks geben. Durchgeführt werden diese vom Sicherheitspersonal.<br><br>

                🎒 <strong>\"kann man bei der gaderobe auch nen Rucksack abgeben? Bestimmt oder?\"<br></strong>
                <strong>Ja</strong>, unsere fleißigen Helfer werden euch auch mit euren Rucksäcken helfen können. Haltet euer Gepäck jedoch bitte möglichst klein und seht davon ab, irgendwelche Wertgegenstände, außer den amtlichen Lichtbildausweis 🪪, einzupacken.<br><br>

                👗 <strong>\"Habt ihr nh Dresscode?\"<br></strong>
                Naja, joa, wir würden uns freuen, wenn ihr nicht in Jogginghose antanzt, allerdings braucht ihr euch auch nicht wie zu einer Hochzeit rausputzen.<br><br>

                🚬 <strong>Ob man auf dem Gelände rauchen kann?<br></strong>
                Das ist möglich, solange ihr das Veranstaltungsgelände nicht verlasst. Das bringt uns auch zur nächsten Frage:<br><br>

                🚶‍♂️ <strong>Dürfen wir rausgehen?<br></strong>
                Natürlich dürft ihr das, seid allerdings gewarnt, dass wer das Veranstaltungsgelände verlässt, der verlässt auch endgültig die Veranstaltung – der Weihnachtsmann hat da dann auch kein Nachsehen mehr. Ansonsten dürft ihr euch auf dem Gelände frei bewegen.<br><br>

                🎸🎧 <strong>Wie kann ich Musikwünsche äußern?<br></strong>
                Während der Veranstaltung dann hier: https://curiegymnasium.de/client/musikwuensche.php<br><br>

                <strong>Ihr werdet außerdem von der Veranstaltung ausgeschlossen, wenn: <br></strong>
                - ❌ ihr euch daneben benehmt<br>
                - ❌ ihr beim Schmuggeln erwischt werdet<br>
                - ❌ ihr euer Armband verliert<br>
                - ❌ ihr euer Ticket bzw. eines eurer Tickets noch nicht bezahlt habt<br>
                Genannte Punkte führen unwiderruflich zum sofortigen Ausschluss von der Veranstaltung und bei Bedarf zum Hinzuziehen der Polizei.<br>
                Wir bitten um das Benehmen eurerseits, damit wir in Zukunft auch noch in dieser Location diese Veranstaltung durchführen können.<br>
                Ihr werdet nicht auf das Gelände gelassen, wenn ihr bereits vor Eintritt zu betrunken seid.<br><br>

                Einlass ist von 18:45 bis 21:00 Uhr. Ab 20:15 Uhr läuft das Ganze dann als Abendkasse – wer also erst danach reinkommt, zahlt 2,50 € extra zum erworbenen Ticketpreis.<br>
            </p>

            <p>
                Die Veranstaltung wird ca. um 00:45 Uhr bis 01:00 Uhr enden. Wir würden uns freuen, wenn sich am Ende der Veranstaltung noch einige freiwillige Helfer finden, die mit Gordon und dem gesamten Orga-Team den Saal schnell aufräumen.<br><br>

                Sollte es irgendwelche Probleme oder Anregungen sowohl technischer als auch allgemeiner Natur geben, antwortet gern auf diese Mail, wendet euch an <code>oscar-streich@t-online.de</code> oder sprecht uns persönlich an!<br>
                Im Anhang findet ihr euer Ticket (PDF)<br><br>
            </p>

            <p>
                Hier kannst du weitere Tickets bestellen:<br>
                <a href='https://curiegymnasium.de/' class='cta-button'>
                    🎟️ Tickets holen
                </a><br><br>

                Bei Fragen oder Problemen wende dich bitte an: <code>oscar-streich@t-online.de</code>
            </p>

            <p>
                🌟 🎁 Wir danken und freuen uns riesig zusammen mit dir auf den 17.10. und wünschen dir eine frohe Sommerzeit bis dahin!<br><br>


                Mit freundlichen Grüßen,<br><strong>Gordon!</strong>
            </p>

            <div class='footer'>
                *Alle Angaben ohne Gewähr; Änderungen vorbehalten; <a href='https://www.curiegymnasium.de/client/bedingungen.php'>Teilnahmebedingungen</a>
            </div>
    </body>
    </html>        
            ";

        try {
            $mail = new PHPMailer(true);
            
            // SMTP-Konfiguration
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Absender und Empfänger
            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Marie-Curie Gymnasium');
            $mail->addReplyTo('oscar-streich@t-online.de', 'Oscar');
            $mail->addAddress($email, $vorname);
            $pdfPfad = __DIR__ . '/gen_pdfs/ticket_person_' . $id . '.pdf';
            if (file_exists($pdfPfad)) {
                $mail->addAttachment($pdfPfad, 'Ticket_'.$vorname.'-'.$nachname.'.pdf');
            } else {
                writeToLog($logHandle, "FEHLER: Ticket-PDF nicht gefunden unter $pdfPfad");
            }

            // E-Mail-Inhalt
            $mail->isHTML(true);
            $mail->Body = $nachricht;
            $mail->Subject = '🎉 Epische Ticketbestätigung: Herbstball MCG-FFR 2025 🍁🌙';
            $mail->AltBody = 'Deine Kosten wurden beglichen. Hier Tickets für den Herbstball des MCG 2025 sichern: https://www.curiegymnasium.de/';

            // E-Mail senden
            // E-Mail senden und loggen
            if ($mail->send()) {
                writeToLog($logHandle, "ERFOLG: E-Mail an {$email} gesendet.");
                setDateInDatabase($conn, $id);
            } else {
                writeToLog($logHandle, "FEHLER: E-Mail an {$email} nicht gesendet. Fehler: " . $mail->ErrorInfo);
            }

            // Empfänger und Anhänge leeren
            $mail->clearAddresses();
            $mail->clearAttachments();
            sleep(1);
        } catch (Exception $e) {
            writeToLog($logHandle, "FEHLER: E-Mail an {$email} nicht gesendet. Fehler: {$mail->ErrorInfo}");
        }
}

function writeToLog($handleOrPath, string $message): void {
    // Aktuelles Datum/Zeit im ISO-Format
    $timestamp = date('Y-m-d H:i:s');

    // Formatierte Logzeile
    $logLine = "[{$timestamp}] {$message}\n";

    // Falls ein Datei-Handle übergeben wurde
    if (is_resource($handleOrPath)) {
        fwrite($handleOrPath, $logLine);
    } 
    // Falls ein Dateipfad übergeben wurde
    elseif (is_string($handleOrPath)) {
        file_put_contents($handleOrPath, $logLine, FILE_APPEND | LOCK_EX);
    } 
    else {
        error_log("writeToLog: Ungültiger Parameter für Log-Ziel.");
    }
}

function setDateInDatabase($conn, $id){
    #Sets the date of the ticket Mail for one person in the database

    $messageForNetwork[] = [
        'status' => 'info',
        'message' => 'Funktion für aktualiserung in der Datenbank würde ausgeführt werden!'
    ];
    $timestamp = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE person SET send_TicketMail = 1, dateSendTicketMail = ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare fehlgeschlagen: " . $conn->error);
    }
    
    // Parameter binden
    if (!$stmt->bind_param("si", $timestamp, $id)) {
        die("Bind fehlgeschlagen: " . $stmt->error);
    }
    
    // Statement ausführen
    if (!$stmt->execute()) {
        die("Ausführung fehlgeschlagen: " . $stmt->error);
    }
    
    // Statement schließen
    $stmt->close();
    $conn->close();
}

echo json_encode($messageForNetwork);