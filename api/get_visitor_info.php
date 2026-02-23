<?php
header('Content-Type: application/json; charset=utf-8');

$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query === '0998887777' || $query === 'HBA002') {
    echo json_encode([
        'success' => true,
        'id' => 999,
        'name' => 'สมหญิง ยิ่งรวย (ข้อมูลจำลอง)',
        'phone' => '099-888-7777'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่พบข้อมูลลูกค้า (ลองใช้เบอร์ 0998887777)'
    ]);
}
?>