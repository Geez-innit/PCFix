<?php
session_start();

require_once "../config/database.php";

// Only admins can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

// Check if request ID was provided
if (!isset($_GET["id"])) {
    header("Location: requests.php");
    exit();
}

$request_id = intval($_GET["id"]);

// Get the maintenance request
$sql = "SELECT 
            mr.id,
            mr.problem_description,
            mr.priority,
            mr.status,
            u.full_name AS user_name,
            c.computer_name,
            c.brand,
            c.model
        FROM maintenance_requests mr
        JOIN users u ON mr.user_id = u.id
        JOIN computers c ON mr.computer_id = c.id
        WHERE mr.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Maintenance request not found.");
}

$request = $result->fetch_assoc();


// Get all technicians
$tech_sql = "SELECT id, full_name, email 
             FROM users 
             WHERE role = 'technician'
             ORDER BY full_name ASC";

$technicians = $conn->query($tech_sql);


// Handle assignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["technician_id"]) || empty($_POST["technician_id"])) {
        $error = "Please select a technician.";
    } else {

        $technician_id = intval($_POST["technician_id"]);

        // Make sure selected user is actually a technician
        $check_sql = "SELECT id FROM users WHERE id = ? AND role = 'technician'";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $technician_id);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {

            $error = "Invalid technician selected.";

        } else {

            // Assign technician and update status
            $update_sql = "UPDATE maintenance_requests
                           SET technician_id = ?, status = 'Assigned'
                           WHERE id = ?";

            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $technician_id, $request_id);

            if ($update_stmt->execute()) {

                header("Location: requests.php");
                exit();

            } else {

                $error = "Failed to assign technician.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Assign Technician - PCFix</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            color: #333;
        }

        .info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info p {
            margin: 8px 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #0056b3;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #007bff;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

    </style>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="container">

    <a href="requests.php" class="back">
        ← Back to Maintenance Requests
    </a>

    <div class="card">

        <h1>Assign Technician</h1>

        <div class="info">

            <p>
                <strong>Request ID:</strong>
                #<?php echo $request["id"]; ?>
            </p>

            <p>
                <strong>User:</strong>
                <?php echo htmlspecialchars($request["user_name"]); ?>
            </p>

            <p>
                <strong>Computer:</strong>
                <?php echo htmlspecialchars($request["computer_name"]); ?>
            </p>

            <p>
                <strong>Brand/Model:</strong>
                <?php
                echo htmlspecialchars(
                    $request["brand"] . " " . $request["model"]
                );
                ?>
            </p>

            <p>
                <strong>Problem:</strong>
                <?php echo htmlspecialchars($request["problem_description"]); ?>
            </p>

            <p>
                <strong>Priority:</strong>
                <?php echo htmlspecialchars($request["priority"]); ?>
            </p>

            <p>
                <strong>Current Status:</strong>
                <?php echo htmlspecialchars($request["status"]); ?>
            </p>

        </div>


        <?php if (isset($error)): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <label for="technician_id">
                Select Technician
            </label>

            <select name="technician_id" id="technician_id" required>

                <option value="">
                    -- Select Technician --
                </option>

                <?php while ($tech = $technicians->fetch_assoc()): ?>

                    <option value="<?php echo $tech["id"]; ?>">

                        <?php
                        echo htmlspecialchars(
                            $tech["full_name"] . " - " . $tech["email"]
                        );
                        ?>

                    </option>

                <?php endwhile; ?>

            </select>


            <button type="submit">
                Assign Technician
            </button>

        </form>

    </div>

</div>

</body>

</html>