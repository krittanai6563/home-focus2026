<?php
session_start();
require_once '../config/db_config.php';

// ตรวจสอบสิทธิ์ว่าใช่ superadmin จริงไหม
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

$company_name = $_SESSION['company_name'];
$profile_img = $_SESSION['profile_img'] ?: 'default-profile.png';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - Home Focus 2026</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --hba-dark: #001a33;
            --hba-navy: #003366;
            --hba-blue: #0056b3;
            --hba-sky: #00a8ff;
            --hba-light: #f8fafd;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--hba-light);
            color: var(--hba-dark);
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }

        /* การ์ดเมนูหลัก */
        .menu-card {
            border: none;
            border-radius: 25px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.05);
            transition: all 0.3s ease;
            text-decoration: none !important;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            border-bottom: 5px solid transparent;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 51, 102, 0.1);
        }

        /* แยกสีตามประเภทเมนู */
        .card-report:hover { border-color: var(--hba-blue); }
        .card-visitor:hover { border-color: #28a745; }
        .card-register:hover { border-color: #ffc107; }
        .card-users:hover { border-color: #dc3545; }

        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 32px;
            transition: all 0.3s ease;
        }

        .menu-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        .menu-title {
            color: var(--hba-navy);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .menu-desc {
            color: #64748b;
            font-size: 0.85rem;
            text-align: center;
        }

        /* ส่วนหัวของหน้า */
        .welcome-section {
            background: linear-gradient(135deg, var(--hba-dark) 0%, var(--hba-navy) 100%);
            border-radius: 30px;
            padding: 40px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 10px 25px rgba(0, 35, 71, 0.2);
        }
    </style>
</head>
<body>



<div class="container py-5">
    
    <div class="welcome-section animate__animated animate__fadeIn">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="font-prompt fw-bold mb-2">ยินดีต้อนรับสู่ระบบจัดการสูงสุด</h2>
                <p class="opacity-75 mb-0">สวัสดีคุณ <span class="fw-bold"><?php echo htmlspecialchars($company_name); ?></span> วันนี้คุณต้องการจัดการส่วนไหนของงาน Home Focus 2026?</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-info bg-opacity-25 text-white p-2 px-3 rounded-pill border border-info border-opacity-50">
                    <i class="fas fa-shield-alt me-2"></i> สิทธิ์เข้าถึงระดับ: Super Admin
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        
        <div class="col-6 col-lg-3">
            <a href="admin_dashboard.php" class="menu-card card-report">
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h5 class="menu-title font-prompt">รายงาน</h5>
                <p class="menu-desc">ดูสถิติผู้เข้าชมงาน<br>และสรุปยอดจองภาพรวม</p>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="visitor_list.php" class="menu-card card-visitor">
                <div class="icon-box bg-success bg-opacity-10 text-success">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h5 class="menu-title font-prompt">ข้อมูลลูกค้า</h5>
                <p class="menu-desc">ตรวจสอบรายชื่อผู้ลงทะเบียน<br>และจัดการข้อมูล Lead</p>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="register_walkin.php" class="menu-card card-register">
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h5 class="menu-title font-prompt">ฟอร์มลงทะเบียน</h5>
                <p class="menu-desc">บันทึกข้อมูลลูกค้า Walk-in<br>ที่หน้างานโดยตรง</p>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="add_user.php" class="menu-card card-users">
                <div class="icon-box bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-store-alt"></i>
                </div>
                <h5 class="menu-title font-prompt">เพิ่มสมาชิกบูธ</h5>
                <p class="menu-desc">จัดการบัญชีผู้ใช้งาน<br>และผู้ประกอบการออกบูธ</p>
            </a>
        </div>

    </div>

    <div class="mt-5 text-center text-muted small animate__animated animate__fadeIn">
        <p><i class="fas fa-info-circle me-1"></i> ระบบเชื่อมต่อฐานข้อมูล hba_expo_2026 พร้อมใช้งาน | เซิร์ฟเวอร์สถานะ: ปกติ</p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>