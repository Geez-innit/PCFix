<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["role"] != "user") {
    die("Access denied.");
}

$user_id = $_SESSION["user_id"];

/* Count registered computers */
$computer_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM computers WHERE user_id = ?"
);

$computer_stmt->bind_param("i", $user_id);
$computer_stmt->execute();

$computer_result = $computer_stmt->get_result();
$computer_count = $computer_result->fetch_assoc()["total"];

$computer_stmt->close();


/* Count maintenance requests */
$request_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM maintenance_requests WHERE user_id = ?"
);

$request_stmt->bind_param("i", $user_id);
$request_stmt->execute();

$request_result = $request_stmt->get_result();
$request_count = $request_result->fetch_assoc()["total"];

$request_stmt->close();


/* Count pending requests */
$pending_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM maintenance_requests
     WHERE user_id = ?
     AND status IN ('Pending', 'Assigned', 'In Progress')"
);

$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();

$pending_result = $pending_stmt->get_result();
$pending_count = $pending_result->fetch_assoc()["total"];

$pending_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PCFix - User Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="header">
    <h1>PCFix</h1>

    <div>
        <span>
            Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </span>

        <a href="../logout.php" class="btn btn-danger">
            Logout
        </a>
    </div>
</div>

    <div class="container">

        <div class="card">

            <h2>User Dashboard</h2>

            <p>
                Welcome,
                <strong>
                    <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
                </strong>
            </p>

        </div>

        <h3>Dashboard Overview</h3>

        <div class="dashboard-grid">

            <div class="stat-card">
                <h3>Registered Computers</h3>
                <p><?php echo $computer_count; ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Maintenance Requests</h3>
                <p><?php echo $request_count; ?></p>
            </div>

            <div class="stat-card">
                <h3>Active Requests</h3>
                <p><?php echo $pending_count; ?></p>
            </div>

        </div>

        <br>

        <div class="card">

            <h3>Quick Actions</h3>

            <a href="../computers.php" class="btn">
                Register My Computer
            </a>

            <a href="../report_problem.php" class="btn">
                Report a Computer Problem
            </a>

            <a href="requests.php" class="btn">
                View My Maintenance Requests
            </a>

        </div>

        <div class="card">

            <h3>Account Information</h3>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($_SESSION["email"]); ?>
            </p>

            <p>
                <strong>Role:</strong>
                <?php echo htmlspecialchars($_SESSION["role"]); ?>
            </p>

        </div>

        <a href="../logout.php" class="btn btn-danger">
            Logout
        </a>

    </div>

</body>

</html>