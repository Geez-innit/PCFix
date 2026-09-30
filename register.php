<?php

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $computer_name = trim($_POST["computer_name"]);
    $brand = trim($_POST["brand"]);
    $model = trim($_POST["model"]);
    $operating_system = trim($_POST["operating_system"]);
    $serial_number = trim($_POST["serial_number"]);

    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($computer_name) ||
        empty($brand) ||
        empty($model) ||
        empty($operating_system)
    ) {

        $message = "Please fill in all required fields.";

    } else {

        // Check whether email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "That email is already registered.";

        } else {

            // Securely hash the password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Start transaction
            $conn->begin_transaction();

            try {

                // Insert user
                $stmt = $conn->prepare(
                    "INSERT INTO users 
                    (full_name, email, password) 
                    VALUES (?, ?, ?)"
                );

                $stmt->bind_param(
                    "sss",
                    $full_name,
                    $email,
                    $hashed_password
                );

                if (!$stmt->execute()) {
                    throw new Exception("Failed to create user.");
                }

                // Get newly created user's ID
                $user_id = $conn->insert_id;

                $stmt->close();

                // Insert computer
                $computer_stmt = $conn->prepare(
                    "INSERT INTO computers
                    (user_id, computer_name, brand, model, operating_system, serial_number)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $computer_stmt->bind_param(
                    "isssss",
                    $user_id,
                    $computer_name,
                    $brand,
                    $model,
                    $operating_system,
                    $serial_number
                );

                if (!$computer_stmt->execute()) {
                    throw new Exception("Failed to register computer.");
                }

                $computer_stmt->close();

                // Everything worked
                $conn->commit();

                $message = "Registration successful! Your account and computer have been registered.";

            } catch (Exception $e) {

                // Undo everything if something failed
                $conn->rollback();

                $message = "Registration failed. Please try again.";
            }
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PCFix - Register</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div class="container">

        <div class="card">

            <h1>PCFix</h1>

            <h2>Create Account</h2>

            <?php if (!empty($message)): ?>

                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <h3>Account Information</h3>

                <div class="form-group">

                    <label for="full_name">Full Name</label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        required
                    >

                </div>


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


                <h3>Computer Information</h3>


                <div class="form-group">

                    <label for="computer_name">Computer Name</label>

                    <input
                        type="text"
                        id="computer_name"
                        name="computer_name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="brand">Brand</label>

                    <input
                        type="text"
                        id="brand"
                        name="brand"
                        placeholder="e.g. HP, Lenovo, Dell"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="model">Model</label>

                    <input
                        type="text"
                        id="model"
                        name="model"
                        placeholder="e.g. EliteBook 840 G7"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="operating_system">Operating System</label>

                    <input
                        type="text"
                        id="operating_system"
                        name="operating_system"
                        placeholder="e.g. Windows 11"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="serial_number">Serial Number (Optional)</label>

                    <input
                        type="text"
                        id="serial_number"
                        name="serial_number"
                    >

                </div>


                <button type="submit" class="btn btn-success">
                    Create Account & Register Computer
                </button>

            </form>


            <p>

                Already have an account?

                <a href="login.php" class="btn">
                    Login
                </a>

            </p>

        </div>

    </div>

</body>

</html>