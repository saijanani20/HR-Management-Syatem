<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* Current Month & Year */
$month = date('m');
$year  = date('Y');

/* US Holidays (Basic – You can extend later) */
$us_holidays = [
    "$year-01-01" => "New Year's Day",
    "$year-01-15" => "Martin Luther King Jr. Day",
    "$year-02-19" => "Presidents' Day",
    "$year-05-27" => "Memorial Day",
    "$year-07-04" => "Independence Day",
    "$year-09-02" => "Labor Day",
    "$year-10-14" => "Columbus Day",
    "$year-11-11" => "Veterans Day",
    "$year-11-28" => "Thanksgiving Day",
    "$year-12-25" => "Christmas Day"
];

/* Calendar Setup */
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$start_day = date('w', $first_day);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Holiday Calendar</title>
    <link rel="stylesheet" href="calendar.css">
</head>
<body>

<div class="calendar-container">
    <h2>USA Holiday Calendar</h2>
    <p class="month"><?= date("F Y"); ?></p>

    <table class="calendar">
        <tr>
            <th>Sun</th><th>Mon</th><th>Tue</th>
            <th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
        </tr>
        <tr>
            <?php
            for ($i = 0; $i < $start_day; $i++) {
                echo "<td></td>";
            }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = "$year-$month-" . str_pad($day, 2, "0", STR_PAD_LEFT);

                if (isset($us_holidays[$date])) {
                    echo "<td class='holiday'>
                            <span>$day</span>
                            <small>{$us_holidays[$date]}</small>
                          </td>";
                } else {
                    echo "<td><span>$day</span></td>";
                }

                if ((($day + $start_day) % 7) == 0) {
                    echo "</tr><tr>";
                }
            }
            ?>
        </tr>
    </table>

    <div class="legend">
        <span class="holiday-box"></span> USA Public Holiday
    </div>

    <a href="<?php
        if ($_SESSION['role'] === 'admin') {
            echo 'admin_dashboard.php';
        } else {
            echo 'dashboard_employee.php';
        }
    ?>" class="back-btn">
        Back
    </a>
</div>

</body>
</html>
