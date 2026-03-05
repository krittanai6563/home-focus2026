<?php
// api/get_visitor_info.php
require_once '../includes/auth.php'; 
require_once '../config/db_config.php'; 

header('Content-Type: application/json; charset=utf-8');

// รับค่าจาก POST หรือ GET (ดักไว้ทั้งสองทาง)
$qr_data = '';
if (isset($_POST['qr_data'])) {
    $qr_data = trim($_POST['qr_data']);
} elseif (isset($_GET['qr_data'])) {
    $qr_data = trim($_GET['qr_data']);
}

// ถ้าค่าว่าง ให้ส่ง Error กลับไป
if (empty($qr_data)) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลสำหรับค้นหา']);
    exit;
}

// ทำความสะอาดข้อมูลเบอร์โทรศัพท์
$clean_qr = preg_replace('/[^0-9]/', '', $qr_data);
if (strlen($clean_qr) >= 9 && substr($clean_qr, 0, 1) !== '0') {
    $clean_qr = '0' . $clean_qr;
}
$clean_qr = substr($clean_qr, 0, 10);

try {
    // ดึงข้อมูลลูกค้า (ต้องมี id เพื่อใช้บันทึก transaction)
    $stmt = $pdo->prepare("SELECT id, full_name, phone, budget_range, target_region FROM visitors WHERE qr_entry = ? OR phone = ? LIMIT 1");
    $stmt->execute([$clean_qr, $clean_qr]);
    $visitor = $stmt->fetch();

    if ($visitor) {
        echo json_encode([
            'success' => true,
            'id' => $visitor['id'],
            'name' => $visitor['full_name'],
            'phone' => $visitor['phone'],
            'budget' => $visitor['budget_range'] ?: 'ไม่ระบุ',
            'region' => $visitor['target_region'] ?: 'ไม่ระบุ'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลลูกค้าในระบบ']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>