<?php
require '../config/db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $qr_id = $_POST['qr_id']; // ข้อมูลจากการสแกน

    // 1. ตรวจสอบว่ามีข้อมูลในระบบไหม
    $stmt = $pdo->prepare("SELECT * FROM visitors WHERE qr_entry = ? OR phone = ?");
    $stmt->execute([$qr_id, $qr_id]);
    $visitor = $stmt->fetch();

    if ($visitor) {
        // 2. ตรวจสอบสถานะการ Redeem [cite: 219-220]
        $stmt_redeem = $pdo->prepare("SELECT * FROM transactions WHERE visitor_id = ? AND discount_amount > 0");
        $stmt_redeem->execute([$visitor['id']]);
        
        if ($stmt_redeem->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'คูปองนี้ถูกใช้งานไปแล้วที่บูธอื่น']);
        } else {
            echo json_encode(['success' => true, 'name' => $visitor['full_name'], 'visitor_id' => $visitor['id']]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ลงทะเบียน']);
    }
}
?>