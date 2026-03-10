<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$booking_id = $_GET['id'] ?? 0;

if ($booking_id > 0) {
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $delete_stmt->execute([$booking_id]);
        $_SESSION['success'] = "Booking deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete booking: " . $e->getMessage();
    }
}

header('Location: admin-bookings.php');
exit();
?>