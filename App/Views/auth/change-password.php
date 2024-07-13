<?php

use Core\Http\{Request, Session};
?>
<div class="form-container auth">
    <form method="post" novalidate>
        <input type="hidden" name="userId" value="124">
        <?php if (Session::getFlash('errors') !== null && isset(Session::getFlash('errors')['formError'])) : ?>
            <div role="alert" class="alert-container">
                <strong class="alert-message">
                    <?= Session::getFlash('errors')['formError'] ?>
                </strong>
            </div>
        <?php endif; ?>
        <?php if ($successFormMessage !== null) : ?>
            <div role="alert" class="alert-container success">
                <strong class="alert-message success">
                    <?= $successFormMessage  ?>
                </strong>
            </div>
        <?php endif; ?>

        <h2 class="form-title">Change Password</h2>
        <p class="form-description">Enter your current password to change password.</p>
        <div class="form-group">
            <label for="currentPassword">Current Password:</label>
            <input type="password" name="currentPassword" id="currentPassword" minlength="6" maxlength="30" required />
            <div class="error-container">
                <?= displayErrorMessage('currentPassword') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="newPassword">New Password:</label>
            <input type="password" name="newPassword" id="newPassword" minlength="6" maxlength="30" required />
            <div class="error-container">
                <?= displayErrorMessage('newPassword') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="newConfirmPassword">Confirm New Password:</label>
            <input type="password" name="confirmNewPassword" id="confirmNewPassword" minlength="6" maxlength="30" required />
            <div class="error-container">
                <?= displayErrorMessage('confirmNewPassword') ?>
            </div>
        </div>

        <button name="<?= Request::ACTION_NAME ?>" value="PUT" class="primary-btn action-btn">Change Password</button>
    </form>
</div>