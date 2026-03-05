<?php 
// 1. ตรวจสอบสิทธิ์และการเชื่อมต่อฐานข้อมูล
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

$exhibitor_id = $_SESSION['exhibitor_id'];
$company_name = $_SESSION['company_name'];
$today = date('Y-m-d');

// รับค่าฟิลเตอร์วันที่จาก URL (ถ้าไม่มีให้เป็นค่าว่างเพื่อดึงทั้งหมด)
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

try {
    // --- [สรุปตัวเลข 3 การ์ดด้านบน] ---
    if ($filter_date) {
        // 1. ผู้ร่วมกิจกรรม (จำนวนคนที่ไม่ซ้ำกัน)
        $stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT visitor_id) FROM booth_visits WHERE exhibitor_id = ? AND DATE(visit_time) = ?");
        $stmtCount->execute([$exhibitor_id, $filter_date]);
        $total_participants = $stmtCount->fetchColumn() ?: 0;

        // 2. ยอดขายรวมสุทธิ
        $stmtSum = $pdo->prepare("SELECT SUM(net_value) FROM transactions WHERE exhibitor_id = ? AND DATE(created_at) = ?");
        $stmtSum->execute([$exhibitor_id, $filter_date]);
        $total_sales_raw = $stmtSum->fetchColumn() ?: 0;

        // 3. จำนวนผู้เข้าชมบูธ (จำนวนครั้งที่มีการสแกนทั้งหมด)
        $stmtVisits = $pdo->prepare("SELECT COUNT(*) FROM booth_visits WHERE exhibitor_id = ? AND DATE(visit_time) = ?");
        $stmtVisits->execute([$exhibitor_id, $filter_date]);
        $total_visits = $stmtVisits->fetchColumn() ?: 0;

    } else {
        $stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT visitor_id) FROM booth_visits WHERE exhibitor_id = ?");
        $stmtCount->execute([$exhibitor_id]);
        $total_participants = $stmtCount->fetchColumn() ?: 0;

        $stmtSum = $pdo->prepare("SELECT SUM(net_value) FROM transactions WHERE exhibitor_id = ?");
        $stmtSum->execute([$exhibitor_id]);
        $total_sales_raw = $stmtSum->fetchColumn() ?: 0;

        $stmtVisits = $pdo->prepare("SELECT COUNT(*) FROM booth_visits WHERE exhibitor_id = ?");
        $stmtVisits->execute([$exhibitor_id]);
        $total_visits = $stmtVisits->fetchColumn() ?: 0;
    }

    // --- [ข้อมูลกราฟ: คงเดิม ดึงภาพรวม] ---
    $stmt = $pdo->prepare("SELECT DATE(visit_time) as visit_date, COUNT(*) as visitor_count FROM booth_visits WHERE exhibitor_id = ? GROUP BY DATE(visit_time) ORDER BY visit_date DESC LIMIT 5");
    $stmt->execute([$exhibitor_id]);
    $daily_stats = array_reverse($stmt->fetchAll());
    $labels_daily = []; $data_daily = [];
    foreach($daily_stats as $row) { $labels_daily[] = date('d M', strtotime($row['visit_date'])); $data_daily[] = $row['visitor_count']; }

    $stmt = $pdo->prepare("SELECT HOUR(visit_time) as visit_hour, COUNT(*) as visitor_count FROM booth_visits WHERE exhibitor_id = ? AND DATE(visit_time) = ? GROUP BY HOUR(visit_time) ORDER BY visit_hour ASC");
    $stmt->execute([$exhibitor_id, date('Y-m-d')]);
    $hourly_raw = $stmt->fetchAll();
    $labels_hourly = ['10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
    $data_hourly = [0, 0, 0, 0, 0, 0]; 
    foreach($hourly_raw as $row) {
        if($row['visit_hour'] >= 10 && $row['visit_hour'] <= 20) {
            $idx = floor(($row['visit_hour'] - 10) / 2);
            if(isset($data_hourly[$idx])) $data_hourly[$idx] += $row['visitor_count'];
        }
    }

    $stmt = $pdo->prepare("SELECT item_detail, SUM(net_value) as total_value FROM transactions WHERE exhibitor_id = ? GROUP BY item_detail ORDER BY total_value DESC LIMIT 5");
    $stmt->execute([$exhibitor_id]);
    $sales_stats = $stmt->fetchAll();
    $labels_sales = []; $data_sales = [];
    foreach($sales_stats as $row) { $labels_sales[] = $row['item_detail'] ?: 'ไม่ระบุแบบบ้าน'; $data_sales[] = $row['total_value'] / 1000000; }

    // --- [ข้อมูลตารางลูกค้า: ดึงข้อมูลตามฟิลเตอร์วันที่] ---
    if ($filter_date) {
        $stmt = $pdo->prepare("
            SELECT v.full_name, v.phone, v.email, v.target_region, v.budget_range, v.usable_area, v.floor_count, v.visit_purpose, v.decision_time, t.item_detail, t.total_value, t.discount_amount, t.net_value, b.visit_time
            FROM booth_visits b
            JOIN visitors v ON b.visitor_id = v.id
            LEFT JOIN transactions t ON (t.visitor_id = v.id AND t.exhibitor_id = b.exhibitor_id)
            WHERE b.exhibitor_id = ? AND DATE(b.visit_time) = ?
            ORDER BY b.visit_time DESC
        ");
        $stmt->execute([$exhibitor_id, $filter_date]);
    } else {
        $stmt = $pdo->prepare("
            SELECT v.full_name, v.phone, v.email, v.target_region, v.budget_range, v.usable_area, v.floor_count, v.visit_purpose, v.decision_time, t.item_detail, t.total_value, t.discount_amount, t.net_value, b.visit_time
            FROM booth_visits b
            JOIN visitors v ON b.visitor_id = v.id
            LEFT JOIN transactions t ON (t.visitor_id = v.id AND t.exhibitor_id = b.exhibitor_id)
            WHERE b.exhibitor_id = ?
            ORDER BY b.visit_time DESC
        ");
        $stmt->execute([$exhibitor_id]);
    }
    $recent_visitors = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - <?php echo htmlspecialchars($company_name); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --hba-navy: #003366; --hba-blue: #0056b3; --hba-light: #f8fafd; --hba-sky: #00a8ff; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--hba-light); color: #333; }
        .font-prompt { font-family: 'Prompt', sans-serif; }
        .stat-card { background: white; border-radius: 25px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; height: 100%; }
        .chart-main { position: relative; height: 300px; width: 100%; }
        .chart-sub { position: relative; height: 220px; width: 100%; }
        
        table.dataTable thead th { background-color: #f8f9fa; color: #888; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #eee; padding: 15px 10px; }
        .dataTables_wrapper .dataTables_filter input { border-radius: 50px; padding: 5px 15px; border: 1px solid #ddd; }
        .dt-buttons .btn { border-radius: 50px; margin-right: 5px; font-family: 'Prompt', sans-serif; font-size: 0.85rem; padding: 5px 15px; }
        
        .modal-content { border-radius: 25px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        .modal-header { background: var(--hba-navy); color: white; border-radius: 25px 25px 0 0; padding: 20px 30px; }
        .detail-label { font-size: 0.75rem; color: #888; text-transform: uppercase; margin-bottom: 2px; }
        .detail-value { font-family: 'Prompt', sans-serif; font-weight: 600; color: var(--hba-navy); margin-bottom: 15px; }

        .summary-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border-left: 5px solid var(--hba-navy); }
        .summary-card.gold { border-left-color: #f39c12; }
        .summary-card.sky { border-left-color: var(--hba-sky); }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    
    <div class="stat-card mb-4 pb-3" style="height: auto;">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h4 class="font-prompt fw-bold text-navy mb-0"><i class="fas fa-chart-line me-2"></i>รายงานภาพรวม</h4>
                <p class="text-muted small mb-0 mt-1">
                    <?php echo $filter_date ? "ข้อมูลประจำวันที่: " . date('d/m/Y', strtotime($filter_date)) : "ข้อมูลสรุปรวมทั้งหมด"; ?>
                </p>
            </div>
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex justify-content-md-end">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="max-width: 320px;">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="date" name="filter_date" class="form-control border-0 bg-white" value="<?php echo htmlspecialchars($filter_date); ?>">
                        <button type="submit" class="btn btn-primary px-4 font-prompt" style="background: var(--hba-navy)">กรอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 col-12">
            <div class="summary-card d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-prompt text-muted mb-1 small">จํานวนผู้ร่วมกิจกรรม (ลีด)</h6>
                    <h3 class="font-prompt fw-bold text-navy mb-0"><?php echo number_format($total_participants); ?> <span class="fs-6 fw-normal">ราย</span></h3>
                </div>
                <div class="bg-light rounded-circle p-3 text-primary d-none d-sm-block"><i class="fas fa-users fs-4"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="summary-card gold d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-prompt text-muted mb-1 small">ยอดขายรวมสุทธิ</h6>
                    <h3 class="font-prompt fw-bold mb-0 text-success"><?php echo number_format($total_sales_raw); ?> <span class="fs-6 fw-normal text-muted">บาท</span></h3>
                </div>
                <div class="bg-light rounded-circle p-3 text-warning d-none d-sm-block"><i class="fas fa-hand-holding-usd fs-4"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="summary-card sky d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-prompt text-muted mb-1 small">จำนวนผู้เข้าชมบูธ (สแกนรวม)</h6>
                    <h3 class="font-prompt fw-bold mb-0" style="color: var(--hba-sky);"><?php echo number_format($total_visits); ?> <span class="fs-6 fw-normal text-muted">ครั้ง</span></h3>
                </div>
                <div class="bg-light rounded-circle p-3 d-none d-sm-block" style="color: var(--hba-sky);"><i class="fas fa-walking fs-4"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="stat-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-users text-primary me-2"></i>จำนวนผู้เข้าชมบูธ (ย้อนหลัง 5 วัน)</h6>
                <div class="chart-main"><canvas id="totalEventChart"></canvas></div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="stat-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-walking text-info me-2"></i>ความหนาแน่นรายชั่วโมง (วันนี้)</h6>
                <div class="chart-sub"><canvas id="boothActivityChart"></canvas></div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="stat-card">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-chart-pie text-warning me-2"></i>ยอดจองสะสมแยกตามแบบบ้าน (ล้านบาท)</h6>
                <div class="chart-sub"><canvas id="salesValueChart"></canvas></div>
            </div>
        </div>

        <div class="col-12">
            <div class="stat-card" style="height: auto;">
                <h6 class="font-prompt fw-bold mb-4 text-navy"><i class="fas fa-list-alt me-2"></i>รายชื่อข้อมูลลูกค้า</h6>
                <div class="table-responsive">
                    <table id="customerTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>เวลาที่แวะชม</th>
                                <th>ข้อมูลลูกค้า</th>
                                <th>เบอร์ติดต่อ</th>
                                <th>ความสนใจ / ทำเล</th>
                                <th>งบประมาณ / พื้นที่</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-end">ยอดจองสุทธิ</th>
                                <th class="text-center" data-orderable="false">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="font-prompt">
                            <?php foreach($recent_visitors as $row): ?>
                            <tr>
                                <td class="text-muted small"><?php echo date('Y/m/d H:i', strtotime($row['visit_time'])); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                    <div class="small text-muted" style="font-size: 0.7rem;"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td class="small text-muted"><i class="fas fa-phone-alt me-1"></i><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td>
                                    <div class="small text-navy fw-bold"><?php echo $row['item_detail'] ?: 'เยี่ยมชมทั่วไป'; ?></div>
                                    <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($row['target_region']); ?></div>
                                </td>
                                <td>
                                    <div class="small">งบ: <span class="text-primary fw-bold"><?php echo $row['budget_range'] ?: '-'; ?></span></div>
                                    <div class="small text-muted">พื้นที่: <?php echo $row['usable_area'] ?: '-'; ?> (<?php echo $row['floor_count'] ?: '-'; ?> ชั้น)</div>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        if (!empty($row['net_value']) && $row['net_value'] != 0) echo '<span class="badge rounded-pill bg-success">Closed</span>';
                                        elseif (strpos($row['budget_range'], '10') !== false || strpos($row['budget_range'], '20') !== false) echo '<span class="badge rounded-pill bg-danger">Hot</span>';
                                        else echo '<span class="badge rounded-pill bg-light text-muted border">General</span>';
                                    ?>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold <?php echo (!empty($row['net_value'])) ? 'text-success' : 'text-muted'; ?>">
                                        <?php echo (!empty($row['net_value'])) ? number_format($row['net_value']) : '0'; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" 
                                            onclick='showCustomerDetail(<?php echo json_encode($row); ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
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

<div class="modal fade" id="customerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-prompt fw-bold"><i class="fas fa-user-circle me-2"></i>รายละเอียดลูกค้า</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-6"><div class="detail-label">ชื่อ-นามสกุล</div><div id="m-name" class="detail-value">-</div></div>
                    <div class="col-6"><div class="detail-label">เบอร์โทรศัพท์</div><div id="m-phone" class="detail-value">-</div></div>
                    <div class="col-12"><div class="detail-label">อีเมล</div><div id="m-email" class="detail-value">-</div></div>
                    <hr class="my-1 opacity-10">
                    <div class="col-6"><div class="detail-label">งบประมาณปลูกสร้าง</div><div id="m-budget" class="detail-value text-primary">-</div></div>
                    <div class="col-6"><div class="detail-label">ทำเลที่สนใจ</div><div id="m-region" class="detail-value">-</div></div>
                    <div class="col-6"><div class="detail-label">พื้นที่ใช้สอย</div><div id="m-area" class="detail-value">-</div></div>
                    <div class="col-6"><div class="detail-label">จำนวนชั้น</div><div id="m-floor" class="detail-value">-</div></div>
                    <div class="col-12"><div class="detail-label">ระยะเวลาตัดสินใจ</div><div id="m-decision" class="detail-value">-</div></div>
                    <div class="col-12 bg-light p-3 rounded-4 mt-2">
                        <div class="detail-label text-success fw-bold">รายการจองล่าสุด</div>
                        <div id="m-item" class="detail-value mb-1">-</div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">ยอดจองสุทธิ:</small>
                            <span class="fw-bold text-navy"><span id="m-net">0</span> บาท</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                <a id="m-link" href="#" class="btn btn-primary rounded-pill px-4" style="background: var(--hba-navy)"><i class="fas fa-edit me-2"></i>เปิดหน้าสแกน</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/NathakonO/pdfmake-thai-font@master/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    // กำหนดฟอนต์ภาษาไทยให้ PDFMake
    pdfMake.fonts = {
        THSarabun: {
            normal: 'THSarabun.ttf',
            bold: 'THSarabun-Bold.ttf',
            italics: 'THSarabun-Italic.ttf',
            bolditalics: 'THSarabun-BoldItalic.ttf'
        }
    };

    $('#customerTable').DataTable({
        dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            { 
                extend: 'excelHtml5', 
                text: '<i class="fas fa-file-excel"></i> ดาวน์โหลด Excel', 
                className: 'btn btn-outline-success',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6],
                    format: {
                        body: function (data, row, column, node) {
                            // จัดฟอร์แมตเอาโค้ด HTML ออก เพื่อให้อ่านง่ายใน Excel
                            var text = data.replace(/<br\s*[\/]?>/gi, " | ").replace(/<\/div>/gi, " | ").replace(/<[^>]+>/g, "");
                            return text.replace(/\s*\|\s*\|\s*/g, " | ").replace(/^\s*\|\s*|\s*\|\s*$/g, "").trim();
                        }
                    }
                }
            },
            { 
                extend: 'pdfHtml5', 
                text: '<i class="fas fa-file-pdf"></i> พิมพ์ PDF', 
                className: 'btn btn-outline-danger',
                exportOptions: { 
                    columns: [0, 1, 2, 3, 4, 5, 6],
                    format: {
                        body: function (data, row, column, node) {
                            // นำแท็ก HTML ออกและขึ้นบรรทัดใหม่แทนใน PDF
                            return data.replace(/<br\s*[\/]?>/gi, "\n").replace(/<\/div>/gi, "\n").replace(/<[^>]+>/g, "").trim();
                        }
                    }
                },
                customize: function (doc) {
                    // บังคับใช้ฟอนต์ภาษาไทยและปรับขนาดคอลัมน์
                    doc.defaultStyle = { font: 'THSarabun', fontSize: 13 };
                    doc.styles.tableHeader = { font: 'THSarabun', fontSize: 14, bold: true, alignment: 'center' };
                    doc.content[1].table.widths = ['10%', '20%', '15%', '15%', '15%', '10%', '15%'];
                }
            }
        ],
        language: {
            search: "ค้นหา (ชื่อ, เบอร์, ทำเล):",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดงลูกค้าคนที่ _START_ ถึง _END_ (ทั้งหมด _TOTAL_ ราย)",
            infoEmpty: "ไม่มีข้อมูล",
            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
            paginate: { first: "แรก", last: "สุดท้าย", next: "ถัดไป", previous: "ก่อนหน้า" }
        },
        order: [[0, 'desc']],
        pageLength: 10
    });
});

function showCustomerDetail(data) {
    document.getElementById('m-name').innerText = data.full_name || '-';
    document.getElementById('m-phone').innerText = data.phone || '-';
    document.getElementById('m-email').innerText = data.email || '-';
    document.getElementById('m-budget').innerText = data.budget_range || '-';
    document.getElementById('m-region').innerText = data.target_region || '-';
    document.getElementById('m-area').innerText = data.usable_area || '-';
    document.getElementById('m-floor').innerText = (data.floor_count) ? data.floor_count + ' ชั้น' : '-';
    document.getElementById('m-decision').innerText = data.decision_time || '-';
    document.getElementById('m-item').innerText = data.item_detail || 'ยังไม่เปิดออเดอร์';
    document.getElementById('m-net').innerText = data.net_value ? Number(data.net_value).toLocaleString() : '0';
    document.getElementById('m-link').href = 'scan_visitor.php?phone=' + data.phone;
    new bootstrap.Modal(document.getElementById('customerDetailModal')).show();
}

Chart.defaults.font.family = "'Sarabun', sans-serif";
new Chart(document.getElementById('totalEventChart'), { type: 'bar', data: { labels: <?php echo json_encode($labels_daily); ?>, datasets: [{ label: 'จำนวนคน', data: <?php echo json_encode($data_daily); ?>, backgroundColor: '#003366', borderRadius: 8 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } } } });
new Chart(document.getElementById('boothActivityChart'), { type: 'line', data: { labels: ['10:00', '12:00', '14:00', '16:00', '18:00', '20:00'], datasets: [{ data: <?php echo json_encode($data_hourly); ?>, borderColor: '#00a8ff', backgroundColor: 'rgba(0, 168, 255, 0.05)', fill: true, tension: 0.4 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } } } });
new Chart(document.getElementById('salesValueChart'), { type: 'doughnut', data: { labels: <?php echo json_encode($labels_sales); ?>, datasets: [{ data: <?php echo json_encode($data_sales); ?>, backgroundColor: ['#003366', '#00a8ff', '#f39c12', '#2ecc71', '#9b59b6'], borderWidth: 0 }] }, options: { maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom' } } } });
</script>

</body>
</html>