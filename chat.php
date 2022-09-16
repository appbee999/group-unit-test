<?php
// Initialize session
session_start();

// Check if user is signed in, if not redirect to login page
if (!$_SESSION["isSignedIn"]) {
    header("location: login.php");
}

require_once "connect.php";

$userReceiver = $userReceiverID = "";

// Sql statement to read all users
$readStmt = $pdo->prepare("SELECT * FROM tblusers");

// Set result variable when done fetching
if ($readStmt->execute()) {
    $result = $readStmt->fetchAll();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    foreach ($result as $user) {
        if ($user["id"] == $_POST["id"]) {
            $userReceiver = $user["firstName"] . ' ' . $user["lastName"];
        }
    }
    $userReceiverID = $_POST["id"];

    // Sql statement to read inbox
    $chatReadStmt = $pdo->prepare("SELECT * FROM inbox WHERE (receiver = :userID AND sender = :otherID) OR (sender = :userID AND receiver = :otherID)");

    $chatReadStmt->bindParam(":userID", $param_id, PDO::PARAM_STR);
    $chatReadStmt->bindParam(":otherID", $param_other_id, PDO::PARAM_STR);
    $param_id = $_SESSION["id"];
    $param_other_id = $_POST["id"];

    // Set result variable when done fetching
    if ($chatReadStmt->execute()) {
        $chatResults = $chatReadStmt->fetchAll();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"]) && isset($_POST["receiverID"]) && $_POST["message"] != "") {
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
    $chatInsertStmt->execute();

    // Unset insert statement
    unset($chatInsertStmt);
}

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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="chat.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <header>
        <h2 class="title">Chat</h2>
        <div class="links">
            <a href="home.php">Home</a>
            <a href="chat.php">Chat</a>
            <a href="my_posts.php">My Posts</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php" onclick="confirmLogout()">Logout</a>
            <script>
                function confirmLogout() {
                    if (confirm("Confirm Logout")) {
                        location.href = "logout.php"
                    }
                }
            </script>
        </div>
    </header>
</head>

<body>

    <div class="content">
        <h2 class="chatTitle">Chat</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="dropdown">
                <button class="btn btn-primary  btn-lg dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php
                    echo $userReceiver != "" ? $userReceiver :
                        "Who would you like to Chat With?";
                    ?>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <?php
                    foreach ($result as $user) {
                        if ($user["id"] != $_SESSION["id"]) {
                            $username = $user["firstName"] . ' ' . $user["lastName"];
                            echo '<button class="dropdown-item" type="submit" name="id" value="' .  $user["id"] . '">' . $username . '</button>';
                        }
                    }
                    ?>
                </div>
            </div>
        </form>

        <div class="chatContent">
            <?php
            if (!empty($chatResults)) {
                foreach ($chatResults as $chat) {
                    $isSentByUser = $chat["sender"] == $_SESSION["id"];
                    $isSentByUserClass = $isSentByUser ? "text-bg-primary sender" : "text-bg-secondary receiver";
                    echo '<div class="card rounded-pill mb-3 ' . $isSentByUserClass . '" style="max-width: 18rem;">';
                    echo '<h6 class="message card-text">' . $chat["message"] . '</h6>';
                    echo '</div>';
                }
            }
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="formGroup chatForm">
                    <input type="hidden" name="receiverID" value="<?php echo $userReceiverID; ?>">

                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="message" placeholder="Send a message">
                        <button class="material-symbols-outlined input-group-text" type="submit">
                            send
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</body>

</html>