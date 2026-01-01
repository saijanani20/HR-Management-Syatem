<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// FIXED QUERY — only existing columns
$stmt = $conn->prepare("
    SELECT 
        id,
        applied_at,
        leave_type,
        start_date,
        end_date,
        status
    FROM leaves
    WHERE user_id = ?
    ORDER BY applied_at DESC
");
$stmt->execute([$user_id]);
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave Summary</title>
    <link rel="stylesheet" href="leave_summary.css">
</head>
<body>

<div class="summary-container">
    <h2>Leave Summary</h2>

    <table class="summary-table">
        <tr>
            <th>No</th>
            <th>Applied Date</th>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
        </tr>

        <?php if (!empty($leaves)): ?>
            <?php $no = 1; foreach ($leaves as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= date("Y-m-d", strtotime($row['applied_at'])); ?></td>
                    <td><?= htmlspecialchars($row['leave_type']); ?></td>
                    <td><?= htmlspecialchars($row['start_date']); ?></td>
                    <td><?= htmlspecialchars($row['end_date']); ?></td>
                    <td class="status <?= strtolower($row['status']); ?>">
                        <?= htmlspecialchars($row['status']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">No leave records found</td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="btn-row">
        <a href="<?php
        if ($_SESSION['role'] === 'admin') {
            echo 'admin_dashboard.php';
        } else {
            echo 'dashboard_employee.php';
        }
    ?>" class="cancel-btn">
        Back 
    </a>
    </div>
</div>

</body>
</html>
