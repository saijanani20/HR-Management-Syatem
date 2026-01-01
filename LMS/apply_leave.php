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

/* FETCH BALANCE */
$stmt = $conn->prepare("
    SELECT annual_balance, casual_balance, halfday_balance, emergency_balance
    FROM users WHERE id=?
");
$stmt->execute([$user_id]);
$bal = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = [
    "Annual"    => (int)$bal['annual_balance'],
    "Casual"    => (int)$bal['casual_balance'],
    "Half Day"  => (int)$bal['halfday_balance'],
    "Emergency" => (int)$bal['emergency_balance']
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $days       = (int)$_POST['days'];
    $reason     = trim($_POST['reason']);

    /* DATE VALIDATION */
    if ($end_date < $start_date) {
        $error = "Recheck the date range";
    }
    /* BALANCE VALIDATION */
    elseif ($days <= 0 || $days > $balance[$leave_type]) {
        $error = "Insufficient leave balance";
    }
    else {

        /* FILE UPLOAD */
        $file_path = null;
        if (!empty($_FILES['document']['name'])) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $file_name = time() . "_" . basename($_FILES['document']['name']);
            $file_path = $upload_dir . $file_name;
            move_uploaded_file($_FILES['document']['tmp_name'], $file_path);
        }

        /* INSERT LEAVE */
        $stmt = $conn->prepare("
            INSERT INTO leaves 
            (user_id, leave_type, start_date, end_date, days, reason, document, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([
            $user_id, $leave_type, $start_date, $end_date,
            $days, $reason, $file_path
        ]);

        /* DEDUCT BALANCE */
        $column = match($leave_type) {
            'Annual'    => 'annual_balance',
            'Casual'    => 'casual_balance',
            'Half Day'  => 'halfday_balance',
            'Emergency' => 'emergency_balance'
        };

        $conn->prepare("
            UPDATE users SET $column = $column - ?
            WHERE id=?
        ")->execute([$days, $user_id]);

        $message = "Leave application submitted successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply Leave</title>
    <link rel="stylesheet" href="leave apply.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="page-wrapper">

    <!-- PIE CHART -->
    <div class="side-chart">
        <h3>Leave Balance</h3>
        <canvas id="leavePie"></canvas>
    </div>

    <!-- FORM -->
    <div class="glass-box">

        <h2>Apply Leave</h2>

        <?php if ($message): ?>
            <div class="success-msg"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-grid">

                <div class="input-box">
                    <label>Leave Type</label>
                    <select name="leave_type" required>
                        <option value="">Select Leave Type</option>
                        <option>Annual</option>
                        <option>Casual</option>
                        <option>Half Day</option>
                        <option>Emergency</option>
                    </select>
                </div>

                <div class="input-box">
                    <label>Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>

                <div class="input-box">
                    <label>End Date</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>

                <div class="input-box">
                    <label>Days</label>
                    <input type="number" id="days" name="days" readonly>
                </div>

                <div class="input-box full-width">
                    <label>Reason</label>
                    <textarea name="reason" required></textarea>
                </div>

                <div class="input-box full-width">
                    <label>Attach Document</label>
                    <input type="file" name="document">
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
/* AUTO DAY CALCULATION + DATE CHECK */
const start = document.getElementById("start_date");
const end   = document.getElementById("end_date");
const days  = document.getElementById("days");

function calcDays() {
    if (!start.value || !end.value) {
        days.value = "";
        return;
    }

    const s = new Date(start.value);
    const e = new Date(end.value);
    const today = new Date();
    today.setHours(0,0,0,0);

    if (s < today || e < s) {
        days.value = "";
        alert("Recheck the date range");
        return;
    }

    days.value = Math.floor((e - s) / 86400000) + 1;
}

start.addEventListener("change", calcDays);
end.addEventListener("change", calcDays);

/* PIE CHART */
new Chart(document.getElementById("leavePie"), {
    type: "pie",
    data: {
        labels: ["Annual", "Casual", "Half Day", "Emergency"],
        datasets: [{
            data: <?= json_encode(array_values($balance)) ?>,
            backgroundColor: ["#007BFF","#28A745","#FFC107","#DC3545"]
        }]
    },
    options: {
        responsive: false,      // ✅ MUST be false
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom"
            }
        }
    }
});
</script>

</body>
</html>
