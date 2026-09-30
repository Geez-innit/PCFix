<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["role"] != "admin") {
    die("Access denied. Administrator account required.");
}


/* Total Users */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
);

$total_users = $result->fetch_assoc()["total"];


/* Total Technicians */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'technician'"
);

$total_technicians = $result->fetch_assoc()["total"];


/* Total Computers */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM computers"
);

$total_computers = $result->fetch_assoc()["total"];


/* Total Maintenance Requests */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM maintenance_requests"
);

$total_requests = $result->fetch_assoc()["total"];


/* Active Requests */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM maintenance_requests
     WHERE status IN ('Pending', 'Assigned', 'In Progress')"
);

$active_requests = $result->fetch_assoc()["total"];


/* Resolved Requests */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM maintenance_requests
     WHERE status = 'Resolved'"
);

$resolved_requests = $result->fetch_assoc()["total"];


/* Recent Maintenance Requests */

$sql = "
    SELECT
        maintenance_requests.id,
        maintenance_requests.problem_description,
        maintenance_requests.priority,
        maintenance_requests.status,
        maintenance_requests.created_at,

        users.full_name,

        computers.computer_name,
        computers.brand

    FROM maintenance_requests

    INNER JOIN users
        ON maintenance_requests.user_id = users.id

    INNER JOIN computers
        ON maintenance_requests.computer_id = computers.id

    ORDER BY maintenance_requests.created_at DESC

    LIMIT 10
";

$requests = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PCFix - Admin Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <h1>PCFix</h1>

    <h2>Administrator Dashboard</h2>

    <p>
        Welcome,
        <strong>
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </strong>
    </p>

    <hr>


    <!-- SYSTEM OVERVIEW -->

    <h3>System Overview</h3>

    <p>
        <strong>Total Users:</strong>
        <?php echo $total_users; ?>
    </p>

    <p>
        <strong>Total Technicians:</strong>
        <?php echo $total_technicians; ?>
    </p>

    <p>
        <strong>Total Computers:</strong>
        <?php echo $total_computers; ?>
    </p>

    <p>
        <strong>Total Maintenance Requests:</strong>
        <?php echo $total_requests; ?>
    </p>

    <p>
        <strong>Active Requests:</strong>
        <?php echo $active_requests; ?>
    </p>

    <p>
        <strong>Resolved Requests:</strong>
        <?php echo $resolved_requests; ?>
    </p>


    <hr>


    <!-- RECENT REQUESTS -->

    <h3>Recent Maintenance Requests</h3>

    <?php if ($requests && $requests->num_rows > 0): ?>

        <table border="1" cellpadding="10">

            <tr>

                <th>ID</th>

                <th>User</th>

                <th>Computer</th>

                <th>Problem</th>

                <th>Priority</th>

                <th>Status</th>

                <th>Date</th>

            </tr>


            <?php while ($request = $requests->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo $request["id"]; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($request["full_name"]); ?>
                    </td>

                    <td>

                        <?php echo htmlspecialchars($request["computer_name"]); ?>

                        <?php if (!empty($request["brand"])): ?>

                            <br>

                            <?php echo htmlspecialchars($request["brand"]); ?>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?php echo htmlspecialchars($request["problem_description"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($request["priority"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($request["status"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($request["created_at"]); ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>No maintenance requests found.</p>

    <?php endif; ?>


    <hr>


    <!-- ADMIN OPTIONS -->

    <h3>Administration</h3>

        <a href="users.php" class="btn">
            Manage Users
        </a>

        <a href="requests.php" class="btn">
            Manage Requests
        </a>

        <a href="reports.php" class="btn">
            Reports & Statistics
        </a>  

    <br>

        <a href="../logout.php" class="btn btn-danger">
            Logout
        </a>
</body>

</html>