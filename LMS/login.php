<?php
session_start();
require_once "db.php";

$message = "";

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] == "admin") {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard_employee.php");
        }
        exit;
    } 
    else {
        $message = "❌ Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | LMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>

<body>

<div class="login-wrapper">

    <!-- LEFT PANEL -->
    <div class="left-panel">

        <div class="overlay"></div>

        <img src="logo.png" class="logo">

        <div class="right-text">
            <h1>Hello, welcome!</h1>
            <p class="quote">“I'm a great believer in luck, and I find the harder I work, the more I have of it.”</p>
        </div>

        <button class="view-btn" onclick="openModal()">View more</button>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

        

        <h2>Login</h2>

        <?php if ($message): ?>
            <p class="error"><?= $message ?></p>
        <?php endif; ?>

        <form method="POST" class="login-form">

            <div class="input-box">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email address" required>
            </div>

            <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="options">
                <label><input type="checkbox"> Remember me</label>
                <a href="forgot_password.php">Forgot password?</a>
            </div>

            <button type="submit" class="login-btn">Login</button>

            <p class="signup-text">Not a member yet?</p>
            <a href="register.php"><button type="button" class="signup-btn">Sign up</button></a>

        </form>
    </div>
</div>

<!-- MODAL POPUP -->
<div id="missionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2>Our Mission</h2>

        <p>
            Our mission at Legacy Health is to create a best-in-class revenue cycle solution 
            that continuously improves service quality, efficiency, and profitability for our clients. 
            With headquarters in Dallas, Texas, and locations in India (Chennai, Trivandrum, 
            Subhash Nagar) and Sri Lanka (Colombo, Kandy), we are strategically positioned 
            to serve our global clientele.
        </p>
    </div>
</div>

<script>
function openModal() {
    document.getElementById("missionModal").style.display = "block";
}

function closeModal() {
    document.getElementById("missionModal").style.display = "none";
}

window.onclick = function(event) {
    let modal = document.getElementById("missionModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
};
</script>

</body>
</html>
