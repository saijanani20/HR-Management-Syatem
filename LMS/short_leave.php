<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $leave_date = $_POST['leave_date'];
    $time_in    = $_POST['time_in'];
    $time_out   = $_POST['time_out'];
    $reason     = trim($_POST['reason']);

    $today = date('Y-m-d');

    if ($leave_date < $today) {
        $error = "Recheck the date";
    } else {
        $start = strtotime("$leave_date $time_in");
        $end   = strtotime("$leave_date $time_out");

        if ($end <= $start) {
            $error = "Invalid time range";
        } else {
            $hours = ($end - $start) / 3600;

            if ($hours > 2) {
                $error = "Short leave cannot exceed 2 hours";
            } else {

                $stmt = $conn->prepare("
                    INSERT INTO short_leaves 
                    (user_id, leave_date, time_in, time_out, duration, reason)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id,
                    $leave_date,
                    $time_in,
                    $time_out,
                    $hours,
                    $reason
                ]);

                $message = "Short leave applied successfully!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Short Leave</title>
    <link rel="stylesheet" href="short_leave.css">
</head>

<body>

<div class="page-wrapper">

    <div class="glass-box">

        <h2>Short Leave Application</h2>

        <?php if ($message): ?>
            <div class="success-msg"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="input-box">
                    <label>Date</label>
                    <input type="date" name="leave_date" required>
                </div>

                <div class="input-box">
                    <label>Time In</label>
                    <input type="time" id="time_in" name="time_in" required>
                </div>

                <div class="input-box">
                    <label>Time Out</label>
                    <input type="time" id="time_out" name="time_out" required>
                </div>

                <div class="input-box">
                    <label>Duration (Hours)</label>
                    <input type="text" id="duration" readonly>
                </div>

                <div class="input-box full-width">
                    <label>Reason</label>
                    <textarea name="reason" required></textarea>
                </div>
            </div>

            <div class="button-row">
                <button class="save-btn">Submit</button>
                <a href="dashboard_employee.php" class="cancel-btn">Cancel</a>
            </div>

        </form>
    </div>
</div>

<script>
const timeIn = document.getElementById("time_in");
const timeOut = document.getElementById("time_out");
const duration = document.getElementById("duration");

function calcDuration() {
    if (timeIn.value && timeOut.value) {
        const start = new Date("1970-01-01T" + timeIn.value);
        const end   = new Date("1970-01-01T" + timeOut.value);

        let diff = (end - start) / 3600000;

        if (diff > 0) {
            duration.value = diff.toFixed(2);
        } else {
            duration.value = "";
        }
    }
}

timeIn.addEventListener("change", calcDuration);
timeOut.addEventListener("change", calcDuration);
</script>

</body>
</html>
