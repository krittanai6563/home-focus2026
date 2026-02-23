<?php
session_start();
require '../config/db_config.php';

if (isset($_SESSION['exhibitor_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM exhibitors WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['exhibitor_id'] = $user['id'];
        $_SESSION['company_name'] = $user['company_name'];
        $_SESSION['role'] = $user['role']; 
        $_SESSION['profile_img'] = $user['profile_img'] ?: 'default-profile.png';
        
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "ขออภัย ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Home Focus 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #002347;
            --primary-blue: #0056b3;
            --accent-blue: #00a8ff;
            --soft-bg: #f0f4f8;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background: radial-gradient(circle at center, #003366 0%, #001a33 100%);
            height: 100vh; 
            display: flex; 
            align-items: center; 
            overflow: hidden;
        }

        /* Glassmorphism Effect */
        .login-card { 
            border: none; 
            border-radius: 30px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: all 0.4s ease;
        }

        .card-header { 
            background: transparent; 
            border: none; 
            padding-top: 50px; 
            text-align: center; 
        }

        .brand-text { 
            font-family: 'Prompt', sans-serif; 
            font-weight: 600; 
            color: var(--primary-dark); 
            font-size: 28px; 
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .form-label {
            font-size: 0.9rem;
            color: var(--primary-dark);
            font-weight: 600;
            margin-left: 5px;
        }

        .form-control { 
            border-radius: 15px; 
            padding: 12px 15px 12px 45px; 
            background: var(--soft-bg);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--accent-blue);
            box-shadow: 0 0 15px rgba(0, 168, 255, 0.2);
        }

        .input-group-icon { 
            position: absolute; 
            left: 18px; 
            top: 42px; 
            color: var(--primary-blue); 
            z-index: 10; 
            font-size: 1.1rem;
        }

        .btn-primary { 
            background: linear-gradient(45deg, var(--primary-dark), var(--primary-blue));
            border: none; 
            border-radius: 15px; 
            padding: 14px; 
            font-weight: 600; 
            font-family: 'Prompt', sans-serif;
            font-size: 1.1rem;
            box-shadow: 0 8px 20px rgba(0, 35, 71, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover { 
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 35, 71, 0.4);
            filter: brightness(1.2);
        }

        .alert-custom {
            border-radius: 12px;
            font-size: 0.85rem;
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        /* Decorative Elements */
        .bg-circles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        .circle {
            position: absolute;
            background: rgba(0, 168, 255, 0.05);
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="bg-circles">
    <div class="circle" style="width: 400px; height: 400px; top: -100px; left: -100px;"></div>
    <div class="circle" style="width: 300px; height: 300px; bottom: -50px; right: -50px;"></div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4 px-4">
            <div class="card login-card">
                <div class="card-header">
                    <div class="brand-text">HOME FOCUS 2026</div>
                    <div class="mt-1">
                        <span class="badge rounded-pill bg-primary px-3 py-2" style="font-size: 0.7rem; font-weight: 300;">EXHIBITOR PORTAL</span>
                    </div>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <?php if($error): ?>
                        <div class="alert alert-custom py-2 mb-4 animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3 position-relative">
                            <label class="form-label">Username</label>
                            <i class="fas fa-user input-group-icon"></i>
                            <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้งาน" required autofocus>
                        </div>
                        <div class="mb-4 position-relative">
                            <label class="form-label">Password</label>
                            <i class="fas fa-lock input-group-icon"></i>
                            <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            เข้าสู่ระบบ <i class="fas fa-sign-in-alt ms-2"></i>
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-5">
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">
                        &copy; 2026 Home Builder Association (HBA)<br>
                        <span style="opacity: 0.7;">Secure Management System</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>