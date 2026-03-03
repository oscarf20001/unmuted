<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? '/admin/dashboard/';

// Sicherheitscheck Redirect
if (strpos($redirect, '/') !== 0) {
    $redirect = '/admin/dashboard/';
}

// User laden
$stmt = $conn->prepare("SELECT id, hashed_password FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

//if ($user && password_verify($password, $user['hashed_password'])) {
if ($user && sha1($password) == $user['hashed_password']) {

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['LAST_ACTIVITY'] = time();

    echo json_encode([
        'success' => true,
        'redirect' => $redirect
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Ungültige Zugangsdaten.'
    ]);
}