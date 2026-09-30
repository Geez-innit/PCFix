<?php
session_start();

require_once "../config/database.php";

// Only admins can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

// Get all maintenance requests
$sql = "SELECT 
            mr.id,
            u.full_name AS user_name,
            c.computer_name,
            c.brand,
            c.model,
            mr.problem_description,
            mr.priority,
            mr.status,
            t.full_name AS technician_name,
            mr.created_at
        FROM maintenance_requests mr
        JOIN users u ON mr.user_id = u.id
        JOIN computers c ON mr.computer_id = c.id
        LEFT JOIN users t ON mr.technician_id = t.id
        ORDER BY mr.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - PCFix</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: auto;
        }

        h1 {
            color: #333;
        }

        .top-links {
            margin-bottom: 20px;
        }

        .top-links a {
            text-decoration: none;
            padding: 10px 15px;
            margin-right: 10px;
            border-radius: 5px;
            color: white;
            background: #007bff;
        }

        .logout {
            background: #dc3545 !important;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: bold;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .assigned {
            background: #cce5ff;
            color: #004085;
        }

        .progress {
            background: #d1ecf1;
            color: #0c5460;
        }

        .resolved {
            background: #d4edda;
            color: #155724;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .priority {
            font-weight: bold;
        }
    </style>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

    <h1>Maintenance Requests</h1>

    <div class="top-links">
        <a href="dashboard.php">Admin Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="../logout.php" class="logout">Logout</a>
    </div>

    <div class="table-container">

        <table>

            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Computer</th>
                <th>Problem</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Technician</th>
                <th>Action</th>
                <th>Date</th>
            </tr>

            <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td><?php echo $row["id"]; ?></td>

                        <td>
                            <?php echo htmlspecialchars($row["user_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["computer_name"]); ?><br>
                            <small>
                                <?php echo htmlspecialchars($row["brand"] . " " . $row["model"]); ?>
                            </small>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["problem_description"]); ?>
                        </td>

                        <td class="priority">
                            <?php echo htmlspecialchars($row["priority"]); ?>
                        </td>

                        <td>
                            <span class="status
                                <?php
                                    if ($row["status"] == "Pending") {
                                        echo "pending";
                                    } elseif ($row["status"] == "Assigned") {
                                        echo "assigned";
                                    } elseif ($row["status"] == "In Progress") {
                                        echo "progress";
                                    } elseif ($row["status"] == "Resolved") {
                                        echo "resolved";
                                    } elseif ($row["status"] == "Cancelled") {
                                        echo "cancelled";
                                    }
                                ?>">
                                <?php echo htmlspecialchars($row["status"]); ?>
                            </span>
                        </td>

                        <td>
                           <?php
                             echo $row["technician_name"]
                             ? htmlspecialchars($row["technician_name"])
                             : "Not Assigned";
                             ?>
                        </td>

                        <td>
                            <a href="assign_technician.php?id=<?php echo $row["id"]; ?>"
                                style="
                                background:#007bff;
                                color:white;
                                padding:7px 10px;
                                text-decoration:none;
                                border-radius:5px;
                                display:inline-block;
                                ">
                                    <?php
                                        echo $row["technician_name"]
                                        ? "Reassign"
                                        : "Assign";
                                    ?>
                            </a>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["created_at"]); ?>
<                       /td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8" style="text-align:center;">
                        No maintenance requests found.
                    </td>
                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>
</html>