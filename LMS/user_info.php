<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT full_name, employee_id, designation, department, role, mobile, address, join_date, profile_pic 
                        FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = $_POST['full_name'];
    $designation = $_POST['designation'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];

    // Profile Image Upload
    $profile_pic = $user['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $imageName = time() . "_" . $_FILES['profile_pic']['name'];
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], "uploads/" . $imageName);
        $profile_pic = $imageName;
    }

    $update = $conn->prepare("UPDATE users SET full_name=?, designation=?, mobile=?, address=?, profile_pic=? WHERE id=?");
    $update->execute([$full_name, $designation, $mobile, $address, $profile_pic, $user_id]);

    header("Location: user_info.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Information</title>
    <link rel="stylesheet" href="user_styles.css">
</head>
<body>

<div class="glass-box">

    <h2>User Information</h2>

    <?php if(isset($_GET['updated'])): ?>
        <p class="success-msg">✔ Profile Updated Successfully!</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- Profile Picture -->
        <div class="profile-section">
            <img src="uploads/<?= $user['profile_pic'] ?? 'default.png' ?>" id="preview" class="profile-img">
            <input type="file" name="profile_pic" accept="image/*" onchange="loadImage(event)">
        </div>

        <div class="form-grid">

            <div class="input-box">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= $user['full_name']; ?>" required>
            </div>

            <div class="input-box">
                <label>Employee ID</label>
                <input type="text" value="<?= $user['employee_id']; ?>" disabled>
            </div>

            <div class="input-box">
                <label>Designation</label>
                <input type="text" name="designation" value="<?= $user['designation']; ?>" required>
            </div>

            <div class="input-box">
                <label>Department</label>
                <input type="text" value="<?= $user['department']; ?>" disabled>
            </div>

            <div class="input-box">
                <label>Role</label>
                <input type="text" value="<?= ucfirst($user['role']); ?>" disabled>
            </div>

            <div class="input-box">
                <label>Mobile Number</label>
                <input type="text" name="mobile" value="<?= $user['mobile']; ?>" required>
            </div>

            <div class="input-box full-width">
                <label>Address</label>
                <input type="text" name="address" value="<?= $user['address']; ?>" required>
            </div>

            <div class="input-box">
                <label>Join Date</label>
                <input type="text" value="<?= $user['join_date']; ?>" disabled>
            </div>

        </div>

        <div id="extra-fields"></div>

        <!-- Add More Info Button -->
        <button type="button" class="add-btn" onclick="addField()">+ Add More Info</button>

        <!-- Buttons -->
        <div class="button-row">
            <button type="submit" class="save-btn">Save</button>
            <a href="<?php
        if ($_SESSION['role'] === 'admin') {
            echo 'admin_dashboard.php';
        } else {
            echo 'dashboard_employee.php';
        }
    ?>" class="cancel-btn">
        Cancel
    </a>
        </div>
    </form>

</div>  

<script>
function loadImage(event) {
    document.getElementById('preview').src = URL.createObjectURL(event.target.files[0]);
}

function addField() {
    const container = document.getElementById("extra-fields");

    const html = `
        <div class="input-box full-width">
            <label>Additional Info</label>
            <input type="text" name="extra_info[]" placeholder="Enter additional detail">
        </div>
    `;

    container.insertAdjacentHTML("beforeend", html);
}
</script>

</body>
</html>
