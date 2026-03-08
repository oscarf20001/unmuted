<?php

/**
 * 
 * ==================================================================================================
 * THIS FILE HANDLES:
 * - The Search for one specific ticket issued through an code with a maximum amount of four digits
 * - The Handback of the possibly found ticket to javascript
 * ==================================================================================================
 * 
 */

header('Content-Type: application/json');

require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'];

if (!$code) {
    fail('Ungültige E-Mail-Adresse', $mailHost, $mailUsername, $mailPassword, $mailPort, $vorname, $nachname, $email);
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

/**
 * 
 * ==================================================================================================
 * DECLARING FUNCTIONS
 * ==================================================================================================
 * 
 */

function searchTicket($conn, $code){

    $stmt = $conn->prepare(
        "SELECT 
            t.*, 
            u.username AS bezahlt_bei, 
            p.new_paid AS summe, 
            p.changed_at AS bezahlt_am
        FROM tickets t
        JOIN payments p ON p.ticket_id = t.id
        JOIN user u ON p.user_id = u.id
        WHERE t.id = ?
        ORDER BY p.changed_at DESC
        LIMIT 1"
    );

    if(!$stmt){
        return [
            'success' => false,
            'message' => 'Fehler beim Vorbereiten des Statements: ' . $conn->error
        ];
    }

    if(!$stmt->bind_param('i', $code)){
        return [
            'success' => false,
            'message' => 'Fehler beim Binden der Parameter: ' . $stmt->error
        ];
    }

    if(!$stmt->execute()){
        return [
            'success' => false,
            'message' => 'Fehler beim Ausführen: ' . $stmt->error
        ];
    }

    $result = $stmt->get_result();

    if($result->num_rows === 0){
        return [
            'success' => false,
            'message' => 'Ticket nicht gefunden'
        ];
    }

    $ticket = $result->fetch_assoc();

    return [
        'success' => true,
        'message' => 'Ticket wurde gefunden',
        'ticket' => $ticket
    ];
}

function checkAlreadyIn($conn, $code){
    $stmt = $conn->prepare("SELECT 1 FROM entrance WHERE ticket_id = ? LIMIT 1");
    if(!$stmt){
        return [
            'success' => false,
            'message' => 'Fehler beim Vorbereiten des Statements: ' . $conn->error
        ];
    }

    if(!$stmt->bind_param('i', $code)){
        return [
            'success' => false,
            'message' => 'Fehler beim Binden der Parameter: ' . $stmt->error
        ];
    }

    if(!$stmt->execute()){
        return [
            'success' => false,
            'message' => 'Fehler beim Ausführen: ' . $stmt->error
        ];
    }

    $result = $stmt->get_result();

    if($result->num_rows > 0){
        return [
            'success' => true,
            'already_inside' => true,
            'message' => 'Ticket wurde bereits eingelassen'
        ];
    }

    return [
        'success' => true,
        'already_inside' => false,
        'message' => 'Ticket noch nicht eingelassen'
    ];
}

function registerEntrance($conn, $ticketId, $userId){
    $stmt = $conn->prepare("INSERT INTO entrance (ticket_id, user_id) VALUES (?, ?)");

    if(!$stmt){
        return [
            'success' => false,
            'message' => 'Fehler beim Vorbereiten des Statements: ' . $conn->error
        ];
    }

    if(!$stmt->bind_param('ii', $ticketId, $userId)){
        return [
            'success' => false,
            'message' => 'Fehler beim Binden der Parameter: ' . $stmt->error
        ];
    }

    if(!$stmt->execute()){
        return [
            'success' => false,
            'message' => 'Fehler beim Ausführen: ' . $stmt->error
        ];
    }

    return [
        'success' => true,
        'message' => 'Einlass erfolgreich registriert'
    ];
}

/**
 * 
 * ==================================================================================================
 * STARTING CHECK: IS TICKET ALREADY IN?
 * ==================================================================================================
 * 
 */

$ticket = searchTicket($conn, $code);
$check = checkAlreadyIn($conn, $code);

if(!$check['success']){
    echo json_encode($check);
    exit;
}

if($check['already_inside']){
    echo json_encode([
        'success' => false,
        'message' => 'Ticket wurde bereits eingelassen',
        'ticket' => $ticket
    ]);
    exit;
}

/**
 * 
 * ==================================================================================================
 * ONGOING: TICKET NOT ENTERED YET
 * ==================================================================================================
 * 
 */

$result = registerEntrance($conn, $code, $userId);

echo json_encode([
    $result, 
    'ticket' => $ticket
]);

/**
 * 
 * ==================================================================================================
 * CHECKS FOR REQUIRED FIELDS
 * ==================================================================================================
 * 
 */

// ✅ Checks successfull - ongoing

/**
 * 
 * ==================================================================================================
 * NO DUPLICATES FOUND - INSERT THE PERSON INTO THE TICKETS DATABASE
 * ==================================================================================================
 * 
 */