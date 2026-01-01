<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Only cancel if status is pending
    $stmt = $conn->prepare("UPDATE leaves SET status='Cancelled' WHERE id=? AND user_id=? AND status='Pending'");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
}

header("Location: dashboard_employee.php");
exit;
?>
