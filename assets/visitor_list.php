<?php
session_start();
require_once '../config/db_config.php';

// 1. ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: login.php");
    exit;
}

// 2. จัดการตัวแปรการค้นหา
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "1=1";
$params = [];

if (!empty($search)) {
    $where_clause = "(full_name LIKE :search OR phone LIKE :search_phone)";
    $params[':search'] = "%$search%";
    $params[':search_phone'] = "%$search%";
}

try {
    // 3. ดึงข้อมูลจากตาราง visitors
    $sql = "SELECT * FROM visitors WHERE $where_clause ORDER BY registered_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $visitors = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error in visitor_list.php: " . $e->getMessage());
    $visitors = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อผู้ลงทะเบียน - Home Focus 2026</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --theme-main: #185a3a; /* เขียว */
            --theme-light: #248255;
            --theme-bg: #f2f7f4;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--theme-bg);
            color: #1e293b;
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }

        .card-custom {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background-color: #f8fafc;
            color: var(--theme-main);
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            border-bottom: 2px solid #edf2f7;
            padding: 15px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-search {
            background-color: var(--theme-main);
            color: white;
            border-radius: 12px;
            padding: 10px 25px;
        }

        .btn-search:hover {
            background-color: var(--theme-light);
            color: white;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 600;
        }

        .badge-checked { background-color: #dcfce7; color: #166534; } /* เช็คอินแล้ว */
        .badge-pending { background-color: #f1f5f9; color: #64748b; } /* ยังไม่เช็คอิน */

        .visitor-info-main { font-weight: 600; color: #1e293b; }
        .visitor-info-sub { font-size: 0.8rem; color: #64748b; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    
    <div class="row align-items-center mb-4 animate__animated animate__fadeIn">
        <div class="col-md-6">
            <h4 class="font-prompt fw-bold text-navy mb-1"><i class="fas fa-users me-2 text-success"></i>รายชื่อผู้ลงทะเบียนเข้างาน</h4>
            <p class="text-muted small mb-0">แสดงข้อมูลลูกค้าและความสนใจด้านการสร้างบ้านทั้งหมด</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <form method="GET" class="d-flex gap-2 justify-content-md-end">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text border-0 bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 shadow-sm font-prompt" 
                           placeholder="ค้นหา ชื่อ / เบอร์โทร..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-search shadow-sm font-prompt">ค้นหา</button>
                <?php if(!empty($search)): ?>
                    <a href="visitor_list.php" class="btn btn-light rounded-pill border shadow-sm"><i class="fas fa-undo"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card card-custom animate__animated animate__fadeInUp">
        <div class="card-body p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ชื่อ-นามสกุล / เบอร์โทร</th>
                            <th>ความสนใจ</th>
                            <th>งบประมาณ</th>
                            <th>ทำเลที่ต้องการ</th>
                            <th class="text-center">สถานะเข้างาน</th>
                            <th class="text-end">ลงทะเบียนเมื่อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($visitors) > 0): ?>
                            <?php foreach($visitors as $v): ?>
                            <tr>
                                <td>
                                    <div class="visitor-info-main"><?php echo htmlspecialchars($v['full_name']); ?></div>
                                    <div class="visitor-info-sub"><i class="fas fa-phone-alt me-1"></i> <?php echo htmlspecialchars($v['phone']); ?></div>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark"><?php echo htmlspecialchars($v['floor_count'] ?? '-'); ?></div>
                                    <div class="visitor-info-sub"><?php echo htmlspecialchars($v['usable_area'] ?? '-'); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25" style="font-size: 0.8rem;">
                                        <?php echo htmlspecialchars($v['budget_range'] ?? 'ไม่ระบุ'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 150px;">
                                        <i class="fas fa-map-marker-alt text-danger me-1 small"></i>
                                        <?php echo htmlspecialchars($v['target_region'] ?? '-'); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($v['check_in_status'] == 1): ?>
                                        <span class="status-badge badge-checked"><i class="fas fa-check-circle me-1"></i> เช็คอินแล้ว</span>
                                    <?php else: ?>
                                        <span class="status-badge badge-pending">รอดำเนินการ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-muted small">
                                    <?php echo date('d/m/Y H:i', strtotime($v['registered_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i><br>
                                    ไม่พบข้อมูลผู้ลงทะเบียนตามที่ค้นหา
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 row">
        <div class="col-md-6">
            <p class="text-muted small">พบข้อมูลลูกค้าทั้งหมด <span class="fw-bold text-dark"><?php echo count($visitors); ?></span> รายการ</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-outline-success btn-sm rounded-pill px-3 font-prompt" onclick="window.print()">
                <i class="fas fa-file-excel me-1"></i> Export ข้อมูลลูกค้า
            </button>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>