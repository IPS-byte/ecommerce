<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "user_auth_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['Fullname']);
    $email = trim($_POST['Email']);
    $role = trim($_POST['role']);
    $password = $_POST['Password'];
    $confirm_password = $_POST['Confirm_password'];

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.history.back();</script>";
        exit;
    }

    // ✅ Only allow specific roles
    $allowed_roles = ['admin', 'seller', 'delivery_partner'];
    if (!in_array($role, $allowed_roles)) {
        echo "<script>alert('Only Admins, Sellers, or Delivery Partners can register!'); window.history.back();</script>";
        exit;
    }

    // ✅ Validate password strength
    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long!'); window.history.back();</script>";
        exit;
    }

    // ✅ Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // ✅ Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered! Please use another.'); window.history.back();</script>";
        exit;
    }

    // ✅ Hash password (BCRYPT)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // ✅ Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $role, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error: Could not register user.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['Fullname']);
    $email = strtolower(trim($_POST['Email']));
    $role = $_POST['role'];
    $password = $_POST['Password'];
    $confirm = $_POST['Confirm_password'];

    // Validate
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    $allowed_roles = ['admin', 'seller', 'delivery_partner'];
    if (!in_array($role, $allowed_roles)) {
        die("You are not allowed to register as this role.");
    }

    // Check if email exists
    global $conn;
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("This email is already registered.");
    }

    $check->close();

    // Hash password and insert
    $hashed = hash_password($password);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $role, $hashed);

    if ($stmt->execute()) {
        echo "Registration successful! Please verify your email.";
    } else {
        echo "Registration failed. Try again.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>User Registration</title> </head> <body> <h2>User Registration Form</h2> <form action="register.php" method="post"> <label for="Fullname">Full name</label><br> <input type="text" id="Fullname" name="Fullname" required><br><br> <label for="Email">Email</label><br> <input type="email" id="Email" name="Email" required><br><br> <label for="role">Role</label><br> <select id="role" name="role" required> <option value="">Select role</option> <option value="admin">Admin</option> <option value="seller">Seller</option> <option value="delivery_partner">Delivery Partner</option> </select><br><br> <label for="Password">Password</label><br> <input type="password" id="Password" name="Password" required minlength="8"><br><br> <label for="Confirm_password">Confirm Password</label><br> <input type="password" id="Confirm_password" name="Confirm_password" required minlength="8"><br><br> <button type="submit">Register</button> </form> </body> </html>