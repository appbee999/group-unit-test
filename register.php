<?php
require_once "connect.php";

// Variables used throughout the register page
$firstName = $lastName = $email = $password = $confirm_password = "";
$firstName_err = $lastName_err = $email_err = $password_err = $confirm_password_err = "";

// Fires when sign up button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate first name
    $firstName = trim($_POST["firstName"]);
    if (empty($firstName)) {
        $firstName_err = "Please enter a first name.";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $firstName)) {
        $firstName_err = "First name can only contain letters and numbers.";
    }

    // Validate last name
    $lastName = trim($_POST["lastName"]);
    if (empty($lastName)) {
        $lastName_err = "Please enter a last name.";
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', trim($lastName))) {
        $lastName_err = "Last name can only contain letters and numbers.";
    }

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

    // Validate confirm password
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm password.";
    } else {
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check if an error exists before creating user details
    if (empty($firstName_err) && empty($lastName_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        // Sql statement to check if email already exists
        $read = "SELECT * FROM tblusers WHERE email = :email";

        // Prepare read statement
        if ($readStmt = $pdo->prepare($read)) {
            // Bind email parameter to prevent sql injection
            $readStmt->bindParam(":email", $read_param_email, PDO::PARAM_STR);
            $read_param_email = $email;

            // Execute read statement
            if ($readStmt->execute()) {
                // Checks if query found an email that already exists in the database
                $isEmailTaken = $readStmt->rowCount();

                // Run insert query if email is not already taken
                if (!$isEmailTaken) {
                    // Sql statement to insert user details to database
                    $insert = "INSERT INTO tblusers (firstName, lastName, email, password) VALUES (:firstName, :lastName, :email, :password)";

                    // Prepare insert statement
                    if ($insertStmt = $pdo->prepare($insert)) {
                        // Bind parameters to prevent sql injection
                        $insertStmt->bindParam(":firstName", $param_firstName, PDO::PARAM_STR);
                        $insertStmt->bindParam(":lastName", $param_lastName, PDO::PARAM_STR);
                        $insertStmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                        $insertStmt->bindParam(":password", $param_password, PDO::PARAM_STR);

                        $param_firstName = $firstName;
                        $param_lastName = $lastName;
                        $param_email = $email;
                        // Hash password to prevent storing them in plaintext
                        $param_password = hash('sha256', $password);

                        // Execute insert statement
                        if ($insertStmt->execute()) {
                            // Redirect to login page
                            header("location: login.php");
                        } else {
                            echo "An error occurred. Please try again later.";
                        }

                        // Unset insert statement
                        unset($insertStmt);
                    }
                } else {
                    $confirm_password_err = "An account with the same email already exists. Please login instead.";
                }
            } else {
                echo "An error occurred. Please try again later.";
            }

            // Unset read statement
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
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="auth.css">
</head>

<body>
    <div class="content">
        <h1>Sign Up</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="formGroup">
                <label>First Name</label>
                <input type="text" name="firstName" class="form-control <?php echo (!empty($firstName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $firstName; ?>">
                <span class="invalid-feedback"><?php echo $firstName_err; ?></span>
            </div>
            <div class="formGroup">
                <label>Last Name</label>
                <input type="text" name="lastName" class="form-control <?php echo (!empty($lastName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $lastName; ?>">
                <span class="invalid-feedback"><?php echo $lastName_err; ?></span>
            </div>
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
            <div class="formGroup">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <center>
                <div class="formGroup">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </center>
        </form>
    </div>
</body>

</html>