<?php
session_start();

require_once "../config/database.php";

// Only technicians can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "technician") {
    header("Location: ../login.php");
    exit();
}

// Check request ID
if (!isset($_GET["id"])) {
    header("Location: requests.php");
    exit();
}

$request_id = intval($_GET["id"]);
$technician_id = $_SESSION["user_id"];


// Get request details
$sql = "SELECT
            mr.id,
            mr.user_id,
            mr.problem_description,
            mr.priority,
            mr.status,
            mr.technician_id,
            mr.created_at,
            u.full_name AS user_name,
            u.email AS user_email,
            c.computer_name,
            c.brand,
            c.model,
            c.operating_system,
            c.serial_number
        FROM maintenance_requests mr
        JOIN users u ON mr.user_id = u.id
        JOIN computers c ON mr.computer_id = c.id
        WHERE mr.id = ?
        AND mr.technician_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $technician_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Request not found or this request is not assigned to you.");
}

$request = $result->fetch_assoc();


// Get existing maintenance record
$record_sql = "SELECT *
               FROM maintenance_records
               WHERE request_id = ?
               ORDER BY created_at DESC
               LIMIT 1";

$record_stmt = $conn->prepare($record_sql);
$record_stmt->bind_param("i", $request_id);
$record_stmt->execute();

$record_result = $record_stmt->get_result();

$record = $record_result->num_rows > 0
    ? $record_result->fetch_assoc()
    : null;


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $diagnosis = trim($_POST["diagnosis"]);
    $action_taken = trim($_POST["action_taken"]);
    $resolution = trim($_POST["resolution"]);
    $status = $_POST["status"];

    // Validate status
    $allowed_statuses = ["In Progress", "Resolved"];

    if (!in_array($status, $allowed_statuses)) {
        $error = "Invalid status selected.";
    } elseif (empty($diagnosis) || empty($action_taken)) {
        $error = "Diagnosis and Action Taken are required.";
    } else {

        // Check if maintenance record already exists
        $check_sql = "SELECT id
                      FROM maintenance_records
                      WHERE request_id = ?
                      LIMIT 1";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            // Update existing maintenance record
            $existing = $check_result->fetch_assoc();

            $update_record = "UPDATE maintenance_records
                              SET diagnosis = ?,
                                  action_taken = ?,
                                  resolution = ?
                              WHERE id = ?";

            $update_stmt = $conn->prepare($update_record);

            $update_stmt->bind_param(
                "sssi",
                $diagnosis,
                $action_taken,
                $resolution,
                $existing["id"]
            );

            $update_stmt->execute();

        } else {

            // Create new maintenance record
            $insert_record = "INSERT INTO maintenance_records
                              (request_id, technician_id, diagnosis, action_taken, resolution)
                              VALUES (?, ?, ?, ?, ?)";

            $insert_stmt = $conn->prepare($insert_record);

            $insert_stmt->bind_param(
                "iisss",
                $request_id,
                $technician_id,
                $diagnosis,
                $action_taken,
                $resolution
            );

            $insert_stmt->execute();
        }


        // Update request status
        $update_request = "UPDATE maintenance_requests
                           SET status = ?
                           WHERE id = ?
                           AND technician_id = ?";

        $request_stmt = $conn->prepare($update_request);

        $request_stmt->bind_param(
            "sii",
            $status,
            $request_id,
            $technician_id
        );

        if ($request_stmt->execute()) {

            header(
                "Location: request_details.php?id=" . $request_id . "&success=1"
            );

            exit();

        } else {

            $error = "Failed to update request.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Request Details - PCFix</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
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

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1,
        h2 {
            color: #333;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info div {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .problem {
            padding: 15px;
            background: #fff3cd;
            border-radius: 5px;
            margin-top: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 7px;
        }

        textarea,
        select {
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        button {
            margin-top: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

    </style>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="container">

    <div class="top-links">

        <a href="requests.php">
            ← My Assigned Requests
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../logout.php" class="logout">
            Logout
        </a>

    </div>


    <?php if (isset($_GET["success"])): ?>

        <div class="success">
            Maintenance information saved successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <!-- REQUEST INFORMATION -->

    <div class="card">

        <h1>
            Maintenance Request #<?php echo $request["id"]; ?>
        </h1>

        <div class="info">

            <div>
                <strong>User:</strong><br>
                <?php echo htmlspecialchars($request["user_name"]); ?>
            </div>

            <div>
                <strong>Email:</strong><br>
                <?php echo htmlspecialchars($request["user_email"]); ?>
            </div>

            <div>
                <strong>Computer:</strong><br>
                <?php echo htmlspecialchars($request["computer_name"]); ?>
            </div>

            <div>
                <strong>Brand:</strong><br>
                <?php
                echo htmlspecialchars(
                    $request["brand"] . " " . $request["model"]
                );
                ?>
            </div>

            <div>
                <strong>Operating System:</strong><br>
                <?php
                echo htmlspecialchars(
                    $request["operating_system"]
                );
                ?>
            </div>

            <div>
                <strong>Serial Number:</strong><br>
                <?php
                echo htmlspecialchars(
                    $request["serial_number"]
                );
                ?>
            </div>

            <div>
                <strong>Priority:</strong><br>
                <?php echo htmlspecialchars($request["priority"]); ?>
            </div>

            <div>
                <strong>Status:</strong><br>
                <?php echo htmlspecialchars($request["status"]); ?>
            </div>

        </div>


        <div class="problem">

            <strong>Reported Problem:</strong>

            <p>
                <?php
                echo nl2br(
                    htmlspecialchars(
                        $request["problem_description"]
                    )
                );
                ?>
            </p>

        </div>

    </div>


    <!-- MAINTENANCE FORM -->

    <div class="card">

        <h2>Maintenance Report</h2>

        <form method="POST">

            <label for="diagnosis">
                Diagnosis
            </label>

            <textarea
                name="diagnosis"
                id="diagnosis"
                placeholder="Describe what you found..."
                required
            ><?php
                echo $record
                    ? htmlspecialchars($record["diagnosis"])
                    : "";
            ?></textarea>


            <label for="action_taken">
                Action Taken
            </label>

            <textarea
                name="action_taken"
                id="action_taken"
                placeholder="Describe the repair or maintenance performed..."
                required
            ><?php
                echo $record
                    ? htmlspecialchars($record["action_taken"])
                    : "";
            ?></textarea>


            <label for="resolution">
                Resolution
            </label>

            <textarea
                name="resolution"
                id="resolution"
                placeholder="Describe the final result..."
            ><?php
                echo $record
                    ? htmlspecialchars($record["resolution"])
                    : "";
            ?></textarea>


            <label for="status">
                Update Status
            </label>

            <select name="status" id="status" required>

                <option value="In Progress"
                    <?php
                    if ($request["status"] == "In Progress") {
                        echo "selected";
                    }
                    ?>>
                    In Progress
                </option>

                <option value="Resolved"
                    <?php
                    if ($request["status"] == "Resolved") {
                        echo "selected";
                    }
                    ?>>
                    Resolved
                </option>

            </select>


            <button type="submit">
                Save Maintenance Report
            </button>

        </form>

    </div>

</div>

</body>

</html>