<?php

require_once '../Core/Database.php';
require_once '../Config/AppConfig.php';
require_once '../Constants/IssueStatus.php';
require_once '../Constants/IssuePriority.php';


use Constants\{IssueStatus, IssuePriority};
use Config\AppConfig;
use Core\Database;

function getRandomDate()
{
    $startDate = strtotime('-1 year');
    $endDate = time();

    $randomTimestamp = rand($startDate, $endDate);

    $randomDate = date('Y-m-d', $randomTimestamp);

    return $randomDate;
}

// Instantiate the Database class
$database = new Database(AppConfig::DB_DRIVER, AppConfig::DB_CONFIG, AppConfig::DB_CREDENTIALS);

function seedUsers(Database $database)
{
    $database->query('DELETE  FROM users');
    $users = [
        ['role' => 'ADMIN', 'name' => 'IMS Admin', 'password' => 'admin123', 'email' => 'admin@ims.com'],
        ['role' => 'USER', 'name' => 'John Doe', 'password' => 'john123', 'email' => 'john@ims.com'],
        ['role' => 'USER', 'name' => 'Jane Doe', 'password' => 'jane123', 'email' => 'jane@ims.com'],
        ['role' => 'USER', 'name' => 'Alice Smith', 'password' => 'alice123', 'email' => 'alice@ims.com'],
        ['role' => 'USER', 'name' => 'Bob Johnson', 'password' => 'bob123', 'email' => 'bob@ims.com'],
        ['role' => 'USER', 'name' => 'Emily Brown', 'password' => 'emily123', 'email' => 'emily@ims.com'],
        ['role' => 'USER', 'name' => 'Michael Wilson', 'password' => 'michael123', 'email' => 'michael@ims.com']
    ];

    foreach ($users as $user) {
        $params = array_merge($user, ['password' => password_hash($user['password'], PASSWORD_BCRYPT)]);
        $queryString = "INSERT INTO users (role, name, password, email) VALUES (:role, :name, :password, :email)";
        $database->query($queryString, $params);
    }
}
function seedIssues(Database $database)
{
    $database->query('DELETE FROM issues');

    $issueTitles = [
        "<script>alert('XSS attack');</script>",
        "DROP TABLE users;--",
        'Issue with email notifications',
        'Page layout broken on mobile devices',
        'Performance degradation on large datasets',
        'Database connection timeout error',
        'Cannot upload attachments to issues',
        'Missing validation on user input',
        'UI glitch in settings page',
        'Compatibility issue with Internet Explorer',
        'Error when exporting data to CSV',
        'Login page not redirecting properly',
        'Incorrect sorting of issues by priority',
        'API endpoint returning 500 error',
        'Integration with third-party service failing',
        'Issue with SSL certificate renewal',
        'Emails not being sent to new users',
        'UI redesign for better user experience',
        'Crash on certain user actions',
        'Mobile app crashing on startup',
        'Permission issue preventing file uploads',
        'User profile fields not updating correctly',
        'Improper error handling in search feature',
        'Memory leak in backend service',
        'Issue with recurring tasks not resetting',
        'Missing documentation for API endpoints',
        'Notification system not working reliably',
        'Broken links in user emails',
        'Issue with timezone conversion in reports'
    ];

    $descriptions = [
        "Users have requested the implementation of a dark mode feature to reduce eye strain during prolonged usage. This feature will enhance user experience and align with modern design trends. <script>alert('XSS');</script>",
        "Users have requested the implementation of a dark mode feature to reduce eye strain during prolonged usage. This feature will enhance user experience and align with modern design trends.",
        "There is an issue with email notifications not being delivered promptly. Users are not receiving important updates, impacting their ability to stay informed about changes.",
        "The mobile page layout is distorted and unusable on various devices with different screen sizes. This affects the accessibility and usability of the application on mobile platforms.",
        "The application's performance significantly degrades when handling large datasets, resulting in slow response times and increased server load. This impacts user productivity and satisfaction.",
        "Database connections are timing out intermittently, causing disruptions in service availability. This issue affects the reliability and stability of the application's backend.",
        "Users are unable to upload attachments to their reported issues, hindering their ability to provide necessary context and evidence. This feature is essential for effective issue tracking.",
        "There is a lack of validation on user input fields, leading to potential security vulnerabilities and data integrity issues. Implementing proper input validation is crucial for system security.",
        "A minor UI glitch has been identified in the settings page, causing elements to overlap and appear incorrectly. While not critical, this issue impacts the overall user experience.",
        "The application experiences compatibility issues with Internet Explorer, resulting in rendering errors and functional limitations. This affects users who rely on the IE browser.",
        "An error occurs when exporting data to CSV files, leading to incomplete or corrupted exports. This issue prevents users from extracting and analyzing data effectively.",
        "After logging in, users are not redirected to the correct page, resulting in a confusing user experience. This issue affects navigation and usability.",
        "The issues are not sorted correctly based on priority, leading to confusion and inefficiencies in issue management. Proper sorting is essential for prioritizing tasks effectively.",
        "An internal server error (HTTP 500) occurs when accessing a specific API endpoint, preventing users from performing critical operations. This issue requires immediate resolution.",
        "Integration with a third-party service is failing intermittently, causing disruptions in data synchronization. This impacts data consistency and system functionality.",
        "The SSL certificate for the application has expired, leading to security warnings and potential security risks. Renewing the SSL certificate is necessary to ensure secure connections.",
        "New users are not receiving confirmation emails after registration, preventing them from activating their accounts. This issue hinders user onboarding and engagement.",
        "The user interface requires a redesign to improve usability and provide a more intuitive user experience. This includes enhancing navigation and visual design elements.",
        "The application crashes when users perform specific actions, such as submitting a form or clicking a button. This issue disrupts user workflow and requires investigation.",
        "The mobile app crashes on startup for certain devices or operating system versions, making it unusable for affected users. This issue impacts mobile user engagement.",
        "Users are unable to upload files due to a permission issue, even though they have the necessary privileges. This prevents users from completing tasks that require file uploads.",
        "User profile fields, such as name and email, do not update correctly when users make changes. This issue affects data accuracy and user profile management.",
        "Error messages displayed during search are unclear and do not provide helpful guidance to users. Improving error handling will enhance user experience and usability.",
        "A memory leak has been identified in the backend service, causing gradual performance degradation over time. This issue requires investigation and memory optimization.",
        "Recurring tasks are not resetting properly after completion, leading to duplicated or missed tasks. This affects task management and productivity.",
        "Documentation for certain API endpoints is missing or incomplete, making it difficult for developers to integrate with the application. Comprehensive documentation is essential for smooth integration.",
        "The notification system is not delivering messages reliably, resulting in missed notifications and delayed updates. This impacts user communication and collaboration.",
        "Links included in user emails are broken, leading to error pages or inaccessible content. Fixing broken links will improve user experience and prevent frustration.",
        "There is an issue with timezone conversion in reports, causing discrepancies in displayed data. Ensuring accurate timezone handling is essential for data consistency and analysis."
    ];

    $usersId = $database->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $usersCount = count($usersId);

    for ($i = 0; $i < 29; $i++) {
        $title = $issueTitles[$i];
        $description = $descriptions[$i];
        $assignee_id = rand(1, 0) || $i === 0 || $i === 1 ? null : $usersId[rand(0, $usersCount - 1)];
        $status = !$assignee_id ? IssueStatus::OPEN : (IssueStatus::getAllExcept(IssueStatus::RESOLVED))[rand(0, count(IssueStatus::getAllExcept(IssueStatus::RESOLVED)) - 1)];
        $priority = $i === 0 || $i === 1 ? IssuePriority::HIGH : (IssuePriority::getAll())[rand(0, count(IssuePriority::getAll()) - 1)];
        $reporter_id = $usersId[rand(0, $usersCount - 1)];
        $issue = [$title, $description, $status, $priority, $assignee_id, $reporter_id, getRandomDate()];
        $queryString = "INSERT INTO issues (title, description, status, priority, assignee_id, reporter_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $database->query($queryString, $issue);
    }
}

// Seed users
seedUsers($database);

// Seed issues
seedIssues($database);

echo "Database seeding completed successfully.\n";
