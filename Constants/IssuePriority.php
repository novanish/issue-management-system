<?php

namespace Constants;

/**
 * Class IssuePriority
 * Represents different priorities for issues.
 */
class IssuePriority
{
    /**
     * Priority constant for "High" priority issues.
     */
    const HIGH = 'HIGH';

    /**
     * Priority constant for "Medium" priority issues.
     */
    const MEDIUM = 'MEDIUM';

    /**
     * Priority constant for "Low" priority issues.
     */
    const LOW = 'LOW';

    /**
     * Get all available priorities.
     *
     * @return array List of all priorities.
     */
    public static function getAll(): array
    {
        return [
            self::HIGH,
            self::MEDIUM,
            self::LOW
        ];
    }

    /**
     * Get all priorities except specified ones.
     *
     * @param string ...$priorities Priorities to exclude.
     * @return array List of priorities excluding specified ones.
     */
    public static function getAllExcept(string ...$priorities): array
    {
        return array_values(array_diff(self::getAll(), $priorities));
    }
}
