<?php
// Session starten, falls noch nicht geschehen
if (session_status() === PHP_SESSION_NONE) {
    //session_start();
}

// Prüfen, ob der User eingeloggt ist
if (isset($_SESSION['user_id'])):
?>
    <!-- User ist eingeloggt → Logout-Icon -->
    <i class="fa-solid fa-arrow-right-from-bracket account"></i>

    <script>
        const accountIcon = document.querySelector('.account');
        accountIcon.addEventListener('click', () => {
            window.location.href = '/php/logout.php';
        });
    </script>

<?php else: ?>
    <!-- User nicht eingeloggt → Login-Icon -->
    <i class="fa-regular fa-circle-user account"></i>

    <script>
        const accountIcon = document.querySelector('.account');
        accountIcon.addEventListener('click', () => {
            window.location.href = '/login/';
        });
    </script>
<?php endif; ?>