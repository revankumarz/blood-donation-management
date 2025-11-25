<?php
require_once 'config.php';

if (is_logged_in()) {
    $user_id = get_user_id();
    $username = $_SESSION['username'];

    // Log the logout action
    log_action($user_id, "USER_LOGOUT", "User logged out: $username");

    // Destroy the session
    session_unset();
    session_destroy();
}

// Redirect to home page
header("Location: index.html");
exit();
?>
