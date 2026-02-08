import { deactiveAll } from "./messages.js";
import { createTicket } from "./ticket.js";

const form = document.getElementById('ticketWrapper');

// Deactive all Messages from MessageBox onload
deactiveAll();

form.addEventListener('submit', (event) => {
    event.preventDefault(); // verhindert Reload
    console.log("✅ Form ready to be send!");

    const formData = new FormData(form);
    console.log("🔜 Creating Ticket");
    createTicket(formData)
});