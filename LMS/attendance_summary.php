<?php
session_start();
require 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

// Fetch all attendance records with employee details
$stmt = $conn->prepare("
    SELECT a.*, e.full_name, e.emp_number
    FROM attendance a
    JOIN employees e ON a.user_id = e.emp_number
    ORDER BY a.attendance_date DESC, e.emp_number ASC
");
$stmt->execute();
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Summary</title>
    <link rel="stylesheet" href="attendance.css">
    <style>
        /* Table */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            font-size: 14px;
            margin-top: 20px;
        }
        .summary-table th {
            background: linear-gradient(135deg,#007bff,#005fcc);
            color: #fff;
            padding: 12px;
            text-align: center;
            font-weight: 600;
        }
        .summary-table td {
            padding: 12px;
            text-align: center;
            background: rgba(255,255,255,0.85);
            color: #2c4f73;
        }
        .summary-table tr:hover {
            transform: translateY(-2px);
            box-shadow:0 8px 18px rgba(0,0,0,0.1);
            transition: .3s;
        }

        /* Status Colors */
        .status-present { color: #198754; font-weight:600; }
        .status-late { color: #dc3545; font-weight:600; }
        .status-absent { color: #6c757d; font-weight:600; }

        /* Cancel Button */
        .cancel-btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 12px 28px;
            background: linear-gradient(135deg,#dc3545,#b52a37);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .cancel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="glass-box">
        <h2>Attendance Summary</h2>

        <!-- Cancel Button -->
        <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>

        <table class="summary-table">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Working Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($attendance_records): ?>
                    <?php foreach($attendance_records as $att): 
                        // Calculate working hours
                        $working_hours = "--";
                        if($att['login_time'] && $att['logout_time']){
                            $login = new DateTime($att['login_time']);
                            $logout = new DateTime($att['logout_time']);
                            $diff = $login->diff($logout);
                            $working_hours = $diff->format('%h:%I:%S');
                        }

                        // Determine status
                        $status_class = "status-absent";
                        if($att['status'] === "Present") $status_class = "status-present";
                        elseif($att['status'] === "Late Attendance") $status_class = "status-late";
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($att['emp_number']) ?></td>
                        <td><?= htmlspecialchars($att['full_name']) ?></td>
                        <td><?= $att['attendance_date'] ?></td>
                        <td><?= $att['login_time'] ?? '--' ?></td>
                        <td><?= $att['logout_time'] ?? '--' ?></td>
                        <td><?= $working_hours ?></td>
                        <td class="<?= $status_class ?>"><?= $att['status'] ?? 'Absent' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No attendance records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
