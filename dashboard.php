<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>PCFix Dashboard</title>
</head>

<body>

    <h1>Welcome to PCFix</h1>

    <h2>
        Hello, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!
    </h2>

    <p>You are successfully logged in.</p>

    <p>
        Your role:
        <?php echo htmlspecialchars($_SESSION["role"]); ?>
    </p>

    <br>

<a href="logout.php">Logout</a>

</body>

</html>