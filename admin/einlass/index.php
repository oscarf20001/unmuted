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
        <p>Einlass - Under construction!</p>
    </main>
    <footer>
        <?php
            require '../../htmlStructure/footer.php';
        ?>
    </footer>
</body>
</html>