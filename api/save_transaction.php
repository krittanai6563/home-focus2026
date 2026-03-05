<?php
// ไฟล์: api/save_transaction.php
require_once '../includes/auth.php';
require_once '../config/db_config.php';

header('Content-Type: application/json; charset=utf-8');

// รับข้อมูลจากหน้าจอ
$visitor_id   = isset($_POST['visitor_id']) ? $_POST['visitor_id'] : '';
$exhibitor_id = $_SESSION['exhibitor_id']; 
$detail       = isset($_POST['detail']) ? $_POST['detail'] : '';
$value        = isset($_POST['value']) ? $_POST['value'] : 0;
$discount     = isset($_POST['discount']) ? $_POST['discount'] : 0;
$net_value    = isset($_POST['net_value']) ? $_POST['net_value'] : 0;

if (empty($visitor_id)) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลลูกค้า (visitor_id is missing)']);
    exit;
}

try {
    // บันทึกข้อมูลลงตาราง transactions (ใช้ชื่อคอลัมน์ที่ปรับปรุงแล้ว)
    $stmt = $pdo->prepare("INSERT INTO transactions (visitor_id, exhibitor_id, item_detail, total_value, discount_amount, net_value) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $visitor_id, 
        $exhibitor_id, 
        $detail, 
        $value, 
        $discount, 
        $net_value
    ]);
    
    echo json_encode(['success' => true, 'message' => 'บันทึกยอดจองสำเร็จ']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>