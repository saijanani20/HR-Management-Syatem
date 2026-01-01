<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ header("Location: login.php"); exit; }

$msg = '';

// fetch employees for dropdown
$employees = $conn->query("SELECT id, full_name, employee_id FROM users WHERE role='employee'")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $user_id = intval($_POST['user_id']);
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $days = isset($_POST['days']) ? intval($_POST['days']) : 1;
    $reason = $_POST['reason'];

    // file upload optional
    $file_path = null;
    if(!empty($_FILES['document']['name'])){
        $uploads_dir = "uploads/";
        if(!is_dir($uploads_dir)) mkdir($uploads_dir,0777,true);
        $fn = time()."_".basename($_FILES['document']['name']);
        $file_path = $uploads_dir.$fn;
        move_uploaded_file($_FILES['document']['tmp_name'], $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO leaves (user_id, leave_type, start_date, end_date, days, reason, document, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $leave_type, $start_date, $end_date, $days, $reason, $file_path]);
    $msg = "Leave applied for employee.";
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Apply Leave</title><link rel="stylesheet" href="admin.css"></head>
<body>
<div class="main-content">
  <h2>Apply Leave (Admin)</h2>
 
      <label>Leave Type</label>
      <select name="leave_type" required>
        <option>Annual</option><option>Casual</option><option>Emergency</option>
        <option>Short Leave</option><option>Half Day</option><option>No Pay Leave</option>
      </select>

      <div style="display:flex; gap:10px;">
        <div style="flex:1"><label>Start Date</label><input type="date" name="start_date" required></div>
        <div style="flex:1"><label>End Date</label><input type="date" name="end_date" required></div>
      </div>

      <label>Days</label>
      <input type="number" name="days" value="1" min="1" required>

      <label>Reason</label>
      <textarea name="reason" rows="3"></textarea>

      <label>Document (optional)</label>
      <input type="file" name="document">

      <button type="submit" class="btn btn-approve">Submit</button>
    </form>
  </div>
</div>
</body>
</html>
