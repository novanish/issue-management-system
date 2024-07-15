<form method="GET" class="form-grid">
    <div class="form-group full-width">
        <label for="search">Search</label>
        <input name="search" id="search" class="input-field" placeholder="Search by issue title, description, assignee or reporter name" />
    </div>
    <div class="form-group half-width">
        <label for="status">Status</label>
        <select id="status" name="status" class="input-field">
            <?php

            use Constants\IssuePriority;
            use Constants\IssueStatus;
            use Core\Http\Session;

            foreach (['All', ...IssueStatus::getAll()] as $issueStatus) : ?>
                <option value="<?= $issueStatus ?>" <?= Session::old('status', 'All') === $issueStatus ? 'selected' : '' ?>><?= capitalize(str_replace('_', ' ', $issueStatus)) ?></option>
                <?php endforeach; ?>>
        </select>
    </div>
    <div class="form-group half-width">
        <label for="priority">Priority</label>
        <select id="priority" class="input-field" name="priority">
            <?php foreach (['All', ...IssuePriority::getAll()] as $issuePriority) : ?>
                <option value="<?= $issuePriority ?>" <?= Session::old('priority', 'All') === $issuePriority ? 'selected' : '' ?>><?= capitalize($issuePriority) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group half-width">
        <label for="start-date">Start Date</label>
        <input type="date" name="start-date" id="start-date" class="input-field">
    </div>
    <div class="form-group half-width">
        <label for="end-date">End Date</label>
        <input type="date" name="end-date" id="end-date" class="input-field">
    </div>
    <button type="submit" class="primary-btn">Filter</button>
</form>