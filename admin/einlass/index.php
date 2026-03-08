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
    <footer>
        <?php
            require '../../htmlStructure/footer.php';
        ?>
    </footer>

    <script>
        function autofocusBarcodeInput(){
            barcodeInputField.focus();
            barcodeInputField.select();
        }
        
        function clearBarcodeInput(){
            barcodeInputField.value = '';
        }

        function getBarcode(){
            return barcodeInputField.value;
        }

        function extractCode(code){
            const last4 = code.slice(-4);
            return parseInt(last4, 10);
        }

        async function fetchData(code){
            try {
                const res = await fetch('../../php/fetchEntranceTicket.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });

                const result = await res.json();
                return result;

            } catch(err) {
                console.error(err);
                throw err;
            }
        }
        
        let barcodeForm = document.getElementById('barcodeForm');
        let barcodeInputField = document.getElementById('scanner-inputField');

        window.onload = function(){
            autofocusBarcodeInput();
        };

        barcodeForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // verhindert Reload
            let rawCode = getBarcode();
            let code = extractCode(rawCode);

            try {
                let ticket = await fetchData(code);
                
            } catch (error) {
                
            }

            clearBarcodeInput();
            autofocusBarcodeInput();
        });
    </script>
</body>
</html>