<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===== USER ===== */
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user['full_name'] ?? 'Employee';

/* ===== LEAVE ALLOCATION ===== */
$total_annual = 14;
$total_casual = 7;
$total_other  = 5;

/* ===== LEAVE COUNTS ===== */
$sql = "
SELECT
SUM(CASE WHEN leave_type='Annual' AND status='Approved' THEN days ELSE 0 END) AS annual_taken,
SUM(CASE WHEN leave_type='Casual' AND status='Approved' THEN days ELSE 0 END) AS casual_taken,
SUM(CASE WHEN leave_type NOT IN ('Annual','Casual') AND status='Approved' THEN days ELSE 0 END) AS other_taken,

COUNT(*) AS total_leaves,
SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) AS approved,
SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending,
SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) AS rejected,
SUM(CASE WHEN status='Cancelled' THEN 1 ELSE 0 END) AS cancelled
FROM leaves WHERE user_id=?
";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$d = $stmt->fetch(PDO::FETCH_ASSOC);

$annual_taken = (int)$d['annual_taken'];
$casual_taken = (int)$d['casual_taken'];
$other_taken  = (int)$d['other_taken'];
$approved     = (int)$d['approved'];
$pending      = (int)$d['pending'];
$rejected     = (int)$d['rejected'];
$cancelled    = (int)$d['cancelled'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Employee Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="logo-title">Legacy Health</div>
    <nav>
        <a class="nav-link active"><i class="fa fa-home"></i>Dashboard</a>
        <a class="nav-link" href="user_info.php"><i class="fa fa-user"></i>User Info</a>
        <a class="nav-link" href="apply_leave.php"><i class="fa fa-file"></i>Leave Apply</a>
        <a class="nav-link" href="short_leave.php"><i class="fa fa-file"></i>Short Leave</a>
        <a class="nav-link" href="leave_summary.php"><i class="fa fa-list"></i>Leave Summary</a>
        <a class="nav-link" href="attendance.php"><i class="fa fa-file"></i>Attendance</a>
        <a class="nav-link" href="calendar.php"><i class="fa fa-calendar"></i>Calendar</a>
    </nav>
</aside>

<!-- TOPBAR --> 
<header class="topbar"> 
    <div class="top-left">
        <div class="hrms-title">HRMS</div>
        <div class="search">
            <i class="fa fa-search"></i>
            <input type="text" placeholder="Search...">
        </div>
    </div>

    <div class="profile">
        <i class="fa fa-user-circle profile-pic"></i>
        <span><?php echo htmlspecialchars($user_name); ?></span>
        <a href="logout.php"><i class="fa fa-sign-out-alt logout"></i></a>
    </div>
</header>

<!-- CONTENT -->
<main class="content">

<section class="cards">
    <div class="card"><div class="card-title">Annual Total</div><div class="card-value"><?php echo $total_annual; ?></div></div>
    <div class="card"><div class="card-title">Annual Taken</div><div class="card-value"><?php echo $annual_taken; ?></div></div>

    <div class="card"><div class="card-title">Casual Total</div><div class="card-value"><?php echo $total_casual; ?></div></div>
    <div class="card"><div class="card-title">Casual Taken</div><div class="card-value"><?php echo $casual_taken; ?></div></div>

    <div class="card"><div class="card-title">Other Total</div><div class="card-value"><?php echo $total_other; ?></div></div>
    <div class="card"><div class="card-title">Other Taken</div><div class="card-value"><?php echo $other_taken; ?></div></div>

    <div class="card approved"><div class="card-title">Approved</div><div class="card-value"><?php echo $approved; ?></div></div>
    <div class="card pending"><div class="card-title">Pending</div><div class="card-value"><?php echo $pending; ?></div></div>
    <div class="card rejected"><div class="card-title">Rejected</div><div class="card-value"><?php echo $rejected; ?></div></div>
    <div class="card cancelled"><div class="card-title">Cancelled</div><div class="card-value"><?php echo $cancelled; ?></div></div>
</section>

<section class="panel">
    <h3>Leave Status Overview</h3>

    <!-- ✅ FIX: wrapper added -->
    <div class="chart-center">
        <canvas id="leavePie"></canvas>
    </div>
</section>

</main>

<script>
new Chart(document.getElementById('leavePie'), {
    type: 'pie',
    data: {
        labels: ['Annual','Casual','Other'],
        datasets: [{
            data: [
                <?php echo $total_annual; ?>,
                <?php echo $total_casual; ?>,
                <?php echo $total_other; ?>
            ],
            backgroundColor: ['#0b4f77','#3ea0ff','#28a745']
        }]
    },
    options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    font: { size: 11 }
                }
            }
        }
    }
});
</script>

</body>
</html>
