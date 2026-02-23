<?php 
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

$association_name = "สมาคมธุรกิจรับสร้างบ้าน (HBA)";
$today = date('d/m/Y');

// --- Mock Data ---
$mock_companies = ['บริษัท A สร้างบ้าน', 'บริษัท B โฮมดีไซน์', 'บริษัท C ลักชูรี่โฮม', 'บริษัท D ก่อสร้างไทย'];
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
        :root {
            --hba-navy: #002366;       /* น้ำเงินเข้ม */
            --hba-blue: #0056b3;       /* ฟ้าเข้ม */
            --hba-azure: #00a8ff;      /* ฟ้าสว่าง */
            --hba-bg: #f0f4f8;         /* พื้นหลังฟ้าอ่อนมากๆ */
            --hba-white: #ffffff;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--hba-bg);
            color: var(--hba-navy);
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }

        /* ระบบ Navbar โทนน้ำเงินไล่เฉด */
        .admin-nav {
            background: linear-gradient(90deg, var(--hba-navy) 0%, var(--hba-blue) 100%);
            padding: 12px 0;
            box-shadow: 0 4px 20px rgba(0, 35, 102, 0.15);
        }

        /* Stat Card ดีไซน์ใหม่ เน้นขอบสีฟ้า */
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

        /* Chart & Container */
        .chart-container-card {
            background: var(--hba-white);
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }
        .chart-wrapper { position: relative; height: 300px; width: 100%; }
        
        /* Table Style */
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
            padding: 8px 25px;
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
            <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">ออกจากระบบ</a>
        </div>
    </div>
</nav>

<div class="container py-4 pb-5">
    
    <div class="row align-items-center mb-4 animate__animated animate__fadeIn">
        <div class="col-md-6">
            <h4 class="font-prompt fw-bold text-navy mb-1">แดชบอร์ดสมาคมภาพรวม</h4>
            <p class="text-muted small mb-0"><i class="fas fa-calendar-check me-1"></i> ประจำวันที่ <?php echo $today; ?></p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-2">
                <select class="form-select rounded-pill border-0 shadow-sm font-prompt" id="boothFilter">
                    <option value="all">แสดงทุกบูธในงาน</option>
                    <?php foreach($mock_companies as $c): ?>
                        <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-navy-pill shadow-sm"><i class="fas fa-filter"></i></button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 animate__animated animate__fadeInUp">
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">ผู้เข้าชมงานทั้งหมด</div>
                <div class="stat-value">45,200</div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar" style="width: 85%; background-color: var(--hba-azure);"></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">จำนวนการสแกนบูธ</div>
                <div class="stat-value text-info">12,850</div>
                <div class="small text-muted">เฉลี่ย 1.5 ครั้ง/คน</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">ยอดขายรวม (ล้าน)</div>
                <div class="stat-value" style="color: #2e7d32;">1,240</div>
                <div class="small text-muted">เป้าหมาย: 1,500M</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-4">
                <div class="stat-label">คูปองที่ใช้แล้ว</div>
                <div class="stat-value text-primary">842</div>
                <div class="small text-muted">รวมส่วนลด 8.42M</div>
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

    <div class="chart-container-card mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-trophy me-2 text-warning"></i>อันดับยอดขายและความนิยมรายบริษัท (Exhibitor Ranking)</h6>
        <div class="chart-wrapper" style="height: 350px;">
            <canvas id="associationCompareChart"></canvas>
        </div>
    </div>

    <div class="table-custom-container animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-prompt fw-bold m-0 text-navy"><i class="fas fa-database me-2"></i>ประวัติกิจกรรมลูกค้าทั้งหมด</h6>
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
                    <tr>
                        <td class="text-muted">23/02/26 14:20</td>
                        <td><strong>คุณสมชาย สายสร้างบ้าน</strong><br><small>081-xxx-xxxx</small></td>
                        <td><span class="badge badge-azure rounded-pill">บริษัท A สร้างบ้าน</span></td>
                        <td>จองโครงการ (คูปอง 10,000)</td>
                        <td class="text-end fw-bold">12,500,000</td>
                    </tr>
                    <tr>
                        <td class="text-muted">23/02/26 14:45</td>
                        <td><strong>คุณวิภาดา รักดี</strong><br><small>092-xxx-xxxx</small></td>
                        <td><span class="badge badge-azure rounded-pill">บริษัท B โฮมดีไซน์</span></td>
                        <td>สแกนร่วมกิจกรรมลุ้นรางวัล</td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
Chart.defaults.font.family = "'Sarabun', sans-serif";
Chart.defaults.color = '#5f7d95';

// 1. กราฟ Peak Time (ใช้สีฟ้าไล่เฉด)
new Chart(document.getElementById('associationPeakChart'), {
    type: 'line',
    data: {
        labels: ['10:00', '12:00', '14:00', '16:00', '18:00', '20:00'],
        datasets: [{
            label: 'ผู้เข้าชมรวม (คน)',
            data: [1200, 3500, 4800, 6200, 4100, 1500],
            borderColor: '#00a8ff',
            backgroundColor: 'rgba(0, 168, 255, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#00a8ff'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

// 2. กราฟโซน (โทนน้ำเงิน-ฟ้า)
new Chart(document.getElementById('associationZoneChart'), {
    type: 'doughnut',
    data: {
        labels: ['โซน A', 'โซน B', 'โซน C'],
        datasets: [{
            data: [60, 25, 15],
            backgroundColor: ['#002366', '#0056b3', '#00a8ff'],
            borderWidth: 0
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom' } } }
});

// 3. กราฟเปรียบเทียบ (Bar Chart)
new Chart(document.getElementById('associationCompareChart'), {
    type: 'bar',
    data: {
        labels: ['บริษัท A', 'บริษัท B', 'บริษัท C', 'บริษัท D', 'บริษัท E'],
        datasets: [
            {
                label: 'ยอดจอง (ล้านบาท)',
                data: [245, 190, 150, 120, 80],
                backgroundColor: '#002366',
                borderRadius: 8
            },
            {
                label: 'ยอดสแกนบูธ (ครั้ง)',
                data: [450, 320, 280, 510, 190],
                backgroundColor: '#00a8ff',
                borderRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>