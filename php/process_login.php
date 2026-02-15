<?php
session_start();
require_once 'config.php'; // hier deine mysqli Verbindung $mysqli

// POST-Daten holen und trimmen
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Bitte alle Felder ausfüllen.']);
    exit;
}

// SHA-1 Passwort-Hash erstellen
$passwordHash = sha1($password);

// Prepared Statement
$stmt = $conn->prepare("SELECT id, username FROM user WHERE username = ? AND hashed_password = ?");
$stmt->bind_param("ss", $username, $passwordHash);
$stmt->execute();
$result = $stmt->get_result();

if($user = $result->fetch_assoc()){
    // Login erfolgreich -> Session setzen
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Benutzername oder Passwort falsch.']);
}

$stmt->close();
$conn->close();
exit;
