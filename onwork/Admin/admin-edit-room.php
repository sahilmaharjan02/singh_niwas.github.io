<?php
session_start();
require_once '../db_connection.php';
require_once 'session_helper.php';

requireAdminLogin();

$room_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$room_id || $room_id < 1) {
    header('Location: admin-rooms.php');
    exit();
}

$error = '';
$success = '';

try {
   
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        header('Location: admin-rooms.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = 'Failed to load room data.';
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security token invalid. Please try again.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $room_number = filter_input(INPUT_POST, 'room_number', FILTER_SANITIZE_STRING);
        $room_type = filter_input(INPUT_POST, 'room_type', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $price_per_night = filter_input(INPUT_POST, 'price_per_night', FILTER_VALIDATE_FLOAT);
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
        $amenities = filter_input(INPUT_POST, 'amenities', FILTER_SANITIZE_STRING) ?? '';
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        
        if (empty($room_number) || empty($room_type) || !$price_per_night || !$capacity) {
            $error = 'Please fill all required fields';
        } elseif ($price_per_night <= 0) {
            $error = 'Price must be greater than 0';
        } elseif ($capacity <= 0 || $capacity > 10) {
            $error = 'Capacity must be between 1 and 10 persons';
        } else {
            try {
                
                $check_stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
                $check_stmt->execute([$room_number, $room_id]);
                
                if ($check_stmt->fetch()) {
                    $error = 'Room number already exists';
                } else {
                    
                    $columns = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'amenities'")->fetch();
                    
                    if ($columns) {
                        $update_stmt = $pdo->prepare("
                            UPDATE rooms SET 
                            room_number = ?, 
                            room_type = ?, 
                            description = ?, 
                            price_per_night = ?, 
                            capacity = ?, 
                            amenities = ?, 
                            status = ? 
                            WHERE id = ?
                        ");
                        $update_stmt->execute([
                            $room_number, 
                            $room_type, 
                            $description, 
                            $price_per_night, 
                            $capacity, 
                            $amenities, 
                            $status, 
                            $room_id
                        ]);
                    } else {
                        $update_stmt = $pdo->prepare("
                            UPDATE rooms SET 
                            room_number = ?, 
                            room_type = ?, 
                            description = ?, 
                            price_per_night = ?, 
                            capacity = ?, 
                            status = ? 
                            WHERE id = ?
                        ");
                        $update_stmt->execute([
                            $room_number, 
                            $room_type, 
                            $description, 
                            $price_per_night, 
                            $capacity, 
                            $status, 
                            $room_id
                        ]);
                    }
                    
                    $success = 'Room updated successfully!';
                    
                    $stmt->execute([$room_id]);
                    $room = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            } catch (PDOException $e) {
                error_log("Update error: " . $e->getMessage());
                $error = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --background-color: #f8f9fa;
            --text-dark: #002960;
            --white: #ffffff;
            --sidebar-width: 260px;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background-color);
            display: flex;
            min-height: 100vh;
            color: #333;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .header {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: var(--text-dark);
            font-size: 24px;
            margin: 0;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: var(--gray);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--text-dark);
        }

        .form-container {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(24, 145, 209, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .required:after {
            content: " *";
            color: var(--danger);
        }

        .form-note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Edit Room: <?php echo htmlspecialchars($room['room_number']); ?></h1>
            <div>
                <a href="admin-room-detail.php?id=<?php echo $room_id; ?>" class="btn btn-secondary">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Details
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <ion-icon name="alert-circle-outline"></ion-icon>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <ion-icon name="checkmark-circle-outline"></ion-icon>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" id="editRoomForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label class="required">Room Number</label>
                    <input type="text" name="room_number" 
                           value="<?php echo htmlspecialchars($room['room_number']); ?>" 
                           required
                           pattern="[A-Za-z0-9\s\-]+"
                           title="Only letters, numbers, spaces, and hyphens allowed">
                </div>
                
                <div class="form-group">
                    <label class="required">Room Type</label>
                    <select name="room_type" required>
                        <option value="">Select Type</option>
                        <option value="Standard Room" <?php echo $room['room_type'] == 'Standard Room' ? 'selected' : ''; ?>>Standard Room</option>
                        <option value="Deluxe Room" <?php echo $room['room_type'] == 'Deluxe Room' ? 'selected' : ''; ?>>Deluxe Room</option>
                        <option value="Executive Suite" <?php echo $room['room_type'] == 'Executive Suite' ? 'selected' : ''; ?>>Executive Suite</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="required">Price Per Night (NRS)</label>
                    <input type="number" name="price_per_night" step="0.01" min="0" 
                           value="<?php echo htmlspecialchars($room['price_per_night']); ?>" required>
                    <div class="form-note">Enter amount in Nepalese Rupees</div>
                </div>
                
                <div class="form-group">
                    <label class="required">Capacity (persons)</label>
                    <input type="number" name="capacity" min="1" max="10" 
                           value="<?php echo htmlspecialchars($room['capacity']); ?>" required>
                    <div class="form-note">Maximum 10 persons per room</div>
                </div>
                
                <?php 
                
                try {
                    $check_column = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'amenities'")->fetch();
                    if ($check_column):
                ?>
                <div class="form-group">
                    <label>Amenities</label>
                    <input type="text" name="amenities" 
                           value="<?php echo htmlspecialchars($room['amenities'] ?? ''); ?>" 
                           placeholder="WiFi, TV, AC, Mini-bar, Room Service">
                    <div class="form-note">Separate amenities with commas</div>
                </div>
                <?php 
                    endif;
                } catch (Exception $e) {
                    
                }
                ?>
                
                <div class="form-group">
                    <label class="required">Status</label>
                    <select name="status" required>
                        <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="occupied" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                        <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <ion-icon name="save-outline"></ion-icon>
                        Update Room
                    </button>
                    <a href="admin-room-detail.php?id=<?php echo $room_id; ?>" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('editRoomForm').addEventListener('submit', function(e) {
            const price = document.querySelector('input[name="price_per_night"]');
            const capacity = document.querySelector('input[name="capacity"]');
            
            if (parseFloat(price.value) <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                price.focus();
                return false;
            }
            
            if (parseInt(capacity.value) <= 0 || parseInt(capacity.value) > 10) {
                e.preventDefault();
                alert('Capacity must be between 1 and 10 persons');
                capacity.focus();
                return false;
            }
        });
    </script>
</body>
</html>