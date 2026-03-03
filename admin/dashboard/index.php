<?php
require '../../php/auth.php';
require '../../php/config.php';

/* =========================
   HISTOGRAMM
========================= */
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
    $labels[] = $date->format('d.m');
    $data[] = (int) $row['anzahl'];
}
$historyTicketsStmt->close();

/* =========================
   Tickets pro Veranstaltungstag
========================= */
$ticketsStmt = $conn->prepare("
    SELECT SUM(ticketCount) AS total, 
           LEFT(day, 10) AS buchungstag 
    FROM tickets 
    GROUP BY LEFT(day, 10) 
    ORDER BY buchungstag
");
$ticketsStmt->execute();
$result = $ticketsStmt->get_result();

$ticketCountPerDay = [];
while ($row = $result->fetch_assoc()) {
    $ticketCountPerDay[] = $row;
}
$ticketsStmt->close();

/* =========================
   Finanzen pro Veranstaltungstag
========================= */
$priceStmt = $conn->prepare("
    SELECT SUM(price) AS total, 
           LEFT(day, 10) AS buchungstag 
    FROM tickets 
    GROUP BY LEFT(day, 10) 
    ORDER BY buchungstag
");
$priceStmt->execute();
$result = $priceStmt->get_result();

$pricePerDay = [];
while ($row = $result->fetch_assoc()) {
    $pricePerDay[] = $row;
}
$priceStmt->close();

/* =========================
   Gesamt Finanzen
========================= */
$overallStmt = $conn->prepare("SELECT SUM(price) AS overallPrices FROM tickets;");
$overallStmt->execute();
$result = $overallStmt->get_result();
$row = $result->fetch_assoc();
$overallPrices = $row['overallPrices'] ?? 0;
$overallStmt->close();

/* =========================
   Durchschnitt
========================= */
$totalTickets = array_sum($data);
$totalDays = count($data);
$averagePerDay = $totalDays > 0 ? $totalTickets / $totalDays : 0;
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Unmuted</title>

    <link rel="stylesheet" href="../../styles/main.css">
    <link rel="stylesheet" href="../../styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <header><?php require '../../htmlStructure/header.php'; ?></header>
    <?php include '../../htmlStructure/account.php'; ?>
    <aside><?php require '../../htmlStructure/sidebar.php'; ?></aside>

    <main style="display:flex;justify-content:center;align-items:center;">
        <ol>

            <!-- Histogramm -->
            <li>
                <div>Histogramm Ticketverkauf pro Tag</div>
                <canvas id="ticketHistogram" style="width:1200px;height:600px;"></canvas>
            </li>

            <!-- Durchschnitt -->
            <li>
                <div>Durchschnittliche Tickets pro Tag</div>
                <div><?= number_format($averagePerDay, 0, ',', '.') ?> Tickets / Tag</div>
            </li>

            <?php
            $max = 350;
            for ($i = 0; $i < count($ticketCountPerDay); $i++):
                $sold = (int) ($ticketCountPerDay[$i]['total'] ?? 0);
                $remaining = max($max - $sold, 0);
                $date = $ticketCountPerDay[$i]['buchungstag'];
                ?>

                <li>
                    <div><?= date('d.m', strtotime($date)) ?> verkaufte Tickets</div>
                    <canvas id="ticketBar<?= $i ?>" width="400" height="80"></canvas>
                </li>

                <li>
                    <div><?= date('d.m', strtotime($date)) ?> Finanzen</div>
                    <div><?= number_format($pricePerDay[$i]['total'] ?? 0, 2, ',', '.') ?> €</div>
                </li>

            <?php endfor; ?>

            <!-- Gesamt Tickets -->
            <li>
                <div>Gesamt verkaufte Tickets</div>
                <canvas id="ticketBarWhole" width="400" height="80"></canvas>
            </li>

            <!-- Gesamt Finanzen -->
            <li>
                <div>Gesamt Finanzen</div>
                <div><?= number_format($overallPrices, 2, ',', '.') ?> €</div>
            </li>

        </ol>
    </main>

    <footer><?php require '../../htmlStructure/footer.php'; ?></footer>

    <script>
        /* Histogramm */
        new Chart(document.getElementById('ticketHistogram'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    data: <?= json_encode($data) ?>,
                    backgroundColor: '#FFC300'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        /* Tages-Balken */
        const maxTickets = 350;
        <?php for ($i = 0; $i < count($ticketCountPerDay); $i++):
            $sold = (int) $ticketCountPerDay[$i]['total'];
            ?>
            new Chart(document.getElementById('ticketBar<?= $i ?>'), {
                type: 'bar',
                data: {
                    labels: ['Tickets'],
                    datasets: [
                        {
                            data: [<?= $sold ?>],
                            backgroundColor: '#FFC300',
                            borderRadius: 8
                        },
                        {
                            data: [maxTickets - <?= $sold ?>],
                            backgroundColor: '#3a0f14',
                            borderRadius: 8
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: false,
                    scales: {
                        x: { stacked: true, min: 0, max: maxTickets, display: false },
                        y: { stacked: true, display: false }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                },
                plugins: [{
                    id: 'centerLabel<?= $i ?>',
                    afterDraw(chart) {
                        const { ctx, chartArea } = chart;
                        const x = chartArea.left + chartArea.width / 2;
                        const y = chartArea.top + chartArea.height / 2 + 4;

                        ctx.save();
                        ctx.font = 'bold 16px Arial';
                        ctx.fillStyle = '#ffffff';
                        ctx.textAlign = 'center';
                        ctx.fillText('<?= $sold ?> / ' + maxTickets + ' Tickets', x, y);
                        ctx.restore();
                    }
                }]
            });
        <?php endfor; ?>

        /* Gesamt */
        const soldWhole = <?= array_sum(array_column($ticketCountPerDay, 'total')) ?>;
        const maxWhole = maxTickets * <?= count($ticketCountPerDay) ?>;

        new Chart(document.getElementById('ticketBarWhole'), {
            type: 'bar',
            data: {
                labels: ['Tickets'],
                datasets: [
                    {
                        data: [soldWhole],
                        backgroundColor: '#4CAF50',
                        borderRadius: 8
                    },
                    {
                        data: [maxWhole - soldWhole],
                        backgroundColor: '#3a0f14',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: false,
                scales: {
                    x: { stacked: true, min: 0, max: maxWhole, display: false },
                    y: { stacked: true, display: false }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            },
            plugins: [{
                id: 'centerLabelWhole',
                afterDraw(chart) {
                    const { ctx, chartArea } = chart;
                    const x = chartArea.left + chartArea.width / 2;
                    const y = chartArea.top + chartArea.height / 2 + 4;

                    ctx.save();
                    ctx.font = 'bold 16px Arial';
                    ctx.fillStyle = '#ffffff';
                    ctx.textAlign = 'center';
                    ctx.fillText(soldWhole + ' / ' + maxWhole + ' Tickets', x, y);
                    ctx.restore();
                }
            }]
        });
    </script>

</body>

</html>