<?php
session_start();

require_once "../config/database.php";

// Only technicians can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "technician") {
    header("Location: ../login.php");
    exit();
}

$technician_id = $_SESSION["user_id"];

// Get requests assigned to the logged-in technician
$sql = "SELECT 
            mr.id,
            u.full_name AS user_name,
            c.computer_name,
            c.brand,
            c.model,
            mr.problem_description,
            mr.priority,
            mr.status,
            mr.created_at
        FROM maintenance_requests mr
        JOIN users u ON mr.user_id = u.id
        JOIN computers c ON mr.computer_id = c.id
        WHERE mr.technician_id = ?
        ORDER BY mr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $technician_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Assigned Requests - PCFix</title>

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

        .top-links .logout {
            background: #dc3545;
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
            min-width: 1000px;
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

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .action-btn {
            background: #007bff;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        .action-btn:hover {
            background: #0056b3;
        }

    </style>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="container">

    <h1>My Assigned Requests</h1>

    <div class="top-links">

        <a href="dashboard.php">
            Technician Dashboard
        </a>

        <a href="../logout.php" class="logout">
            Logout
        </a>

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
                <th>Date</th>
                <th>Action</th>
            </tr>

            <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            #<?php echo $row["id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["user_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["computer_name"]); ?>

                            <br>

                            <small>
                                <?php
                                echo htmlspecialchars(
                                    $row["brand"] . " " . $row["model"]
                                );
                                ?>
                            </small>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["problem_description"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["priority"]); ?>
                        </td>

                        <td>

                            <span class="status
                                <?php
                                if ($row["status"] == "Assigned") {
                                    echo "assigned";
                                } elseif ($row["status"] == "In Progress") {
                                    echo "progress";
                                } elseif ($row["status"] == "Resolved") {
                                    echo "resolved";
                                } elseif ($row["status"] == "Pending") {
                                    echo "pending";
                                }
                                ?>
                            ">

                                <?php
                                echo htmlspecialchars($row["status"]);
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["created_at"]); ?>
                        </td>

                        <td>

                            <a
                                href="request_details.php?id=<?php echo $row["id"]; ?>"
                                class="action-btn"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8" style="text-align:center;">

                        No maintenance requests have been assigned to you.

                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>

</html>