<?php
session_start();

require_once "../config/database.php";

// Only logged-in users can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "user") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get the user's maintenance requests and technician reports
$sql = "SELECT
            mr.id,
            mr.problem_description,
            mr.priority,
            mr.status,
            mr.created_at,
            c.computer_name,
            c.brand,
            c.model,
            t.full_name AS technician_name,
            mrec.diagnosis,
            mrec.action_taken,
            mrec.resolution,
            mrec.created_at AS report_date
        FROM maintenance_requests mr
        JOIN computers c
            ON mr.computer_id = c.id
        LEFT JOIN users t
            ON mr.technician_id = t.id
        LEFT JOIN maintenance_records mrec
            ON mr.id = mrec.request_id
        WHERE mr.user_id = ?
        ORDER BY mr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Requests - PCFix</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
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

        .request-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .request-header h2 {
            margin: 0;
            color: #333;
        }

        .status {
            padding: 6px 12px;
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

        .info {
            margin-bottom: 15px;
        }

        .info p {
            margin: 8px 0;
        }

        .problem {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .report {
            background: #f8f9fa;
            padding: 18px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #28a745;
        }

        .report h3 {
            margin-top: 0;
            color: #333;
        }

        .report-section {
            margin-bottom: 15px;
        }

        .report-section strong {
            display: block;
            margin-bottom: 5px;
        }

        .no-report {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            color: #666;
        }

        .empty {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }

    </style>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="container">

    <h1>My Maintenance Requests</h1>

    <div class="top-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../report_problem.php">
            Report Another Problem
        </a>

        <a href="../logout.php" class="logout">
            Logout
        </a>

    </div>


    <?php if ($result->num_rows > 0): ?>

        <?php while ($row = $result->fetch_assoc()): ?>

            <div class="request-card">

                <div class="request-header">

                    <h2>
                        Request #<?php echo $row["id"]; ?>
                    </h2>

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
                        ?>
                    ">

                        <?php echo htmlspecialchars($row["status"]); ?>

                    </span>

                </div>


                <div class="info">

                    <p>
                        <strong>Computer:</strong>
                        <?php
                        echo htmlspecialchars(
                            $row["computer_name"]
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Brand/Model:</strong>
                        <?php
                        echo htmlspecialchars(
                            $row["brand"] . " " . $row["model"]
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Priority:</strong>
                        <?php
                        echo htmlspecialchars(
                            $row["priority"]
                        );
                        ?>
                    </p>

                    <p>
                        <strong>Reported:</strong>
                        <?php
                        echo htmlspecialchars(
                            $row["created_at"]
                        );
                        ?>
                    </p>

                    <?php if ($row["technician_name"]): ?>

                        <p>
                            <strong>Technician:</strong>
                            <?php
                            echo htmlspecialchars(
                                $row["technician_name"]
                            );
                            ?>
                        </p>

                    <?php endif; ?>

                </div>


                <div class="problem">

                    <strong>Problem Reported:</strong>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $row["problem_description"]
                            )
                        );
                        ?>
                    </p>

                </div>


                <?php if ($row["diagnosis"]): ?>

                    <div class="report">

                        <h3>
                            🔧 Technician Maintenance Report
                        </h3>


                        <div class="report-section">

                            <strong>Diagnosis:</strong>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $row["diagnosis"]
                                )
                            );
                            ?>

                        </div>


                        <div class="report-section">

                            <strong>Action Taken:</strong>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $row["action_taken"]
                                )
                            );
                            ?>

                        </div>


                        <?php if ($row["resolution"]): ?>

                            <div class="report-section">

                                <strong>Resolution:</strong>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $row["resolution"]
                                    )
                                );
                                ?>

                            </div>

                        <?php endif; ?>


                        <?php if ($row["report_date"]): ?>

                            <small>
                                Report updated:
                                <?php
                                echo htmlspecialchars(
                                    $row["report_date"]
                                );
                                ?>
                            </small>

                        <?php endif; ?>

                    </div>

                <?php else: ?>

                    <div class="no-report">

                        🔧 No technician report has been added yet.

                        <?php if ($row["technician_name"]): ?>

                            The request has been assigned to
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $row["technician_name"]
                                );
                                ?>
                            </strong>.

                        <?php else: ?>

                            Your request is waiting to be assigned
                            to a technician.

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">

            <h2>No Maintenance Requests</h2>

            <p>
                You have not submitted any maintenance requests yet.
            </p>

            <a href="../report_problem.php">
                Report a Problem
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>