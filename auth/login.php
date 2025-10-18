<?php
require_once '../includes/helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    global $conn;
    $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (verify_password($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['full_name'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../dashboards/admin_dashboard.php");
                    break;
                case 'seller':
                    header("Location: ../dashboards/seller_dashboard.php");
                    break;
                case 'delivery_partner':
                    header("Location: ../dashboards/delivery_dashboard.php");
                    break;
                default:
                    header("Location: ../index.php");
            }
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this email.";
    }
    $stmt->close();
}
?>
