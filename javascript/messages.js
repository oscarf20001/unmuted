// Defining the needed variables
const container = document.getElementById('messageContainer');
const text = document.getElementById('message-text');
const successLight = document.getElementById('successLight-true');
const failLight = document.getElementById('successLight-false');

export function createAndDisplayMessage(success, message){
    // Set correct Feedback
    if(!!success){
        // Icons
        successLight.style.display = 'block';
        failLight.style.display = 'none';

        // Hintergrund
        container.classList.add('success');
        container.classList.remove('fail');
    }else{
        // Icons
        successLight.style.display = 'none';
        failLight.style.display = 'block';

        // Hintergrund
        container.classList.remove('success');
        container.classList.add('fail');
    }

    // Set Text
    text.textContent = message;

    // Make Container visible
    container.style.display = 'block';
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