<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ header("Location: login.php"); exit; }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id>0){
    // mark approved
    $stmt = $conn->prepare("UPDATE leaves SET status='Approved', approved_by=? WHERE id=?");
    $stmt->execute([$_SESSION['user_id'], $id]);

    // reduce leave balance for that user if not No Pay Leave
    $info = $conn->prepare("SELECT user_id, leave_type, days FROM leaves WHERE id=?");
    $info->execute([$id]); $row = $info->fetch();
    if($row && $row['leave_type'] !== 'No Pay Leave'){
        $dec = $conn->prepare("UPDATE users SET leave_balance = GREATEST(leave_balance - ?, 0) WHERE id=?");
        $dec->execute([$row['days'], $row['user_id']]);
    } else if($row && $row['leave_type'] === 'No Pay Leave'){
        // no leave balance change, but we might track no-pay in payroll later
    }
}
header("Location: manage_leave.php");
exit;
