<?php
header("Location: login.php");
exit();
?>
<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, full_name, email, password, role FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] == "admin") {
                 header("Location: admin/dashboard.php");
                 exit();

                } elseif ($user["role"] == "technician") {
                 header("Location: technician/dashboard.php");
                 exit();

                } else {
                  header("Location: user/dashboard.php");
                  exit();
                }
                

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "No account found with that email.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PCFix - Login</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="container">

        <div class="card">

            <h1>PCFix</h1>

            <h2>Login</h2>

            <?php if (!empty($message)): ?>

                <div class="alert alert-error">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="form-group">

                    <label for="email">Email</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >

                </div>

                <button type="submit" class="btn">
                    Login
                </button>

            </form>

            <p>
                    Don't have an account?
                    <a href="register.php" class="btn btn-success">
                        Register Here
                    </a>
            </p>

        </div>

    </div>

</body>

</html>
