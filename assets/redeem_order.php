<?php 
// 1. ตรวจสอบสิทธิ์และการเชื่อมต่อฐานข้อมูล
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

$exhibitor_id = $_SESSION['exhibitor_id'];
$company_name = $_SESSION['company_name'];

// รับค่าฟิลเตอร์วันที่ ถ้าไม่มีการเลือกให้ใช้วันนี้
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');

try {
    // สรุปตัวเลขด้านบน (อิงตามวันที่เลือก)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM booth_visits WHERE exhibitor_id = ? AND DATE(visit_time) = ?");
    $stmtCount->execute([$exhibitor_id, $filter_date]);
    $total_participants = $stmtCount->fetchColumn() ?: 0;

    $stmtSum = $pdo->prepare("SELECT SUM(net_value) FROM transactions WHERE exhibitor_id = ? AND DATE(created_at) = ?"); // สมมติว่ามี created_at ในตาราง transactions
    // หาก transactions ไม่มี created_at ให้ลบเงื่อนไข AND DATE(...) ออกเพื่อดูยอดรวมทั้งหมดแทน
    $stmtSum->execute([$exhibitor_id, $filter_date]);
    $total_sales_raw = $stmtSum->fetchColumn() ?: 0;

    // ข้อมูลตารางผู้เข้าชม (กรองตามวันที่เลือก)
    $stmt = $pdo->prepare("
        SELECT 
            v.*, t.item_detail, t.net_value, b.visit_time
        FROM booth_visits b
        JOIN visitors v ON b.visitor_id = v.id
        LEFT JOIN transactions t ON (t.visitor_id = v.id AND t.exhibitor_id = b.exhibitor_id)
        WHERE b.exhibitor_id = ? AND DATE(b.visit_time) = ?
        ORDER BY b.visit_time DESC
    ");
    $stmt->execute([$exhibitor_id, $filter_date]);
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

    <style>
        :root { --hba-navy: #003366; --hba-blue: #0056b3; --hba-light: #f8fafd; --hba-sky: #00a8ff; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--hba-light); color: #333; }
        .font-prompt { font-family: 'Prompt', sans-serif; }
        .stat-card { background: white; border-radius: 25px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; height: 100%; }
        .summary-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border-left: 5px solid var(--hba-navy); }
        .summary-card.gold { border-left-color: #f39c12; }
        
        /* ปรับแต่งปุ่ม Export ของ DataTables */
        .dt-buttons .btn { border-radius: 50px; margin-right: 5px; font-family: 'Prompt', sans-serif; font-size: 0.85rem; }
        .dataTables_filter input { border-radius: 50px; padding: 5px 15px; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    
    <div class="stat-card mb-4 pb-3">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h4 class="font-prompt fw-bold text-navy mb-0">รายงานข้อมูลผู้เยี่ยมชม</h4>
            </div>
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex justify-content-md-end">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="date" name="filter_date" class="form-control border-light shadow-sm" value="<?php echo htmlspecialchars($filter_date); ?>">
                        <button type="submit" class="btn btn-primary" style="background: var(--hba-navy)">ดูข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6">
            <div class="summary-card d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-prompt text-muted mb-1 small">ผู้ร่วมกิจกรรม (ตามวันที่เลือก)</h6>
                    <h3 class="font-prompt fw-bold text-navy mb-0"><?php echo number_format($total_participants); ?> <span class="fs-6 fw-normal">ราย</span></h3>
                </div>
                <div class="bg-light rounded-circle p-3 text-primary d-none d-sm-block">
                    <i class="fas fa-users fs-4"></i>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="summary-card gold d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-prompt text-muted mb-1 small">ยอดขายสุทธิ (ตามวันที่เลือก)</h6>
                    <h3 class="font-prompt fw-bold mb-0 text-success"><?php echo number_format($total_sales_raw); ?> <span class="fs-6 fw-normal text-muted">บาท</span></h3>
                </div>
                <div class="bg-light rounded-circle p-3 text-warning d-none d-sm-block">
                    <i class="fas fa-hand-holding-usd fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="stat-card">
            <div class="table-responsive">
                <table id="exportTable" class="table table-hover align-middle w-100">
                    <thead class="bg-light font-prompt text-muted small">
                        <tr>
                            <th>เวลา</th>
                            <th>ข้อมูลลูกค้า</th>
                            <th>เบอร์โทร / อีเมล</th>
                            <th>ความสนใจ / ทำเล</th>
                            <th>งบประมาณ</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-end">ยอดจองสุทธิ</th>
                        </tr>
                    </thead>
                    <tbody class="font-prompt">
                        <?php foreach($recent_visitors as $row): ?>
                        <tr>
                            <td class="text-muted small"><?php echo date('H:i', strtotime($row['visit_time'])); ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($row['phone']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                            </td>
                            <td>
                                <div class="small text-navy fw-bold"><?php echo $row['item_detail'] ?: 'ยังไม่เปิดออเดอร์'; ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['target_region']); ?></div>
                            </td>
                            <td>
                                <div class="small text-primary fw-bold"><?php echo $row['budget_range'] ?: '-'; ?></div>
                                <div class="small text-muted"><?php echo $row['usable_area'] ?: '-'; ?></div>
                            </td>
                            <td class="text-center">
                                <?php 
                                    if (!empty($row['net_value']) && $row['net_value'] != 0) echo '<span class="badge rounded-pill bg-success">Closed Deal</span>';
                                    elseif (strpos($row['budget_range'], '10') !== false || strpos($row['budget_range'], '20') !== false) echo '<span class="badge rounded-pill bg-danger">Hot Lead</span>';
                                    else echo '<span class="badge rounded-pill bg-light text-muted border">ทั่วไป</span>';
                                ?>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold <?php echo (!empty($row['net_value'])) ? 'text-success' : 'text-muted'; ?>">
                                    <?php echo (!empty($row['net_value'])) ? number_format($row['net_value']) : '0'; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // เปิดใช้งาน DataTables
    $('#exportTable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-outline-success' },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-outline-danger' },
            { extend: 'print', text: '<i class="fas fa-print"></i> พิมพ์', className: 'btn btn-outline-secondary' }
        ],
        language: {
            search: "ค้นหาข้อความ:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "ไม่พบข้อมูล",
            zeroRecords: "ไม่มีข้อมูลในวันที่ระบุ",
            paginate: { first: "หน้าแรก", last: "หน้าสุดท้าย", next: "ถัดไป", previous: "ก่อนหน้า" }
        },
        order: [[0, 'desc']] // เรียงลำดับจากเวลาล่าสุด
    });
});
</script>

</body>
</html>