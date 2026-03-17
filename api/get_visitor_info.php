<?php
// ไฟล์: api/get_visitor_info.php
require_once '../includes/auth.php'; 
require_once '../config/db_config.php'; 

header('Content-Type: application/json; charset=utf-8');

$qr_data = '';
if (isset($_POST['qr_data'])) {
    $qr_data = trim($_POST['qr_data']);
} elseif (isset($_GET['qr_data'])) {
    $qr_data = trim($_GET['qr_data']);
}

if (empty($qr_data)) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลสำหรับค้นหา']);
    exit;
}

$clean_qr = preg_replace('/[^0-9]/', '', $qr_data);
if (strlen($clean_qr) >= 9 && substr($clean_qr, 0, 1) !== '0') {
    $clean_qr = '0' . $clean_qr;
}
$clean_qr = substr($clean_qr, 0, 10);

try {
    // 1. ดึงข้อมูลลูกค้าจากตาราง visitors
    $stmt = $pdo->prepare("SELECT id, full_name, phone, budget_range, target_region, check_in_status FROM visitors WHERE qr_entry = ? OR phone = ? LIMIT 1");
    $stmt->execute([$clean_qr, $clean_qr]);
    $visitor = $stmt->fetch();

    if ($visitor) {
        // 2. เช็คว่าลูกค้ารายนี้เคยได้ส่วนลดคูปองในตาราง transactions ไปแล้วหรือยัง
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE visitor_id = ? AND discount_amount > 0");
        $stmt_check->execute([$visitor['id']]);
        
        // ถ้า COUNT(*) มากกว่า 0 แปลว่าเคยได้ส่วนลดไปแล้ว ให้ตัวแปรนี้เป็น true
        $has_used_coupon = ($stmt_check->fetchColumn() > 0) ? true : false;

        // 3. ส่งข้อมูลทั้งหมด รวมถึงสถานะ has_used_coupon กลับไปให้ JavaScript
        echo json_encode([
            'success' => true,
            'id' => $visitor['id'],
            'name' => $visitor['full_name'],
            'phone' => $visitor['phone'],
            'budget' => $visitor['budget_range'] ?: 'ไม่ระบุ',
            'region' => $visitor['target_region'] ?: 'ไม่ระบุ',
            'check_in_status' => (int)$visitor['check_in_status'],
            'has_used_coupon' => $has_used_coupon 
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ท่านยังไม่ได้ลงทะเบียนเข้างาน (ไม่พบข้อมูลในระบบ)']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>