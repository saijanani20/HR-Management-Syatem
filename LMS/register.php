<?php
require_once "db.php";
$message = "";

// --- Form Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name   = trim($_POST['full_name']);
    $employee_id = trim($_POST['employee_id']);
    $department  = trim($_POST['department']);
    $designation = trim($_POST['designation']);
    $role        = trim($_POST['role']);
    $mobile      = trim($_POST['mobile']);
    $join_date   = trim($_POST['join_date']);
    $email       = trim($_POST['email']);
    $password    = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Check if email already exists
        $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "<span class='error-msg'>❌ Email already registered!</span>";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users(full_name, employee_id, department, designation, role, mobile, join_date, email, password) 
                                    VALUES(?,?,?,?,?,?,?,?,?)");

            $stmt->execute([
                $full_name,
                $employee_id,
                $department,
                $designation,
                $role,
                $mobile,
                $join_date,
                $email,
                $password
            ]);

            $message = "<span class='success-msg'>✅ Registration Successful! <a href='login.php'>Login Now</a></span>";
        }

    } catch (Exception $e) {
        $message = "<span class='error-msg'>❌ Error occurred: " . $e->getMessage() . "</span>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | LMS</title>
    <link rel="stylesheet" href="regi.css">
</head>

<body>

<div class="register-container">

    <h2>Create Your Account</h2>

    <?php if (!empty($message)): ?>
        <div class="msg-box"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-box">
            <input type="text" name="full_name" placeholder="Full Name" required>
        </div>

        <div class="input-box">
            <input type="text" name="employee_id" placeholder="Employee ID" required>
        </div>

        <div class="input-box">
            <input type="text" name="department" placeholder="Department" required>
        </div>

        <div class="input-box">
            <input type="text" name="designation" placeholder="Designation" required>
        </div>

        <div class="input-box">
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="input-box">
            <input type="text" name="mobile" placeholder="Mobile Number" required>
        </div>

        <div class="input-box">
            <input type="date" name="join_date" required>
        </div>

        <div class="input-box">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button class="btn" type="submit">Register →</button>

        <div class="bottom-text">
            <a href="login.php">Already Registered? Login</a>
        </div>

    </form>
</div>

</body>
</html>
