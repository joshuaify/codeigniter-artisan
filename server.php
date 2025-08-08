<?php

if (!function_exists('findHostName')) {
    function findHostName()
    {
        // Method 1: Using $_SERVER["HTTP_HOST"]
        if (isset($_SERVER["HTTP_HOST"])) {
            return $_SERVER["HTTP_HOST"];
        }

        // Method 2: Using $_SERVER["SERVER_NAME"]
        if (isset($_SERVER["SERVER_NAME"])) {
            return $_SERVER["SERVER_NAME"];
        }

        // Method 7: Using getenv()
        $hostname = getenv('HTTP_HOST');
        if ($hostname !== false) {
            return $hostname;
        }

        // Method 3: Using $_SERVER["SERVER_ADDR"]
        if (isset($_SERVER["SERVER_ADDR"])) {
            return $_SERVER["SERVER_ADDR"];
        }

        // Method 4: Using $_SERVER["REQUEST_URI"] with parse_url()
        if (isset($_SERVER['REQUEST_URI'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $url = $scheme . '://' . $_SERVER['REQUEST_URI'];
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['host'])) {
                return $parsedUrl['host'];
            }
        }

        // Method 5: Using gethostname() for local host name
        $hostname = gethostname();
        if ($hostname !== false) {
            return $hostname;
        }

        // Method 6: Using php_uname('n') for local host name
        $hostname = php_uname('n');
        if ($hostname !== false) {
            return $hostname;
        }

        // If no method succeeded, return null
        return null;
    }
}


if (!function_exists('isLocalHost')) {
    /**
     * Determines if the current environment is a local host.
     *
     * This function checks the server name against a whitelist of known local host names,
     * as well as common localhost patterns. It also checks if the server name contains '192'.
     *
     * @return bool Returns true if the current environment is a local host, false otherwise.
     */
    function isLocalHost()
    {
        $whitelist = ['127.0.0.1', '::1', '192.168.95.98', 'localhost', 'server.local'];

        $serverName = findHostName();

        // Check if the host is in the whitelist
        if (in_array($serverName, $whitelist)) {
            return true;
        }

        // Check for common localhost patterns
        $localPatterns = [
            '/.+\.dev\.(lh|localhost|local)$/',
            '/.+\.(lh|localhost|local)$/'
        ];

        foreach ($localPatterns as $pattern) {
            if (preg_match($pattern, $serverName)) {
                return true;
            }
        }

        // Check if the server name contains '192'
        if (strpos($serverName, '192.168') !== false) {
            return true;
        }
        return false;
    }
}

if (!function_exists('getSubdomain')) {
    function getSubdomain()
    {
        $host = $_SERVER['HTTP_HOST']; // or use $_SERVER['SERVER_NAME']

        // Break the host into parts
        $parts = explode('.', $host);

        // Assuming the domain is something like sub.example.com
        if (count($parts) > 2) {
            // Remove the last two elements (domain and TLD)
            array_pop($parts); // removes the TLD (e.g., .com)
            array_pop($parts); // removes the main domain (e.g., example)

            // The remaining parts are the subdomain(s)
            $subdomain = implode('.', $parts);
            return $subdomain;
        }

        // No subdomain present
        return null;
    }
}

if (!function_exists('getTopLevelDomain')) {
    function getTopLevelDomain($host)
    {
        // Remove protocol if exists (http:// or https://)
        $host = preg_replace('/^https?:\/\//', '', $host);

        // Get the domain part (without subdomain)
        $parts = explode('.', $host);

        // Handle cases with "www" or other subdomains
        if (count($parts) > 2) {
            // Assume top-level domain is the last two parts of the domain
            $mainDomain = $parts[count($parts) - 2];
        } else {
            // If it's a simple domain like myvivera.com
            $mainDomain = $parts[0];
        }

        return $mainDomain;
    }
}
function getProjectRootSegment()
{
    $path = explode(DIRECTORY_SEPARATOR, realpath(__DIR__)); // Adjust to get correct folder
    return end($path); // Returns last folder name (project folder)
}


defined('TIMEZONE') || define('TIMEZONE', "Africa/Lagos");
date_default_timezone_set(TIMEZONE);
defined('isLocalHost') || define('isLocalHost', isLocalHost());

defined('USER_PATH') || define('USER_PATH', 'user/');
defined('ADMIN_PATH') || define('ADMIN_PATH', 'admin/');
defined('AUTH_PATH') || define('AUTH_PATH', 'auth/');
defined('AUTHADMIN_PATH') || define('AUTHADMIN_PATH', 'authadmin/');
defined('HOME_PATH') || define('HOME_PATH', '');
defined('DEFAULT_CONTROLLER') || define('DEFAULT_CONTROLLER', 'home');
defined('CONTROLLER_PATH') || define('CONTROLLER_PATH', DEFAULT_CONTROLLER . '/');
defined('CONFIG_PATH') || define('CONFIG_PATH', 'config/');
defined('SIGNUP_PATH') || define('SIGNUP_PATH', 'signup');
defined('ADDON_PATH') || define('ADDON_PATH', 'addons/');
defined('LMS_PATH') || define('LMS_PATH', 'lms/');
defined('ENVIRONMENT') || define('ENVIRONMENT', isLocalHost ? 'development' : 'production');
defined('COOKIE_PREFIX') || define('COOKIE_PREFIX', getTopLevelDomain(findHostName()) . (isLocalHost() ? getProjectRootSegment() : '') . '_');

defined('LOCATION_PROTOCOL') || define('LOCATION_PROTOCOL', (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ? "https" : "http"));
defined('HTTPS') || define('HTTPS', LOCATION_PROTOCOL . '://');
