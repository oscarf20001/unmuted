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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>   <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <header>
        <?php
            require '../../htmlStructure/header.php';
        ?>
    </header>
    <aside>
        <?php
            require '../../htmlStructure/sidebar.php';
        ?>
    </aside>
    <main style="display:flex;justify-content:center;align-items:center;">
        <ol>
            <li>
                <div>Histogramm Ticketverkaufhistorie</div>
                <?php
                    $historyTicketsStmt = $conn->prepare("
                        SELECT DATE(booked) AS buchungstag, 
                            SUM(ticketCount) AS anzahl 
                        FROM tickets 
                        GROUP BY DATE(booked) 
                        ORDER BY buchungstag
                    ");
                    $historyTicketsStmt->execute();
                    $result = $historyTicketsStmt->get_result();

                    $labels = [];
                    $data = [];

                    while ($row = $result->fetch_assoc()) {
                        $date = new DateTime($row['buchungstag']);
                        $labels[] = $date->format('d.m'); // z.B. 28.02
                        $data[] = (int)$row['anzahl'];
                    }

                    $historyTicketsStmt->close();
                ?>

                <div class="histogram">
                    <canvas id="ticketHistogram" style="max-width: 600px; height: 300px;"></canvas>
                    <script>
                        const ctx = document.getElementById('ticketHistogram').getContext('2d');

                        const ticketHistogram = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode($labels) ?>,
                                datasets: [{
                                    label: 'Tickets pro Tag',
                                    data: <?= json_encode($data) ?>,
                                    backgroundColor: '#3498db',
                                    borderColor: '#2980b9',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.parsed.y + ' Tickets';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Anzahl Tickets'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Datum'
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </li>
            <li>
                <div>Durchschnittliche Tickets pro Tag</div>
                <div>
                    <?php
                        $totalTickets = array_sum($data);        // Summe aller Ticketzahlen
                        $totalDays = count($data);               // Anzahl der Tage

                        $averagePerDay = $totalDays > 0
                            ? $totalTickets / $totalDays
                            : 0;
                    ?>

                    <?= number_format($averagePerDay, 0, ',', '.') ?> Tickets / Tag
                </div>
            </li>
            <li>
                <div>11.03 verkaufte Tickets</div>
                <div>
                    <canvas id="ticketGauge" width="400" height="200"></canvas>

                    <?php
                        $tickets11Stmt = $conn->prepare('SELECT SUM(ticketCount) AS total, LEFT(day, 10) AS buchungstag FROM tickets GROUP BY LEFT(day, 10) ORDER BY buchungstag;');
                        $tickets11Stmt->execute();
                        $result = $tickets11Stmt->get_result();

                        $ticketCountPerDay = [];
                        while ($row = $result->fetch_assoc()) {
                            $ticketCountPerDay[] = $row;
                        }
                    ?>

                   <script>
                        const sold = <?= (int)$ticketCountPerDay[0]['total'] ?>;
                        const maxTickets = 400;
                        const remaining = Math.max(maxTickets - sold, 0);

                        const gauge = document.getElementById('ticketGauge').getContext('2d');

                        new Chart(gauge, {
                            type: 'doughnut',
                            data: {
                                datasets: [{
                                    data: [sold, remaining],
                                    backgroundColor: [
                                        sold < 150 ? '#FF4C4C' : sold < 300 ? '#FFC300' : '#4CAF50',
                                        '#3a0f14'
                                    ],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                rotation: -Math.PI,
                                circumference: Math.PI,
                                cutout: '75%',
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { enabled: false }
                                }
                            },
                            plugins: [{
                                id: 'centerText',
                                afterDraw(chart) {
                                    const { ctx, chartArea } = chart;
                                    const centerX = (chartArea.left + chartArea.right) / 2;
                                    const centerY = chartArea.bottom - 20;

                                    ctx.save();
                                    ctx.font = 'bold 22px Arial';
                                    ctx.fillStyle = '#fff';
                                    ctx.textAlign = 'center';
                                    ctx.fillText(`${sold} / ${maxTickets}`, centerX, centerY);
                                    ctx.font = '14px Arial';
                                    ctx.fillText('Tickets verkauft', centerX, centerY + 20);
                                    ctx.restore();
                                }
                            }]
                        });
                    </script>
                </div>
            </li>
            <li>
                <div>11.03 Finanzen</div>
                <div>
                    <?php
                        $tickets11Stmt = $conn->prepare('SELECT SUM(price) AS total, LEFT(day, 10) AS buchungstag FROM tickets GROUP BY LEFT(day, 10) ORDER BY buchungstag;');
                        $tickets11Stmt->execute();
                        $result = $tickets11Stmt->get_result();

                        $pricePerDay = [];
                        while ($row = $result->fetch_assoc()) {
                            $pricePerDay[] = $row;
                        }
                    ?>

                    <?= $pricePerDay[0]['total'] ?>€
                </div>
            </li>
            <li>
                <div>12.03 verkaufte Tickets</div>
                <div><?= $ticketCountPerDay[1]['total'] ?></div>
            </li>
            <li>
                <div>12.03 Finanzen</div>
                <div><?= $pricePerDay[1]['total'] ?>€</div>
            </li>
            <li>
                <div>Gesamt verkaufte Tickets</div>
                <div>
                    <?php
                        $overallTicketsStmt = $conn->prepare('SELECT SUM(ticketCount) AS overalLTickets FROM tickets;');
                        $overallTicketsStmt->execute();
                        $result = $overallTicketsStmt->get_result();

                        $row = $result->fetch_assoc(); // fetch_assoc() holen
                        $overallTickets = $row['overalLTickets'];

                    ?>
                    <?= $overallTickets ?>
                </div>
            </li>
            <li>Gesamt Finanzen
                <div></div>
                <div>
                    <?php
                        $overallFinances = $conn->prepare('SELECT SUM(price) AS overallPrices FROM tickets;');
                        $overallFinances->execute();
                        $result = $overallFinances->get_result();

                        $row = $result->fetch_assoc(); // fetch_assoc() holen
                        $overallPrices = $row['overallPrices'];

                    ?>
                    <?= $overallPrices ?>€
                </div>
            </li>
        </ol>
    </main>
    <footer>
        <?php
            require '../../htmlStructure/footer.php';
        ?>
    </footer>
</body>
</html>

<?php
else:
header('Location: ../../login/');
endif;
?>