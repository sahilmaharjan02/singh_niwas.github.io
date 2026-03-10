<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $total_amount = $_POST['total_amount'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_amount]);
        
        $stmt = $pdo->prepare("UPDATE rooms SET status = 'reserved' WHERE id = ?");
        $stmt->execute([$room_id]);
        
        $pdo->commit();
        
        $_SESSION['booking_success'] = 'Room booked successfully! Please complete payment to confirm your booking.';
        header('Location: dashboard.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['booking_error'] = 'Booking failed: ' . $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
}
?>