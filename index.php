<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to home dashboard
    header('Location: home.php');
    exit;
}

// If not logged in, redirect to login page
header('Location: login.php');
exit;
