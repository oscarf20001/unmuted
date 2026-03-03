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
    <link rel="stylesheet" href="../../styles/search.css">
    <link rel="stylesheet" href="../../styles/ticketList.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <!-- JAVASCRIPTS -->
    <script type="module" src="../../javascript/payment.js"></script>
</head>
<body>

<header>
    <?php require '../../htmlStructure/header.php'; ?>
</header>

<div>
    <?php include '../../htmlStructure/account.php'; ?>
</div>

<aside>
    <?php require '../../htmlStructure/sidebar.php'; ?>
</aside>

<main style="display:flex;justify-content:center;align-items:center;flex-direction:column;">
    <form id="searchWrapper">
        <!-- Vorname -->
        <div class="input-field search_parameter">
            <input type="text" id="search_parameter" name="search_parameter" required placeholder=" ">
            <label for="search_parameter">Parameter:<sup>*</sup></label>
        </div>

        <!-- Tickets -->
        <div class="input-field search_type">
            <select name="search_type" id="search_type" required>
                <option value="" disabled selected hidden></option>
                <option value="email">Email</option>
                <option value="vorname">Vorname</option>
                <option value="nachname">Nachname</option>
            </select>
            <label for="search_type" id="search_typeLabel">Typ:<sup>*</sup></label>
        </div>

        <!-- Submit -->
        <div class="input-field submit">
            <input type="submit" id="submit" name="submit" value="Suchen">
        </div>
    </form>

    <div id="displaySearchResultContainer">
        <p id="ticketCountResponseText"></p>
    </div>

    <div id="ticketContainer"></div>

    <form id="takePaymentContainer" style="display:none;gap:2rem;align-items:flex-end;">
        <!-- Geld -->
        <div class="input-field receivedMoney">
            <input type="number" id="receivedMoney" name="receivedMoney" required placeholder=" ">
            <label for="receivedMoney">Empfangenes Geld in EUR:<sup>*</sup></label>
        </div>

        <!-- Methode -->
        <div class="input-field paymentMethod">
            <select name="paymentMethod" id="paymentMethod" required>
                <option value="" disabled selected hidden></option>
                <option value="PayPal">PayPal</option>
                <option value="Bar" disabled>Bar</option>
            </select>
            <label for="paymentMethod" id="paymentMethodLabel">Methode:<sup>*</sup></label>
        </div>

        <!-- Submit -->
        <div class="input-field submit">
            <input type="submit" id="submit" name="submit" value="Buchen">
        </div>
    </form>
</main>

<footer>
    <?php require '../../htmlStructure/footer.php'; ?>
</footer>

<div id="messageContainer" class="message success toast">
    <div id="message-icon">
        <i id="successLight-true" class="fa-solid fa-check"></i>
        <i id="successLight-false" class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <p id="message-text">
        Deine Ticketbestätigung ist eingegangen.
    </p>
</div>

<script>
    // Navigation Highlight
    const siteLinks = document.querySelectorAll('.navigation-link-element');
    siteLinks.forEach(element => element.classList.remove('active'));

    const url = window.location.href;
    const parts = url.replace(/\/$/, '').split('/');
    const result = parts[parts.length - 1];

    const currentLink = document.getElementById('navigation-' + result);
    if(currentLink) currentLink.classList.add('active');
</script>

</body>
</html>