<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ 
    header("Location: login.php"); 
    exit; 
}

// Fetch employees
$employees = $conn->query("SELECT id, full_name FROM users WHERE role='employee'")->fetchAll();

// Fetch leave types
$leave_types = $conn->query("SELECT DISTINCT leave_type FROM leaves")->fetchAll(PDO::FETCH_COLUMN);

// Filters
$filter_emp   = $_GET['employee'] ?? '';
$filter_leave = $_GET['leave_type'] ?? '';
$from         = $_GET['from'] ?? '';
$to           = $_GET['to'] ?? '';

$params = [];
$sql = "SELECT l.*, u.full_name 
        FROM leaves l 
        JOIN users u ON u.id = l.user_id 
        WHERE 1=1";

if($filter_emp){
    $sql .= " AND l.user_id = ?";
    $params[] = $filter_emp;
}
if($filter_leave){
    $sql .= " AND l.leave_type = ?";
    $params[] = $filter_leave;
}
if($from){
    $sql .= " AND l.applied_at >= ?";
    $params[] = $from;
}
if($to){
    $sql .= " AND l.applied_at <= ?";
    $params[] = $to;
}

$sql .= " ORDER BY l.applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

/* ================= CSV DOWNLOAD ================= */
if(isset($_GET['download']) && $_GET['download'] === 'csv'){
    if (ob_get_length()) ob_clean();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=leave_report.csv');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');

    fputcsv($out, [
        'Employee',
        'Leave Type',
        'Start Date',
        'End Date',
        'Days',
        'Status',
        'Applied At',
        
    ]);

    foreach($results as $r){
        fputcsv($out, [
            $r['full_name'],
            $r['leave_type'],
            $r['start_date'],
            $r['end_date'],
            $r['days'],
            $r['status'],
            $r['applied_at'],
            $r['mode']
        ]);
    }

    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        /* YOUR CSS — UNCHANGED */
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, rgba(0,85,165,0.45), rgba(0,140,255,0.35)), url('bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .summary-container {
            width: 92%;
            max-width: 1100px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(14px);
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }
        .summary-container h2 {
            text-align: center;
            color: #004b94;
            font-weight: 700;
            margin-bottom: 25px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter-form select,
        .filter-form input,
        .filter-form button {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .filter-form button {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            font-weight: 600;
            border: none;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            padding: 14px;
        }
        .summary-table td {
            padding: 12px;
            background: #f8fbff;
        }
        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 12px 28px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="summary-container">
    <h2>Leave Reports</h2>

    <form method="GET" class="filter-form">
        <select name="employee">
            <option value="">All Employees</option>
            <?php foreach($employees as $e): ?>
                <option value="<?= $e['id']; ?>" <?= ($filter_emp==$e['id'])?'selected':''; ?>>
                    <?= htmlspecialchars($e['full_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="leave_type">
            <option value="">All Leave Types</option>
            <?php foreach($leave_types as $lt): ?>
                <option value="<?= htmlspecialchars($lt); ?>" <?= ($filter_leave==$lt)?'selected':''; ?>>
                    <?= htmlspecialchars($lt); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="from" value="<?= htmlspecialchars($from); ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($to); ?>">

        <button type="submit" name="generate" value="1">Generate</button>
        <button type="submit" name="download" value="csv">Download CSV</button>
    </form>

    <?php if($results && isset($_GET['generate'])): ?>
    <table class="summary-table">
        <tr>
            <th>No</th>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Start</th>
            <th>End</th>
            <th>Days</th>
            <th>Status</th>
            <th>Applied</th>
        </tr>
        <?php $i=1; foreach($results as $r): ?>
        <tr>
            <td><?= $i++; ?></td>
            <td><?= htmlspecialchars($r['full_name']); ?></td>
            <td><?= htmlspecialchars($r['leave_type']); ?></td>
            <td><?= $r['start_date']; ?></td>
            <td><?= $r['end_date']; ?></td>
            <td><?= $r['days']; ?></td>
            <td><?= ucfirst($r['status']); ?></td>
            <td><?= $r['applied_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>
