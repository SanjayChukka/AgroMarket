<?php

// Start session only if one is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent protected pages from being cached
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

function require_login(string $requiredRole, string $loginPath): void
{
    // Check whether the user is logged in
    if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
        header("Location: " . $loginPath);
        exit;
    }

    // Check whether the user has the required role
    if ($_SESSION['user_role'] !== $requiredRole) {
        header("Location: " . $loginPath);
        exit;
    }
}
