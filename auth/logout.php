<?php
// auth/logout.php

// 1. Start the session to access it
session_start();

// 2. Unset all of the session variables
$_SESSION = [];

// 3. Delete the session cookie
// This will delete the session, not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session
session_destroy();

// 5. Redirect to the login page
header('Location: login.php');
exit();
?>
