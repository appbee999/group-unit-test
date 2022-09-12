<?php
// Initialize session
session_start();

// Check if user is signed in, if not redirect to login page
if (!$_SESSION["isSignedIn"]) {
    header("location: login.php");
}

require_once "connect.php";

$postTitle = $postMessage = $postMessage_err = $postImage_err = $fileName = "";

// Check if submit button is pressed
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Set $postTitle and trim trailing spaces
    $postTitle = trim($_POST["postTitle"]);

    // Validate post message
    $postMessage = trim($_POST["postMessage"]);
    if (empty($postMessage)) {
        $postMessage_err = "Post message cannot be empty.";
    }

    if (isset($_FILES["postImage"])) {
        $currentDir = realpath(dirname(__FILE__));
        $image = $_FILES["postImage"];
        $fileName = $image["name"];
        $tempPath =  $currentDir . "\images\\" . baseName($fileName);
        $fileType = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));

        // Check if uploaded file is an image
        $allowTypes = array('jpg', 'png', 'jpeg');
        if (!in_array($fileType, $allowTypes)) {
            $postImage_err = "Only .jpg, .jpeg, and .png is allowed.";
        }

        // Check uploaded image size
        if ($image["size"] > 16777215) {
            $postImage_err = "Image can't be larger than 16MB";
        }
    }

    // Check if there is no error and move the file to temporary directory
    if (empty($postMessage_err) && empty($postImage_err) && (!isset($_FILES["postImage"]) || move_uploaded_file($image["tmp_name"],  $tempPath))) {
        // Sql statement to insert post details to database
        $insert = "INSERT INTO posts (postedBy, postUser, postTitle, postMsg,postImg) VALUES (:postedBy, :postUser, :postTitle, :postMsg,:postImg)";
        // Prepare insert statement
        if ($insertStmt = $pdo->prepare($insert)) {
            // Bind parameters to prevent sql injection
            $insertStmt->bindParam(":postedBy", $param_postedBy, PDO::PARAM_STR);
            $insertStmt->bindParam(":postUser", $param_postUser, PDO::PARAM_STR);
            $insertStmt->bindParam(":postTitle", $param_postTitle, PDO::PARAM_STR);
            $insertStmt->bindParam(":postMsg", $param_postMessage, PDO::PARAM_STR);
            $insertStmt->bindParam(":postImg", $param_postImage, PDO::PARAM_STR);

            $param_postedBy = $_SESSION["id"];
            $param_postUser =  $_SESSION["firstName"] . " " . $_SESSION["lastName"];
            $param_postTitle = $postTitle;
            $param_postMessage = $postMessage;
            $param_postImage = $fileName;

            // Execute insert statement
            if ($insertStmt->execute()) {
                // Redirect to Home page
                header("location: home.php");
            } else {
                echo "An error occurred. Please try again later.";
            }
        }
        // Unset insert statement
        unset($insertStmt);
    }
}

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="add_post.css">
    <title>New Post</title>
</head>

<body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <div class="mb-3 formGroup">
            <label for="postInput" class="form-label">Post Title</label>
            <input type="text" name="postTitle" class="form-control <?php echo (!empty($postTitle_err)) ? 'is-invalid' : ''; ?>" id="postInput" value="<?php echo $postTitle; ?>" placeholder="Post Title">
        </div>
        <div class="mb-3 formGroup">
            <label for="postInput" class="form-label">Post Message</label>
            <input type="text" name="postMessage" class="form-control <?php echo (!empty($postMessage_err)) ? 'is-invalid' : ''; ?>" id="postInput" value="<?php echo $postMessage; ?>" placeholder="Post Message">
            <span class="invalid-feedback"><?php echo $postMessage_err; ?></span>
        </div>

        <div class="formGroup">
            <label class="form-label" for="postImage">Post Image</label>
            <input type="file" class="form-control" name="postImage" />
            <span class="invalid-feedback"><?php echo $postImage_err; ?></span>
        </div>

        <center><button type="submit" class="btn btn-primary">Post</button></center>
    </form>
</body>

</html>