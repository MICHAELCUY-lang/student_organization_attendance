<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/dashboard.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        setFlashMessage('login_error', 'Please enter both username and password', 'danger');
    } else {
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlashMessage('flash_message', 'Welcome back, ' . $user['username'], 'success');
                redirect(BASE_URL . '/dashboard.php');
            } else {
                setFlashMessage('login_error', 'Invalid password. Please try again.', 'danger');
            }
        } else {
            setFlashMessage('login_error', 'User not found. Please check your username.', 'danger');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-container">
                    <div class="text-center">
                        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" class="login-logo">
                        <h4 class="login-title mb-4"><?= APP_NAME ?></h4>
                    </div>
                    
                    <?php 
                        displayFlashMessage('login_error');
                        displayFlashMessage('login_required');
                        displayFlashMessage('logout_success');
                    ?>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="text-center mb-4">Sign In</h5>
                            
                            <form class="login-form" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
                                <div class="form-group mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" 
                                            placeholder="Enter your username" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="password" class="form-label">Password</label>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                            placeholder="Enter your password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-login">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="login-footer mt-4">
                        <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?> | All Rights Reserved</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>