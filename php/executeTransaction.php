<?php
session_start();
require_once 'config.php'; // hier deine mysqli Verbindung $mysqli

$data = json_decode(file_get_contents("php://input"), true);

$ticketId = $data['ticketId'] ?? null;
$money = $data['money'] ?? null;
$method = $data['method'] ?? null;

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

/**
 * ==============================
 * GENERELL CHECKS OF VARIABLES
 * ==============================
*/

if($ticketId == null){
    echo json_encode(['success' => false, 'message' => 'Ungültige Ticket-Id']);
    exit;
}

if($money == null){
    echo json_encode(['success' => false, 'message' => 'Ungültiger Betrag']);
    exit;
}

if($method == null){
    echo json_encode(['success' => false, 'message' => 'Ungültige Bezahlmethode']);
    exit;
}

if($user_id == null){
    echo json_encode(['success' => false, 'message' => 'Ungültiger Benutzer']);
    exit;
}

/**
 * =====================================================
 * COLLECT TO "OLD_PAYED" FROM DATABASE FOR THIS TICKET
 * =====================================================
*/

$executeTransactionStatement = $conn->prepare("SELECT new_paid FROM payments WHERE ticket_id = ? ORDER BY id DESC LIMIT 1");
$executeTransactionStatement->bind_param("i", $ticketId);
$executeTransactionStatement->execute();
$result = $executeTransactionStatement->get_result();

if ($result->num_rows === 0) {
    $old_paid = 0;
} else {
    $row = $result->fetch_assoc();
    $old_paid = (int)$row['new_paid'];
}

/**
 * =====================================================
 * EXECUTE ACTUALL PAYMENT
 * =====================================================
*/

$executeTransactionStatement = $conn->prepare("INSERT INTO payments (user_id, ticket_id, old_paid, added, method) VALUES (?,?,?,?,?)");
$executeTransactionStatement->bind_param("iiiis", $user_id, $ticketId, $old_paid, $money, $method);
$success = $executeTransactionStatement->execute();

if(!$success){
    echo json_encode(['success' => false, 'message' => 'Fehler: ' . $executeTransactionStatement->error]);
    exit;
}

/**
 * ===================================================================================
 * CHECK, IF MAIL SHOULD BE SEND (DEPENDS ON THE FINANCIAL SITUATION OF THE TICKET)
 * ===================================================================================
*/

$needsToBePaidSumStatement = $conn->prepare('SELECT t.price, p.new_paid AS current_paid FROM tickets t LEFT JOIN payments p ON t.id = p.ticket_id WHERE t.id = ? ORDER BY p.id DESC LIMIT 1;'
);

$needsToBePaidSumStatement->bind_param('i', $ticketId);
$needsToBePaidSumStatement->execute();
$result = $needsToBePaidSumStatement->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Es konnte kein Vergleich bezüglich der noch zu begleichenden Summe hergestellt werden'
    ]);    
    exit;
}

$row = $result->fetch_assoc();

// Ticketpreis
$price = (int)$row['price'];

// Bereits gezahlter Betrag
$currentPaid = isset($row['current_paid']) ? (int)$row['current_paid'] : 0;

/**
 * =====================================================
 * SEND CONFIRMATION MAIL
 * =====================================================
*/

if($currentPaid >= $price){
    require 'sendConfirmationMail.php';

    $data = getNeccessaryData($conn, $ticketId);
    if(!$data['success']){
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $data['message']]);    
        exit;
    }

    $confirmationMail = sendConfirmationEmail($data['email'], $data['vorname'], $data['nachname'], $data['ticketCount'], $data['day'], $mailHost, $mailUsername, $mailPassword, $mailPort);
    if(!$confirmationMail['success']){
        echo json_encode(['success' => false, 'message' => 'Fehler: ' . $data['message']]);    
        exit;
    }

    setConfirmationTimestamp($conn, $ticketId);
    echo json_encode(['success' => true, 'message' => 'Transaktion und Email erfolgreich verschickt']);
    exit;

}else{
    echo json_encode(['success' => true, 'message' => 'Nur Transaktion wurde durchgeführt']);
}