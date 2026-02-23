<?php 
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

$company_name = $_SESSION['company_name'];
$today = date('Y-m-d');

// --- ข้อมูลจำลอง (Mock Data) สำหรับการแสดงผล ---
$mock_customers = [
    ['time' => '10:30', 'name' => 'คุณสมชาย สายสร้างบ้าน', 'phone' => '081-234-5678', 'detail' => 'Modern Luxury A1', 'location' => 'กรุงเทพฯ', 'status' => 'Hot Lead', 'amount' => 12500000, 'bg' => 'danger'],
    ['time' => '11:15', 'name' => 'คุณวิภาดา รักดี', 'phone' => '092-888-9999', 'detail' => 'Minimal M2', 'location' => 'เชียงใหม่', 'status' => 'สนใจพิเศษ', 'amount' => 5800000, 'bg' => 'warning text-dark'],
    ['time' => '13:45', 'name' => 'คุณธนา ตั้งใจเรียน', 'phone' => '085-444-3322', 'detail' => 'Tropical T1', 'location' => 'ระยอง', 'status' => 'ทั่วไป', 'amount' => 8200000, 'bg' => 'light text-muted']
];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root { --hba-navy: #003366; --hba-blue: #0056b3; --hba-light: #f8fafd; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--hba-light); color: #333; }
        .font-prompt { font-family: 'Prompt', sans-serif; }
        
        /* Layout Styling */
        .stat-card { background: white; border-radius: 25px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; height: 100%; }
        .filter-box { background: white; border-radius: 20px; padding: 20px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        
        /* Chart Sizing */
        .chart-main { position: relative; height: 300px; width: 100%; }
        .chart-sub { position: relative; height: 220px; width: 100%; }

        /* Table Styling */
        .table thead th { background-color: #f8f9fa; color: #888; font-weight: 600; font-size: 0.8rem; border: none; padding: 15px; }
        .badge { font-weight: 400; padding: 6px 12px; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    
    <div class="filter-box animate__animated animate__fadeIn">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <h4 class="font-prompt fw-bold text-navy mb-0">แผงควบคุมสถิติภาพรวม</h4>
                <p class="text-muted small mb-0">ข้อมูลประจำวันที่ <?php echo date('d/m/Y'); ?></p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <div class="d-flex gap-2 justify-content-md-end">
                    <input type="date" id="filterDate" class="form-control rounded-pill border-light shadow-sm w-auto" value="<?php echo $today; ?>">
                    <button class="btn btn-primary rounded-pill px-4 font-prompt" style="background: var(--hba-navy)">ดูข้อมูล</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 mb-2">
            <div class="stat-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-users text-primary me-2"></i>จำนวนผู้เข้าชมงานโดยรวม (Day 1 - Day 5)</h6>
                <div class="chart-main">
                    <canvas id="totalEventChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="stat-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-walking text-info me-2"></i>กิจกรรมในบูธ (รายชั่วโมง)</h6>
                <div class="chart-sub">
                    <canvas id="boothActivityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="stat-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-chart-pie text-warning me-2"></i>ยอดจองแยกตามแบบบ้าน</h6>
                <div class="chart-sub text-center">
                    <canvas id="salesValueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 mt-2">
            <div class="stat-card">
                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="font-prompt fw-bold m-0 text-navy"><i class="fas fa-id-card-alt me-2"></i>ข้อมูลผู้สนใจล่าสุด</h6>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control bg-light border-0 font-prompt" 
                                   placeholder="ค้นหาชื่อ, เบอร์โทร, หรือจังหวัด..." onkeyup="filterTable()">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="customerTable">
                        <thead>
                            <tr>
                                <th>ข้อมูลลูกค้า</th>
                                <th>ความสนใจ / ทำเล</th>
                                <th>สถานะ</th>
                                <th class="text-end">ยอดจอง</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="font-prompt">
                            <?php foreach($mock_customers as $cust): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold visitor-name"><?php echo $cust['name']; ?></div>
                                    <small class="text-muted visitor-phone"><i class="fas fa-phone-alt me-1"></i><?php echo $cust['phone']; ?></small>
                                </td>
                                <td>
                                    <div class="small">แบบบ้าน: <span class="fw-bold text-navy"><?php echo $cust['detail']; ?></span></div>
                                    <small class="text-muted visitor-location"><i class="fas fa-map-marker-alt me-1"></i><?php echo $cust['location']; ?></small>
                                </td>
                                <td><span class="badge rounded-pill bg-<?php echo $cust['bg']; ?>"><?php echo $cust['status']; ?></span></td>
                                <td class="text-end">
                                    <div class="fw-bold text-success"><?php echo number_format($cust['amount']); ?></div>
                                    <small class="text-muted"><?php echo $cust['time']; ?> น.</small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ระบบค้นหาในตาราง
function filterTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("customerTable");
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let textContent = tr[i].textContent.toLowerCase();
        tr[i].style.display = textContent.includes(filter) ? "" : "none";
    }
}

// ตั้งค่ากราฟ
Chart.defaults.font.family = "'Sarabun', sans-serif";

// 1. กราฟผู้เข้าชมงานรวม
new Chart(document.getElementById('totalEventChart'), {
    type: 'bar',
    data: {
        labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
        datasets: [{
            label: 'จำนวนคน',
            data: [12500, 18200, 14500, 22000, 9500],
            backgroundColor: '#003366',
            borderRadius: 10
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

// 2. กราฟกิจกรรมบูธ
new Chart(document.getElementById('boothActivityChart'), {
    type: 'line',
    data: {
        labels: ['10:00', '12:00', '14:00', '16:00', '18:00'],
        datasets: [{
            data: [20, 55, 35, 70, 40],
            borderColor: '#00a8ff',
            backgroundColor: 'rgba(0, 168, 255, 0.05)',
            fill: true,
            tension: 0.4 
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

// 3. กราฟยอดขาย
new Chart(document.getElementById('salesValueChart'), {
    type: 'doughnut',
    data: {
        labels: ['Luxury', 'Minimal', 'Classic'],
        datasets: [{
            data: [15, 8.5, 4.2],
            backgroundColor: ['#003366', '#00a8ff', '#f39c12'],
            borderWidth: 0
        }]
    },
    options: { maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>