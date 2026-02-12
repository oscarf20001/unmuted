<?php

/**
 * 
 * ==================================================================================================
 * THIS FILE HANDLES:
 * - DATA-VALIDATION
 * - THE CHECKS FOR AN DUPLICATE TICKET
 * - THE INSERTION OF AN TICKET, IF NO DUPLICATE FOUND
 * - THE PROCESS OF SENDING THE FIRST EMAIL TO THE CLIENT
 * ==================================================================================================
 * 
 */

header('Content-Type: application/json');

require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

/**
 * 
 * ==================================================================================================
 * DECLARING FUNCTIONS
 * ==================================================================================================
 * 
 */

function fail($msg, $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email) {

    include 'oscarsErrorMail.php';

    $errorHandlingMailResult = informOscar($mailHost, $mailUsername, $mailPassword, $mailPort, $msg);

    echo json_encode([
        'success' => false,
        'message' => $msg
    ]);
    exit;
}

function insertIntoDb($conn, $vorname, $nachname, $email, $ticketCount, $price, $day, $visited){
    $insertTicketStatement = $conn->prepare("INSERT INTO tickets (vorname, nachname, email, ticketCount, price, day, booked) VALUES (?,?,?,?,?,?,?)");
    if(!$insertTicketStatement){
        $response = [   
                        'success' => false,
                        'message' => 'Fehler beim Vorbereiten des Statements: ' . $conn->error
        ];
        return $response;
    }

    if(!$insertTicketStatement->bind_param('sssiiss', $vorname, $nachname, $email, $ticketCount, $price, $day, $visited)){
        $response = [   
                        'success' => false,
                        'message' => 'Fehler beim Binden der Parameter: ' . $conn->error
        ];
        return $response;
    }

    if(!$insertTicketStatement->execute()){
        $response = [   
                        'success' => false,
                        'message' => 'Fehler beim Ausführen des Befehls: ' . $conn->error
        ];
        return $response;
    }

    return [   
                        'success' => true,
                        'message' => 'Ticket konnte erfolgreich eingefügt werden'
    ];
}

/**
 * 
 * ==================================================================================================
 * CHECKS FOR REQUIRED FIELDS
 * ==================================================================================================
 * 
 */

// Pflichtfelder prüfen
$required = [
    'vorname',
    'nachname',
    'email',
    'ticketCount',
    'price',
    'presentation',
    'visited'
];

foreach ($required as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        fail("Feld '$field' ist ungültig oder fehlt", $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email);
    }
}

// Typen & Inhalte prüfen
$vorname     = trim($data['vorname']);
$nachname    = trim($data['nachname']);
$email       = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$ticketCount = filter_var($data['ticketCount'], FILTER_VALIDATE_INT);
$price       = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
$day         = trim($data['presentation']);
$visited     = trim($data['visited']);

if (!$email) {
    fail('Ungültige E-Mail-Adresse', $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email);
}

if ($ticketCount <= 0) {
    fail('Ticket-Anzahl muss größer als 0 sein', $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email);
}

if ($price <= 0) {
    fail('Preis ungültig', $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email);
}

// ✅ Checks successfull - ongoing

/**
 * 
 * ==================================================================================================
 * CHECK FOR ALREADY EXISTING TICKETS ON A DAY FOR THIS PERSON (DUPLICATES)
 * ==================================================================================================
 * 
 */

require_once 'checkForDuplicates.php';

/**
 * 
 * ==================================================================================================
 * NO DUPLICATES FOUND - INSERT THE PERSON INTO THE TICKETS DATABASE
 * ==================================================================================================
 * 
 */

$insert = insertIntoDb($conn, $vorname, $nachname, $email, $ticketCount, $price, $day, $visited);

if(!$insert['success']){
    fail($insert['message'], $mailHost, $mailUsername, $mailPassword, mailPort: $mailPort, vorname: $vorname, nachname: $nachname, email: $email); // sofort abbrechen + Fehler an Fronte, $mailHost, $mailUsername, $mailPassword, $mailPortnd
}

/**
 * 
 * ==================================================================================================
 * MAILING-PROCESS
 * ==================================================================================================
 * 
 */

require_once 'pleasePayEmail.php';

$mailResult = sendPleasePayEmail(
    $email,
    $vorname,
    $ticketCount,
    $price,
    $day,
    $mailHost,
    $mailUsername,
    $mailPassword,
    $mailPort
);

if ($mailResult !== true) {
    fail('Mailversand fehlgeschlagen: ' . $mailResult, $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email);
}

echo json_encode([
    'success' => true,
    'message' => 'Ticket erfolgreich erstellt! Bitte überprüfe deine Mails'
]);