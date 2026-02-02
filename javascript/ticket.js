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

    if(!person){
        console.error("Fehler beim Erstellen der Person");
        return;
    }

    try {
        const handover = await handOverToPHP(person);
        console.log('PHP Response:', handover);
        return handover;
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