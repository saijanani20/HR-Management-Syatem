<?php
session_start();
require_once 'db.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ===== GET ID ===== */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===== FETCH DATA ===== */
if ($id > 0) {

    // SINGLE LEAVE VIEW (WITH DAYS CALCULATION)
    $stmt = $conn->prepare("
        SELECT 
            l.*,
            u.full_name,
            u.employee_id,
            DATEDIFF(l.end_date, l.start_date) + 1 AS days
        FROM leaves l
        JOIN users u ON u.id = l.user_id
        WHERE l.id = ?
    ");
    $stmt->execute([$id]);
    $leave = $stmt->fetch(PDO::FETCH_ASSOC);

} else {

    // LEAVE LIST VIEW (WITH DAYS CALCULATION)
    $stmt = $conn->query("
        SELECT 
            l.*,
            u.full_name,
            DATEDIFF(l.end_date, l.start_date) + 1 AS days
        FROM leaves l
        JOIN users u ON u.id = l.user_id
        ORDER BY l.applied_at DESC
    ");
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Leave Requests | HRMS</title>
    <link rel="stylesheet" href="manage leave.css">
</head>

<body>

<div class="summary-container">

    <h2>Manage Leave Requests</h2>

    <?php if (isset($leave) && $leave): ?>
        <!-- ===== SINGLE LEAVE VIEW ===== -->
        <div class="form-card">

            <p><strong>Employee:</strong>
                <?= htmlspecialchars($leave['full_name']) ?>
                (<?= htmlspecialchars($leave['employee_id']) ?>)
            </p>

            <p><strong>Leave Type:</strong>
                <?= htmlspecialchars($leave['leave_type']) ?>
            </p>

            <p><strong>Dates:</strong>
                <?= $leave['start_date'] ?> → <?= $leave['end_date'] ?>
            </p>

            <p><strong>Total Days:</strong>
                <?= (int)$leave['days'] ?>
            </p>

            <p><strong>Reason:</strong><br>
                <?= nl2br(htmlspecialchars($leave['reason'])) ?>
            </p>

            <?php if (!empty($leave['document'])): ?>
                <p><strong>Document:</strong>
                    <a href="<?= htmlspecialchars($leave['document']) ?>" target="_blank">
                        Download
                    </a>
                </p>
            <?php endif; ?>

            <p>
                <strong>Status:</strong>
                <span class="status <?= strtolower($leave['status']) ?>">
                    <?= htmlspecialchars($leave['status']) ?>
                </span>
            </p>

            <?php if ($leave['status'] === 'Pending'): ?>
                <a class="btn btn-approve" href="approve_leave.php?id=<?= $leave['id'] ?>">Approve</a>
                <a class="btn btn-reject" href="reject_leave.php?id=<?= $leave['id'] ?>">Reject</a>
            <?php endif; ?>

            <div class="btn-row">
                <a class="back-btn" href="admin_dashboard.php">← Back to Dashboard</a>
            </div>
        </div>

    <?php else: ?>
        <!-- ===== LIST VIEW ===== -->
        <table class="summary-table">
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Date Range</th>
                <th>Days</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php $i = 1; foreach ($list as $row): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td><?= $row['start_date'] ?> → <?= $row['end_date'] ?></td>
                    <td><?= (int)$row['days'] ?></td>
                    <td>
                        <span class="status <?= strtolower($row['status']) ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn btn-view" href="manage_leave.php?id=<?= $row['id'] ?>">View</a>

                        <?php if ($row['status'] === 'Pending'): ?>
                            <a class="btn btn-approve" href="approve_leave.php?id=<?= $row['id'] ?>">Approve</a>
                            <a class="btn btn-reject" href="reject_leave.php?id=<?= $row['id'] ?>">Reject</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="btn-row">
            <a class="back-btn" href="admin_dashboard.php">← Back to Dashboard</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
