<div class="login-wrapper">
    <form id="loginForm" class="login-form" action="process_login.php" method="POST">
        <h2><i class="fa-solid fa-circle-user"></i> Login</h2>

        <div class="input-field">
            <input type="text" name="username" id="username" required placeholder=" ">
            <label for="username">Benutzername</label>
        </div>

        <div class="input-field">
            <input type="password" name="password" id="password" required placeholder=" ">
            <label for="password">Passwort</label>
        </div>

        <?php
            $redirect = $_GET['redirect'] ?? '/';
        ?>

        <input type="hidden" name="redirect" id="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <button type="submit">Einloggen</button>

        <p id="loginError" style="color: #d6455d; display: none; margin-top: 1rem;">Bitte füllen Sie alle Felder korrekt aus.</p>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e){
    e.preventDefault();

    const formData = new FormData(this);

    const response = await fetch('../php/process_login.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    });

    const result = await response.json();

    if(result.success){
        window.location.href = result.redirect;
    } else {
        loginError.textContent = result.message;
        loginError.style.display = 'block';
    }
});
</script>