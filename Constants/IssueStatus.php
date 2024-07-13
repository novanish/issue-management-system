<?php

namespace Constants;


/**
 * Class IssueStatus
 * Represents different statuses for issues.
 */
class IssueStatus
{
    /**
     * Status constant for "Complete" issues.
     */
    const RESOLVED = 'RESOLVED';

    /**
     * Status constant for "In Progress" issues.
     */
    const IN_PROGRESS = 'IN_PROGRESS';

    /**
     * Status constant for "Open" issues.
     */
    const OPEN = 'OPEN';

    /**
     * Get all available statuses.
     *
     * @return array List of all statuses.
     */
    public static function getAll(): array
    {
        return [
            self::RESOLVED,
            self::IN_PROGRESS,
            self::OPEN
        ];
    }

    /**
     * Get all statuses except specified ones.
     *
     * @param string ...$statuses Priorities to exclude.
     * @return array List of statuses excluding specified ones.
     */
    public static function getAllExcept(string ...$statuses): array
    {
        return array_values(array_diff(self::getAll(), $statuses));
    }
}
