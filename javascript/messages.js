// Defining the needed variables
const container = document.getElementById('messageContainer');
const text = document.getElementById('message-text');
const successLight = document.getElementById('successLight-true');
const failLight = document.getElementById('successLight-false');
let currentToastTimeout = null;

export function createAndDisplayMessage(success, message){
    container.style.display = 'flex';

    // Set correct Feedback
    if(!!success){
        // Icons
        successLight.style.display = 'flex';
        failLight.style.display = 'none';

        // Hintergrund
        container.classList.add('success');
        container.classList.remove('fail');

        showToast("success", message, 3200);
    }else{
        // Icons
        successLight.style.display = 'none';
        failLight.style.display = 'flex';

        // Hintergrund
        container.classList.remove('success');
        container.classList.add('fail');

        showToast("error", message, 4500);
    }
}

function deleteMessage(messageId){
    document.getElementById(messageId).style.display = 'none';
}

// Function for onload - deactive the message-container
export function deactiveAll(){
    text.textContent = '';
    successLight.style.display = 'none';
    failLight.style.display = 'none';
    container.style.display = 'none';
}

export function showToast(type, text, duration) {
  const toast = document.getElementById("messageContainer");
  const messageText = document.getElementById("message-text");

  // 🛑 Alten Timeout abbrechen
  if (currentToastTimeout) {
    clearTimeout(currentToastTimeout);
  }

  toast.style.display = "flex";
  toast.classList.remove("success", "error", "show", "hide");
  void toast.offsetWidth;

  toast.classList.add("toast", type);

  if (text) messageText.textContent = text;

  requestAnimationFrame(() => {
    toast.classList.add("show");
  });

  // ✅ Neuen Timeout speichern
  currentToastTimeout = setTimeout(() => {
    toast.classList.remove("show");
    toast.classList.add("hide");

    toast.addEventListener("animationend", () => {
      toast.style.display = "none";
      toast.classList.remove("hide");
    }, { once: true });

  }, duration);
}