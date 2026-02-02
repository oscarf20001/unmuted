import { createTicket } from "./ticket.js";

const form = document.getElementById('ticketWrapper');

form.addEventListener('submit', (event) => {
    event.preventDefault(); // verhindert Reload
    console.log("✅ Form ready to be send!");

    const formData = new FormData(form);
    console.log("🔜 Creating Ticket");
    createTicket(formData)
});