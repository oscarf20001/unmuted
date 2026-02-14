<?php
$email   = filter_input(INPUT_GET, 'email');
$tickets = filter_input(INPUT_GET, 'tickets', FILTER_VALIDATE_INT);

if (!$email || !$tickets) {
    $email = 'email@provider';
    $tickets = 'ticket_Anzahl';
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Zahlungsinformationen</title>
  <link rel="stylesheet" href="../styles/payment.css">
</head>
<body>
  <main class="card">
    <header>
      <div class="logo">€</div>
      <div>
        <h1>Zahlungsinformationen</h1>
        <div class="subtitle">Musical Unmuted</div>
      </div>
    </header>

    <div class="content">
      <section class="section">
        <h2>Bezahlung via PayPal</h2>

        <div class="row">
          <div class="label">Empfänger</div>
          <div class="value">PayPal: stark.eventsolution@gmail.com</div>
        </div>

        <div class="row">
          <div class="label">Verwendungszweck</div>
          <div class="value"><?= $email . "+" . $tickets?>+MusicalUnmuted</div>
        </div>

        <p class="hint">
          Bitte den <strong>Verwendungszweck exakt</strong> wie angegeben übernehmen,
          damit die Zahlung korrekt zugeordnet werden kann.
        </p>
        <br>
        <h2>Bezahlung via Bargeld</h2>

        <p class="hint">
          Hier steht, ab wann man in der Schule wo bezahlen kann!
        </p>
      </section>

      <section class="qr-wrapper">
        <h3>PayPal QR-Code</h3>
        <!-- Beispiel-QR-Code. Ersetze den Link im src-Attribut bei Bedarf -->
        <img
            class="qr"
            src="../img/paypal.jpeg"
            alt="PayPal QR-Code"
        />
        <div class="hint">Mit der PayPal-App scannen</div>
      </section>
    </div>

    <div class="footer-note">
      Vielen Dank für deine Unterstützung ❤️
    </div>
  </main>
</body>
</html>