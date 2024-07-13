<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Issue Title</th>
                <th>
                    <a href="<?= createSortLink('status', 'ASC') ?>" data-partial>
                        Status
                    </a>
                </th>
                <th>
                    <a href="<?= createSortLink('priority', 'ASC') ?>" data-partial>
                        Priority
                    </a>
                </th>
                <th>
                    <a href="<?= createSortLink('assignee', 'DESC') ?>" data-partial>
                        Assigneed To
                    </a>
                </th>
                <th class="hidden" data-remove-class="hidden">
                    <a href="<?= createSortLink('date', 'ASC') ?>" data-partial>
                        Date
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php require view('/components/issues-table/table-body-content') ?>
        </tbody>
    </table>
</div>

<?php require view('/components/pagination') ?>