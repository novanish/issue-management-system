<?php

use Config\AppConfig;
use Core\Http\Cookie;
use Core\Http\Request;
use Core\Http\Session;

/**
 * Converts a string path to the system-specific path format.
 *
 * @param string $path The path to be converted.
 * @return string The converted path with system-specific directory separators.
 */
function convertToSystemPath(string $path): string
{
    return preg_replace('#[/\\\\]#', DIRECTORY_SEPARATOR, $path);
}

/**
 * Gets the absolute base path joined with the given relative path.
 *
 * @param string $path The relative path.
 * @return string The absolute path.
 */
function basePath(string $path): string
{
    return BASE_PATH . convertToSystemPath($path);
}

/**
 * Generates the absolute path for a view file.
 *
 * @param string $path The relative path of the view file.
 * @return string The absolute path of the view file.
 */
function view(string $path): string
{
    return basePath("/App/Views$path.php");
}

/**
 * Generates the URL for a JavaScript file.
 *
 * @param string $path The relative path of the JavaScript file.
 * @return string The URL of the JavaScript file.
 */
function js(string $path): string
{
    return "/static/js{$path}.js";
}

/**
 * Generates the URL for a CSS file.
 *
 * @param string $path The relative path of the CSS file.
 * @return string The URL of the CSS file.
 */
function css(string $path): string
{
    return "/static/css{$path}.css";
}


function _prettify(mixed $v, callable $fn, $preventDieing): void
{
    echo "<pre>";
    if (gettype($v) === 'boolean') {
        var_dump($v);
    } else {
        $fn($v);
    }
    echo "</pre>";

    if (!$preventDieing) die();
}

/**
 * Outputs the prettified version of a variable using print_r.
 *
 * @param mixed $v The variable to be prettified.
 * @param bool $preventDieing Whether to prevent script termination after outputting.
 * @return void
 */
function pd(mixed $v, bool $preventDieing = false): void
{
    _prettify($v, 'print_r', $preventDieing);
}

/**
 * Outputs the prettified version of a variable using var_dump.
 *
 * @param mixed $v The variable to be prettified.
 * @param bool $preventDieing Whether to prevent script termination after outputting.
 * @return void
 */
function dd(mixed $v, bool $preventDieing = false): void
{
    _prettify($v, 'var_dump', $preventDieing);
}

/**
 * Displays an error message retrieved from session flash data.
 *
 * @param string $key The key for accessing the error message in session flash data.
 * @return void
 */
function displayErrorMessage(string $key): void
{
    if (Session::getFlash('errors') !== null && isset(Session::getFlash('errors')[$key])) {
        echo '<p class="error-msg">';
        echo Session::getFlash('errors')[$key][0];
        echo '</p>';
    }
}

/**
 * Convert an associative array to a string of HTML attribute key-value pairs.
 *
 * @param array $assocArray The associative array containing attribute key-value pairs.
 * @return string The string representation of HTML attributes.
 *
 * @example
 * $attributes = array(
 *     'name' => 'John Doe',
 *     'age' => 25,
 *     'gender' => 'male'
 * );
 * 
 * echo arrayToAttributesString($attributes);
 * Output: "name=\"John Doe\" age=\"25\" gender=\"male\""
 */
function arrayToAttributesString(array $assocArray): string
{
    $attributes = '';

    foreach ($assocArray as $key => $value) {
        $attributes .= $value = "" ? " $key" : " $key=\"$value\"";
    }

    return trim($attributes);
}


/**
 * Capitalizes the first letter of each word in a string.
 *
 * @param string $str The string to be capitalized.
 * @return string The capitalized string.
 */
function capitalize(string $str)
{
    return ucwords(strtolower(($str)));
}

/**
 * Converts a string to a CSS class format.
 *
 * @param string $class The string to be converted.
 * @return string The converted CSS class string.
 */
function convertToCSSClass(string $class)
{
    return str_replace('_', '-', strtolower($class));
}

/**
 * Formats a date string according to the specified timezone.
 *
 * @param string $date The date string to be formatted.
 * @param string $timezone The target timezone.
 * @return string The formatted date string.
 */
function formatDate(string $date, string $timezone): string
{
    $date = new DateTime($date, new DateTimeZone(AppConfig::TIMEZONE));
    $date->setTimezone(new DateTimeZone($timezone));
    return $date->format('M d, Y');
}


/**
 * Generates an array representing pagination links.
 *
 * @param int $totalPages The total number of pages.
 * @param int $currentPage The current page number.
 * @return array The array representing pagination links.
 */
function generatePagination(int $totalPages, int $currentPage): array
{
    if ($totalPages <= 7) return range(1, $totalPages);

    if ($currentPage <= 3) return array_merge(range(1, 4), ['...', $totalPages - 1, $totalPages]);

    if ($currentPage >= $totalPages - 2) return [1, 2, '...', $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];

    return [1, '...', $currentPage - 1, $currentPage, $currentPage + 1, '...', $totalPages];
}


/**
 * Merges multiple arrays of query parameters into a single query string.
 *
 * @param array ...$params The arrays of query parameters.
 * @return string The merged query string.
 */
function mergeQueryParametes(array ...$params): string
{
    $mergedParams = array_merge(...$params);
    if (isset($mergedParams['partial'])) unset($mergedParams['partial']);

    return http_build_query($mergedParams);
}

/**
 * Creates a URL for a specific page number.
 *
 * @param int $page The page number.
 * @return string The URL for the page.
 */
