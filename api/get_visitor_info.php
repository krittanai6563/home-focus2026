<?php
// ไฟล์: api/get_visitor_info.php
require_once '../includes/auth.php';
require_once '../config/db_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['qr_data'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูล QR Code']);
    exit;
}

$qr_data = trim($_POST['qr_data']);

// ทำความสะอาดเบอร์โทร
$clean_qr = preg_replace('/[^0-9]/', '', $qr_data);
if (strlen($clean_qr) >= 9 && substr($clean_qr, 0, 1) !== '0') {
    $clean_qr = '0' . $clean_qr;
}
$clean_qr = substr($clean_qr, 0, 10);

try {
    // -------------------------------------------------------------------------
    // จุดสำคัญที่แก้ไข: เพิ่ม usable_area และ floor_count ลงในคำสั่ง SELECT
    // -------------------------------------------------------------------------
    $stmt = $pdo->prepare("SELECT full_name, phone, budget_range, target_region, usable_area, floor_count FROM visitors WHERE qr_entry = ? OR phone = ?");
    $stmt->execute([$clean_qr, $clean_qr]);
    $visitor = $stmt->fetch();

    if ($visitor) {
        // ส่งข้อมูลกลับไปให้ Javascript ที่หน้า scan_visitor.php
        echo json_encode([
            'success' => true,
            'name' => $visitor['full_name'],
            'phone' => $visitor['phone'],
            'budget' => $visitor['budget_range'] ?: 'ไม่ระบุ',
            'region' => $visitor['target_region'] ?: 'ไม่ระบุ',
            'area' => $visitor['usable_area'] ?: 'ไม่ระบุ',   // <--- เพิ่มบรรทัดนี้
            'floor' => $visitor['floor_count'] ?: 'ไม่ระบุ'   // <--- เพิ่มบรรทัดนี้
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลลูกค้าในระบบ อาจต้องลงทะเบียน Walk-in']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>