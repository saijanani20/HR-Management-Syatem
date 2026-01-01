<?php
session_start();
require 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;
$message = "";

/* ================= DELETE ================= */
if($action === 'delete' && $id){
    $stmt = $conn->prepare("DELETE FROM employees WHERE emp_id=?");
    $stmt->execute([$id]);
    header("Location: employee.php");
    exit;
}

/* ================= FETCH FOR EDIT ================= */
$employee = null;
if($action === 'edit' && $id){
    $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_id=?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
}

/* ================= ADD / UPDATE ================= */
if(isset($_POST['save'])){
    if(!empty($_POST['emp_id'])){   // UPDATE
        $sql = "UPDATE employees SET 
                emp_number=?, full_name=?, nic=?, gender=?, address=?, 
                job_role=?, designation=?, basic_salary=?
                WHERE emp_id=?";
        $conn->prepare($sql)->execute([
            $_POST['emp_number'],
            $_POST['full_name'],
            $_POST['nic'],
            $_POST['gender'],
            $_POST['address'],
            $_POST['job_role'],
            $_POST['designation'],
            $_POST['basic_salary'],
            $_POST['emp_id']
        ]);
        $message = "Employee updated successfully";
    }else{                           // ADD
        $sql = "INSERT INTO employees
                (emp_number, full_name, nic, gender, address, job_role, designation, basic_salary)
                VALUES (?,?,?,?,?,?,?,?)";
        $conn->prepare($sql)->execute([
            $_POST['emp_number'],
            $_POST['full_name'],
            $_POST['nic'],
            $_POST['gender'],
            $_POST['address'],
            $_POST['job_role'],
            $_POST['designation'],
            $_POST['basic_salary']
        ]);
        $message = "Employee added successfully";
    }
}

/* ================= FETCH ALL ================= */
$employees = $conn->query("SELECT * FROM employees ORDER BY emp_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<title>Employee Management</title>
<link rel="stylesheet" href="employee.css">
</head>
<body>

<h2>Employee Management</h2>

<?php if($message): ?>
<div class="success-msg"><?= $message ?></div>
<?php endif; ?>

<!-- ================= FORM ================= -->
<div class="glass-box">
<form method="POST" class="form-box">
    <input type="hidden" name="emp_id" value="<?= $employee['emp_id'] ?? '' ?>">

    <input type="text" name="emp_number" placeholder="Employee Number"
           value="<?= $employee['emp_number'] ?? '' ?>" required>

    <input type="text" name="full_name" placeholder="Full Name"
           value="<?= $employee['full_name'] ?? '' ?>" required>

    <input type="text" name="nic" placeholder="NIC Number"
           value="<?= $employee['nic'] ?? '' ?>" required>

    <select name="gender" required>
        <option value="">Select Gender</option>
        <option <?= ($employee['gender'] ?? '')=='Male'?'selected':'' ?>>Male</option>
        <option <?= ($employee['gender'] ?? '')=='Female'?'selected':'' ?>>Female</option>
        <option <?= ($employee['gender'] ?? '')=='Other'?'selected':'' ?>>Other</option>
    </select>

    <textarea name="address" placeholder="Address"><?= $employee['address'] ?? '' ?></textarea>

    <input type="text" name="job_role" placeholder="Job Role"
           value="<?= $employee['job_role'] ?? '' ?>">

    <input type="text" name="designation" placeholder="Designation"
           value="<?= $employee['designation'] ?? '' ?>">

    <input type="number" step="0.01" name="basic_salary" placeholder="Basic Salary"
           value="<?= $employee['basic_salary'] ?? '' ?>" required>

    <button type="submit" name="save">
        <?= $employee ? 'Update Employee' : 'Add Employee' ?>
    </button>

    <button type="button" class="cancel-btn" onclick="window.location.href='admin_dashboard.php'">
    Cancel
</button>

</form>
</div>

<!-- ================= TABLE ================= -->
<table class="attendance-table">
<tr>
    <th>Emp No</th>
    <th>Name</th>
    <th>Designation</th>
    <th>Basic Salary</th>
    <th>Actions</th>
</tr>

<?php foreach($employees as $emp): ?>
<tr>
    <td><?= $emp['emp_number'] ?></td>
    <td><?= $emp['full_name'] ?></td>
    <td><?= $emp['designation'] ?></td>
    <td><?= number_format($emp['basic_salary'],2) ?></td>
    <td>
        <a href="employee.php?action=edit&id=<?= $emp['emp_id'] ?>">Edit</a> |
        <a href="employee.php?action=delete&id=<?= $emp['emp_id'] ?>"
           onclick="return confirm('Delete this employee?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
