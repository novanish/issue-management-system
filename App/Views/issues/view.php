<?php

use Config\AppConfig;
?>
<div class="container">
    <h1 class="title">Issue Details</h1>
    <div class="grid">
        <div class="info">
            <p>Issue ID:</p>
            <p><?= $issue['id'] ?></p>
        </div>
        <div class="info">
            <p>Status:</p>
            <p class="badge <?= convertToCSSClass($issue['status']) ?>"><?= capitalize(str_replace('_', ' ', $issue['status'])) ?></p>
        </div>
        <div class="info">
            <p>Reported By:</p>
            <p><?= $issue['reporter_name'] ?></p>
        </div>
        <?php if ($issue['reporter_email']) : ?>
            <div class="info">
                <p>Reporter Email:</p>
                <a href="mailto:<?= $issue['reporter_email'] ?>"><?= $issue['reporter_email'] ?></a>
            </div>
        <?php endif; ?>
        <div class="info">
            <p>Assigned To:</p>
            <p><?= $issue['assignee_name'] ?? 'Unassigned'  ?></p>
        </div>
        <?php if ($issue['assignee_email']) : ?>
            <div class="info">
                <p>Assignee Email:</p>
                <a href="mailto:<?= $issue['assignee_email'] ?>"><?= $issue['assignee_email'] ?></a>
            </div>
        <?php endif; ?>
        <div class="info">
            <p>Priority:</p>
            <p class="badge <?= convertToCSSClass($issue['priority']) ?>"><?= capitalize($issue['priority']) ?></p>
        </div>
        <div class="info hidden" data-remove-class="hidden">
            <p>Created At:</p>
            <p data-date="<?= $issue['created_at'] ?>" data-date-format="DATETIME" data-timezone="<?= AppConfig::TIMEZONE ?>"></p>
        </div>
        <div class="info hidden" data-remove-class="hidden">
            <p>Updated At:</p>
            <p data-date="<?= $issue['updated_at'] ?>" data-date-format="DATETIME" data-timezone="<?= AppConfig::TIMEZONE ?>"></p>
        </div>
    </div>

    <div class="desc-container">
        <h4 class="desc-title">Description</h4>
        <p class="desc"><?= $issue['description'] ?></p>
    </div>

    <div class="actions">
        <a class="primary-btn" href="/issues">Go to Issues</a>
    </div>
</div>