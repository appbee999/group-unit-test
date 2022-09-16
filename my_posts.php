<?php
// Initialize session
session_start();

// Check if user is signed in, if not redirect to login page
if (!$_SESSION["isSignedIn"]) {
    header("location: login.php");
}

require_once "connect.php";

// Sql statement to read user details
$userReadStmt = $pdo->prepare("SELECT * FROM tblusers WHERE id = :id LIMIT 1");

// Bind parameters
$userReadStmt->bindParam(":id", $param_user_id, PDO::PARAM_STR);
$param_user_id = $_SESSION["id"];

// Set result variable when done fetching
if ($userReadStmt->execute()) {
    $user = array_values($userReadStmt->fetchAll())[0];
}

// Sql statement to read posts
$readStmt = $pdo->prepare("SELECT * FROM posts WHERE postedBy = :id");

// Bind parameters
$readStmt->bindParam(":id", $param_id, PDO::PARAM_STR);
$param_id = $_SESSION["id"];

// Set result variable when done fetching
if ($readStmt->execute()) {
    $result = $readStmt->fetchAll();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="home.css">
    <header class="bg-primary">
        <div class="title">
            <h2>My Posts</h2>
        </div>
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
        <div class="spacer"></div>

        <div class="d-flex flex-row" style="align-items:center;">
            <?php
            if (!empty($user["profilePic"])) {
                echo '<img src="images/' . $user["profilePic"] .  '" class="rounded-circle" width="100px" height="100px">';
            }
            ?>

            <h2 class="<?php echo !empty($user["profilePic"]) ? 'p-4' : '' ?>"><?php echo $user["firstName"] . " " . $user["lastName"]; ?></h2>
        </div>
        <div class="spacer"></div>
        <h2>These are your recent posts</h2>
        <div class="spacer"></div>

        <?php
        foreach ($result as $post) {
            echo "<div class='card post'>";
            echo "<h2 class='postUser'>" . $post["postUser"] .  "</h2>";
            echo "<h2 class ='card-title postTitle'>" . $post["postTitle"] .  "</h2>";
            echo "<p class='card-text postMsg'>" . $post["postMsg"] .  "</p>";
            echo "";
            if (!empty($post["postImg"])) {
                echo '<img  src="images/' . $post["postImg"] .  '">';
                echo '<div class="spacer"></div>';
            }

            echo "</div>";
        }

        ?>

    </div>


    <a class="btn btn-primary p-3 btn-l shadow-md postButton" type="button" href="add_post.php">
        <span class="material-symbols-outlined">
            add
        </span>
    </a>

</body>

</html>