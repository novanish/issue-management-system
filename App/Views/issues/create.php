<?php

use Constants\IssueStatus;
use Core\Http\Session;
?>

<div class="form-container">
    <form method="post" novalidate>
        <h2 class="form-title">Create Issue</h2>
        <p class="form-description">Fill out the form to create a new issue.</p>

        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?= Session::old('title', '') ?>" placeholder="Title" required />
            <div class="error-container">
                <?= displayErrorMessage('title') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description" placeholder="Describe the issue in detail" rows="4"><?= Session::old('description', '') ?></textarea>

            <div class="error-container">
                <?= displayErrorMessage('description') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="assignee">Assignee:</label>
            <select name="assignee" id="assignee">
                <option value="">Select Assignee</option>
                <?php $sessionUser = Session::get('user');
                foreach ($users as $user) : ?>
                    <option value="<?= $user['id'] ?>" <?= (int) Session::old('assignee') === $user['id'] ? 'selected' : '' ?>><?= $user['id'] === $sessionUser['id'] ? 'Me' :  $user['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="error-container">
                <?= displayErrorMessage('assignee') ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:25px; ">
            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <?php foreach ($issueStatuses as $issueStatus) : ?>
                        <option value="<?= $issueStatus ?>" <?= Session::old('status', IssueStatus::OPEN) === $issueStatus ? 'selected' : '' ?>><?= capitalize(str_replace('_', ' ', $issueStatus)) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error-container">
                    <?= displayErrorMessage('status') ?>
                </div>
            </div>

            <div class="form-group">
                <label for="priority">Priority:</label>
                <select name="priority" id="priority">
                    <option value="">Select Priority</option>
                    <?php foreach ($issuePriorities as $issuePriority) : ?>
                        <option value="<?= $issuePriority ?>" <?= Session::old('priority', '') === $issuePriority ? 'selected' : '' ?>><?= capitalize($issuePriority) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error-container">
                    <?= displayErrorMessage('priority') ?>
                </div>
            </div>
        </div>

        <button class="primary-btn action-btn">Create Issue</button>
    </form>
</div>