<?php

require_once 'config.php'; // hier deine mysqli Verbindung $mysqli

$data = json_decode(file_get_contents("php://input"), true);

$search_parameter = $data['search_parameter'];
$search_type = $data['search_type'];

switch ($search_type) {
    case 'email':
        # Suche nach Email
        $searchStatement = $conn->prepare("SELECT 
                                                    t.*, 
                                                    COALESCE(p.new_paid, 0) AS current_paid 
                                                    FROM tickets t 
                                                    LEFT JOIN payments p 
                                                    ON p.ticket_id = t.id 
                                                    WHERE t.email = ? 
                                                    ORDER BY p.id DESC
                                                    LIMIT 1;");
        $searchStatement->bind_param('s', $search_parameter);
        break;

    case 'vorname':
        # Suche nach Vorname
        $searchStatement = $conn->prepare("SELECT 
                                                        t.*,
                                                        COALESCE(p.new_paid, 0) AS current_paid
                                                    FROM tickets t
                                                    LEFT JOIN payments p
                                                        ON p.ticket_id = t.id
                                                        AND p.id = (
                                                            SELECT MAX(p2.id)
                                                            FROM payments p2
                                                            WHERE p2.ticket_id = t.id
                                                        )
                                                    WHERE t.vorname LIKE ?;");
        $search_param = "%" . $search_parameter . "%";
        $searchStatement->bind_param("s", $search_param);
        break;

    case 'nachname':
        # Suche nach Nachname
        $searchStatement = $conn->prepare("SELECT 
                                                        t.*,
                                                        COALESCE(p.new_paid, 0) AS current_paid
                                                    FROM tickets t
                                                    LEFT JOIN payments p
                                                        ON p.ticket_id = t.id
                                                        AND p.id = (
                                                            SELECT MAX(p2.id)
                                                            FROM payments p2
                                                            WHERE p2.ticket_id = t.id
                                                        )
                                                    WHERE t.nachname LIKE ?;");
        $search_param = "%" . $search_parameter . "%";
        $searchStatement->bind_param("s", $search_param);
        break;

    case 'id':
        # Suche nach Nachname
        $searchStatement = $conn->prepare("SELECT t.*, COALESCE(p.new_paid, 0) AS new_paid FROM tickets t LEFT JOIN payments p ON p.ticket_id = t.id WHERE t.id = 72 ORDER BY p.id DESC LIMIT 1;");
        $searchStatement->bind_param("i", $search_param);
        break;
    
    default:
        # Ungültige Suche
        echo json_encode(['success' => false, 'message' => 'Ungülgtige Suchanfrage. Bitte Parameter abändern.']);
        exit;
}

$searchStatement->execute();
$result = $searchStatement->get_result();

$tickets = [];

while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

if(count($tickets) < 1){
    echo json_encode([
        'success' => false,
        'message' => 'Keine Personen für diese Suchanfrage gefunden'
    ]);

    exit;
}

echo json_encode([
    'success' => true,
    'count' => count($tickets),
    'data' => $tickets
]);

exit;