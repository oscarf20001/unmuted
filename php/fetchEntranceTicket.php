<?php

header('Content-Type: application/json');
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? null;
$userId = $_SESSION['user_id'] ?? null;


/*
|--------------------------------------------------------------------------
| STANDARD RESPONSE
|--------------------------------------------------------------------------
*/

function respond($success, $message, $error = null, $data = [])
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'error' => $error,
        'data' => array_merge([
            'ticket' => null,
            'scans' => 0,
            'max' => 0
        ], $data)
    ]);
    exit;
}


/*
|--------------------------------------------------------------------------
| GET TICKET
|--------------------------------------------------------------------------
*/

function getTicket($conn, $ticketId)
{
    $stmt = $conn->prepare(
        "SELECT 
            t.*,
            u.username AS bezahlt_bei,
            p.new_paid AS summe,
            p.changed_at AS bezahlt_am
        FROM tickets t
        LEFT JOIN payments p ON p.ticket_id = t.id
        LEFT JOIN user u ON p.user_id = u.id
        WHERE t.id = ?
        ORDER BY p.changed_at DESC
        LIMIT 1"
    );

    if (!$stmt) {
        return ['error' => 'DB_PREPARE_FAILED'];
    }

    $stmt->bind_param('i', $ticketId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['ticket' => null];
    }

    return ['ticket' => $result->fetch_assoc()];
}


/*
|--------------------------------------------------------------------------
| COUNT ENTRANCES
|--------------------------------------------------------------------------
*/

function getEntranceCount($conn, $ticketId)
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS scans FROM entrance WHERE ticket_id = ?"
    );

    if (!$stmt) {
        return ['error' => 'DB_PREPARE_FAILED'];
    }

    $stmt->bind_param('i', $ticketId);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return ['scans' => (int)$result['scans']];
}


/*
|--------------------------------------------------------------------------
| REGISTER ENTRANCE
|--------------------------------------------------------------------------
*/

function registerEntrance($conn, $ticketId, $userId)
{
    $stmt = $conn->prepare(
        "INSERT INTO entrance (ticket_id, user_id) VALUES (?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $ticketId, $userId);

    return $stmt->execute();
}


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (!$code) {
    respond(false, 'Ungültiger Ticketcode', 'INVALID_CODE');
}


/*
|--------------------------------------------------------------------------
| LOAD TICKET
|--------------------------------------------------------------------------
*/

$result = getTicket($conn, $code);

if (isset($result['error'])) {
    respond(false, 'Datenbankfehler beim Laden des Tickets', $result['error']);
}

$ticket = $result['ticket'];

if (!$ticket) {
    respond(false, 'Ticket nicht gefunden', 'TICKET_NOT_FOUND');
}


/*
|--------------------------------------------------------------------------
| CHECK PAYMENT
|--------------------------------------------------------------------------
*/

if ((int)$ticket['confirmed'] === 0) {
    respond(false, 'Ticket ist noch nicht bezahlt', 'TICKET_NOT_PAID', [
        'ticket' => $ticket
    ]);
}


/*
|--------------------------------------------------------------------------
| COUNT SCANS
|--------------------------------------------------------------------------
*/

$countResult = getEntranceCount($conn, $ticket['id']);

if (isset($countResult['error'])) {
    respond(false, 'Fehler beim Prüfen des Einlasses', $countResult['error'], [
        'ticket' => $ticket
    ]);
}

$scans = $countResult['scans'];
$max = (int)$ticket['ticketCount'];


/*
|--------------------------------------------------------------------------
| CHECK LIMIT
|--------------------------------------------------------------------------
*/

if ($scans >= $max) {

    respond(false, 'Alle Personen dieses Tickets sind bereits eingelassen', 'TICKET_FULL', [
        'ticket' => $ticket,
        'scans' => $scans,
        'max' => $max
    ]);
}


/*
|--------------------------------------------------------------------------
| REGISTER ENTRANCE
|--------------------------------------------------------------------------
*/

if (!registerEntrance($conn, $ticket['id'], $userId)) {
    respond(false, 'Fehler beim Registrieren des Einlasses', 'ENTRANCE_INSERT_FAILED', [
        'ticket' => $ticket
    ]);
}


/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

respond(true, 'Einlass erfolgreich registriert', null, [
    'ticket' => $ticket,
    'scans' => $scans + 1,
    'max' => $max
]);