import { createAndDisplayMessage } from "./messages.js";

class Ticket {
    constructor(data) {
        this.vorname = data.get('vorname');
        this.nachname = data.get('nachname');
        this.email = data.get('email');
        this.presentation = data.get('dayOfPresentation');

        this.ticketCount = parseInt(data.get('quantity'), 10);
        this.ticketPrice = 10;

        this.price = this.calculatePrice(); // 👈 speichern

        this.visited = this.getSQLTimestamp();
    }
    
    calculatePrice() {
        return this.ticketCount * this.ticketPrice;
    }

    getSQLTimestamp(date = new Date()){
        const pad = n => String(n).padStart(2, '0');

        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ` +
                `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
    }
}

export async function createTicket(data){
    const person = new Ticket(data);
    console.log(person);

    createAndDisplayMessage(true, 'Working on it');

    if(!person){
        console.error("Fehler beim Erstellen der Person");
        return;
    }

    try {
        const handover = await handOverToPHP(person);
        createAndDisplayMessage(handover.success, handover.message);
        clearInputs();
        console.log('PHP Response:', handover);
    } catch(err) {
        console.error('Fehler beim Senden an PHP', err);
        return false;
    }
}

async function handOverToPHP(person){
    try {
        console.log("Waiting for Server-Reponse...");

        const res = await fetch('../php/loadTicketIntoDb.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(person)
        });

        const result = await res.json();
        return result;
    } catch(err) {
        console.error(err);
        throw err; // Optional: wirft Fehler weiter
    }
}

export async function checkCapacity(){
    // 400 is limit for each presentation
    let maxValue = 400;

    let firstShowDate = '11-03-2026 19:00:00';
    let firstShowCapacity = await getCapacity(firstShowDate);
    if(firstShowCapacity.capacity > maxValue){
        blockEvent(firstShowDate);
    }

    let secondShowDate = '12-03-2026 19:00:00';
    let secondShowCapacity = await getCapacity(secondShowDate);
    if(secondShowCapacity.capacity > maxValue){
        blockEvent(secondShowDate);
    }
}

function blockEvent(event){
    console.warn("Blocking Event: " + event);
    const element = document.getElementById(replaceSpaceWithDash(event));
    element.disabled = true;
    element.classList.add('disbaled');
}

function replaceSpaceWithDash(dateTime) {
    return dateTime.replace(/\s+/g, '-');
}

export function clearInputs(){
    let vorname = document.getElementById('vorname');
    let nachname = document.getElementById('nachname');
    let email = document.getElementById('email');

    vorname.value = '';
    nachname.value = '';
    email.value = '';

    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.checked = false;
    });

    document.getElementById('mySelect').value = '1';
}

async function getCapacity(date){
    try {
        console.log("Collecting Capacetiy for event: " + date + " ...");

        const res = await fetch('../php/getCapacity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(date)
        });

        const result = await res.json();
        console.log("Got Capacetiy for event: " + date + ". Current reserved tickets: " + result.capacity);
        return result;
    } catch(err) {
        console.error(err);
        throw err; // Optional: wirft Fehler weiter
    }
}

checkCapacity();