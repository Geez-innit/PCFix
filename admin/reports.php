<?php
session_start();

require_once "../config/database.php";

// Only admins can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

// Request statistics
$total_sql = "SELECT COUNT(*) AS total FROM maintenance_requests";
$pending_sql = "SELECT COUNT(*) AS total FROM maintenance_requests WHERE status = 'Pending'";
$assigned_sql = "SELECT COUNT(*) AS total FROM maintenance_requests WHERE status = 'Assigned'";
$progress_sql = "SELECT COUNT(*) AS total FROM maintenance_requests WHERE status = 'In Progress'";
$resolved_sql = "SELECT COUNT(*) AS total FROM maintenance_requests WHERE status = 'Resolved'";
$cancelled_sql = "SELECT COUNT(*) AS total FROM maintenance_requests WHERE status = 'Cancelled'";

$total = $conn->query($total_sql)->fetch_assoc()["total"];
$pending = $conn->query($pending_sql)->fetch_assoc()["total"];
$assigned = $conn->query($assigned_sql)->fetch_assoc()["total"];
$progress = $conn->query($progress_sql)->fetch_assoc()["total"];
$resolved = $conn->query($resolved_sql)->fetch_assoc()["total"];
$cancelled = $conn->query($cancelled_sql)->fetch_assoc()["total"];


// Priority statistics
$priority_sql = "SELECT priority, COUNT(*) AS total
                 FROM maintenance_requests
                 GROUP BY priority";

$priority_result = $conn->query($priority_sql);


// Technician workload
$tech_sql = "SELECT
                u.full_name,
                COUNT(mr.id) AS total_requests
             FROM users u
             LEFT JOIN maintenance_requests mr
                ON u.id = mr.technician_id
             WHERE u.role = 'technician'
             GROUP BY u.id, u.full_name
             ORDER BY total_requests DESC";

$tech_result = $conn->query($tech_sql);


// Recent maintenance history
$history_sql = "SELECT
                    mr.id,
                    u.full_name AS user_name,
                    c.computer_name,
                    mr.status,
                    mr.priority,
                    mr.created_at
                FROM maintenance_requests mr
                JOIN users u
                    ON mr.user_id = u.id
                JOIN computers c
                    ON mr.computer_id = c.id
                ORDER BY mr.created_at DESC
                LIMIT 10";

$history_result = $conn->query($history_sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reports & Statistics - PCFix</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1300px;
            margin: auto;
        }

        h1,
        h2 {
            color: #333;
        }

        .top-links {
            margin-bottom: 25px;
        }

        .top-links a {
            text-decoration: none;
            padding: 10px 15px;
            margin-right: 10px;
            border-radius: 5px;
            color: white;
            background: #007bff;
        }

        .top-links .logout {
            background: #dc3545;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            margin-top: 0;
            color: #666;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
            font-weight: bold;
        }

        .resolved {
            color: #28a745;
        }

        .pending {
            color: #856404;
        }

        .assigned {
            color: #004085;
        }

        .progress {
            color: #0c5460;
        }

        .cancelled {
            color: #721c24;
        }

        @media (max-width: 800px) {

            .stats {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 14px;
            }

        }

    </style>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="container">

    <h1>Reports & Statistics</h1>

    <div class="top-links">

        <a href="dashboard.php">
            Admin Dashboard
        </a>

        <a href="requests.php">
            Maintenance Requests
        </a>

        <a href="users.php">
            Manage Users
        </a>

        <a href="../logout.php" class="logout">
            Logout
        </a>

    </div>


    <!-- REQUEST STATISTICS -->

    <div class="stats">

        <div class="stat-card">
            <h3>Total Requests</h3>
            <div class="stat-number">
                <?php echo $total; ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Pending</h3>
            <div class="stat-number">
                <?php echo $pending; ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Assigned</h3>
            <div class="stat-number">
                <?php echo $assigned; ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>In Progress</h3>
            <div class="stat-number">
                <?php echo $progress; ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Resolved</h3>
            <div class="stat-number">
                <?php echo $resolved; ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Cancelled</h3>
            <div class="stat-number">
                <?php echo $cancelled; ?>
            </div>
        </div>

    </div>


    <!-- PRIORITY -->

    <div class="section">

        <h2>Requests by Priority</h2>

        <table>

            <tr>
                <th>Priority</th>
                <th>Total Requests</th>
            </tr>

            <?php if ($priority_result->num_rows > 0): ?>

                <?php while ($row = $priority_result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($row["priority"]); ?>
                        </td>

                        <td>
                            <?php echo $row["total"]; ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="2">
                        No priority data available.
                    </td>
                </tr>

            <?php endif; ?>

        </table>

    </div>


    <!-- TECHNICIAN WORKLOAD -->

    <div class="section">

        <h2>Technician Workload</h2>

        <table>

            <tr>
                <th>Technician</th>
                <th>Assigned Requests</th>
            </tr>

            <?php if ($tech_result->num_rows > 0): ?>

                <?php while ($row = $tech_result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($row["full_name"]); ?>
                        </td>

                        <td>
                            <?php echo $row["total_requests"]; ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="2">
                        No technicians found.
                    </td>
                </tr>

            <?php endif; ?>

        </table>

    </div>


    <!-- RECENT HISTORY -->

    <div class="section">

        <h2>Recent Maintenance History</h2>

        <table>

            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Computer</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

            <?php if ($history_result->num_rows > 0): ?>

                <?php while ($row = $history_result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            #<?php echo $row["id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["user_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["computer_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["priority"]); ?>
                        </td>

                        <td class="status
                            <?php
                            if ($row["status"] == "Resolved") {
                                echo "resolved";
                            } elseif ($row["status"] == "Pending") {
                                echo "pending";
                            } elseif ($row["status"] == "Assigned") {
                                echo "assigned";
                            } elseif ($row["status"] == "In Progress") {
                                echo "progress";
                            } elseif ($row["status"] == "Cancelled") {
                                echo "cancelled";
                            }
                            ?>
                        ">

                            <?php echo htmlspecialchars($row["status"]); ?>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["created_at"]); ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="6">
                        No maintenance history available.
                    </td>
                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>

</html>