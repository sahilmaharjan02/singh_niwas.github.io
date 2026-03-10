<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;

if ($user_id > 0) {
    try {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $_SESSION['error'] = "User not found!";
            header('Location: admin-users.php');
            exit();
        }
        
        $bookings_stmt = $pdo->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE user_id = ?");
        $bookings_stmt->execute([$user_id]);
        $booking_count = $bookings_stmt->fetch()['booking_count'];
        
        $payments_stmt = $pdo->prepare("SELECT COUNT(*) as payment_count FROM payments WHERE user_id = ?");
        $payments_stmt->execute([$user_id]);
        $payment_count = $payments_stmt->fetch()['payment_count'];
        
        if ($payment_count > 0) {
            $_SESSION['error'] = "Cannot delete user '{$user['name']}' because they have {$payment_count} payment record(s). Please delete the payment records first.";
        } 
        
        else {
            $pdo->beginTransaction();
            
            if ($booking_count > 0) {
                $delete_bookings = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
                $delete_bookings->execute([$user_id]);
            }
            
            $delete_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delete_user->execute([$user_id]);

            $pdo->commit();
            
            $_SESSION['success'] = "User '{$user['name']}' deleted successfully!" . 
                                  ($booking_count > 0 ? " {$booking_count} booking(s) were also removed." : "");
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Failed to delete user: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid user ID!";
}

header('Location: admin-users.php');
exit();
?>