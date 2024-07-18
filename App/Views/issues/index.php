<div class="issue-container">
    <h2 class="title">Issues Stats</h2>
    <div class="card-container">
        <div class="grid-container">
            <div class="card">
                <h3>Open Issues</h3>
                <p><?= $stats['openIssuesCount'] ?></p>
            </div>
            <div class="card">
                <h3>Resolved Issues</h3>
                <p><?= $stats['resolvedIssuesCount'] ?></p>
            </div>
            <div class="card">
                <h3>In Progress Issues</h3>
                <p><?= $stats['inProgressIssuesCount'] ?></p>
            </div>
            <div class="card">
                <h3>High Priority Issues</h3>
                <p><?= $stats['highPriorityIssuesCount'] ?></p>
            </div>
            <div class="card">
                <h3>Medium Priority Issues</h3>
                <p><?= $stats['mediumPriorityIssuesCount'] ?></p>
            </div>
            <div class="card">
                <h3>Low Priority Issues</h3>
                <p><?= $stats['lowPriorityIssuesCount'] ?></p>
            </div>
        </div>
        <div class="flex-container">
            <div class="card">
                <h3>Total Issues</h3>
                <p><?= $stats['totalIssues'] ?></p>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between;">
        <h2 class="title">Issues</h2>
        <?php if ($isAdmin) : ?>
            <a href="/issues/delete-logs/download" class="primary-btn">Export Delete logs to csv</a>
        <?php endif; ?>
        <a href="/issues/create" class="primary-btn">Create Issue</a>
    </div>

    <?php require view('/components/issues-table/filter-form') ?>

    <div id="issues">
        <?php require view('/components/issues-table/table') ?>
    </div>
</div>