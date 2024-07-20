<?php

namespace App\Services;

use Constants\{IssueStatus, IssuePriority};
use Core\Database;
use Core\Http\Session;
use Core\ValidationHandler;
use Core\Validators\StringValidator;
use DateTime;
use PDO;

/**
 * Class IssueService
 * Service class for handling issues.
 */
class IssueService
{
    /**
     * IssueService constructor.
     *
     * @param Database $db The database instance.
     * @param UserService $userService The user service instance.
     */
    public function __construct(
        private Database $db,
        private UserService $userService
    ) {
    }


    /**
     * Validates the issue data.
     *
     * @param array $issue The issue data to validate.
     * @return array Validation result.
     */
    public function validateIssue(array $issue): array
    {
        $customAssigneeValidator =    function (string $assignee): bool {
            return $assignee !== '' && !$this->userService->doesUserExistWithId((int) $assignee);
        };

        $customStatusValidator = function (string $status) use ($issue): bool {
            $user = Session::get('user');
            $assignee = $issue['assignee'] === '' ?: (int)$issue['assignee'];

            $isUnassignedAndInProgress = $assignee === '' && $status === IssueStatus::IN_PROGRESS;
            $isAssignedToOtherAndInProgress = $assignee !== $user['id'] && $status === IssueStatus::IN_PROGRESS;

            return $isUnassignedAndInProgress || $isAssignedToOtherAndInProgress;
        };

        $issueValidators = [
            "title" => (new StringValidator($issue['title']))
                ->trim()
                ->minLength(1, 'Title cannot be empty.')
                ->maxLength(255, 'Title cannot be longer than 255 characters.'),
            "description" => (new StringValidator($issue['description']))
                ->trim()
                ->minLength(1, 'Description cannot be empty.')
                ->maxLength(1000, 'Description cannot be longer than 1000 characters.'),
            "status" => (new StringValidator($issue['status']))
                ->transform(fn ($status) => strtoupper($status))
                ->oneOf(IssueStatus::getAllExcept(IssueStatus::RESOLVED), 'Invalid status.')
                ->custom($customStatusValidator, 'Only the assignee can set the status to "IN_PROGRESS".'),
            "priority" => (new StringValidator($issue['priority']))
                ->oneOf(IssuePriority::getAll()),
            "assignee" => (new StringValidator($issue['assignee']))
                ->custom($customAssigneeValidator, 'Invalid assignee.')
        ];


        return ValidationHandler::validate($issueValidators, $issue);
    }

    /**
     * Creates a new issue.
     *
     * @param array $issue The issue data.
     * @return void
     */
    function createIssue(array $issue): void
    {
        $query = <<<SQL
            INSERT INTO issues (
                 title, 
                 description, 
                 status, 
                 priority, 
                 assignee_id, 
                 reporter_id
            ) VALUES (
                :title, 
                :description, 
                :status, 
                :priority, 
                :assignee, 
                :reporter_id
            )
        SQL;

        $params = array_merge(
            $issue,
            ['assignee' => $issue['assignee'] ?: null, 'reporter_id' => Session::get('user')['id']]
        );

        $this
            ->db
            ->query($query, $params);
    }


    /**
     * Retrieves the count of issues.
     *
     * @return int The count of issues.
     */
    public function getIssueCount(array $options): int
    {
        $query = <<<SQL
            SELECT COUNT(*) AS count
            FROM issues as i
            LEFT JOIN users as u 
            ON i.assignee_id = u.id
        SQL;

        $query .= $this->getWhereClauseForIssues($options);
        $paramsAndTypes = $this->prepareQueryParameters($options);

        $user = Session::get('user');
        $params = ["id" => $user['id'], 'role' => $user['role'], ...$paramsAndTypes['params']];

        return (int) $this
            ->db
            ->query($query, $params)
            ->fetch()['count'];
    }


    /**
     * Retrieves an issue by its ID.
     *
     * @param string|int $id The ID of the issue.
     * @return array|bool The issue data if found, otherwise false.
     */
    public function getIssueById(string | int $id): array|bool
    {
        $query = <<<SQL
            SELECT i.*, 
                u1.name AS assignee_name,
                u1.email AS assignee_email,
                u2.name AS reporter_name,
                u2.email AS reporter_email
            FROM issues AS i
            LEFT JOIN users AS u1
                ON i.assignee_id = u1.id
            LEFT JOIN users AS u2
                ON i.reporter_id = u2.id
            WHERE i.id = :id AND is_deleted = 0
        SQL;

        $issue = $this
            ->db
            ->query($query, compact('id'))
            ->fetch();

        return sanitize($issue);
    }

