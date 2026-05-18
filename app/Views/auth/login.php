<?php
/** @var string|null $error */
/** @var string $email */
$this->extend('layouts/auth');
?>
<section class="auth-card glass">
    <div class="auth-brand">
        <span class="brand-mark big">G</span>
        <h1>Greyshades</h1>
        <p>Innovations Pvt. Ltd. — Media Platform</p>
    </div>

    <?php if ($error): ?>
    <div class="form-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('/login') ?>" class="auth-form">
        <?= \App\Core\Csrf::field() ?>
        <label>
            <span>Email</span>
            <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus>
        </label>
        <div class="password-wrap">
            <span>Password</span>
            <input type="password" name="password" id="login-password" required autocomplete="current-password">
            <button type="button" class="toggle-pw" id="toggle-pw" aria-label="Show password">
                <svg id="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg id="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
        </div>
        <div class="auth-extras">
            <label class="remember-me">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>
        </div>
        <button type="submit" class="btn-primary btn-block">Sign in</button>
        <p class="auth-hint">Authorised personnel only. All activity is logged.</p>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('toggle-pw');
    const input = document.getElementById('login-password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');
    if (toggle && input) {
        toggle.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eyeOpen.style.display = show ? 'none' : 'block';
            eyeClosed.style.display = show ? 'block' : 'none';
        });
    }
});
</script>
