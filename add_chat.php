<?php
// Initialize session
session_start();

// Check if user is signed in, if not redirect to login page
if (!$_SESSION["isSignedIn"]) {
    header("location: login.php");
}

require_once "connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"]) && isset($_POST["receiverID"])) {
    $chatInsertStmt = $pdo->prepare("INSERT INTO inbox (receiver,sender,image,message) VALUES (:receiver,:sender,:image,:message)");

    // Bind parameters to prevent sql injection
    $chatInsertStmt->bindParam(":receiver", $param_receiver, PDO::PARAM_STR);
    $chatInsertStmt->bindParam(":sender", $param_sender, PDO::PARAM_STR);
    $chatInsertStmt->bindParam(":image", $param_image, PDO::PARAM_STR);
    $chatInsertStmt->bindParam(":message", $param_message, PDO::PARAM_STR);

    $param_receiver =  $_POST["receiverID"];
    $param_sender = $_SESSION["id"];
    $param_image = null;
    $param_message = $_POST["message"];

    // Execute insert statement
    if ($chatInsertStmt->execute()) {
    } else {
    }

    // Unset insert statement
    unset($chatInsertStmt);
}
