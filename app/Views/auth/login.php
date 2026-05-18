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
        <label>
            <span>Password</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit" class="btn-primary btn-block">Sign in</button>
        <p class="auth-hint">Authorised personnel only. All activity is logged.</p>
    </form>
</section>
