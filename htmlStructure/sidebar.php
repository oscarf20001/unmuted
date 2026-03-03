<?php if (isset($_SESSION['user_id'])): ?>
    
<!-- Hamburger Button (nur mobil sichtbar) -->
<div id="hamburger">
    <i class="fa-solid fa-bars"></i>
</div>

<!-- Overlay für Mobile -->
<div id="sidebar-overlay"></div>

<!-- Sidebar -->
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

// Hamburger Toggle
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('navigation-links');
const overlay = document.getElementById('sidebar-overlay');

hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
});
</script>

<?php endif; ?>