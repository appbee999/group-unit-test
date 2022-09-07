<?php
// Initialize session
session_start();

// Unset all variables in session
session_unset();

// Destory the session to log user out
session_destroy();

// Redirect to login page
header("location: login.php");
