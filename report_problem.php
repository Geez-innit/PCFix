<?php

session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Get the user's computers
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, computer_name, brand, model
     FROM computers
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$computers = $stmt->get_result();


// Handle maintenance request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $computer_id = $_POST["computer_id"];
    $problem_description = trim($_POST["problem_description"]);
    $priority = $_POST["priority"];

    if (
        empty($computer_id) ||
        empty($problem_description)
    ) {

        $message = "Please fill in all required fields.";

    } else {

        $insert = $conn->prepare(
            "INSERT INTO maintenance_requests
            (user_id, computer_id, problem_description, priority)
            VALUES (?, ?, ?, ?)"
        );

        $insert->bind_param(
            "iiss",
            $user_id,
            $computer_id,
            $problem_description,
            $priority
        );

        if ($insert->execute()) {

            $message = "Maintenance request submitted successfully!";

        } else {

            $message = "Failed to submit maintenance request.";

        }

        $insert->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PCFix - Report Problem</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div class="header">

        <h1>PCFix</h1>

        <div>

            <a href="user/dashboard.php">
                Dashboard
            </a>

            <a href="logout.php" class="btn btn-danger">
                Logout
            </a>

        </div>

    </div>


    <div class="container">

        <div class="card">

            <h2>Report a Computer Problem</h2>

            <p>
                Submit a maintenance request for one of your registered computers.
            </p>


            <?php if (!empty($message)): ?>

                <div class="alert alert-success">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <?php if ($computers->num_rows > 0): ?>

                <form method="POST">


                    <div class="form-group">

                        <label for="computer_id">
                            Select Computer
                        </label>

                        <select
                            id="computer_id"
                            name="computer_id"
                            required
                        >

                            <option value="">
                                -- Select Computer --
                            </option>

                            <?php while ($computer = $computers->fetch_assoc()): ?>

                                <option value="<?php echo $computer["id"]; ?>">

                                    <?php

                                    echo htmlspecialchars(
                                        $computer["computer_name"]
                                    );

                                    if (!empty($computer["brand"])) {

                                        echo " - " .
                                            htmlspecialchars($computer["brand"]);

                                    }

                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="problem_description">
                            Describe the Problem
                        </label>

                        <textarea
                            id="problem_description"
                            name="problem_description"
                            placeholder="Describe the problem with your computer..."
                            required
                        ></textarea>

                    </div>


                    <div class="form-group">

                        <label for="priority">
                            Priority
                        </label>

                        <select
                            id="priority"
                            name="priority"
                        >

                            <option value="Low">
                                Low
                            </option>

                            <option value="Medium" selected>
                                Medium
                            </option>

                            <option value="High">
                                High
                            </option>

                        </select>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        Submit Maintenance Request
                    </button>


                </form>


            <?php else: ?>

                <div class="alert alert-error">

                    You haven't registered a computer yet.

                </div>

                <a
                    href="computers.php"
                    class="btn"
                >
                    Register a Computer
                </a>

            <?php endif; ?>


        </div>


        <a 
            href="user/dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>

    </div>

</body>

</html>