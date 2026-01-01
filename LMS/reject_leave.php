<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ header("Location: login.php"); exit; }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id>0){
    $stmt = $conn->prepare("UPDATE leaves SET status='Rejected', approved_by=? WHERE id=?");
    $stmt->execute([$_SESSION['user_id'], $id]);
}
header("Location: manage_leave.php");
exit;
