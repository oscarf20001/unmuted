<?php
require '../../php/auth.php';
require '../../php/config.php';

$stmt = $conn->prepare("SELECT * FROM tickets ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tickets | Unmuted</title>

<link rel="stylesheet" href="../../styles/main.css">
<link rel="stylesheet" href="../../styles/ticketList.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>

<body>

<header>
    <?php require '../../htmlStructure/header.php'; ?>
</header>

<?php include '../../htmlStructure/account.php'; ?>

<aside>
    <?php require '../../htmlStructure/sidebar.php'; ?>
</aside>

<main id="ticket-container">

<?php if (empty($tickets)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-ticket"></i>
        <p>Keine Tickets vorhanden.</p>
    </div>
<?php endif; ?>

<?php foreach ($tickets as $ticket): ?>
<section class="ticket-card">
    <h2>
        <i class="fa-solid fa-ticket"></i>
        <?= htmlspecialchars($ticket['vorname']) . " " . htmlspecialchars($ticket['nachname']) ?>
    </h2>

    <ol class="ticket-list">
        <li>ID: <span><?= (int)$ticket['id'] ?></span></li>
        <li>Email: <span><?= htmlspecialchars($ticket['email'] ?: '-') ?></span></li>
        <li>Tickets: <span><?= (int)$ticket['ticketCount'] ?></span></li>
        <li>Betrag: 
            <span><?= number_format($ticket['price'], 2, ',', '.') ?> €</span>
        </li>
        <li>Vorstellung:
            <span><?= date('d.m.Y H:i', strtotime($ticket['day'])) ?> Uhr</span>
        </li>
        <li>Gebucht:
            <span><?= date('d.m.Y H:i', strtotime($ticket['booked'])) ?> Uhr</span>
        </li>
    </ol>
</section>
<?php endforeach; ?>

</main>

<footer>
    <?php require '../../htmlStructure/footer.php'; ?>
</footer>

<script>
// Active Sidebar Highlight (robuster)
document.addEventListener("DOMContentLoaded", () => {
    const path = window.location.pathname.split('/').filter(Boolean);
    const last = path[path.length - 1];

    document.querySelectorAll('.navigation-link-element')
        .forEach(el => el.classList.remove('active'));

    const current = document.getElementById('navigation-' + last);
    if(current) current.classList.add('active');
});
</script>

</body>
</html>