function createPageLinks(int $page)
{
    $queryParams = mergeQueryParametes(Request::getQueryParameter(), ['p' => $page]);
    return Request::getCurrentPath() . '?' . $queryParams;
}

/**
 * Creates a URL for sorting by a specific parameter and order.
 *
 * @param string $by The parameter to sort by.
 * @param string $order The sort order.
 * @return string The URL for sorting.
 */
function createSortLink(string $orderBy, string $order)
{
    $order = strtoupper(Request::getQueryParameter('order', $order));
    $order = $order === 'ASC' ? 'DESC' : 'ASC';
    $queryParams = mergeQueryParametes(Request::getQueryParameter(), compact('orderBy', 'order'));
    return Request::getCurrentPath() . '?' . $queryParams;
}

/**
 * Sanitizes input data to prevent XSS attacks.
 *
 * @param array|string $data The data to be sanitized.
 * @return array|string The sanitized data.
 */
function sanitize(array|string $data): array|string
{
    if (is_string($data)) return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    $newData = [];
    foreach ($data as $key => $value) {
        if (!is_string($value) && !is_array($value)) {
            $newData[$key] = $value;
            continue;
        }

        $newData[$key] = sanitize($value);
    }

    return $newData;
}

/**
 * Generates a remember me token.
 *
 * @return array Associative array with 'selector', 'token', and 'hashedValidator' keys.
 * @throws Exception If random_bytes() fails.
 */
function generateRememberMeToken()
{
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $token = "$selector:$validator";
    $hashedValidator = password_hash($validator, PASSWORD_BCRYPT);

    return compact('selector', 'token', 'hashedValidator');
}

/**
 * Parses the remember me token from the cookie.
 *
 * @return array|null Associative array with 'selector' and 'validator' keys, or null if token is invalid.
 */
function parseRememberMeToken()
{
    $token = Cookie::get(AppConfig::REMEMBER_ME_COOKIE_NAME);
    if (!$token) return null;

    $rememberMeToken = explode(':', $token);
    if (count($rememberMeToken) !== 2) {
        Cookie::remove(AppConfig::REMEMBER_ME_COOKIE_NAME);
        return null;
    }

    return ["selector" => $rememberMeToken[0], "validator" => $rememberMeToken[1]];
}

/**
 * Generates a CSRF token.
 *
 * @return string Generated CSRF token.
 * @throws Exception If random_bytes() fails.
 */
function generateCSRFToken()
{
    return bin2hex(random_bytes(32));
}

/**
 * Generates and sets a CSRF token in the session, and outputs a hidden input field for CSRF protection.
 *
 * @return void
 */
function csrf()
{
    $token = generateCSRFToken();
    Session::set('CSRF', $token);

    echo sprintf('<input type="hidden" name="CSRF" value="%s" />', $token);
}

function logSessionAndCookie($logFile)
{
    // Ensure the log file is writable
    if (!is_writable($logFile)) {
        error_log("Log file is not writable: $logFile");
        return false;
    }

    // Open the log file in append mode
    $fileHandle = fopen($logFile, 'a');
    if (!$fileHandle) {
        error_log("Failed to open log file: $logFile");
        return false;
    }

    date_default_timezone_set('Asia/Kathmandu');

    // Prepare log data
    $timestamp = date('Y-m-d h:i:s A');
    $sessionData = isset($_SESSION) ? print_r($_SESSION, true) : 'No session data';
    $cookieData = isset($_COOKIE) ? print_r($_COOKIE, true) : 'No cookie data';

    // Format log entry
    $logEntry = "Timestamp: $timestamp\n";
    $logEntry = "Method: " . Request::getMethod() . "\n";
    $logEntry .= "URI: " . Request::getRequestURI() . "\n";
    $logEntry .= "Session Data:\n$sessionData\n";
    $logEntry .= "Cookie Data:\n$cookieData\n";
    $logEntry .= "----------------------------------------\n";

    // Write log entry to file
    fwrite($fileHandle, $logEntry);

    // Close the file handle
    fclose($fileHandle);

    return true;
}



function saveAllSessionsToFile($outputFile = 'data.txt')
{
    // Get the session save path
    $sessionPath = ini_get('session.save_path');

    // Check if the session directory exists and is readable
    if (!is_dir($sessionPath) || !is_readable($sessionPath)) {
        throw new Exception("Session directory is not accessible.");
    }

    // Open the output file for writing
    $fileHandle = fopen($outputFile, 'w');
    if (!$fileHandle) {
        throw new Exception("Failed to open output file for writing.");
    }

    // Open the session directory
    if ($handle = opendir($sessionPath)) {
        // Loop through the files in the directory
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $sessionFile = "$sessionPath/$file";

                // Read session file content
                $sessionData = file_get_contents($sessionFile);

                // Unserialize the session data
                session_start();
                $_SESSION = [];
                session_decode($sessionData);
                $sessionDataArray = $_SESSION;
                session_write_close();

                // Write session ID and data to the output file
                fwrite($fileHandle, "Session ID: $file\n");
                fwrite($fileHandle, "Session Data:\n");
                fwrite($fileHandle, print_r($sessionDataArray, true));
                fwrite($fileHandle, "\n\n");
            }
        }
        closedir($handle);
    } else {
        fclose($fileHandle);
        throw new Exception("Failed to open session directory.");
    }

    // Close the output file
    fclose($fileHandle);
    echo "Session data saved to $outputFile successfully.\n";
}