    /**
     * Retrieves statistics about issues.
     *
     * @return array An array containing statistics about issues.
     */
    public function getIssueStats(): array
    {
        $query = <<<SQL
            SELECT COUNT(*) AS totalIssues,
                SUM(status = 'OPEN') AS openIssuesCount,
                SUM(status = 'RESOLVED') AS resolvedIssuesCount,
                SUM(status = 'IN_PROGRESS') AS inProgressIssuesCount,
                SUM(priority = 'HIGH') AS highPriorityIssuesCount,
                SUM(priority = 'MEDIUM') AS mediumPriorityIssuesCount,
                SUM(priority = 'LOW') AS lowPriorityIssuesCount
            FROM issues
            WHERE is_deleted = 0
                AND (reporter_id = :id
                OR assignee_id = :id
                OR :role = 'ADMIN')  
        SQL;

        $user = Session::get('user');
        $params = ["id" => $user['id'], 'role' => $user['role']];

        $stats = $this
            ->db
            ->query($query, $params)
            ->fetch();
        return array_map(fn ($v) => is_null($v) ? 0 : $v, $stats);
    }


    /**
     * Construct the WHERE clause for the issues query based on the provided options.
     *
     * This function generates the WHERE clause for the SQL query to retrieve issues.
     * It includes conditions for filtering based on deletion status, user roles, search keywords,
     * issue status, priority, and date range.
     *
     * @param array $options An associative array of options to customize the WHERE clause.
     * @return string The constructed WHERE clause string.
     */
    private function getWhereClauseForIssues(array $options)
    {
        $where = ['is_deleted = 0', "(reporter_id = :id OR assignee_id = :id OR :role = 'ADMIN')"];

        if ($options['search']) {
            $where[] = '(LOWER(title) LIKE :keyword OR LOWER(u.name) LIKE :keyword)';
        }

        if (in_array(strtoupper($options['status']), IssueStatus::getAll())) {
            $where[] = 'status = :status';
        }

        if (in_array(strtoupper($options['priority']), IssuePriority::getAll())) {
            $where[] = 'priority = :priority';
        }

        $startDate = DateTime::createFromFormat('Y-m-d', $options['start']);
        $endDate = DateTime::createFromFormat('Y-m-d', $options['end']);

        if ($startDate && $endDate) {
            $where[] = 'i.created_at BETWEEN :start AND :end';
        } elseif ($startDate) {
            $where[] = 'i.created_at >= :start';
        } elseif ($endDate) {
            $where[] = 'i.created_at <= :end';
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    /**
     * Create the SQL query to retrieve all issues with specified options.
     *
     * This function constructs an SQL query to retrieve all issues from the database,
     * allowing for optional filtering, ordering, and pagination. The results can be
     * ordered by creation date, status, priority, or assignee.
     *
     * @param array $options An associative array of options to customize the query.
     *                       Supported keys:
     *                       - 'orderBy': The column to order by (default is 'created_at').
     *                       - 'order': The order direction ('asc' or 'desc', default is 'desc').
     * @return string The constructed SQL query string.
     */
    private function createGetAllIssueQuery(array $options): string
    {
        $orderBy = $options['orderBy'] ?? 'created_at';
        $order = strtolower($options['order'] ?? 'desc') === 'desc' ? 'DESC' : 'ASC';

        $query =
            <<<SQL
            SELECT i.*, u.name as assignee
            FROM issues as i
            LEFT JOIN users as u 
            ON i.assignee_id = u.id
        SQL;

        $query .= $this->getWhereClauseForIssues($options);

        $query .= ' ORDER BY ';
        switch ($orderBy) {
            case 'status':
                $query .= "FIELD(status, 'OPEN', 'IN_PROGRESS', 'RESOLVED') $order";
                break;
            case 'priority':
                $query .= "FIELD(priority, 'HIGH', 'MEDIUM', 'LOW') $order";
                break;
            case 'assignee':
                $query .= "assignee IS NULL $order";
                break;
            default:
                $query .= "created_at $order";
        }

        if (isset($options['limit']) && isset($options['offset']))
            $query .= " LIMIT :limit OFFSET :offset";

        return $query;
    }

    /**
     * Prepares parameters and types for a SQL query.
     *
     * @param array $options The options containing query parameters.
     * @return array An array containing 'params' and 'types' for PDO binding.
     */
    private function prepareQueryParameters(array $options): array
    {
        $params = [];
        $types = [];

        if ($options['search']) {
            $params['keyword'] = '%' . strtolower($options['search']) . '%';
            $types['keyword'] = PDO::PARAM_STR;
        }

        if (in_array(strtoupper($options['status']), IssueStatus::getAll())) {
            $params['status'] = $options['status'];
            $types['status'] = PDO::PARAM_STR;
        }

        if (in_array(strtoupper($options['priority']), IssuePriority::getAll())) {
            $params['priority'] = $options['priority'];
            $types['priority'] = PDO::PARAM_STR;
        }

        $startDate = DateTime::createFromFormat('Y-m-d', $options['start']);
        $endDate = DateTime::createFromFormat('Y-m-d', $options['end']);

        if ($startDate) {
            $params['start'] = $startDate->format('Y-m-d');
            $types['start'] = PDO::PARAM_STR;
        }

        if ($endDate) {
            $params['end'] = $endDate->format('Y-m-d');
            $types['end'] = PDO::PARAM_STR;
        }

        if (isset($options['limit']) && isset($options['offset'])) {
            $params['limit'] = $options['limit'];
            $types['limit'] = PDO::PARAM_INT;
            $params['offset'] = $options['offset'];
            $types['offset'] = PDO::PARAM_INT;
        }

        return [
            'params' => $params,
            'types' => $types,
        ];
    }


    /**
     * Retrieves all issues within a specified range.
     *
     * @param int $offset The offset for pagination.
     * @param int $limit The maximum number of issues to retrieve.
     * @return array An array containing issues within the specified range.
     */
    public function getAllIssues(array $options): array
    {
        $query = $this->createGetAllIssueQuery($options);
        $paramsAndTypes = $this->prepareQueryParameters($options);

        $user = Session::get('user');
        $params = [
            "id" => $user['id'],
            'role' => $user['role'],
            ...$paramsAndTypes['params']
        ];
        $types = [
            "id" => PDO::PARAM_INT,
            "role" => PDO::PARAM_STR,
            ...$paramsAndTypes['types']
        ];


        $issues = $this
            ->db
            ->query($query, $params, $types)
            ->fetchAll();

        return sanitize($issues);
    }


    /**
     * Deletes an issue by its ID.
     *
     * @param int|string $issueId The ID of the issue to delete.
     * @return void
     */
    public function deleteIssue(int | string $issueId): void
    {
        $this
            ->db
            ->transaction(function ($db) use ($issueId) {
                $updateQuery = <<<SQL
                    UPDATE issues
                    SET is_deleted = 1
                    WHERE id = :issueId
                SQL;

                $db->query($updateQuery, compact('issueId'));

                $insertQuery = <<<SQL
                    INSERT INTO issue_deletion_logs (issue_id, deleted_by)
                    VALUES (:issueId, :userId)
                SQL;

                $user = Session::get('user');
                $db->query($insertQuery, ['issueId' => $issueId, 'userId' => $user['id']]);
            });
    }

    /**
     * Validates the data for editing an issue.
     *
     * @param array $issue The original issue data.
     * @param array $data The updated issue data.
     * @return array Validation result.
     */
    public function validateEditIssue(array $issue, array $data): array
    {
        $issueValidators = [
            'priority' => (new StringValidator($data['priority']))
                ->transform(fn ($priority) => strtoupper($priority))
                ->oneOf(IssuePriority::getAll())
        ];

        $user = Session::get('user');
        $isAdmin = $user['role'] === 'ADMIN';
        $isReporter = $user['id'] === $issue['reporter_id'];
        $isAssignee = $user['id'] === $issue['assignee_id'];

        if ($isAssignee && $issue['status'] !== IssueStatus::RESOLVED) {
            $issueValidators['status'] = (new StringValidator($data['status']))
                ->transform(fn ($status) => strtoupper($status))
                ->oneOf(IssueStatus::getAll());
        }

        if ($isReporter || $isAdmin) {
            $issueValidators['title'] = (new StringValidator($data['title']))
                ->trim()
                ->minLength(1, 'Title cannot be empty.')
                ->maxLength(255, 'Title cannot be longer than 255 characters.');
            $issueValidators["description"] = (new StringValidator($data['description']))
                ->trim()
                ->minLength(1, 'Description cannot be empty.');
        }

        if ($isAdmin) {
            $customAssigneeValidator =    function (string $assignee): bool {
                return $assignee !== '' && !$this->userService->doesUserExistWithId((int) $assignee);
            };

            $issueValidators['assignee'] = (new StringValidator($data['assignee']))
                ->custom($customAssigneeValidator, 'Invalid assignee.');

            $issueValidators['status'] = (new StringValidator($data['status']))
                ->transform(fn ($status) => strtoupper($status))
                ->oneOf(IssueStatus::getAll())
                ->custom(function ($status) use ($data, $user) {
                    $isAssignee = strval($user['id']) === ($data['assignee'] ?? null);
                    $isInProgress = $status === IssueStatus::IN_PROGRESS;
                    return !$isAssignee && $isInProgress;
                }, 'Only the assignee can set the status to "IN_PROGRESS".')
                ->custom(
                    function ($status) use ($data, $user, $isAdmin) {
                        $isAssignee = strval($user['id']) === ($data['assignee'] ?? null);
                        $isResolved = $status === IssueStatus::RESOLVED;
                        return (!$isAssignee && $isResolved);
                    },
                    'Only the assignee can set the status to "RESOLVED".'
                );
        }


        return ValidationHandler::validate($issueValidators, $data);
    }


    /**
     * Updates an existing issue with the provided data.
     *
     * @param string|int $issueId The ID of the issue to update.
     * @param array $data The updated issue data.
     * @return void
     */
    public function updateIssue(string | int $issueId, array $data): void
    {
        $query = 'UPDATE issues SET';
        $params = ['issueId' => $issueId];

        if (isset($data['title'])) {
            $query .= ' title = :title,';
            $params['title'] = $data['title'];
        }

        if (isset($data['description'])) {
            $query .= ' description = :description,';
            $params['description'] = $data['description'];
        }

        if (isset($data['status'])) {
            $query .= ' status = :status,';
            $params['status'] = $data['status'];
        }

        if (isset($data['priority'])) {
            $query .= ' priority = :priority,';
            $params['priority'] = $data['priority'];
        }

        if (isset($data['assignee'])) {
            $query .= ' assignee_id = :assignee,';
            $params['assignee'] = $data['assignee'] === '' ? null : $data['assignee'];
        }

        $query = rtrim($query, ',') . ' WHERE id = :issueId';

        $this->db->query($query, $params);
    }

    /**
     * Get the logs of deleted issues.
     *
     * This function retrieves information about deleted issues from the database.
     * It includes details such as the reporter, assignee, who deleted the issue,
     * and other issue-related information.
     *
     * @return array The list of deleted issue logs.
     */
    public function getDeletedIssueLogs()
    {
        $query = <<<SQL
            SELECT cu.name AS `Reporter Name`,
                au.name AS `Assignee Name`,
                u.name AS `Deleted By`,
                i.title AS `Issue Title`,
                i.description AS `Issue Description`,
                i.status AS `Issue Status`,
                i.priority AS `Issue Priority`
            FROM issue_deletion_logs AS idl
            JOIN issues AS i
            ON idl.issue_id = i.id
            JOIN users as u
            ON idl.deleted_by = u.id
            LEFT JOIN users AS au
            ON i.assignee_id = au.id 
            LEFT JOIN users AS cu
            ON i.reporter_id = cu.id 
        SQL;

        return $this
            ->db
            ->query($query)
            ->fetchAll();
    }

    /**
     * Write data to a CSV file.
     *
     * This function writes an array of data to a CSV file. It includes the headers
     * as the first row of the CSV file. The file is created or overwritten if it
     * already exists.
     *
     * @param string $fileName The name of the CSV file to write to.
     * @param array $data The data to write to the CSV file.
     * @return void
     */
    public function writeToCSVFile(string $fileName, array $data)
    {
        $file = fopen($fileName, 'w');
        $isHeaderSet = false;

        foreach ($data as $row) {
            if (!$isHeaderSet) {
                fputcsv($file, array_keys($row));
                $isHeaderSet = true;
            }

            fputcsv($file, array_values($row));
        }

        fclose($file);
    }
}
