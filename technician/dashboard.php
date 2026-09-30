<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Only technicians can access this page
if ($_SESSION["role"] != "technician") {
    die("Access denied. Technician account required.");
}

// Get all maintenance requests
$sql = "
    SELECT 
        maintenance_requests.id,
        maintenance_requests.problem_description,
        maintenance_requests.priority,
        maintenance_requests.status,
        maintenance_requests.created_at,
        users.full_name,
        computers.computer_name,
        computers.brand,
        computers.model
    FROM maintenance_requests
    INNER JOIN users
        ON maintenance_requests.user_id = users.id
    INNER JOIN computers
        ON maintenance_requests.computer_id = computers.id
    ORDER BY maintenance_requests.created_at DESC
";

$requests = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PCFix - Technician Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <h1>PCFix</h1>

    <h2>Technician Dashboard</h2>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
    </p>

    <hr>

    <h3>Maintenance Requests</h3>

    <?php if ($requests->num_rows > 0): ?>

        <table class="technician-table">

            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Computer</th>
                <th>Problem</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>

            <?php while ($request = $requests->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo $request["id"]; ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $request["full_name"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $request["computer_name"]
                        );
                        ?>

                        <?php if (!empty($request["brand"])): ?>

                            <br>

                            <?php
                            echo htmlspecialchars(
                                $request["brand"]
                            );
                            ?>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $request["problem_description"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $request["priority"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $request["status"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $request["created_at"]
                        );
                        ?>
                    </td>

                </tr>
                <td>
                <a href="request_details.php?id=<?php echo $request["id"]; ?>">
                   View / Manage
                 </a>
                </td>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>No maintenance requests found.</p>

    <?php endif; ?>

    <br><br>

<a href="../logout.php" class="btn btn-danger">Logout</a>

<a href="requests.php"
    style="
    display:inline-block;
    background:#007bff;
    color:white;
    padding:12px 20px;
    text-decoration:none;
    border-radius:5px;
    margin-top:20px;
    ">
        My Assigned Requests
</a>

</body>

</html>