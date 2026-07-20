<?php
session_start();
require_once '../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verifikasi password (gunakan password_verify untuk hash)
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - FF Account Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a0000 0%, #2d0a0a 50%, #1a0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(20, 20, 20, 0.95);
            border-radius: 20px;
            padding: 50px;
            width: 100%;
            max-width: 420px;
            border: 2px solid #c9a44e;
            box-shadow: 0 20px 60px rgba(201, 164, 78, 0.2);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header i {
            font-size: 50px;
            color: #c9a44e;
            margin-bottom: 15px;
        }
        
        .login-header h1 {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
        }
        
        .login-header p {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #c9a44e;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group .input-group {
            position: relative;
        }
        
        .form-group .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: #2a2a2a;
            border: 2px solid #333;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #c9a44e;
            box-shadow: 0 0 20px rgba(201, 164, 78, 0.1);
        }
        
        .form-group input::placeholder {
            color: #555;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #c9a44e, #a8832e);
            border: none;
            border-radius: 10px;
            color: #1a0000;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201, 164, 78, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
            color: #ff4444;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 13px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-crown"></i>
            <h1>FF Account Store</h1>
            <p>Admin Panel Login</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Masukkan username" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="login-footer">
            <p>© 2026 FF Account Store - All Rights Reserved</p>
        </div>
    </div>
</body>
</html>