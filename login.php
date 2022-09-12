<?php
require_once "connect.php";

// Variables used throughout the login page
$email = $password = "";
$email_err = $password_err = "";

// Fires when sign in button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    $email = trim($_POST["email"]);
    if (empty($email)) {
        $email_err = "Please enter an email.";
    } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
        $email_err = "Please enter a valid email";
    }

    // Validate password
    $password = trim($_POST["password"]);
    if (empty($password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($password) < 8) {
        $password_err = "Password must have at least 8 characters.";
    }

    // Check if an error exists before creating user details
    if (empty($email_err) && empty($password_err)) {
        // Sql statement to check if an account with the email and password exists
        $read = "SELECT * FROM tblusers WHERE email = :email AND password = :password";

        // Prepare read statement
        if ($readStmt = $pdo->prepare($read)) {
            // Bind parameters to prevent sql injection
            $readStmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $readStmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $param_email = $email;
            $param_password = hash('sha256', $password);

            // Execute read statement
            if ($readStmt->execute()) {
                // If email and password is the same as the one in database, start a session
                if ($readStmt->rowCount() == 1) {
                    if ($row = $readStmt->fetch()) {
                        $id = $row["id"];
                        $firstName = $row["firstName"];
                        $lastName = $row["lastName"];
                        $email = $row["email"];

                        // Start a session
                        session_start();

                        // Store session data
                        $_SESSION["isSignedIn"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["firstName"] = $firstName;
                        $_SESSION["lastName"] = $lastName;
                        $_SESSION["email"] = $email;

                        // Redirect to home page
                        header("location: home.php");
                    }
                } else {
                    $password_err = "Invalid email or password";
                }
            } else {
                echo "An error occurred. Please try again later.";
            }

            // Unset statement
            unset($readStmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="auth.css">
</head>

<body>
    <div class="content">
        <h1>Sign in</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="formGroup">
                <label>Email</label>
                <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="formGroup">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <center>
                <div class="formGroup">
                    <input type="submit" class="btn btn-primary" value="Sign in">
                </div>
                <p>Don't have an account? <a href="register.php">Sign up</a></p>
            </center>
        </form>
    </div>
</body>

</html>