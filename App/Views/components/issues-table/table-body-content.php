<?php

use Config\AppConfig;
use Core\Http\Session;

?>


<?php foreach ($issues as $issue) : ?>
    <tr>
        <td><?= $issue['title'] ?></td>
        <td>
            <span class="badge <?= convertToCSSClass($issue['status']) ?>"><?= capitalize(str_replace('_', ' ', $issue['status'])) ?></span>
        </td>
        <td>
            <span class="badge <?= convertToCSSClass($issue['priority']) ?>"><?= capitalize($issue['priority']) ?></span>
        </td>
        <td>
            <?= $issue['assignee_id'] === Session::get('user')['id'] ? 'Me' : $issue['assignee'] ?? 'Unassigned' ?>
        </td>
        <td class="hidden" data-remove-class="hidden" data-date-format="DATE" data-date="<?= $issue['created_at'] ?>" data-timezone="<?= AppConfig::TIMEZONE ?>">
        </td>
        <td class="actions">
            <a href="/issues/view/<?= $issue['id']  ?>">View</a>
            <a href="/issues/edit/<?= $issue['id']  ?>">Edit</a>
        </td>
    </tr>
<?php endforeach; ?>