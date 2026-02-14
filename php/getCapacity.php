<?php

/**
 * 
 * ==================================================================================================
 * THIS FILE HANDLES:
 * - THE COLLECTION AND SENDING OF THE CURRENT CAPACITYS FOR AN EVENT
 * ==================================================================================================
 * 
 */

header('Content-Type: application/json');

require_once 'config.php';

$date = json_decode(file_get_contents('php://input'), true);

function getCapacity($conn, $date){
    $getCapacityStatement = $conn->prepare("SELECT SUM(ticketCount) FROM tickets WHERE day = ?");
    $getCapacityStatement->bind_param('s', $date);
    $getCapacityStatement->execute();

    $db_capacity = 0;
    $getCapacityStatement->bind_result($db_capacity);
    $getCapacityStatement->fetch();
    $getCapacityStatement->close();
    
    return $db_capacity;
}

$capacity = getCapacity($conn, $date);

echo json_encode([
    'success' => true,
    'message' => 'Capacity was catched',
    'capacity' => $capacity,
    'date' => $date
]);