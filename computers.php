<?php

session_start();
require_once "config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $computer_name = trim($_POST["computer_name"]);
    $brand = trim($_POST["brand"]);
    $model = trim($_POST["model"]);
    $operating_system = trim($_POST["operating_system"]);
    $serial_number = trim($_POST["serial_number"]);

    if (empty($computer_name)) {

        $message = "Computer name is required.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO computers 
            (user_id, computer_name, brand, model, operating_system, serial_number)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isssss",
            $_SESSION["user_id"],
            $computer_name,
            $brand,
            $model,
            $operating_system,
            $serial_number
        );

        if ($stmt->execute()) {
            $message = "Computer registered successfully!";
        } else {
            $message = "Failed to register computer.";
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

    <title>PCFix - My Computers</title>
</head>

<body>

    <h1>PCFix</h1>

    <h2>Register Your Computer</h2>

    <?php if (!empty($message)): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Computer Name</label><br>
        <input type="text" name="computer_name" placeholder="e.g. My Laptop" required>

        <br><br>

        <label>Brand</label><br>
        <input type="text" name="brand" placeholder="e.g. HP">

        <br><br>

        <label>Model</label><br>
        <input type="text" name="model" placeholder="e.g. EliteBook 840">

        <br><br>

        <label>Operating System</label><br>
        <input type="text" name="operating_system" placeholder="e.g. Windows 11">

        <br><br>

        <label>Serial Number</label><br>
        <input type="text" name="serial_number">

        <br><br>

        <button type="submit">
            Register Computer
        </button>

    </form>

    <br>

    <a href="dashboard.php">Back to Dashboard</a>

</body>

</html>