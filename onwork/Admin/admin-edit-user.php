<?php
session_start();
require_once '../db_connection.php';
require_once 'session_helper.php';

requireAdminLogin();

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id || $user_id < 1) {
    $_SESSION['error'] = 'Invalid user ID.';
    header('Location: admin-users.php');
    exit();
}

$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: admin-users.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error in admin-edit-user.php: " . $e->getMessage());
    $error = 'Failed to load user data. Please try again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $status = $_POST['status'] ?? 'active';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif (!empty($new_password) && !preg_match('/^(?=.*[A-Za-z])(?=.*\d)/', $new_password)) {
        $error = 'Password must contain at least one letter and one number';
    } else {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_stmt->execute([$email, $user_id]);
            
            if ($check_stmt->fetch()) {
                $error = 'Email already exists';
            } else {
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, phone = ?, address = ?, status = ?, password = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $update_stmt->execute([$name, $email, $phone, $address, $status, $hashed_password, $user_id]);
                } else {
                    $update_stmt = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, phone = ?, address = ?, status = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $update_stmt->execute([$name, $email, $phone, $address, $status, $user_id]);
                }
                
                $success = 'User updated successfully!';
                
                // Refresh user data
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            $error = 'Update failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Singh Niwas</title>
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

        .btn-success {
            background: var(--success);
            color: white;
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

        .password-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .form-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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

        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
            border: 1px solid #e9ecef;
        }

        .user-info h4 {
            margin-bottom: 15px;
            color: var(--text-dark);
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .user-info p {
            margin-bottom: 8px;
            color: #495057;
        }

        .user-info strong {
            color: var(--text-dark);
            min-width: 120px;
            display: inline-block;
        }

        .required:after {
            content: " *";
            color: var(--danger);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
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
            <h1>Edit User: <?php echo htmlspecialchars($user['name']); ?></h1>
            <div>
                <a href="admin-users.php" class="btn btn-secondary">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Users
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
            <form method="POST" id="editUserForm">
                <div class="form-group">
                    <label class="required">Full Name</label>
                    <input type="text" name="name" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" 
                           required
                           pattern="[A-Za-z\s\.\-]+"
                           title="Only letters, spaces, dots, and hyphens allowed">
                </div>
                
                <div class="form-group">
                    <label class="required">Email Address</label>
                    <input type="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           pattern="[0-9+\-\s]+"
                           title="Only numbers, plus, hyphen, and spaces allowed">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Account Status</label>
                    <select name="status">
                        <option value="active" <?php echo ($user['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($user['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <div class="form-note">Inactive users cannot log in or make new bookings</div>
                </div>
                
                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" id="new_password">
                    <div class="password-note">Minimum 6 characters with at least one letter and one number</div>
                    <div id="passwordStrength" style="font-size: 12px; margin-top: 5px;"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <ion-icon name="save-outline"></ion-icon>
                        Update User
                    </button>
                    <a href="admin-users.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
            
            <div class="user-info">
                <h4>User Information</h4>
                <p><strong>User ID:</strong> #<?php echo htmlspecialchars($user['id']); ?></p>
                <p><strong>Current Status:</strong> 
                    <span class="status-badge status-<?php echo $user['status'] ?? 'active'; ?>">
                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                    </span>
                </p>
                <p><strong>Joined Date:</strong> <?php echo date('F d, Y H:i', strtotime($user['created_at'])); ?></p>
                <p><strong>Last Updated:</strong> <?php echo date('F d, Y H:i'); ?></p>
                
                <?php if (isset($user['last_login'])): ?>
                <p><strong>Last Login:</strong> <?php echo date('F d, Y H:i', strtotime($user['last_login'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function checkPasswordStrength(password) {
            const strengthElement = document.getElementById('passwordStrength');
            
            if (!password) {
                strengthElement.textContent = '';
                return;
            }
            
            let strength = 0;
            let message = '';
            let color = '';
            
            if (password.length >= 6) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                message = 'Weak password';
                color = '#dc3545';
            } else if (strength === 3) {
                message = 'Fair password';
                color = '#ffc107';
            } else if (strength === 4) {
                message = 'Good password';
                color = '#28a745';
            } else {
                message = 'Strong password';
                color = '#20c997';
            }
            
            strengthElement.textContent = message;
            strengthElement.style.color = color;
        }
        
        document.getElementById('new_password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
     
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters');
                return false;
            }
            
            if (newPassword && !/(?=.*[A-Za-z])(?=.*\d)/.test(newPassword)) {
                e.preventDefault();
                alert('Password must contain at least one letter and one number');
                return false;
            }
        });
    </script>
</body>
</html>