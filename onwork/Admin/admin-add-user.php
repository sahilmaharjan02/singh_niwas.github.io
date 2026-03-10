<?php
session_start();
require_once '../db_connection.php';
require_once 'session_helper.php';

requireAdminLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        if ($check_stmt->fetch()) {
            $error = 'Email already exists';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, address, status) VALUES (?, ?, ?, ?, ?, ?)");
                $insert_stmt->execute([$name, $email, $phone, $hashed_password, $address, $status]);
                
                $success = 'User added successfully!';
                $_POST = []; 
            } catch (Exception $e) {
                $error = 'Failed to add user: ' . $e->getMessage();
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
    <title>Add New User - Singh Niwas</title>
    <style>
        /* Add your existing styles here */
        .form-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Add New User</h1>
            <div>
                <a href="admin-users.php" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Users
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6">
                    <div class="password-note">Minimum 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Account Status</label>
                    <select name="status">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="form-note">Inactive users cannot log in or make new bookings</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <ion-icon name="person-add-outline"></ion-icon>
                        Add User
                    </button>
                    <a href="admin-users.php" class="btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>