<?php
// Initialize session
session_start();

// Check if user is signed in, if not redirect to login page
if (!$_SESSION["isSignedIn"]) {
    header("location: login.php");
}

require_once "connect.php";

$dbUsername = $dbEmail = $dbProfilePic = "";
$email = $currentPassword = $newPassword = "";
$email_err = $current_password_err = $new_password_err = $profile_pic_err = "";

// Prepare read statement
if ($readStmt = $pdo->prepare("SELECT * FROM tblusers WHERE id = :id")) {
    // Bind parameters to prevent sql injection
    $readStmt->bindParam(":id", $param_id, PDO::PARAM_STR);
    $param_id = $_SESSION["id"];
    // Execute read statement
    if ($readStmt->execute()) {
        // If email and password is the same as the one in databse, start a session
        if ($readStmt->rowCount() == 1) {
            $row = $readStmt->fetch();
            $dbUsername = $row["firstName"] . " " . $row["lastName"];
            $dbEmail = $row["email"];
            $dbProfilePic = $row["profilePic"];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["postImage"])) {
        // Validate email
        $email = trim($_POST["email"]);
        if (!empty($email) && !preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
            $email_err = "Please enter a valid email";
        }

        // Validate current password
        $currentPassword = trim($_POST["currentPassword"]);
        if (!empty($currentPassword) && strlen($currentPassword) < 8) {
            $current_password_err = "Password must have at least 8 characters.";
        }

        // Validate new password
        $newPassword = trim($_POST["newPassword"]);
        if (!empty($currentPassword) && empty($newPassword)) {
            $new_password_err = "Type your new password.";
        } elseif (!empty($newPassword) && strlen($newPassword) < 8) {
            $new_password_err = "Password must have at least 8 characters.";
        }

        // If user wants to update email, make sure that the same email doesn't already exist in the database
        if (!empty($email) && empty($email_err)) {
            // Prepare read statement
            if ($readStmt = $pdo->prepare("SELECT * FROM tblusers WHERE email = :email")) {
                // Bind parameters to prevent sql injection
                $readStmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                $param_email = $email;

                // Execute read statement
                if ($readStmt->execute()) {
                    // If email and password is the same as the one in databse, start a session
                    if ($readStmt->rowCount() < 1) {
                        if ($updateStmt = $pdo->prepare("UPDATE tblusers SET email = :email WHERE id = :id")) {
                            // Bind parameters to prevent sql injection
                            $updateStmt->bindParam(":id", $param_id, PDO::PARAM_STR);
                            $updateStmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                            $param_id = $_SESSION["id"];
                            $param_email = $email;

                            if ($updateStmt->execute()) {
                                $dbEmail = $email;
                            }
                        }
                        // Unset update statement
                        unset($updateStmt);
                    }
                }
            }
            // Unset read statement
            unset($readStmt);
        }

        if (empty($email_err) && empty($current_password_err) && empty($new_password_err) && $_FILES["profilePic"]['size'] == 0) {
            $currentDir = realpath(dirname(__FILE__));
            $image = $_FILES["profilePic"];
            $fileName = $image["name"];
            $tempPath =  $currentDir . $fileName;
            $fileType = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));

            // Check if uploaded file is an image
            $allowTypes = array('jpg', 'png', 'jpeg');
            if (!in_array($fileType, $allowTypes)) {
                $profile_pic_err = "Only .jpg, .jpeg, and .png is allowed.";
            }

            // Check uploaded image size
            if ($image["size"] > 16777215) {
                $profile_pic_err = "Image can't be larger than 16MB";
            }

            if (empty($profile_pic_err) && move_uploaded_file($image["tmp_name"],  $tempPath)) {
                if ($updateStmt = $pdo->prepare("UPDATE tblusers SET profilePic = :profilePic WHERE id = :id")) {
                    // Bind parameters to prevent sql injection
                    $updateStmt->bindParam(":id", $param_id, PDO::PARAM_STR);
                    $updateStmt->bindParam(":profilePic", $param_profile_pic, PDO::PARAM_STR);
                    $param_id = $_SESSION["id"];
                    $param_profile_pic = $fileName;
                    if ($updateStmt->execute()) {
                        echo "update work";
                    }
                }
                // Unset update statement
                unset($updateStmt);
            }
        }

        // Check if current password is correct
        if (!empty($currentPassword) && !empty($newPassword) && empty($current_password_err) && empty($new_password_err)) {
            // Prepare read statement
            if ($readStmt = $pdo->prepare("SELECT * FROM tblusers WHERE id = :id AND password = :password")) {
                // Bind parameters to prevent sql injection
                $readStmt->bindParam(":id", $param_id, PDO::PARAM_STR);
                $readStmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                $param_id = $_SESSION["id"];
                $param_password = hash('sha256', $currentPassword);

                // Execute read statement
                if ($readStmt->execute()) {
                    // If email and password is the same as the one in database, update password
                    if ($readStmt->rowCount() == 1) {
                        if ($updateStmt = $pdo->prepare("UPDATE tblusers SET password = :password WHERE id = :id")) {
                            // Bind parameters to prevent sql injection
                            $updateStmt->bindParam(":id", $param_id, PDO::PARAM_STR);
                            $updateStmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                            $param_id = $_SESSION["id"];
                            $param_password = hash('sha256', $newPassword);
                            $updateStmt->execute();
                        }
                        // Unset update statement
                        unset($updateStmt);
                    } else {
                        $current_password_err = "Current password is incorrect.";
                    }
                }
            }
            // Unset read statement
            unset($readStmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Settings</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="home.css">
    <header>
        <div class="title">
            <h2>Settings</h2>
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
        <h2><?php echo $dbUsername ?>'s Settings</h2>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Edit Page</title>
        </head>

        <body>
            <div class="spacer"></div>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                <div class="formGroup">
                    <label class="form-label" for="profilePic">Profile Picture</label>
                    <input type="file" class="form-control" name="profilePic" />
                    <span class="invalid-feedback"><?php echo $profile_pic_err; ?></span>
                </div>
                <div class="spacer"></div>
                <div class="formGroup">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $dbEmail; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="spacer"></div>
                <div class="formGroup">
                    <label>Current Password</label>
                    <input type="password" name="currentPassword" class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $current_password_err; ?></span>
                </div>
                <div class="spacer"></div>
                <div class="formGroup">
                    <label>New Password</label>
                    <input type="password" name="newPassword" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                </div>
                <div class="spacer"></div>
                <center><button type="submit" class="btn btn-primary">Save</button></center>
            </form>
        </body>

        </html>




    </div>
</body>

</html>