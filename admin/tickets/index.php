<?php
session_start();
require '../../php/config.php';

if (isset($_SESSION['user_id'])):
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page-Title | Unmuted</title>
    <link rel="stylesheet" href="../../styles/main.css">
    <link rel="stylesheet" href="../../styles/ticketList.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>   <!-- Font Awesome -->
</head>
<body>
    <header>
        <?php
            require '../../htmlStructure/header.php';
        ?>
    </header>
    <div>
        <?php
            include '../../htmlStructure/account.php';
        ?>
    </div>
    <aside>
        <?php
            require '../../htmlStructure/sidebar.php';
        ?>
    </aside>
    <main id="ticket-container">
        <?php
        
        $stmt = $conn->prepare("SELECT * FROM tickets ORDER BY id DESC");
        $stmt->execute();

        $result = $stmt->get_result();
        $tickets = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        foreach ($tickets as $ticket): ?>
            <section class="ticket-card">
                <h2>
                    <i class="fa-solid fa-ticket"></i>
                    <?= htmlspecialchars($ticket['vorname']) . " " . htmlspecialchars($ticket['nachname']) ?>
                </h2>

                <ol class="ticket-list">
                    <li>
                        <p>ID: <span><?= (int)$ticket['id'] ?></span></p>
                    </li>
                    <li>
                        <p>Email: <span><?= htmlspecialchars($ticket['email']) ?></span></p>
                    </li>
                    <li>
                        <p>Tickets: <span><?= (int)$ticket['ticketCount'] ?></span></p>
                    </li>
                    <li>
                        <p>Betrag: <span><?= number_format($ticket['price'], 2, ',', '.') ?>€</span></p>
                    </li>
                    <li>
                        <p>Vorstellungstag:
                            <span>
                                <?= date('d.m.Y H:i', strtotime($ticket['day'])) ?> Uhr
                            </span>
                        </p>
                    </li>
                    <li>
                        <p>Gebucht:
                            <span>
                                <?= date('d.m.Y H:i', strtotime($ticket['booked'])) ?> Uhr
                            </span>
                        </p>
                    </li>
                </ol>
            </section>
        <?php endforeach; ?>

    </main>
    <footer>
        <?php
            require '../../htmlStructure/footer.php';
        ?>
    </footer>

    <script>
        const siteLinks = document.querySelectorAll('.navigation-link-element');
        siteLinks.forEach(element => {
            element.classList.remove('active');
        });

        const url = window.location.href;
        const parts = url.replace(/\/$/, '').split('/');
        const result = parts[parts.length - 1];

        const currentLink = document.getElementById('navigation-' + result);
        currentLink.classList.add('active');
    </script>
</body>
</html>

<?php
else:
header('Location: ../../login/');
endif;
?>