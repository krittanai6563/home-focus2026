<?php
session_start();
require '../config/db_config.php';
require '../includes/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {

    header("Location: dashboard.php");
    exit;
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ตรวจสอบว่ามี username นี้ในระบบหรือยัง
    $stmt = $pdo->prepare("SELECT id FROM exhibitors WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        $message = "ชื่อผู้ใช้งาน (Username) นี้มีอยู่ในระบบแล้ว กรุณาตั้งชื่ออื่น";
        $message_type = "danger";
    } else {
        // เข้ารหัสผ่านเพื่อความปลอดภัย
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // บันทึกลงฐานข้อมูล โดยกำหนด role เป็น 'exhibitor' อัตโนมัติ
        $insert = $pdo->prepare("INSERT INTO exhibitors (company_name, username, password_hash, role) VALUES (?, ?, ?, 'exhibitor')");
        
        if ($insert->execute([$company_name, $username, $password_hash])) {
            $message = "เพิ่มข้อมูลบูธ <b>{$company_name}</b> สำเร็จเรียบร้อยแล้ว!";
            $message_type = "success";
        } else {
            $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง";
            $message_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสมาชิกบูธ - Home Focus 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f4f7f6; 
        }
        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Prompt', sans-serif;
        }
        .form-card { 
            border-radius: 15px; 
            border: none; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(45deg, #002347, #0056b3);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5 shadow-sm">
    <div class="container">
        <a class="navbar-brand brand-font fw-bold" href="admin_dashboard.php">HOME FOCUS 2026</a>
        <div class="d-flex align-items-center">
            <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> กลับหน้าแรก</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card form-card">
                <div class="card-header text-white text-center py-4">
                    <h4 class="mb-0 brand-font"><i class="fas fa-user-plus me-2"></i> สร้างบัญชีสมาชิกบูธ</h4>
                    <p class="mb-0 mt-1" style="font-size: 0.85rem; opacity: 0.8;">สำหรับผู้จัดแสดงสินค้า (Exhibitor)</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php if($message_type == 'success') echo '<i class="fas fa-check-circle me-2"></i>'; else echo '<i class="fas fa-exclamation-triangle me-2"></i>'; ?>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label brand-font fw-bold text-secondary">ชื่อบริษัท / ชื่อบูธ</label>
                            <input type="text" name="company_name" class="form-control form-control-lg fs-6" placeholder="เช่น บริษัท รับสร้างบ้าน จำกัด" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label brand-font fw-bold text-secondary">Username (สำหรับเข้าสู่ระบบ)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="username" class="form-control form-control-lg fs-6" placeholder="ตั้งชื่อผู้ใช้ภาษาอังกฤษ" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label brand-font fw-bold text-secondary">Password (รหัสผ่าน)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control form-control-lg fs-6" placeholder="ตั้งรหัสผ่าน" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg brand-font fs-6 shadow-sm">
                                <i class="fas fa-save me-1"></i> บันทึกข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
