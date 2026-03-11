import { createAndDisplayMessage } from "./messages.js";

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

function renderTicket(ticket, scans, max, error) {
    const feedbackContainer = document.getElementById('feedback');
    let entranceRow = '';

    if(error == 'TICKET_FULL'){
        entranceRow = `<li>Einlass: <span>Bereits eingelassen</span></li>`  
    }

    const html = `
    <section class="ticket-card">
        <h2>
            <i class="fa-solid fa-ticket"></i>
            ${escapeHtml(ticket.vorname)} ${escapeHtml(ticket.nachname)}
        </h2>

        <ol class="ticket-list">
            <li>ID: <span>${ticket.id}</span></li>
            <li>Vorstellungstag: <span>${ticket.day == '11-03-2026 19:00:00' ? 'Mittwoch' : 'Donnerstag'}</span></li>
            <li>Email: <span>${ticket.email ? escapeHtml(ticket.email) : '-'}</span></li>
            <li>Besätigungsmail am: <span>${ticket.confirmed ? escapeHtml(ticket.confirmed) : '-'}</span></li>
            <li>Status: <span>${ticket.confirmed == '0000-00-00 00:00:00' ? 'Nicht bezahlt' : 'Bezahlt'}</span></li>
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
    const feedbackContainer = document.getElementById('feedback');
    feedbackContainer.innerHTML = '';
    event.preventDefault(); // verhindert Reload
    let rawCode = getBarcode();
    let code = extractCode(rawCode);

    try {
        let result = await fetchData(code);

        console.log(result);

        if (result.data && result.data.ticket) {
            renderTicket(result.data.ticket, result.data.scans, result.data.max, result.error);
            console.log(result.data.ticket);
        }

        if(!!result.error){
            createAndDisplayMessage(false, result.error);
        }
    } catch (error) {
        console.log('Einlasssystem-Fehler: ' + error);
    }

    clearBarcodeInput();
    autofocusBarcodeInput();
});