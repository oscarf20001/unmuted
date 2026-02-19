import { createAndDisplayMessage } from "../../javascript/messages.js";
import { deactiveAll } from "../../javascript/messages.js";

// Deactive all Messages from MessageBox onload
deactiveAll();

function checkPaymentSearchFormular(data){
    if(!data.get('search_parameter')){
        createAndDisplayMessage(false, 'Ungültiger Suchparameter!');
        return false;
    }

    if(!data.get('search_type')){
        createAndDisplayMessage(false, 'Ungültiger Suchtyp!');
        return false;
    }

    console.log('✅ Valid Formular!');
    return true;
}

function checkExecutePaymentFormular(data, ticketId){
    if(!data.get('paymentMethod')){
        createAndDisplayMessage(false, 'Ungültige Zahlungsmethode!');
        return false;
    }

    if(!data.get('receivedMoney')){
        createAndDisplayMessage(false, 'Ungültiger Betrag!');
        return false;
    }

    if(ticketId == 0){
        createAndDisplayMessage(false, 'Ungültiges Ticket!');
        return false;
    }

    console.log('✅ Valid Formular!');
    return true;
}

async function searchForPerson(data){
    try {
        console.log("Waiting for Server-Reponse...");

        const res = await fetch('../../php/searchPerson.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await res.json();
        return result;
    } catch(err) {
        throw err; // Optional: wirft Fehler weiter
    }
}

function clearInputs(section){
    switch (section) {
        case 'payment':
            
            break;

        case 'search':
            let param = document.getElementById('search_parameter');
            let typ = document.getElementById('search_type');

            param.value = '';
            typ.value = '';

            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.checked = false;
            });

            document.getElementById('search_type').value = '1';

            break;
    
        default:
            console.error('No defined area to clear the inputs off');
            break;
    }
}

function createTicketCards(count, tickets, textId){
    const container = document.getElementById('ticketContainer');
    clearTicketContainer(container);

    switch (count) {
        case 1:
            textId.innerHTML = '<span>' + count + '</span> Ticket gefunden!';
            break;
    
        default:
            textId.innerHTML = '<span>' + count + '</span> Tickets gefunden!';
            break;
    }

    tickets.forEach(ticket => {

        const ticketHTML = `
            <section class="ticket-card" id="ticket-card-${ticket.id}">
                <h2>
                    <i class="fa-solid fa-ticket"></i>
                    ${ticket.vorname} ${ticket.nachname}
                </h2>

                <ol class="ticket-list">
                    <li><p>ID: <span>${ticket.id}</span></p></li>
                    <li><p>Email: <span>${ticket.email}</span></p></li>
                    <li><p>Betrag: <span>${ticket.price}€</span></p></li>
                    <li><p>Vorstellungstag:
                        <span>${ticket.day} Uhr</span>
                    </p></li>
                    <li><p>Bezahlt:
                        <span>${ticket.current_paid} Uhr</span>
                    </p></li>
                    <li><p>Confirmed:
                        <span>${ticket.confirmed} Uhr</span>
                    </p></li>
                </ol>

                <div class="selectForPayment">
                    <input 
                        type="checkbox"
                        id="selectForPayment-${ticket.id}"
                        class="payment-checkbox"
                    >
                    <label for="selectForPayment-${ticket.id}">
                        bestätigen
                    </label>
                </div>

            </section>
        `;

        container.insertAdjacentHTML('beforeend', ticketHTML);

        activeCheckboxListener();
        showPaymentForm();
    });

    // Checkboxes
    const checkboxes = document.querySelectorAll('.checkbox');
    checkboxes.forEach(element => {
        element.addEventListener('click', () => {
            deactiveAllCheckboxes();
            activeCurrentCheckbox(element.id);
        });
    });
}

function activeCheckboxListener(){
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    checkboxes.forEach(element => {
        element.addEventListener('click', ()=>{
            deactiveAllCheckboxes(checkboxes);
            activateCurrentCheckbox(element.id);
        });
    });
}

function deactiveAllCheckboxes(elements){
    elements.forEach(element => {
        element.checked = false;
    });
}

function activateCurrentCheckbox(id){
    document.getElementById(id).checked = true;
}

