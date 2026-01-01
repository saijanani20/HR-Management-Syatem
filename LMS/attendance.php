<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$message = "";
$error = "";

/* FETCH TODAY ATTENDANCE */
$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? AND attendance_date=?");
$stmt->execute([$user_id, $today]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

/* LOGIN */
if (isset($_POST['login'])) {
    $login_time = date('H:i:s');
    $late_time = "08:00:00";
    $last_time = "08:30:00";

    if ($login_time > $last_time) {
        $error = "Login closed after 08:30 AM";
    } elseif ($attendance) {
        $error = "Attendance already marked today";
    } else {
        $status = ($login_time > $late_time) ? "Late Attendance" : "Present";

        $stmt = $conn->prepare(
            "INSERT INTO attendance (user_id, attendance_date, login_time, status)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$user_id, $today, $login_time, $status]);

        $message = "Login successful";
    }
}

/* LOGOUT */
if (isset($_POST['logout'])) {
    if (!$attendance || $attendance['logout_time']) {
        $error = "Invalid logout request";
    } else {
        $logout_time = date('H:i:s');

        $stmt = $conn->prepare(
            "UPDATE attendance 
             SET logout_time=? 
             WHERE user_id=? AND attendance_date=?"
        );
        $stmt->execute([$logout_time, $user_id, $today]);

        $message = "Logout time recorded";
    }
}

/* FETCH ALL ATTENDANCE RECORDS (HISTORY) */
$stmt = $conn->prepare(
    "SELECT * FROM attendance WHERE user_id=? ORDER BY attendance_date DESC"
);
$stmt->execute([$user_id]);
$all_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Attendance</title>
    <link rel="stylesheet" href="attendance.css">
</head>
<body>

<div class="page-wrapper">
    <div class="glass-box">

        <h2>Attendance</h2>

        <?php if ($message): ?>
            <div class="success-msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="attendance-info">
            <p><strong>Date:</strong> <?= $today ?></p>
            <p><strong>Login Time:</strong> <?= $attendance['login_time'] ?? '--' ?></p>
            <p><strong>Logout Time:</strong> <?= $attendance['logout_time'] ?? '--' ?></p>
            <p><strong>Status:</strong> <?= $attendance['status'] ?? 'Absent' ?></p>
        </div>

        <form method="POST" class="button-row">
            <?php if (!$attendance): ?>
                <button type="submit" name="login" class="save-btn">Login</button>

            <?php elseif (!$attendance['logout_time']): ?>
                <button type="submit" name="logout" class="cancel-btn">Logout</button>
            <?php endif; ?>

            <!-- CANCEL BUTTON -->
            <a href="dashboard_employee.php" class="neutral-btn">Cancel</a>
        </form>

        <hr style="margin:25px 0;">

        <h3>Attendance History</h3>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($all_attendance): ?>
                    <?php foreach ($all_attendance as $att): ?>
                        <tr>
                            <td><?= $att['attendance_date'] ?></td>
                            <td><?= $att['login_time'] ?? '--' ?></td>
                            <td><?= $att['logout_time'] ?? '--' ?></td>
                            <td><?= $att['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No attendance records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>
