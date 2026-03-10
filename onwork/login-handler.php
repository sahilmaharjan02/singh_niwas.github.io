<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter email and password';
        header('Location: login.php');
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        if (isset($user['status']) && $user['status'] === 'inactive') {
            $_SESSION['login_error'] = 'Your account has been deactivated. Please contact administrator.';
            header('Location: login.php');
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
        if (isset($_POST['remember']) && $_POST['remember'] == '1') {
            $cookie_value = base64_encode($user['email'] . ':' . $user['id']);
            setcookie('remember_me', $cookie_value, time() + (86400 * 30), "/"); 
        }
        
        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['login_error'] = 'Invalid email or password';
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>