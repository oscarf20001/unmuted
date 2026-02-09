// Defining the needed variables
const container = document.getElementById('messageContainer');
const text = document.getElementById('message-text');
const successLight = document.getElementById('successLight-true');
const failLight = document.getElementById('successLight-false');

export function createAndDisplayMessage(success, message){
    // Set correct Feedback
    if(!!success){
        // Icons
        successLight.style.display = 'flex';
        failLight.style.display = 'none';

        // Hintergrund
        container.classList.add('success');
        container.classList.remove('fail');

        showToast("success", "Deine Ticketbestätigung ist eingegangen.", 3200);
    }else{
        // Icons
        successLight.style.display = 'none';
        failLight.style.display = 'flex';

        // Hintergrund
        container.classList.remove('success');
        container.classList.add('fail');

        showToast("error","Leider ist etwas schiefgelaufen. Bitte erneut versuchen.",4000);
    }

    // Set Text
    text.textContent = message;

    // Make Container visible
    container.style.display = 'flex';
}

function deleteMessage(messageId){

}

// Function for onload - deactive the message-container
export function deactiveAll(){
    text.textContent = '';
    successLight.style.display = 'none';
    failLight.style.display = 'none';
    container.style.display = 'none';
}

export function showToast(type = "success", text = "", duration = 3000) {
  const toast = document.getElementById("messageContainer");
  const messageText = document.getElementById("message-text");

  toast.classList.remove("success", "error", "show", "hide");
  toast.classList.add(type, "toast");

  if (text) messageText.textContent = text;

  // Show
  requestAnimationFrame(() => {
    toast.classList.add("show");
  });

  // Hide after duration
  setTimeout(() => {
    toast.classList.remove("show");
    toast.classList.add("hide");
  }, duration);
}