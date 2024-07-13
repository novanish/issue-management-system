<?php

use Core\Http\Session;
?>
<div class="form-container auth">
    <form method="post" novalidate>
        <?php if (Session::getFlash('errors') !== null && isset(Session::getFlash('errors')['formError'])) : ?>
            <div role="alert" class="alert-container">
                <strong class="alert-message">
                    <?= Session::getFlash('errors')['formError'] ?>
                </strong>
            </div>
        <?php endif; ?>

        <h2 class="form-title">Sign In</h2>
        <p class="form-description">Enter your credentials to sign in into your account</p>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= Session::old('email', '') ?>" placeholder="Enter your email" required />
            <div class="error-container">
                <?= displayErrorMessage('email') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required />
            <div class="error-container">
                <?= displayErrorMessage('password') ?>
            </div>
        </div>

        <div class="form-group remember-me">
            <input type="checkbox" name="rememberMe" id="rememberMe" minlength="6" maxlength="30" required />
            <label for="rememberMe">Remember Me</label>
        </div>

        <button class="primary-btn action-btn">Sign In</button>
        <p class="message">
            Don't have an account yet?
            <a href="/auth/signup" rel="ugc">Sign Up</a>
        </p>
    </form>
</div>