<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Timeout (30 Minuten)
$timeout = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && 
   (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {

    session_unset();
    session_destroy();
}

$_SESSION['LAST_ACTIVITY'] = time();

// Nicht eingeloggt → Redirect
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}