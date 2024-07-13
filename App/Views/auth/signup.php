<?php

use Core\Http\Session;
?>

<div class="form-container auth">
    <form method="post" novalidate>
        <h2 class="form-title">Sign Up</h2>
        <p class="form-description">Enter your information to create an account</p>

        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" value="<?= Session::old('name', '') ?>" placeholder="Enter your name" minlength="2" maxlength="35" required />
            <div class=" error-container">
                <?= displayErrorMessage('name') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= Session::old('email', '') ?>" placeholder="Enter your email" required />
            <div class="error-container">
                <?= displayErrorMessage('email') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" minlength="6" maxlength="30" required />
            <div class="error-container">
                <?= displayErrorMessage('password') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" name="confirmPassword" id="confirmPassword" minlength="6" maxlength="30" required />
            <div class="error-container">
                <?= displayErrorMessage('confirmPassword') ?>
            </div>
        </div>

        <div class="form-group remember-me">
            <input type="checkbox" name="rememberMe" id="rememberMe" minlength="6" maxlength="30" required />
            <label for="rememberMe">Remember Me</label>
        </div>

        <button class="primary-btn action-btn">Sign Up</button>
        <p class="message">
            Already have an account?
            <a href="/auth/signin" rel="ugc">Sign In</a>
        </p>
    </form>
</div>