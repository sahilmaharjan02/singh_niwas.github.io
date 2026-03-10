<?php

session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check if payment belongs to user and is pending
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$payment_id, $user_id]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            echo json_encode(['success' => false, 'message' => 'Payment not found or cannot be deleted']);
            exit();
        }
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->execute([$payment_id]);
        
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'unpaid' WHERE id = ?");
        $stmt->execute([$payment['booking_id']]);
        
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'pending' WHERE id = ?");
        $stmt->execute([$payment['booking_id']]);
        
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'reserved' WHERE id = ?");
        $stmt->execute([$payment['booking_id']]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>