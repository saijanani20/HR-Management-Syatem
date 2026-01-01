<?php
session_start();
require 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

$message = "";
$emp_details = null;
$salary_details = [];

/* FETCH EMPLOYEE DETAILS */
if(isset($_POST['fetch'])){
    $emp_num = $_POST['emp_number'];

    $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_number=?");
    $stmt->execute([$emp_num]);
    $emp_details = $stmt->fetch();

    if(!$emp_details){
        $message = "Employee not found!";
    }
}

/* CALCULATE SALARY */
if(isset($_POST['calculate']) && isset($_POST['emp_number'])){
    $emp_num = $_POST['emp_number'];
    $basic_salary = $_POST['basic_salary'];
    $special_allowance = $_POST['special_allowance'] ?? 0;

    // EPF 8%
    $epf = $basic_salary * 0.08;

    // ETF 3%
    $etf = $basic_salary * 0.03;

    // Insurance 1%
    $insurance = $basic_salary * 0.01;

    // Total Salary
    $total_salary = $basic_salary + $special_allowance - ($epf + $insurance);

    $salary_details = [
        'basic_salary' => $basic_salary,
        'special_allowance' => $special_allowance,
        'epf' => $epf,
        'etf' => $etf,
        'insurance' => $insurance,
        'total_salary' => $total_salary
    ];

    // Fetch employee details again
    $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_number=?");
    $stmt->execute([$emp_num]);
    $emp_details = $stmt->fetch();
}

/* GENERATE PAYROLL REPORT (DOWNLOADABLE HTML) */
if(isset($_POST['receipt'])){
    $emp_num = $_POST['emp_number'];
    $basic_salary = $_POST['basic_salary'];
    $special_allowance = $_POST['special_allowance'];
    $epf = $_POST['epf'];
    $etf = $_POST['etf'];
    $insurance = $_POST['insurance'];
    $total_salary = $_POST['total_salary'];

    // Fetch employee details
    $stmt = $conn->prepare("SELECT * FROM employees WHERE emp_number=?");
    $stmt->execute([$emp_num]);
    $emp = $stmt->fetch();

    if($emp){
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=Payroll_{$emp_num}.html");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Payroll Report - {$emp['full_name']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h2,h3 { text-align:center; color:#003f7f; }
                table { width:100%; border-collapse: collapse; margin-top:20px; }
                th, td { border:1px solid #ccc; padding:10px; text-align:left; }
                th { background:#007BFF; color:#fff; }
                tr:hover { background:#f2f2f2; }
            </style>
        </head>
        <body>
            <h2>Payroll Report</h2>
            <h3>Employee Details</h3>
            <table>
                <tr><th>Employee Number</th><td>{$emp['emp_number']}</td></tr>
                <tr><th>Name</th><td>{$emp['full_name']}</td></tr>
                <tr><th>NIC</th><td>{$emp['nic']}</td></tr>
                <tr><th>Gender</th><td>{$emp['gender']}</td></tr>
                <tr><th>Address</th><td>{$emp['address']}</td></tr>
                <tr><th>Designation</th><td>{$emp['designation']}</td></tr>
                <tr><th>Job Role</th><td>{$emp['job_role']}</td></tr>
            </table>

            <h3>Salary Details</h3>
            <table>
                <tr><th>Basic Salary</th><td>".number_format($basic_salary,2)."</td></tr>
                <tr><th>Special Allowance</th><td>".number_format($special_allowance,2)."</td></tr>
                <tr><th>EPF (8%)</th><td>".number_format($epf,2)."</td></tr>
                <tr><th>ETF (3%)</th><td>".number_format($etf,2)."</td></tr>
                <tr><th>Insurance (1%)</th><td>".number_format($insurance,2)."</td></tr>
                <tr><th>Total Salary</th><td>".number_format($total_salary,2)."</td></tr>
            </table>
        </body>
        </html>";
        exit();
    } else {
        $message = "Employee not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payroll System</title>
    <link rel="stylesheet" href="payroll.css">
</head>
<body>
<div class="page-wrapper">
    <div class="glass-box">
        <h2>Payroll Calculation</h2>

        <?php if($message): ?>
            <div class="error-msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- FETCH EMPLOYEE FORM -->
        <form method="POST" class="form-box">
            <input type="number" name="emp_number" placeholder="Enter Employee ID" required>
            <button type="submit" name="fetch">Fetch Employee</button>
        </form>

        <?php if($emp_details): ?>
            <!-- EMPLOYEE DETAILS TABLE -->
            <h3>Employee Details</h3>
            <table class="attendance-table">
                <tr><th>Employee Number</th><td><?= $emp_details['emp_number'] ?></td></tr>
                <tr><th>Name</th><td><?= $emp_details['full_name'] ?></td></tr>
                <tr><th>NIC</th><td><?= $emp_details['nic'] ?></td></tr>
                <tr><th>Gender</th><td><?= $emp_details['gender'] ?></td></tr>
                <tr><th>Address</th><td><?= $emp_details['address'] ?></td></tr>
                <tr><th>Designation</th><td><?= $emp_details['designation'] ?></td></tr>
                <tr><th>Job Role</th><td><?= $emp_details['job_role'] ?></td></tr>
            </table>

            <!-- PAYROLL CALCULATION FORM -->
            <form method="POST" class="form-box">
                <input type="hidden" name="emp_number" value="<?= $emp_details['emp_number'] ?>">
                <input type="number" name="basic_salary" value="<?= $emp_details['basic_salary'] ?>" placeholder="Basic Salary" required>
                <input type="number" name="special_allowance" value="<?= $salary_details['special_allowance'] ?? 0 ?>" placeholder="Special Allowance">
                <button type="submit" name="calculate">Calculate Salary</button>
            </form>
        <?php endif; ?>

        <!-- DISPLAY SALARY DETAILS TABLE -->
        <?php if($salary_details): ?>
            <hr style="margin:20px 0;">
            <h3>Salary Details</h3>
            <table class="attendance-table">
                <tr><th>Basic Salary</th><td><?= number_format($salary_details['basic_salary'],2) ?></td></tr>
                <tr><th>Special Allowance</th><td><?= number_format($salary_details['special_allowance'],2) ?></td></tr>
                <tr><th>EPF (8%)</th><td><?= number_format($salary_details['epf'],2) ?></td></tr>
                <tr><th>ETF (3%)</th><td><?= number_format($salary_details['etf'],2) ?></td></tr>
                <tr><th>Insurance (1%)</th><td><?= number_format($salary_details['insurance'],2) ?></td></tr>
                <tr><th>Total Salary</th><td><?= number_format($salary_details['total_salary'],2) ?></td></tr>
            </table>

            <!-- GENERATE PAYROLL REPORT -->
            <form method="POST" class="form-box">
                <input type="hidden" name="emp_number" value="<?= $emp_details['emp_number'] ?>">
                <input type="hidden" name="basic_salary" value="<?= $salary_details['basic_salary'] ?>">
                <input type="hidden" name="special_allowance" value="<?= $salary_details['special_allowance'] ?>">
                <input type="hidden" name="epf" value="<?= $salary_details['epf'] ?>">
                <input type="hidden" name="etf" value="<?= $salary_details['etf'] ?>">
                <input type="hidden" name="insurance" value="<?= $salary_details['insurance'] ?>">
                <input type="hidden" name="total_salary" value="<?= $salary_details['total_salary'] ?>">
                <button type="submit" name="receipt">Generate Payroll Report</button>
                <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
