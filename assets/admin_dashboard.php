<?php 
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

$association_name = "สมาคมธุรกิจรับสร้างบ้าน (HBA)";

try {
    // ==========================================
    // 0. จัดการตัวแปร Filter วันที่และบูธ
    // ==========================================
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $selected_booth = $_GET['booth'] ?? 'all';

    // Base Where
    $where_visitors = "1=1";
    $where_visits = "1=1";
    $where_transactions = "1=1";
    $params = []; // เก็บแค่ Parameter ของวันที่

    $display_date_text = "ข้อมูลทั้งหมด (All Time)";

    // กรองวันที่
    if (!empty($start_date) && !empty($end_date)) {
        $where_visitors = "DATE(registered_at) BETWEEN :start_date AND :end_date";
        $where_visits = "DATE(visit_time) BETWEEN :start_date AND :end_date";
        $where_transactions = "DATE(created_at) BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
        $display_date_text = ($start_date === $end_date) 
            ? "ประจำวันที่ " . date('d/m/Y', strtotime($start_date)) 
            : "ระหว่างวันที่ " . date('d/m/Y', strtotime($start_date)) . " ถึง " . date('d/m/Y', strtotime($end_date));
    } elseif (!empty($start_date)) {
        $where_visitors = "DATE(registered_at) >= :start_date";
        $where_visits = "DATE(visit_time) >= :start_date";
        $where_transactions = "DATE(created_at) >= :start_date";
        $params[':start_date'] = $start_date;
        $display_date_text = "ตั้งแต่วันที่ " . date('d/m/Y', strtotime($start_date));
    } elseif (!empty($end_date)) {
        $where_visitors = "DATE(registered_at) <= :end_date";
        $where_visits = "DATE(visit_time) <= :end_date";
        $where_transactions = "DATE(created_at) <= :end_date";
        $params[':end_date'] = $end_date;
        $display_date_text = "ถึงวันที่ " . date('d/m/Y', strtotime($end_date));
    }

    // ==========================================
    // สร้างเงื่อนไขสำหรับ "เลือกบริษัท/บูธ"
    // ==========================================
    $params_booth = $params; // คัดลอก params วันที่มาใช้ต่อ
    $booth_filter_sql = "";
    
    if ($selected_booth !== 'all') {
        $booth_filter_sql = " AND e.company_name = :booth_name";
        $params_booth[':booth_name'] = $selected_booth;
    }

    // นามแฝง (Alias) เพื่อใช้ตอน JOIN ตารางป้องกันความสับสนของฟิลด์
    $where_visits_alias = str_replace('visit_time', 'bv.visit_time', $where_visits);
    $where_trans_alias = str_replace('created_at', 't.created_at', $where_transactions);

    // ==========================================
    // 1. ดึงข้อมูลสถิติภาพรวม (Stat Cards)
    // ==========================================
    
    // 1.1 ผู้เข้าชมงานรวม (อิงตามวันที่อย่างเดียว ไม่ถูกหักลบด้วยชื่อบูธ เพราะเป็นภาพรวมคนเข้างาน)
    $stmt_vis = $pdo->prepare("SELECT COUNT(*) FROM visitors WHERE $where_visitors");
    $stmt_vis->execute($params);
    $total_visitors = $stmt_vis->fetchColumn();

    // 1.2 จำนวนการสแกน (อิงตามวันที่ + กรองตามบูธที่เลือก)
    $stmt_scan = $pdo->prepare("
        SELECT COUNT(*) 
        FROM booth_visits bv 
        LEFT JOIN exhibitors e ON bv.exhibitor_id = e.id 
        WHERE $where_visits_alias $booth_filter_sql
    ");
    $stmt_scan->execute($params_booth);
    $total_scans = $stmt_scan->fetchColumn();

    // 1.3 ยอดขายรวม และคูปองที่ใช้ (อิงตามวันที่ + กรองตามบูธที่เลือก)
    $stmt_trans = $pdo->prepare("
        SELECT SUM(t.net_value) as total_sales, COUNT(*) as total_coupons 
        FROM transactions t 
        LEFT JOIN exhibitors e ON t.exhibitor_id = e.id 
        WHERE $where_trans_alias $booth_filter_sql
    ");
    $stmt_trans->execute($params_booth);
    $trans_data = $stmt_trans->fetch();
    
    $total_sales_raw = $trans_data['total_sales'] ?: 0;
    $total_sales_million = $total_sales_raw / 1000000;
    $total_coupons_used = $trans_data['total_coupons'] ?: 0;

    // ==========================================
    // 2. ดึงรายชื่อบริษัท (สำหรับ Dropdown Filter)
    // ==========================================
    // เพิ่มเงื่อนไข WHERE role = 'exhibitor' เพื่อไม่ให้ดึงชื่อของ admin มาแสดงใน Dropdown
    $companies_stmt = $pdo->query("SELECT DISTINCT company_name FROM exhibitors WHERE role = 'exhibitor' AND company_name IS NOT NULL AND company_name != '' ORDER BY company_name ASC");
    $companies = $companies_stmt->fetchAll();

    // ==========================================
    // 3. ดึงประวัติกิจกรรมล่าสุด (Recent Activities)
    // ==========================================
    $recent_sql = "
        SELECT 
            t.created_at as activity_time, 
            v.full_name as visitor_name, 
            v.phone, 
            e.company_name, 
            t.net_value 
        FROM transactions t
        LEFT JOIN visitors v ON t.visitor_id = v.id
        LEFT JOIN exhibitors e ON t.exhibitor_id = e.id
        WHERE $where_trans_alias $booth_filter_sql
        ORDER BY t.created_at DESC
        LIMIT 20
    ";
    $recent_stmt = $pdo->prepare($recent_sql);
    $recent_stmt->execute($params_booth);
    $recent_activities = $recent_stmt->fetchAll();

    // ==========================================
    // 4. ดึงข้อมูลสำหรับสร้างกราฟ (Chart Data)
    // ==========================================
    
    // กราฟ 1: ความหนาแน่นตามช่วงเวลา (กรองตามบูธได้)
    $peak_sql = "
        SELECT HOUR(bv.visit_time) as visit_hour, COUNT(*) as total 
        FROM booth_visits bv
        LEFT JOIN exhibitors e ON bv.exhibitor_id = e.id
        WHERE $where_visits_alias $booth_filter_sql
        GROUP BY HOUR(bv.visit_time) 
        ORDER BY visit_hour
    ";
    $peak_stmt = $pdo->prepare($peak_sql);
    $peak_stmt->execute($params_booth);
    $peak_results = $peak_stmt->fetchAll(PDO::FETCH_ASSOC);

    $hours_data = array_fill(9, 12, 0); 
    foreach($peak_results as $row) {
        $h = (int)$row['visit_hour'];
        if(isset($hours_data[$h])) {
            $hours_data[$h] = $row['total'];
        }
    }
    $labels_peak = []; $data_peak = [];
    foreach($hours_data as $h => $total) {
        $labels_peak[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        $data_peak[] = $total;
    }

    // กราฟ 2: สัดส่วนยอดจองรายบริษัท (Top 5)
    // *หมายเหตุ: กราฟวงกลมนี้จะใช้แค่ Filter วันที่ เพื่อให้แสดงภาพรวมเปรียบเทียบบริษัท Top 5 เสมอ
    $zone_sql = "
        SELECT COALESCE(e.company_name, 'ไม่ระบุบริษัท') as chart_label, SUM(t.net_value) as total_sales 
        FROM transactions t
        LEFT JOIN exhibitors e ON t.exhibitor_id = e.id
        WHERE $where_trans_alias
        GROUP BY e.company_name
        ORDER BY total_sales DESC
        LIMIT 5
    ";
    $zone_stmt = $pdo->prepare($zone_sql);
    $zone_stmt->execute($params); // ใช้ตัวแปร $params ที่มีแค่วันที่
    $zone_results = $zone_stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels_zone = []; $data_zone = [];
    foreach($zone_results as $row) {
        $labels_zone[] = $row['chart_label'];
        $data_zone[] = (float)$row['total_sales'];
    }

} catch (PDOException $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
    $total_visitors = 0; $total_scans = 0; $total_sales_million = 0; $total_coupons_used = 0;
    $companies = []; $recent_activities = [];
    $labels_peak = []; $data_peak = [];
    $labels_zone = []; $data_zone = [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HBA Command Center - Association Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* CSS คงเดิมไว้ทั้งหมด */
        :root {
            --hba-navy: #002366;
            --hba-blue: #0056b3;
            --hba-azure: #00a8ff;
            --hba-bg: #f0f4f8;
            --hba-white: #ffffff;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--hba-bg);
            color: var(--hba-navy);
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }

        .admin-nav {
            background: linear-gradient(90deg, var(--hba-navy) 0%, var(--hba-blue) 100%);
            padding: 12px 0;
            box-shadow: 0 4px 20px rgba(0, 35, 102, 0.15);
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            background: var(--hba-white);
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            border-top: 5px solid var(--hba-azure);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0, 51, 102, 0.1); }
        .stat-label { font-size: 0.85rem; color: #5f7d95; font-weight: 600; text-transform: uppercase; }
        .stat-value { font-family: 'Prompt'; font-weight: 600; font-size: 2rem; color: var(--hba-navy); }

        .chart-container-card {
            background: var(--hba-white);
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }
        .chart-wrapper { position: relative; height: 300px; width: 100%; }
        
        .table-custom-container {
            background: var(--hba-white);
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }
        .table thead th {
            background-color: #f8fbff;
            color: var(--hba-blue);
            font-weight: 600;
            border-bottom: 2px solid #eef2f7;
        }

        .btn-navy-pill {
            background-color: var(--hba-navy);
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            transition: 0.3s;
        }
        .btn-navy-pill:hover { background-color: var(--hba-blue); color: white; }
        .badge-azure { background-color: #e1f5fe; color: var(--hba-blue); border: 1px solid #b3e5fc; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark admin-nav sticky-top">
    <div class="container">
        <div class="navbar-brand font-prompt fw-bold d-flex align-items-center">
            <i class="fas fa-landmark me-2"></i> HBA MANAGEMENT CENTER
        </div>
        <div class="d-flex align-items-center">
            <div class="text-white me-3 d-none d-md-block small">ยินดีต้อนรับ, <span class="fw-bold">Administrator</span></div>
            <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">ออกจากระบบ</a>
        </div>
    </div>
</nav>

<div class="container py-4 pb-5">
    
    <form method="GET" class="row align-items-center mb-4 animate__animated animate__fadeIn">
        <div class="col-xl-4 col-lg-3 mb-3 mb-lg-0">
            <h4 class="font-prompt fw-bold text-navy mb-1">แดชบอร์ดสมาคม</h4>
            <p class="text-muted small mb-0"><i class="fas fa-calendar-check me-1"></i> <?php echo $display_date_text; ?></p>
        </div>
        <div class="col-xl-8 col-lg-9">
            <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                <div class="input-group input-group-sm shadow-sm" style="width: auto; border-radius: 50px; overflow: hidden; background: #fff;">
                    <span class="input-group-text border-0 bg-transparent text-muted"><i class="fas fa-calendar-alt"></i></span>
                    <input type="date" name="start_date" class="form-control border-0 font-prompt" value="<?php echo htmlspecialchars($start_date); ?>">
                    <span class="input-group-text border-0 bg-transparent text-muted px-1">-</span>
                    <input type="date" name="end_date" class="form-control border-0 font-prompt" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <select class="form-select form-select-sm rounded-pill border-0 shadow-sm font-prompt" name="booth" style="width: auto; min-width: 150px;">
                    <option value="all">แสดงทุกบูธในงาน</option>
                    <?php foreach($companies as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['company_name']); ?>" <?php echo ($selected_booth === $c['company_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['company_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-navy-pill shadow-sm"><i class="fas fa-search me-1"></i> กรองข้อมูล</button>
                <a href="admin_dashboard.php" class="btn btn-sm btn-light rounded-pill shadow-sm text-muted"><i class="fas fa-redo"></i></a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4 animate__animated animate__fadeInUp">
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">ผู้เข้าชมตามช่วงเวลาที่เลือก</div>
                <div class="stat-value"><?php echo number_format($total_visitors); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">การสแกนบูธ (ครั้ง)</div>
                <div class="stat-value text-info"><?php echo number_format($total_scans); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">ยอดขายรวม (ล้านบาท)</div>
                <div class="stat-value" style="color: #2e7d32;"><?php echo number_format($total_sales_million, 2); ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">คูปองที่ใช้ (รายการ)</div>
                <div class="stat-value text-primary"><?php echo number_format($total_coupons_used); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
        <div class="col-lg-8">
            <div class="chart-container-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-chart-line me-2 text-azure"></i>ความหนาแน่นผู้เข้าชมตามช่วงเวลา</h6>
                <div class="chart-wrapper">
                    <canvas id="associationPeakChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy text-center">สัดส่วนยอดจองรายโซน</h6>
                <div class="chart-wrapper">
                    <canvas id="associationZoneChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="table-custom-container animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-prompt fw-bold m-0 text-navy"><i class="fas fa-database me-2"></i>ประวัติกิจกรรมลูกค้า (กรองตามวันที่)</h6>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 font-prompt">
                <i class="fas fa-download me-1"></i> Export Excel
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="small text-uppercase">
                        <th>วัน/เวลา</th>
                        <th>ลูกค้า</th>
                        <th>บูธที่ติดต่อ</th>
                        <th>ประเภทกิจกรรม</th>
                        <th class="text-end">มูลค่าจอง (บาท)</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if(count($recent_activities) > 0): ?>
                        <?php foreach($recent_activities as $act): ?>
                        <tr>
                            <td class="text-muted"><?php echo date('d/m/y H:i', strtotime($act['activity_time'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($act['visitor_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($act['phone']); ?></small>
                            </td>
                            <td><span class="badge badge-azure rounded-pill"><?php echo htmlspecialchars($act['company_name'] ?? 'ไม่มีระบุ'); ?></span></td>
                            <td>จองโครงการ</td>
                            <td class="text-end fw-bold text-success"><?php echo number_format($act['net_value'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลในช่วงเวลาที่เลือก</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
Chart.defaults.font.family = "'Sarabun', sans-serif";
Chart.defaults.color = '#5f7d95';

// ข้อมูลสำหรับกราฟ Peak Time
const peakLabelsJS = <?php echo json_encode($labels_peak); ?>;
const peakDataJS = <?php echo json_encode($data_peak); ?>;

// ข้อมูลสำหรับกราฟโซน/บริษัท
const zoneLabelsJS = <?php echo json_encode($labels_zone); ?>;
const zoneDataJS = <?php echo json_encode($data_zone); ?>;

// ชุดสีสำหรับกราฟโดนัท (เผื่อมีหลายโซน)
const donutColors = ['#002366', '#0056b3', '#00a8ff', '#4db8ff', '#99d6ff', '#e6f5ff'];

// 1. วาดกราฟความหนาแน่นผู้เข้าชม
new Chart(document.getElementById('associationPeakChart'), {
    type: 'line',
    data: {
        labels: peakLabelsJS,
        datasets: [{
            label: 'ผู้เข้าชมรวม (คน)',
            data: peakDataJS,
            borderColor: '#00a8ff',
            backgroundColor: 'rgba(0, 168, 255, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#00a8ff'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

// 2. วาดกราฟสัดส่วนยอดจอง
new Chart(document.getElementById('associationZoneChart'), {
    type: 'doughnut',
    data: {
        labels: zoneLabelsJS.length > 0 ? zoneLabelsJS : ['ไม่มีข้อมูล'],
        datasets: [{
            data: zoneDataJS.length > 0 ? zoneDataJS : [1],
            backgroundColor: zoneDataJS.length > 0 ? donutColors : ['#e0e0e0'],
            borderWidth: 0
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        cutout: '75%', 
        plugins: { 
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        if (zoneDataJS.length === 0) return 'ไม่มีข้อมูล';
                        // แสดงตัวเลขมีคอมม่าขั้น (บาท)
                        return label + ': ' + new Intl.NumberFormat('th-TH').format(value) + ' บาท';
                    }
                }
            }
        } 
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>