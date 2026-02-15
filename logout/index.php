<?php
// Session starten, falls noch nicht geschehen
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Alle Session-Variablen löschen
$_SESSION = [];

// Session-Cookie löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Session zerstören
session_destroy();

// Optional: Zurück zur Login-Seite oder Startseite
header("Location: ../");
exit;