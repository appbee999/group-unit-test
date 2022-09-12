<?php
// Initialize session
session_start();

// Check if user is signed in, if not redirect to login page
if (!$_SESSION["isSignedIn"]) {
    header("location: login.php");
}

require_once "connect.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="home.css">
    <header>
        <div class="title">
            <h2>Chat</h2>
        </div>
        <div class="links">
            <a href="home.php">Home</a>
            <a href="inbox.php">Inbox</a>
            <a href="chat.php">Chat</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>
</head>

<body>

    <div class="content">
        <h2>Chat</h2>
    </div>
</body>

</html>