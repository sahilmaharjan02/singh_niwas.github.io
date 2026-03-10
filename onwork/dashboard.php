<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

try {
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, r.room_type, r.price_per_night, 
               COALESCE(p.status, 'unpaid') as payment_status
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $available_stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'available'");
    $available_rooms = $available_stmt->fetchAll(PDO::FETCH_ASSOC);

    $payments_stmt = $pdo->prepare("
        SELECT p.*, b.room_id, r.room_number, r.room_type 
        FROM payments p 
        JOIN bookings b ON p.booking_id = b.id 
        JOIN rooms r ON b.room_id = r.id 
        WHERE p.user_id = ? 
        ORDER BY p.payment_date DESC 
        LIMIT 5
    ");
    $payments_stmt->execute([$user_id]);
    $recent_payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status = 'completed'");
    $total_stmt->execute([$user_id]);
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_paid = $total_result['total'] ?? 0;

    $pending_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE user_id = ? AND status = 'pending'");
    $pending_stmt->execute([$user_id]);
    $pending_result = $pending_stmt->fetch(PDO::FETCH_ASSOC);
    $pending_payments_count = $pending_result['count'] ?? 0;

    $payment_success = $payment_error = $booking_success = $booking_error = null;
    
    if (isset($_SESSION['payment_success'])) {
        $payment_success = $_SESSION['payment_success'];
        unset($_SESSION['payment_success']);
    }

    if (isset($_SESSION['payment_error'])) {
        $payment_error = $_SESSION['payment_error'];
        unset($_SESSION['payment_error']);
    }

    if (isset($_SESSION['booking_success'])) {
        $booking_success = $_SESSION['booking_success'];
        unset($_SESSION['booking_success']);
    }

    if (isset($_SESSION['booking_error'])) {
        $booking_error = $_SESSION['booking_error'];
        unset($_SESSION['booking_error']);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --secondary-color: #6f42c1;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --background-color: #e6f2ff;
            --card-background: #ffffff;
            --text-dark: #002960;
            --text-light: #666;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0, 0, 0, 0.05);
            --shadow-medium: rgba(0, 0, 0, 0.1);
            --white: #ffffff;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            color: var(--text-dark);
        }


        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background: var(--primary-color);
            color: var(--white);
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .user-info {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .user-info h3 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .user-info p {
            color: #666;
            font-size: 14px;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s;
            gap: 10px;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--background-color);
            color: var(--primary-color);
        }

        .nav-link ion-icon {
            font-size: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        .header {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text h1 {
            color: var(--text-dark);
            font-size: 24px;
            margin-bottom: 5px;
        }

        .welcome-text p {
            color: #666;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-home {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: background 0.3s;
            font-family: inherit;
            font-size: 14px;
        }

        .btn-home:hover {
            background: var(--primary-dark);
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.5s ease;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .close-message {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            line-height: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        

.error-input {
    border-color: #dc3545 !important;
    border-width: 2px !important;
}

.error-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.success-message {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

        
        /* NEW BOOKING SECTION ENHANCEMENTS */
        #new-booking-section .bookings-section {
            background: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 20px var(--shadow-light);
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .booking-form-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-header h3 {
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(24, 145, 209, 0.1);
        }

        .form-group input:hover,
        .form-group select:hover {
            border-color: #b0b0b0;
        }

        .room-selection {
            background: var(--background-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .room-card {
            background: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .room-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-light);
        }

        .room-card.selected {
            border-color: var(--primary-color);
            background: rgba(24, 145, 209, 0.05);
        }

        .room-card h4 {
            color: var(--text-dark);
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .room-card p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .room-price {
            color: var(--success-color) !important;
            font-weight: 600;
            font-size: 1.1rem !important;
            margin-top: 10px !important;
        }

        .date-range-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .date-input-group {
            position: relative;
        }

        .date-icon {
            position: absolute;
            right: 15px;
            top: 42px;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .summary-card {
            background: var(--background-color);
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid var(--primary-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .summary-label {
            color: var(--text-light);
        }

        .summary-value {
            color: var(--text-dark);
            font-weight: 500;
        }

        .total-amount-display {
            font-size: 1.8rem !important;
            color: var(--success-color) !important;
            font-weight: 700 !important;
        }

        .booking-terms {
            background: var(--background-color);
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .terms-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .terms-checkbox input[type="checkbox"] {
            width: auto;
            transform: scale(1.2);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--border-color);
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: var(--text-light);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-weight: 500;
            min-width: 150px;
            justify-content: center;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(24, 145, 209, 0.2);
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        #profile-section .bookings-section {
            background: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 20px var(--shadow-light);
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .profile-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(24, 145, 209, 0.2);
        }

        .profile-info h3 {
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .profile-info p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--background-color);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .stat-card h4 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .profile-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .password-change-section {
            background: var(--background-color);
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            border-left: 4px solid var(--warning-color);
        }

        .password-change-section h4 {
            color: var(--text-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .password-strength {
            margin-top: 15px;
            padding: 15px;
            background: var(--white);
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        .strength-meter {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            background: var(--danger-color);
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .strength-fill.weak { width: 33%; background: var(--danger-color); }
        .strength-fill.fair { width: 66%; background: var(--warning-color); }
        .strength-fill.good { width: 100%; background: var(--success-color); }

        .password-requirements {
            margin-top: 15px;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }

        .requirement.valid {
            color: var(--success-color);
        }

        .requirement.invalid {
            color: var(--text-light);
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .btn-danger-outline {
            background: transparent;
            color: var(--danger-color);
            border: 2px solid var(--danger-color);
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .btn-danger-outline:hover {
            background: var(--danger-color);
            color: white;
        }

        .form-group.valid input,
        .form-group.valid select {
            border-color: var(--success-color);
        }

        .form-group.invalid input,
        .form-group.invalid select {
            border-color: var(--danger-color);
        }

        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .form-group.invalid .error-message {
            display: block;
        }

        .success-message {
            color: var(--success-color);
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .form-group.valid .success-message {
            display: block;
        }

        /* Loading States */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row,
            .date-range-container,
            .profile-form-grid {
                grid-template-columns: 1fr;
            }

            .room-grid {
                grid-template-columns: 1fr;
            }

            .form-actions,
            .profile-actions {
                flex-direction: column;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .btn-primary,
            .btn-secondary,
            .btn-danger-outline {
                width: 100%;
                justify-content: center;
            }

            .summary-card {
                padding: 15px;
            }

            .booking-terms {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            #new-booking-section .bookings-section,
            #profile-section .bookings-section {
                padding: 20px;
            }

            .form-header h3 {
                font-size: 1.3rem;
            }

            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }

            .profile-info h3 {
                font-size: 1.3rem;
            }

            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .section {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input[type="checkbox"] {
            accent-color: var(--primary-color);
        }

        .info-tooltip {
            position: relative;
            display: inline-block;
            margin-left: 5px;
            color: var(--primary-color);
            cursor: help;
        }

        .info-tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--text-dark);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 5px;
        }

        .section-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--border-color);
            transition: all 0.3s ease;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
            color: white;
        }

        .card-icon.bookings { background: #1891d1; }
        .card-icon.rooms { background: #28a745; }
        .card-icon.pending { background: #ffc107; }
        .card-icon.payments { background: #6f42c1; }
        .card-icon.total { background: #17a2b8; }

        .card h3 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .card p {
            color: #666;
            font-size: 14px;
        }

        .bookings-section {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            color: var(--text-dark);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: background 0.3s;
            font-size: 14px;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--background-color);
            color: var(--text-dark);
            font-weight: 600;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #cce5ff;
            color: #004085;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-unpaid {
            background: #fff3cd;
            color: #856404;
        }

        .status-refunded {
            background: #f8d7da;
            color: #721c24;
        }

        /* Payment Status Badge */
        .payment-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 5px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header h2, .user-info, .nav-link span {
                display: none;
            }
            
            .nav-link {
                justify-content: center;
                padding: 15px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .header-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .section-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
        .btn-warning {
    background: #fc2424;
    color: #212529;
    border: none;
    padding: 8px 10px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    transition: background 0.3s;
    font-size: 12px;
    font-weight: 500;
}

.btn-warning:hover {
    background: #1891d1;
    color: #212529;
}

.text-muted {
    color: #6c757d;
    font-size: 12px;
    font-style: italic;
}
        
        @media (max-width: 480px) {
            .header-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-home, .logout-btn {
                width: 100%;
                justify-content: center;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Singh Niwas</h2>
        </div>
        
        <div class="user-info">
            <h3><?php echo htmlspecialchars($user_name); ?></h3>
            <p>Welcome back!</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active" onclick="showSection('dashboard')">
                    <ion-icon name="home-outline"></ion-icon>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('bookings')">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>My Bookings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('payments')">
                    <ion-icon name="cash-outline"></ion-icon>
                    <span>My Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('new-booking')">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>New Booking</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('profile')">
                    <ion-icon name="person-outline"></ion-icon>
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Manage your bookings and payments</p>
            </div>
            <div class="header-buttons">
                <a href="index.php" class="btn-home">
                    <ion-icon name="home-outline"></ion-icon>
                    Back to Home
                </a>
                <form action="logout.php" method="POST">
                    <button type="submit" class="logout-btn">
                        <ion-icon name="log-out-outline"></ion-icon>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Success Messages -->
        <?php if (isset($payment_success)): ?>
            <div class="message success-message" id="paymentSuccessMessage">
                <span><?php echo htmlspecialchars($payment_success); ?></span>
                <button class="close-message" onclick="document.getElementById('paymentSuccessMessage').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($booking_success)): ?>
            <div class="message success-message" id="bookingSuccessMessage">
                <span><?php echo htmlspecialchars($booking_success); ?></span>
                <button class="close-message" onclick="document.getElementById('bookingSuccessMessage').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (isset($payment_error)): ?>
            <div class="message error-message" id="paymentErrorMessage">
                <span><?php echo htmlspecialchars($payment_error); ?></span>
                <button class="close-message" onclick="document.getElementById('paymentErrorMessage').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($booking_error)): ?>
            <div class="message error-message" id="bookingErrorMessage">
                <span><?php echo htmlspecialchars($booking_error); ?></span>
                <button class="close-message" onclick="document.getElementById('bookingErrorMessage').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <div id="dashboard-section" class="section">
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon bookings">
                        <ion-icon name="calendar-outline"></ion-icon>
                    </div>
                    <h3><?php echo count($bookings); ?></h3>
                    <p>Total Bookings</p>
                </div>
                
                <div class="card">
                    <div class="card-icon rooms">
                        <ion-icon name="bed-outline"></ion-icon>
                    </div>
                    <h3><?php echo count($available_rooms); ?></h3>
                    <p>Available Rooms</p>
                </div>
                
                <div class="card">
                    <div class="card-icon pending">
                        <ion-icon name="time-outline"></ion-icon>
                    </div>
                    <h3><?php echo $pending_payments_count; ?></h3>
                    <p>Pending Payments</p>
                </div>
                
                <div class="card">
                    <div class="card-icon payments">
                        <ion-icon name="card-outline"></ion-icon>
                    </div>
                    <h3><?php echo count($recent_payments); ?></h3>
                    <p>Recent Payments</p>
                </div>

                <div class="card">
                    <div class="card-icon total">
                        <ion-icon name="wallet-outline"></ion-icon>
                    </div>
                    <h3>NRS<?php echo number_format($total_paid, 2); ?></h3>
                    <p>Total Paid</p>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bookings-section">
                <div class="section-header">
                    <h2>Recent Bookings</h2>
                    <a href="#" class="btn-primary" onclick="showSection('new-booking')">
                        <ion-icon name="add-outline"></ion-icon>
                        New Booking
                    </a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <p>No bookings yet. <a href="#" onclick="showSection('new-booking')">Make your first booking!</a></p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($bookings, 0, 5) as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['room_type'] . ' - ' . $booking['room_number']); ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['check_out'])); ?></td>
                                <td>NRS<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                    <?php if (isset($booking['payment_status'])): ?>
                                        <span class="payment-badge status-<?php echo $booking['payment_status']; ?>">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($booking['status'] == 'pending' && ($booking['payment_status'] ?? 'unpaid') == 'unpaid'): ?>
                                        <a href="make-payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn-success">
                                            Pay Now
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Recent Payments -->
            <div class="bookings-section">
                <div class="section-header">
                    <h2>Recent Payments</h2>
                    <a href="payment-history.php" class="btn-primary">
                        <ion-icon name="receipt-outline"></ion-icon>
                        View All Payments
                    </a>
                </div>
                
                <?php if (empty($recent_payments)): ?>
                    <p>No payments yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_payments as $payment): ?>
                            <tr>
                                <td>#<?php echo $payment['id']; ?></td>
                                <td><?php echo htmlspecialchars($payment['room_type'] . ' - Room ' . $payment['room_number']); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td><?php echo $payment['transaction_id'] ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?></td>
                                <td>NRS<?php echo number_format($payment['amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Bookings Section -->
        <div id="bookings-section" class="section" style="display: none;">
            <div class="bookings-section">
                <div class="section-header">
                    <h2>My Bookings</h2>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['room_type'] . ' - ' . $booking['room_number']); ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['check_out'])); ?></td>
                                <td>NRS<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                    <?php if (isset($booking['payment_status'])): ?>
                                        <span class="payment-badge status-<?php echo $booking['payment_status']; ?>">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($booking['payment_status'] ?? 'unpaid') == 'unpaid' && $booking['status'] == 'pending'): ?>
                                        <a href="make-payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn-success">Pay Now</a>
                                    <?php elseif (($booking['payment_status'] ?? '') == 'paid'): ?>
                                        <span class="status-badge status-paid">Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                       <?php if ($booking['status'] == 'pending'): ?>
                                            <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" class="btn-danger">
                                             <ion-icon name="close-circle-outline"></ion-icon>
                                                Cancel
                                            </button>
                                         <?php elseif ($booking['status'] == 'confirmed'): ?>
                                            <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" class="btn-warning" 
                                                 title="Cancellation may incur charges">
                                                <ion-icon name="alert-circle-outline"></ion-icon>
                                                Request Cancel
                                             </button>
                                         <?php else: ?>
                                          <span class="text-muted">No actions</span>
                                         <?php endif; ?>
                                     </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

<div id="payments-section" class="section" style="display: none;">
    <div class="bookings-section">
        <div class="section-header">
            <h2>My Payments</h2>
            <a href="payment-history.php" class="btn-primary">
                <ion-icon name="receipt-outline"></ion-icon>
                View Full History
            </a>
        </div>
        
        <?php if (empty($recent_payments)): ?>
            <p>No payments yet. <a href="#" onclick="showSection('new-booking')">Make a booking first!</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Booking</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_payments as $payment): ?>
                    <tr>
                        <td>#<?php echo $payment['id']; ?></td>
                        <td><?php echo htmlspecialchars($payment['room_type'] . ' - Room ' . $payment['room_number']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                        <td><?php echo ucfirst($payment['payment_method']); ?></td>
                        <td><?php echo $payment['transaction_id'] ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?></td>
                        <td>NRS<?php echo number_format($payment['amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $payment['status']; ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($payment['status'] == 'pending'): ?>
                                    <a href="make-payment.php?payment_id=<?php echo $payment['id']; ?>" class="btn-success">
                                        Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Payment Statistics -->
        <div style="margin-top: 30px; padding: 20px; background: var(--background-color); border-radius: 8px;">
            <h3 style="margin-bottom: 15px; color: var(--text-dark);">Payment Statistics</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <p style="color: #666666; font-size: 14px; margin-bottom: 5px;">Total Paid</p>
                    <h4 style="color: var(--primary-color); font-size: 24px; font-weight: 600;">
                        NRS<?php echo number_format($total_paid, 2); ?>
                    </h4>
                </div>
                <div>
                    <p style="color: #666; font-size: 14px; margin-bottom: 5px;">Pending Payments</p>
                    <h4 style="color: #ffc107; font-size: 24px; font-weight: 600;">
                        <?php echo $pending_payments_count; ?>
                    </h4>
                </div>
                <div>
                    <p style="color: #666; font-size: 14px; margin-bottom: 5px;">Total Transactions</p>
                    <h4 style="color: var(--text-dark); font-size: 24px; font-weight: 600;">
                        <?php echo count($recent_payments); ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
 </div>

        <!-- New Booking Section -->
        <div id="new-booking-section" class="section" style="display: none;">
            <div class="bookings-section">
                <div class="section-header">
                    <h2>New Booking</h2>
                </div>
                
                <form id="booking-form" method="POST" action="book-room.php">
                    <div class="form-group">
                        <label for="room">Select Room</label>
                        <select id="room" name="room_id" required>
                            <option value="">Select a room</option>
                            <?php foreach($available_rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>" data-price="<?php echo $room['price_per_night']; ?>">
                                <?php echo htmlspecialchars($room['room_type'] . ' - Room ' . $room['room_number'] . ' (NRS' . $room['price_per_night'] . '/night)'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Total Amount: <span id="total-amount">NRS0</span></label>
                        <input type="hidden" id="total_amount" name="total_amount" value="0">
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <ion-icon name="checkmark-outline"></ion-icon>
                        Book Now
                    </button>
                </form>
            </div>
        </div>

        <div id="profile-section" class="section" style="display: none;">
            <div class="bookings-section">
                <div class="section-header">
                    <h2>My Profile</h2>
                </div>
                
                <?php
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                
                <form id="profile-form" method="POST" action="update-profile.php">
                    <div class="form-group">
                        <label for="profile_name">Full Name</label>
                        <input type="text" id="profile_name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_email">Email</label>
                        <input type="email" id="profile_email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_phone">Phone Number</label>
                        <input type="tel" id="profile_phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password (leave blank to keep current)</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password">
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <ion-icon name="save-outline"></ion-icon>
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/hide sections
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
            
            // Show selected section
            const targetSection = document.getElementById(section + '-section');
            if (targetSection) {
                targetSection.style.display = 'block';
            }
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            event.target.closest('.nav-link').classList.add('active');
            
            return false;
        }

        // Calculate total amount for booking
        function calculateTotal() {
            const roomSelect = document.getElementById('room');
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            
            if (roomSelect.value && checkIn && checkOut) {
                const price = parseFloat(roomSelect.selectedOptions[0].dataset.price);
                const days = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
                
                if (days > 0) {
                    const total = price * days;
                    document.getElementById('total-amount').textContent = 'NRS' + total.toFixed(2);
                    document.getElementById('total_amount').value = total;
                } else {
                    document.getElementById('total-amount').textContent = 'NRS0';
                    document.getElementById('total_amount').value = 0;
                }
            }
        }

        // Event listeners for booking form
        document.getElementById('room')?.addEventListener('change', calculateTotal);
        document.getElementById('check_in')?.addEventListener('change', calculateTotal);
        document.getElementById('check_out')?.addEventListener('change', calculateTotal);

        // Set min dates for date inputs
        const today = new Date().toISOString().split('T')[0];
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        if (checkInInput) {
            checkInInput.min = today;
            checkInInput.addEventListener('change', function() {
                if (checkOutInput) {
                    checkOutInput.min = this.value;
                    calculateTotal();
                }
            });
        }

        // Cancel booking function
        // Simple version of cancel booking function
function cancelBooking(bookingId) {
    if (confirm('Are you sure you want to cancel this booking?\n\nThis action cannot be undone.')) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<span>Processing...</span>';
        button.disabled = true;
        
        fetch('cancel-booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'message success-message';
                successMsg.innerHTML = `
                    <span>Booking cancelled successfully!</span>
                    <button class="close-message" onclick="this.parentElement.remove()">&times;</button>
                `;
                document.querySelector('.main-content').prepend(successMsg);
                
                // Auto-remove success message after 3 seconds
                setTimeout(() => {
                    if (successMsg.parentElement) {
                        successMsg.remove();
                    }
                }, 3000);
                
                // Reload the page after 1 second to show updated status
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Error: ' + data.message);
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profile-form');
    
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
           
            const name = document.getElementById('profile_name');
            const phone = document.getElementById('profile_phone');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_new_password');
            
            clearErrors();
            
            let isValid = true;
            
            if (name.value.trim().length < 2) {
                showError(name, 'Name must be at least 2 characters long');
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(name.value.trim())) {
                showError(name, 'Name can only contain letters and spaces');
                isValid = false;
            }
            
            if (phone.value.trim() !== '') {
                const phoneDigits = phone.value.replace(/\D/g, '');
                if (phoneDigits.length < 10 || phoneDigits.length > 15) {
                    showError(phone, 'Please enter a valid phone number (10-15 digits)');
                    isValid = false;
                }
            }
            
            // Validate Password (if provided)
            if (newPassword.value.trim() !== '') {
                // Check password length
                if (newPassword.value.length < 6) {
                    showError(newPassword, 'Password must be at least 6 characters long');
                    isValid = false;
                }
                
                if (newPassword.value !== confirmPassword.value) {
                    showError(confirmPassword, 'Passwords do not match');
                    isValid = false;
                }
            }
            
            if (isValid) {
                // Optional: Show success message
                showSuccessMessage('Profile updated successfully!');
                
                 this.submit();
                
                // console.log('Form is valid! Submitting...');
            }
        });
    }
    
    // Helper function to show error message
    function showError(inputElement, message) {
        // Remove any existing error for this input
        const existingError = inputElement.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error class to input
        inputElement.classList.add('error-input');
        
        // Create and insert error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        
        inputElement.parentNode.appendChild(errorDiv);
    }
    
    // Helper function to clear all errors
    function clearErrors() {
        // Remove all error messages
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        // Remove error class from all inputs
        document.querySelectorAll('.error-input').forEach(el => {
            el.classList.remove('error-input');
        });
    }
    
    // Helper function to show success message
    function showSuccessMessage(message) {
        // Remove any existing success message
        const existingSuccess = document.querySelector('.success-message');
        if (existingSuccess) {
            existingSuccess.remove();
        }
        
        // Create success message
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.style.backgroundColor = '#d4edda';
        successDiv.style.color = '#155724';
        successDiv.style.padding = '10px';
        successDiv.style.borderRadius = '4px';
        successDiv.style.marginBottom = '15px';
        successDiv.style.textAlign = 'center';
        successDiv.textContent = message;
        
        // Insert at top of form
        profileForm.insertBefore(successDiv, profileForm.firstChild);
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (successDiv) {
                successDiv.remove();
            }
        }, 3000);
    }
    
    // Add real-time validation for better UX
    const phoneInput = document.getElementById('profile_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Auto-format phone number (optional)
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                // You can add formatting logic here if needed
            }
        });
    }
    
    // Real-time password match validation
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_new_password');
    
    if (passwordInput && confirmInput) {
        function checkPasswordMatch() {
            if (confirmInput.value.trim() !== '' && passwordInput.value !== confirmInput.value) {
                confirmInput.classList.add('error-input');
            } else {
                confirmInput.classList.remove('error-input');
            }
        }
        
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
});
    </script>
</body>
</html>