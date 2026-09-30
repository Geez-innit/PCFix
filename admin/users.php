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

$message = "";


/* Change user role */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = intval($_POST["user_id"]);
    $new_role = $_POST["role"];

    $allowed_roles = ["user", "technician", "admin"];

    if (!in_array($new_role, $allowed_roles)) {

        $message = "Invalid role selected.";

    } elseif ($user_id == $_SESSION["user_id"]) {

        $message = "You cannot change your own role.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE users SET role = ? WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $new_role,
            $user_id
        );

        if ($stmt->execute()) {
            $message = "User role updated successfully!";
        } else {
            $message = "Failed to update user role.";
        }

        $stmt->close();
    }
}


/* Get all users */

$sql = "
    SELECT
        id,
        full_name,
        email,
        role,
        created_at
    FROM users
    ORDER BY created_at DESC
";

$users = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PCFix - User Management</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <h1>PCFix</h1>

    <h2>User Management</h2>

    <p>
        Welcome,
        <strong>
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </strong>
    </p>

    <hr>


    <?php if (!empty($message)): ?>

        <p>
            <strong>
                <?php echo htmlspecialchars($message); ?>
            </strong>
        </p>

    <?php endif; ?>


    <h3>Registered Users</h3>


    <?php if ($users && $users->num_rows > 0): ?>

        <table>

            <tr>

                <th>ID</th>

                <th>Full Name</th>

                <th>Email</th>

                <th>Role</th>

                <th>Date Registered</th>

                <th>Manage</th>

            </tr>


            <?php while ($user = $users->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo $user["id"]; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["full_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["email"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["role"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["created_at"]); ?>
                    </td>

                    <td>

                        <?php if ($user["id"] != $_SESSION["user_id"]): ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="user_id"
                                    value="<?php echo $user["id"]; ?>"
                                >

                                <select name="role">

                                    <option
                                        value="user"
                                        <?php
                                        if ($user["role"] == "user") {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        User
                                    </option>

                                    <option
                                        value="technician"
                                        <?php
                                        if ($user["role"] == "technician") {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Technician
                                    </option>

                                    <option
                                        value="admin"
                                        <?php
                                        if ($user["role"] == "admin") {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Admin
                                    </option>

                                </select>

                                <button type="submit" class="btn btn-warning">
                                    Update Role
                                </button>

                            </form>

                        <?php else: ?>

                            <strong>Current Account</strong>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        </table>

    <?php else: ?>

        <p>No users found.</p>

    <?php endif; ?>


    <br><br>

    <a href="dashboard.php" class="btn">
        Back to Admin Dashboard
    </a>

    <br><br>

    <a href="../logout.php" class="btn btn-danger">
        Logout
    </a>

</body>

</html>