function getSelectedCheckbox() {
    // Holt alle angekreuzten Checkboxen
    const checkedBoxes = document.querySelectorAll('.payment-checkbox:checked');

    if (checkedBoxes.length === 0) {
        console.log("Keine Checkbox ausgewählt");
        return 0;
    }

    if (checkedBoxes.length > 1) {
        console.warn("Mehr als eine Checkbox ist ausgewählt! Nur die erste wird zurückgegeben.");
    }

    // Nimm die erste angekreuzte Checkbox
    const selectedCheckbox = checkedBoxes[0];

    // Finde die zugehörige Ticket-Karte
    const ticketCard = selectedCheckbox.closest('.ticket-card');

    // Hole die Ticket-ID (aus dem ersten <li><span> der Liste)
    const ticketId = ticketCard.querySelector('.ticket-list li span').textContent;

    return ticketId;
}

function showPaymentForm(){
    document.getElementById('takePaymentContainer').style.display = 'flex';
}

function clearTicketContainer(parentElement){
    parentElement.innerHTML = ''; // vorherige Ergebnisse löschen
    document.getElementById('ticketCountResponseText').innerHTML = '<span></span>';
}

function hidePaymentForm(){
    document.getElementById('takePaymentContainer').style.display = 'none';
    clearPaymentForm();
}

function clearPaymentForm(){
    let betragInput = document.getElementById('receivedMoney');
    let methodInput = document.getElementById('paymentMethod');

    betragInput.value = '';
    methodInput.value = '';
}

async function executeTransaction(data){
    try {
        console.log("Waiting for Server-Reponse...");

        const res = await fetch('../../php/executeTransaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await res.json();
        return result;
    } catch(err) {
        throw err; // Optional: wirft Fehler weiter
    }
}

const searchForm = document.getElementById('searchWrapper');

searchForm.addEventListener('submit', async (event) => {
    event.preventDefault(); // verhindert Reload
    console.log("✅ Form ready to be send!");

    const formData = new FormData(searchForm);
    console.log("🔜 Checking Formular...");

    createAndDisplayMessage(true, 'Searching for Person...');

    if(!!checkPaymentSearchFormular(formData)){
        console.log('🔜 Searching for Person...');

        let data = {
            search_parameter: formData.get('search_parameter'),
            search_type: formData.get('search_type')
        };

         try {
            const handover = await searchForPerson(data);
            clearInputs('search');
            console.log('PHP Response:', handover);

            // Validieren der Antwort
            let responseText = document.getElementById('ticketCountResponseText');

            if(handover.success == false){
                responseText.innerHTML = '<span>0</span> Tickets gefunden. Bitte passe deine Suche an!';
                clearTicketContainer(document.getElementById('ticketContainer'));
                hidePaymentForm();
                return;
            }

            createTicketCards(handover.count, handover.data, responseText);
            return;

        } catch(err) {
            createAndDisplayMessage(false, 'Serverfehler: ' + err);
            console.error('Fehler beim Senden an PHP', err);
            return false;
        }
    }

    createAndDisplayMessage(false, 'Ungültiges Formular');
    console.log("❌ Invalid Formular - Aborting");
    return;
});

const paymentForm = document.getElementById('takePaymentContainer');

paymentForm.addEventListener('submit', async (event) => {
    console.log("================ PAYMENT TRANSACTION ================");
    event.preventDefault(); // verhindert Reload
    console.log("✅ Form ready to be send!");

    const formData = new FormData(paymentForm);
    console.log("🔜 Checking Formular...");

    const selectedTicketId = getSelectedCheckbox();
    if(!!checkExecutePaymentFormular(formData, selectedTicketId)){

        createAndDisplayMessage(true, 'Trying to execute payment and mail delivery!');

        let data = {
            ticketId: parseInt(selectedTicketId),
            money: parseInt(formData.get('receivedMoney')),
            method: formData.get('paymentMethod')
        };

        const h2 = document.querySelector('#ticket-card-' + selectedTicketId + ' h2').textContent;
        const vorname = h2.trim();
        confirm('Transaktion für "' + vorname + '" mit '+ formData.get('receivedMoney') +'€ bestätigen?');

         try {
            const handover = await executeTransaction(data);
            clearInputs('payment');
            console.log('PHP Response:', handover);

            // Nachricht anzeigen, dass alles geklappt hat: 
            createAndDisplayMessage(true, handover.message);

            // Payment-Form löschen und nicht mehr anzeigen:
            hidePaymentForm();

            // Ticket Container leeren:
            clearTicketContainer(document.getElementById('ticketContainer'));

            return;
        } catch(err) {
            createAndDisplayMessage(false, 'Fehler beim Ausführen des Bezahlvorgangs: ' + err);
            console.error('Fehler beim Senden an PHP', err);
            return false;
        }
    }
    createAndDisplayMessage(false, 'Keine Checkbox ausgewählt!')
    console.log("❌ Invalid Formular - Aborting");
    return;
});