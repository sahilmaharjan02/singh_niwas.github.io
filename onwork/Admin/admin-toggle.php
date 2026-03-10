<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;
$new_status = $_GET['status'] ?? '';

if ($user_id > 0 && in_array($new_status, ['active', 'inactive'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        $_SESSION['success'] = "User status updated to " . $new_status . " successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to update user status: " . $e->getMessage();
    }
}

header('Location: admin-users.php');
exit();
?>