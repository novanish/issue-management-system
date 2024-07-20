<?php

namespace App\Controllers;

use App\Services\{IssueService, UserService};
use Constants\{IssuePriority, IssueStatus};
use Core\{Container, Router};
use Core\Http\HttpStatus;
use Core\Http\Request;
use Core\Http\Session;


/**
 * Class IssueController
 *
 * Controller class for handling issue-related operations.
 */
class IssueController extends \Core\Controller
{
    private IssueService $issueService;
    private UserService $userService;

    private const ISSUE_PER_PAGE = 5;

    /**
     * IssueController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->issueService = Container::resolve(IssueService::class);
        $this->userService = Container::resolve(UserService::class);
    }

    /**
     * Fetches issues data for views.
     *
     * @return array
     */
    private function getIssuesData(bool $paginate = true): array
    {
        $options = Request::getQueryParameter([
            'orderBy',
            'order',
            'search',
            'end',
            'start',
            'status',
            'priority'
        ]);

        if (!$paginate) {
            return $this->issueService->getAllIssues($options);
        }

        $currentPage = Request::getQueryParameter('p');
        $currentPage = (int)$currentPage ?: 1;
        $issueCount = $this->issueService->getIssueCount($options);
        $totalPages = (int) ceil($issueCount / self::ISSUE_PER_PAGE);
        $currentPage = (int)min(max($currentPage, 1), $totalPages);
        $offset = $totalPages === 0 ? 0 : ($currentPage - 1) * self::ISSUE_PER_PAGE;
        $limit = self::ISSUE_PER_PAGE;

        $issues = $this->issueService->getAllIssues([...$options, ...compact('limit', 'offset')]);
        $pages = generatePagination($totalPages, $currentPage);

        $user = Session::get('user');
        $userRole = $user['role'];
        $isAdmin = $userRole === 'ADMIN';
        return compact('issues', 'pages', 'currentPage', 'totalPages', 'isAdmin');
    }


    /**
     * Displays the issues view.
     *
     * Method: GET
     * Path: '/issues'
     * Description: Renders the issues view.
     *
     * @return void
     */
    public function issuesView()
    {
        $data = $this->getIssuesData();

        $isPartial = Request::getQueryParameter('partial', 'false') === 'true';

        if ($isPartial) {
            $this->partialIssuesView();
            return;
        }

        $stats = $this->issueService->getIssueStats();

        $this
            ->setPageTitle('Issues')
            ->addStyle(css('/issues'), css('/pagination'))
            ->addScriptInBody(js('/formatDate'), js('/srp'))
            ->renderView('/issues/index', array_merge($data, compact('stats')));
    }

    /**
     * Displays the partial issues view.
     *
     * Method: GET
     * Path: '/partial/issues'
     * Description: Renders the partial issues view.
     *
     * @return void
     */
    public function partialIssuesView()
    {
        $data = $this->getIssuesData();

        $this
            ->renderPartialView('/components/issues-table/table', $data);
    }

    /**
     * Displays the create issue form.
     *
     * Method: GET
     * Path: '/issues/create'
     * Description: Renders the create issue form view.
     *
     * @return void
     */
    public function createIssueView()
    {
        $users = $this->userService->getAllUsers();
        $issueStatuses = IssueStatus::getAllExcept(IssueStatus::RESOLVED);
        $issuePriorities = IssuePriority::getAll();

        $this
            ->setPageTitle('Create Issue')
            ->addStyle(css('/form'))
            ->renderView('/issues/create', compact('users', 'issueStatuses', 'issuePriorities'));
    }

    /**
     * Handles the create issue action.
     *
     * Method: POST
     * Path: '/issues/create'
     * Description: Validates the issue data and creates a new issue.
     *
     * @return void
     */
    public function createIssue()
    {
        $issue = $this->issueService->validateIssue(Request::body());

        $this->issueService->createIssue($issue);
        Router::redirectTo('/issues');
    }


    /**
     * Renders the issue not found view.
     *
     * @return void
     */
    private function issueNotFound()
    {
        $this
            ->addStyle(css('/errors'))
            ->setPageTitle(HttpStatus::NOT_FOUND . ' - Issue Not Found')
            ->renderView("/issues/not-found");
    }

    /**
     * Authorizes the user based on issue ownership.
     *
     * @param array $issue The issue data.
     * @return bool True if the user is authorized, false otherwise.
     */
    private function authorizeUser(array $issue)
    {
        $user = Session::get('user');
        $isCurrentUserAdmin = $user['role'] === 'ADMIN';
        $isCurrentUserReporter = $issue['reporter_id'] === $user['id'];
        $isCurrentUserAssignee = $issue['assignee_id'] === $user['id'];

        if (!$isCurrentUserAssignee && !$isCurrentUserAdmin && !$isCurrentUserReporter) {
            (new ErrorsController())->forbidden();
            return false;
        }

        return true;
    }

