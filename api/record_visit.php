<?php
header('Content-Type: application/json; charset=utf-8');

// รับค่าจากหน้าจอ
$qr_data = isset($_POST['qr_data']) ? $_POST['qr_data'] : '';

// ข้อมูลจำลองสำหรับการทดสอบ
if ($qr_data === '0812345678' || $qr_data === 'HBA001') {
    echo json_encode([
        'success' => true,
        'name' => 'สมชาย สายสร้างบ้าน (ข้อมูลจำลอง)',
        'phone' => '081-234-5678'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่พบข้อมูลในระบบทดสอบ (ลองใช้เบอร์ 0812345678)'
    ]);
}
?>