<?php

class Security {

    /**
     * Generate a CSRF token and store it in the session.
     * @return string The CSRF token.
     */
    public static function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate the provided CSRF token against the session token.
     * @param string $token The token to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateCsrfToken($token) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            // Token is valid. We do not unset it to allow multiple form submissions (e.g. back button, multiple tabs)
            return true;
        }
        return false;
    }

}