    /**
     * Displays the view issue view.
     *
     * Method: GET
     * Path: '/issues/view/:issueId'
     * Description: Renders the view issue view.
     *
     * @param int $issueId The ID of the issue to view.
     * @return void
     */
    public function issueView()
    {
        $issueId = Router::getRouteParams('issueId');
        $issue = $this->issueService->getIssueById($issueId);
        if (!$issue) return $this->issueNotFound();

        if (!$this->authorizeUser($issue)) {
            return;
        }

        $user = Session::get('user');
        if ($user['id'] === $issue['assignee_id']) {
            $issue['assignee_name'] = 'Me';
            $issue['assignee_email'] = null;
        }
        if ($user['id'] === $issue['reporter_id']) {
            $issue['reporter_name'] = 'Me';
            $issue['reporter_email'] = null;
        }

        $this
            ->setPageTitle('Issue - ' . $issue['title'])
            ->addStyle(css('/view-issue'))
            ->addScriptInHead(js('/formatDate'))
            ->renderView('/issues/view', compact('issue'));
    }

    /**
     * Displays the edit issue form.
     *
     * Method: GET
     * Path: '/issues/edit/:issueId'
     * Description: Renders the edit issue form view.
     *
     * @param int $issueId The ID of the issue to edit.
     * @return void
     */
    public function editIssueView()
    {
        $issueId = Router::getRouteParams('issueId');
        $issue =  $this->issueService->getIssueById($issueId);
        if (!$issue) return $this->issueNotFound();

        if (!$this->authorizeUser($issue)) {
            return;
        }

        $user = Session::get('user');

        $isAdmin = $user['role'] === 'ADMIN';
        $isReporter = $user['id'] === $issue['reporter_id'];
        $isAssignee = $user['id'] === $issue['assignee_id'];
        $users = $this->userService->getAllUsers();
        $issueStatuses = !$isAssignee
            && $issue['status'] !== IssueStatus::RESOLVED ?
            IssueStatus::getAllExcept() : IssueStatus::getAll();
        $issuePriorities = IssuePriority::getAll();
        $isIssueDeletable = $this->isIssueDeletable($issue);

        $data = compact('isAdmin', 'isReporter', 'isAssignee', 'users', 'issueStatuses', 'issuePriorities', 'issue', 'isIssueDeletable');

        $this
            ->setPageTitle('Edit Issue - ' . $issue['title'])
            ->addStyle(css('/form'))
            ->renderView('/issues/edit', $data);
    }

    /**
     * Handles the edit issue action.
     *
     * Method: PUT
     * Path: '/issues/edit/:issueId'
     * Description: Validates the updated issue data and updates the issue.
     *
     * @param int $issueId The ID of the issue to edit.
     * @return void
     */
    public function editIssue()
    {
        $issueId = Router::getRouteParams('issueId');
        $issue =  $this->issueService->getIssueById($issueId);
        if (!$issue) return $this->issueNotFound();

        if (!$this->authorizeUser($issue)) {
            return;
        }

        $data = Request::body();
        $data = $this->issueService->validateEditIssue($issue, $data);
        $this->issueService->updateIssue($issueId, $data);
        Router::redirectTo('/issues');
    }

    private function isIssueDeletable(array $issue): bool
    {
        $isIssueInProgess = $issue['status'] === IssueStatus::IN_PROGRESS;
        $isIssueAssigned = isset($issue['assignee_id']);
        if ($isIssueInProgess || $isIssueAssigned)
            return false;

        return true;
    }


    /**
     * Handles the delete issue action.
     *
     * Method: DELETE
     * Path: '/issues/delete/:issueId'
     * Description: Deletes the specified issue.
     *
     * @param int $issueId The ID of the issue to delete.
     * @return void
     */
    public function deleteIssue()
    {
        $issueId = Router::getRouteParams('issueId');
        $issue = $this->issueService->getIssueById($issueId);
        if (!$issue) return Router::redirectTo('/issues');

        if (!$this->isIssueDeletable($issue))
            return Router::redirectTo('/issues');

        $this->issueService->deleteIssue($issueId);

        Router::redirectTo('/issues');
    }

    /**
     * Handles the download of deleted issue logs.
     *
     * Method: GET
     * Path: '/issues/delete-logs/download'
     * Description: Downloads a CSV file containing the logs of deleted issues.
     *              Only accessible by users with the 'ADMIN' role.
     *
     * @return void
     */
    public function downloadDeleteLog()
    {
        $user = Session::get('user');
        $role = $user['role'];
        if ($role !== 'ADMIN') Router::redirectTo('/');

        $deletedIssueLogs = $this->issueService->getDeletedIssueLogs();
        $fileName = 'delete_logs_' . date('Y_m_d') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$fileName");

        $this->issueService->writeToCSVFile('php://output', $deletedIssueLogs);
    }


    /**
     * Exports all issues as a CSV file.
     *
     * Method: GET
     * Path: '/issues/export-to-csv'
     * Description: Downloads a CSV file containing all issues.
     *
     * @return void
     */
    public function exportIssuesAsCSV()
    {
        $issues = $this->getIssuesData(paginate: false);
        $fileName = 'issues_' . date('Y_m_d') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$fileName");

        $this->issueService->writeToCSVFile('php://output', $issues);
    }
}
