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
    <link rel="stylesheet" href="../../styles/einlass.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>   <!-- Font Awesome -->
</head>
<body>
    <header>
        <?php
            require '../../htmlStructure/header.php';
        ?>
    </header>
    <aside>
        <?php
            require '../../htmlStructure/sidebar.php';
        ?>
    </aside>
    <main style="display:flex;justify-content:center;align-items:center;">
        <div id="scanner">
            <form action="" id="barcodeForm">
                <input type="text" name="" id="scanner-inputField" autofocus>
                <input type="submit" value="Suchen">
            </form>
        </div>
        <div id="feedback">

        </div>
        <div id="action"></div>
    </main>
    <!--<footer>
        <?php
            require '../../htmlStructure/footer.php';
        ?>
    </footer>-->
    <div id="messageContainer" class="message success toast">
        <div id="message-icon">
            <i id="successLight-true" class="fa-solid fa-check"></i>
            <i id="successLight-false" class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <p id="message-text">
            Deine Ticketbestätigung ist eingegangen.
        </p>
    </div>
    <script src="../../javascript/einlass.js" type="module"></script>
</body>
</html>