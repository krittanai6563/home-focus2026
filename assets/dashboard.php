<?php 
// 1. ตรวจสอบสิทธิ์และการเชื่อมต่อฐานข้อมูล
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

// 2. ดึงข้อมูลจาก Session ที่เก็บไว้ตอน Login
$exhibitor_id = $_SESSION['exhibitor_id'];
$company_name = $_SESSION['company_name'];
$profile_img  = $_SESSION['profile_img'] ?: 'default-profile.png';
$today        = date('Y-m-d');

try {
    // 3. ดึงสถิติจำนวนผู้เยี่ยมชมบูธวันนี้
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM booth_visits WHERE exhibitor_id = ? AND DATE(visit_time) = ?");
    $stmt->execute([$exhibitor_id, $today]);
    $visitor_count = $stmt->fetchColumn();

    // 4. ดึงสรุปยอดจองของบูธตนเอง (Net Value)
    $stmt = $pdo->prepare("SELECT SUM(net_value) FROM transactions WHERE exhibitor_id = ?");
    $stmt->execute([$exhibitor_id]);
    $total_sales = $stmt->fetchColumn() ?: 0;

} catch (PDOException $e) {
    // กรณีฐานข้อมูลขัดข้อง
    error_log($e->getMessage());
    $visitor_count = 0;
    $total_sales = 0;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($company_name); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
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

        /* Welcome Section */
        .welcome-section { padding: 30px 0 10px 0; }
        .welcome-title { font-family: 'Prompt', sans-serif; font-weight: 600; color: var(--hba-navy); }

        /* Stat Cards ดีไซน์ทันสมัย */
        .card-stat {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.05);
            transition: transform 0.3s ease;
        }
        .card-stat:hover { transform: translateY(-5px); }
        .stat-label { font-size: 0.85rem; color: #666; font-weight: 400; }
        .stat-value { font-family: 'Prompt', sans-serif; font-weight: 600; color: var(--hba-navy); }

        /* Action Buttons ขนาดใหญ่สำหรับมือถือ */
        .action-card {
            border: none;
            border-radius: 25px;
            overflow: hidden;
            position: relative;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .btn-lead { background: linear-gradient(45deg, var(--hba-navy), var(--hba-blue)); color: white; }
        .btn-redeem { background: linear-gradient(45deg, #f39c12, #e67e22); color: white; }

        .action-card:hover { transform: scale(1.03); color: white; filter: brightness(1.1); }
        .action-icon { font-size: 3.5rem; opacity: 0.2; position: absolute; right: -10px; bottom: -10px; }
        .action-content { position: relative; z-index: 2; text-align: center; }
        .action-title { font-family: 'Prompt', sans-serif; font-weight: 600; font-size: 1.3rem; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container pb-5">
    
    <div class="welcome-section animate__animated animate__fadeIn">
        <h4 class="welcome-title">แผงควบคุมระบบ</h4>
        <p class="text-muted small">สรุปข้อมูลงาน Home Focus 2026 วันที่ <?php echo date('d/m/Y'); ?></p>
    </div>

    <div class="row g-3 mb-4 animate__animated animate__fadeInUp">
        <div class="col-6">
            <div class="card card-stat">
                <div class="card-body py-4 text-center text-sm-start">
                    <div class="stat-label mb-1"><i class="fas fa-users text-info me-1"></i> ลีดสะสมวันนี้</div>
                    <div class="stat-value h2 mb-0"><?php echo number_format($visitor_count); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card card-stat">
                <div class="card-body py-4 text-center text-sm-start">
                    <div class="stat-label mb-1"><i class="fas fa-hand-holding-usd text-success me-1"></i> ยอดจอง (ล้าน)</div>
                    <div class="stat-value h2 mb-0"><?php echo number_format($total_sales / 1000000, 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        <div class="col-12 col-md-6">
            <a href="scan_visitor.php" class="action-card btn-lead">
                <i class="fas fa-qrcode action-icon"></i>
                <div class="action-content">
                    <div class="action-title">สแกนเก็บ Lead</div>
                    <div class="action-desc small opacity-75">บันทึกข้อมูลผู้สนใจ (QR สีฟ้า)</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="redeem_order.php" class="action-card btn-redeem">
                <i class="fas fa-ticket-alt action-icon"></i>
                <div class="action-content">
                    <div class="action-title">รับออเดอร์ / Redeem</div>
                    <div class="action-desc small opacity-75">ใช้คูปองส่วนลด (QR สีเหลือง)</div>
                </div>
            </a>
        </div>
        
        <div class="col-12 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div class="card card-stat">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h6 class="font-prompt mb-1">พบลูกค้า Walk-in?</h6>
                        <p class="text-muted small mb-0">ลงทะเบียนลูกค้าใหม่ที่ไม่มีคิวอาร์โค้ด</p>
                    </div>
                    <a href="register_walkin.php" class="btn btn-outline-primary rounded-pill px-4 font-prompt">
                        <i class="fas fa-user-plus me-1"></i> ลงทะเบียน
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 opacity-50">
        <p class="small">&copy; 2026 Home Builder Association System</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>