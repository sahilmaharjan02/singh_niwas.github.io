
<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-dashboard.php');
} else {
    header('Location: admin-login.php');
}
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --background-color: #f8f9fa;
            --text-dark: #002960;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .welcome-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            min-height: 600px;
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, rgba(24, 145, 209, 0.1), rgba(29, 100, 194, 0.1));
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel {
            flex: 1;
            background: var(--white);
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .logo-text h1 {
            font-size: 28px;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .logo-text p {
            color: #666;
            font-size: 14px;
        }

        .welcome-title {
            font-size: 36px;
            color: var(--text-dark);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .welcome-title span {
            color: var(--primary-color);
        }

        .features {
            margin: 40px 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .feature-item:hover {
            transform: translateX(10px);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .feature-text h4 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .feature-text p {
            color: #666;
            font-size: 14px;
        }

        .login-form {
            margin-top: 30px;
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

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            background: var(--primary-dark);
        }

        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 14px;
        }

        .quick-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .quick-link {
            padding: 10px 20px;
            background: var(--background-color);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .quick-link:hover {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 992px) {
            .welcome-card {
                flex-direction: column;
            }
            
            .left-panel, .right-panel {
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .welcome-title {
                font-size: 28px;
            }
            
            .left-panel, .right-panel {
                padding: 20px;
            }
            
            .feature-item {
                padding: 12px;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="container">
        <div class="welcome-card">
            <!-- Left Panel - Welcome Message -->
            <div class="left-panel">
                <div class="logo">
                    <div class="logo-icon">
                        <ion-icon name="shield-checkmark-outline"></ion-icon>
                    </div>
                    <div class="logo-text">
                        <h1>Singh Niwas</h1>
                        <p>Administration Portal</p>
                    </div>
                </div>

                <h2 class="welcome-title">
                    Welcome to <span>Admin Panel</span>
                </h2>
                <p style="color: #666; line-height: 1.6; margin-bottom: 30px;">
                    Manage your hotel operations efficiently with our comprehensive admin dashboard. 
                    Monitor bookings, manage rooms, and oversee customer data securely.
                </p>

                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                        <div class="feature-text">
                            <h4>User Management</h4>
                            <p>View and manage all registered users</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <ion-icon name="bed-outline"></ion-icon>
                        </div>
                        <div class="feature-text">
                            <h4>Room Management</h4>
                            <p>Add, edit, and manage room inventory</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <div class="feature-text">
                            <h4>Booking Management</h4>
                            <p>Monitor and manage all reservations</p>
                        </div>
                    </div>
                </div>

                <div class="security-notice">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <strong>Secure Access:</strong> This portal is protected. Please use your admin credentials to login.
                </div>
            </div>

            
            <div class="right-panel">
                <h2 style="color: var(--text-dark); margin-bottom: 30px; font-size: 24px;">
                    Administrator Login
                </h2>

                <?php if (isset($_SESSION['login_error'])): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
                        <?php echo htmlspecialchars($_SESSION['login_error']); ?>
                        <?php unset($_SESSION['login_error']); ?>
                    </div>
                <?php endif; ?>

                <form action="admin-login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter your username or email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>

                    <button type="submit" class="login-btn">
                        <ion-icon name="log-in-outline"></ion-icon>
                        Login to Dashboard
                    </button>
                </form>

                <div class="quick-links">
                    <a href="../index.php" class="quick-link">
                        <ion-icon name="home-outline"></ion-icon>
                        Back to Home
                    </a>
                    <a href="../login.php" class="quick-link">
                        <ion-icon name="person-outline"></ion-icon>
                        User Login
                    </a>
                    <a href="mailto:admin@singhniwas.com" class="quick-link">
                        <ion-icon name="help-circle-outline"></ion-icon>
                        Need Help?
                    </a>
                </div>

                <p style="color: #666; font-size: 12px; margin-top: 30px; text-align: center;">
                    &copy; <?php echo date('Y'); ?> Singh Niwas. All rights reserved.<br>
                    <span style="color: #999;">v1.0.0</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>