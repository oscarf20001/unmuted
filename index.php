<!--
=================================================
This Code was written by Oscar Streich
© 2026 Oscar Streich
=================================================
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unmuted - Zeig, wer du bist! Ticketreservierung</title>

    <!-- STYLE SHEETS -->
    <link rel="stylesheet" href="styles/main.css">                                                              <!-- Default-Style der Seite -->
    <!--<link rel="stylesheet" href="styles/inputFields.css">                                                       <!-- Style für alle Input-Felder -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>   <!-- Font Awesome -->


    <!-- JAVASCRIPTS -->
    <script type="module" src="javascript/main.js"></script>
    <script type="module" src="javascript/updatePriceTag.js"></script>
    <script type="module" src="javascript/ticket.js"></script>
    <script type="module" src="javascript/messages.js"></script>
</head>
<body>
    <!--
    =================================================
    KOPFZEILE
    =================================================
    -->
    <header>
        <?php
            include 'htmlStructure/header.php';
        ?>
    </header>

    <!--
    =================================================
    HAUPTFENSTER
    =================================================
    -->
    <main>
        <?php
            include 'htmlStructure/main.php';
        ?>
    </main>

    <!--
    =================================================
    FUßZEILE
    =================================================
    -->
    <footer>
        <?php
            include 'htmlStructure/footer.php';
        ?>
    </footer>

    <!--
    =================================================
    MESSAGE-CONTAINER
    =================================================
    -->
    <div id="messageContainer">
        <div id="message-icon">
            <i id="successLight-true" class="fa-solid fa-check"></i>
            <i id="successLight-false" class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <p id="message-text">
            <!-- Text is later inserted via js -->
             Das ist ein Test
        </p>
    </div>

    <!--
    =================================================
    JAVASCRIPT
    =================================================
    -->
    <script>

    </script>
</body>
</html>