<?php
require_once 'db.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $emp_id = $_POST['emp_id'];
    $department = $_POST['department'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    try {
        // Update using PDO
        $stmt = $conn->prepare("UPDATE users 
                                SET password = ? 
                                WHERE email = ? AND employee_id = ? AND department = ?");
        
        $stmt->execute([$new_password, $email, $emp_id, $department]);

        if ($stmt->rowCount() > 0) {
            $msg = "Password updated successfully. <a href='login.php'>Login here</a>";
        } else {
            $msg = "Invalid Email / Employee ID / Department.";
        }

    } catch (Exception $e) {
        $msg = "Error updating password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="fog.css">
</head>
<body class="forgot-bg">

<div class="forgot-container">
    <h2>Reset Password</h2>

    <?php if ($msg): ?>
        <p class="msg-box"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter your Email" required>
        <input type="text" name="emp_id" placeholder="Enter Employee ID" required>
        <input type="text" name="department" placeholder="Enter Department" required>
        <input type="password" name="new_password" placeholder="Enter New Password" required>

        <button type="submit" class="btn-reset">Update Password</button>
    </form>

    <div class="back-link">
        <a href="login.php">← Back to Login</a>
    </div>
</div>

</body>
</html>
