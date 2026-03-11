<?php
require '../../php/auth.php';
require '../../php/config.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page-Title | Unmuted</title>
    <link rel="stylesheet" href="../../styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>   <!-- Font Awesome -->
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
        <div id="scanner">
            <form action="" id="barcodeForm">
                <input type="text" name="" id="scanner-inputField" autofocus>
                <input type="submit" value="Suchen">
            </form>
        </div>
        <div id="feedback">

        </div>
        <div id="action"></div>
    </main>
    <footer>
        <?php
            require '../../htmlStructure/footer.php';
        ?>
    </footer>

    <script>
        function autofocusBarcodeInput(){
            barcodeInputField.focus();
            barcodeInputField.select();
        }
        
        function clearBarcodeInput(){
            barcodeInputField.value = '';
        }

        function getBarcode(){
            return barcodeInputField.value;
        }

        function extractCode(code){
            const last4 = code.slice(-4);
            return parseInt(last4, 10);
        }

        function formatDate(dateString) {
            const d = new Date(dateString);
            return d.toLocaleString('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatPrice(price) {
            return Number(price).toLocaleString('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderTicket(ticket, scans, max) {
    const feedbackContainer = document.getElementById('feedback');
    const entranceRow = ticket.eingelassen
        ? `<li>Einlass: <span>Bereits eingelassen</span></li>`
        : '';

    const html = `
    <section class="ticket-card">
        <h2>
            <i class="fa-solid fa-ticket"></i>
            ${escapeHtml(ticket.vorname)} ${escapeHtml(ticket.nachname)}
        </h2>

        <ol class="ticket-list">
            <li>ID: <span>${ticket.id}</span></li>
            <li>Email: <span>${ticket.email ? escapeHtml(ticket.email) : '-'}</span></li>
            <li>Status: <span>${ticket.confirmed == 1 ? 'Bezahlt' : 'Nicht bezahlt'}</span></li>
            <li>Betrag: <span>${ticket.summe ? formatPrice(ticket.summe) + ' €' : '-'}</span></li>
            <li>Bezahlt am: <span>${ticket.bezahlt_am ? formatDate(ticket.bezahlt_am) + ' Uhr' : '-'}</span></li>
            <li>Bezahlt bei: <span>${ticket.bezahlt_bei ? escapeHtml(ticket.bezahlt_bei) : '-'}</span></li>
            <li>Einlass: <span>${scans} / ${max}</span></li>
            ${entranceRow}
        </ol>
    </section>
    `;

    feedbackContainer.innerHTML = html;
}

        async function fetchData(code){
            try {
                const res = await fetch('../../php/fetchEntranceTicket.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });

                const result = await res.json();
                return result;

            } catch(err) {
                console.error(err);
                throw err;
            }
        }
        
        let barcodeForm = document.getElementById('barcodeForm');
        let barcodeInputField = document.getElementById('scanner-inputField');

        window.onload = function(){
            autofocusBarcodeInput();
        };

        barcodeForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // verhindert Reload
            let rawCode = getBarcode();
            let code = extractCode(rawCode);

            try {
                let result = await fetchData(code);

                console.log(result);
                console.log(Boolean(result.data));
                console.log(Boolean(result.data.ticket));

                if (result.data && result.data.ticket) {
                    renderTicket(result.data.ticket, result.data.scans, result.data.max);
                    console.log(result.data.ticket);
                }
            } catch (error) {
                
            }

            clearBarcodeInput();
            autofocusBarcodeInput();
        });
    </script>
</body>
</html>