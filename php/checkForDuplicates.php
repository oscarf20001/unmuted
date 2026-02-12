<?php

function checkDuplicate($conn, $vorname, $nachname, $day)
{
    $checkDuplicateStatement = $conn->prepare("SELECT * FROM tickets WHERE vorname = ? AND nachname = ? AND day = ?");
    if (!$checkDuplicateStatement) {
        $response = [
            'success' => false,
            'message' => 'Prepared Statement failed during check of Duplicates: ' . $conn->error
        ];
        return $response;
    }

    if (!$checkDuplicateStatement->bind_param('sss', $vorname, $nachname, $day)) {
        $response = [
            'success' => false,
            'message' => 'Binding of Params failed during check of Duplicates: ' . $conn->error
        ];
        return $response;
    }

    if (!$checkDuplicateStatement->execute()) {
        $response = [
            'success' => false,
            'message' => 'Execution of Statement failed during check of Duplicates: ' . $conn->error
        ];
        return $response;
    }

    $result = $checkDuplicateStatement->get_result();

    if ($result->num_rows > 0) {
        // Es gibt bereits mindestens 1 Eintrag
        return [
            'success' => false,
            'message' => 'Ein Ticket für diese Person und diesen Tag existiert bereits',
            'vorname' => $vorname,
            'nachname' => $nachname
        ];
    }

    // Kein Duplikat → Insert kann erfolgen

    $response = [
        'success' => true,
        'message' => 'Kein Doppelten Eintrag gefunden'
    ];
    return $response;
}

$duplicateCheck = checkDuplicate($conn, $vorname, $nachname, $day);

if (!$duplicateCheck['success']) {
    fail($duplicateCheck['message'], $mailHost, $mailUsername, $mailPassword, $mailPort, $duplicateCheck['vorname'], $duplicateCheck['nachname'], null);
}