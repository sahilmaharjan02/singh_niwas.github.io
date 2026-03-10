<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $new_password = $_POST['new_password'];
    
    try {
        if (!empty($new_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $hashed_password, $user_id]);
        } else {
            // Update without password
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $user_id]);
        }
        
        $_SESSION['user_name'] = $name;
        $_SESSION['profile_success'] = 'Profile updated successfully!';
        
    } catch (Exception $e) {
        $_SESSION['profile_error'] = 'Update failed: ' . $e->getMessage();
    }
    
    header('Location: dashboard.php');
    exit();
}
?>