<?php
session_start();
require_once 'db.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

/* ===== ADMIN NAME ===== */
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id=?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$admin_name = $admin['full_name'] ?? 'Admin';

/* ===== LEAVE COUNTS ===== */
$annual   = $conn->query("SELECT COUNT(*) FROM leaves WHERE leave_type='Annual'")->fetchColumn();
$casual     = $conn->query("SELECT COUNT(*) FROM leaves WHERE leave_type='Casual'")->fetchColumn();
$other    = $conn->query("SELECT COUNT(*) FROM leaves WHERE leave_type='Other'")->fetchColumn();
$pending  = $conn->query("SELECT COUNT(*) FROM leaves WHERE status='Pending'")->fetchColumn();
$approved = $conn->query("SELECT COUNT(*) FROM leaves WHERE status='Approved'")->fetchColumn();
$cancel   = $conn->query("SELECT COUNT(*) FROM leaves WHERE status='Cancelled'")->fetchColumn();

/* ===== PENDING LIST ===== */
$pendingList = $conn->query("
    SELECT 
        l.id,
        u.full_name,
        l.leave_type,
        l.start_date,
        l.end_date,
        l.applied_at,
        DATEDIFF(l.end_date,l.start_date)+1 AS days
    FROM leaves l
    JOIN users u ON u.id = l.user_id
    WHERE l.status='Pending'
    ORDER BY l.applied_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | HRMS</title>

<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="logo">Legacy Health</div>

    <nav class="nav">
        <a class="nav-link active"><i class="fa fa-home"></i><span>Dashboard</span></a>
        <a class="nav-link" href="user_info.php"><i class="fa fa-user"></i><span>User Info</span></a>
        <a class="nav-link" href="apply_leave.php"><i class="fa fa-file"></i><span>Leaves Apply</span></a>
        <a class="nav-link" href="leave_summary.php"><i class="fa fa-list"></i><span>Leaves Summary</span></a>
        <a class="nav-link" href="manage_leave.php"><i class="fa fa-list"></i><span>Manage Leave</span></a>
        <a class="nav-link" href="payroll.php"><i class="fa fa-list"></i><span>Payroll</span></a>
        <a class="nav-link" href="employee.php"><i class="fa fa-list"></i><span>Manage Employee</span></a>
        <a class="nav-link" href="attendance_summary.php"><i class="fa fa-file"></i><span>Attendance Summary</span></a>
        <a class="nav-link" href="reports.php"><i class="fa fa-file"></i><span>Reports</span></a>
        <a class="nav-link" href="calendar.php"><i class="fa fa-calendar"></i><span>Calendar</span></a>
        
    </nav>
</aside>

<!-- ===== TOPBAR ===== -->
<header class="topbar">
    <div class="top-left">  
        <h2>HRMS</h2>

        <div class="search">
            <i class="fa fa-search"></i>
            <input type="text" placeholder="Search employees, leaves...">
        </div>
    </div>

    <div class="top-right">
        <button class="icon-btn">
            <i class="fa fa-bell"></i>
            <?php if($pending>0): ?><span class="badge"><?= $pending ?></span><?php endif; ?>
        </button>

        <div class="profile">
            <i class="fa fa-user-circle"></i>
            <span><?= htmlspecialchars($admin_name) ?></span>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i></a>
        </div>
    </div>
</header>

<!-- ===== CONTENT ===== -->
<main class="content">

<!-- ===== METRIC CARDS ===== -->
<section class="cards">
    <div class="card"><h4>Annual Leave</h4><p><?= $annual ?></p></div>
    <div class="card"><h4>Casual Leave</h4><p><?= $casual ?></p></div>
    <div class="card"><h4>Other Leave</h4><p><?= $other ?></p></div>
    <div class="card"><h4>Pending Requests</h4><p><?= $pending ?></p></div>
    <div class="card"><h4>Approved Leave</h4><p><?= $approved ?></p></div>
    <div class="card"><h4>Cancel Leave</h4><p><?= $cancel ?></p></div>
</section>

<!-- ===== CHART ===== -->
<section class="panel">
    <h3>Leave Summary Overview</h3>
    <canvas id="leaveChart"></canvas>
</section>

<!-- ===== PENDING TABLE ===== -->
<section class="panel">
    <h3>Pending Approval</h3>

    <table>
        <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Dates</th>
            <th>Days</th>
            <th>Action</th>
        </tr>

        <?php foreach($pendingList as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= $r['leave_type'] ?></td>
            <td><?= $r['start_date'] ?> → <?= $r['end_date'] ?></td>
            <td><?= $r['days'] ?></td>
            <td>
                <a class="btn approve" href="approve_leave.php?id=<?= $r['id'] ?>">Approve</a>
                <a class="btn reject" href="reject_leave.php?id=<?= $r['id'] ?>">Reject</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

</main>

<script>
new Chart(document.getElementById('leaveChart'),{
    type:'bar',
    data:{
        labels:['Annual','Casual','Other','Pending','Approved'],
        datasets:[{
            data:[<?= $annual ?>,<?= $casual ?>,<?= $other ?>,<?= $pending ?>,<?= $approved ?>],
            backgroundColor:['#0b4f77','#4dabf7','#6c757d','#ff9800','#2ecc71']
        }]
    },
    options:{ responsive:true, plugins:{legend:{display:false}} }
});
</script>

</body>
</html>
