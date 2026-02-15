<?php
//session_start();
if (isset($_SESSION['user_id'])): // User ist eingeloggt
?>
<div id="navigation-links">
    <div id="navigation-dashboard" class="navigation-link-element">
        <i class="fa-solid fa-gauge"></i>
        <p>Dashboard</p>
    </div>
    <div id="navigation-tickets" class="navigation-link-element">
        <i class="fa-solid fa-ticket"></i>
        <p>Tickets</p>
    </div>
    <div id="navigation-einzahlung" class="navigation-link-element">
        <i class="fa-solid fa-euro-sign"></i>
        <p>Einzahlung</p>
    </div>
    <div id="navigation-einlass" class="navigation-link-element">
        <i class="fa-solid fa-door-open"></i>
        <p>Einlass</p>
    </div>
    <div id="navigation-emails" class="navigation-link-element">
        <i class="fa-solid fa-at"></i>
        <p>Emails</p>
    </div>
</div>

<script>
    const elements = document.querySelectorAll('.navigation-link-element');
    elements.forEach(container => {
        container.addEventListener('click', () => {
            const id = container.id;
            const id_array = id.split('-');
            const elementSpecification = id_array[1];
            window.location.href = "/admin/" + elementSpecification + "/";
        });
    });
</script>
<?php
endif;
?>