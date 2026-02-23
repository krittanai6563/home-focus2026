<?php
require '../includes/auth.php';
require '../config/db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $exhibitor_id = $_SESSION['exhibitor_id'];

    try {
        $pdo->beginTransaction();

        // 1. เพิ่มข้อมูลในตาราง visitors (ถ้ายังไม่มี)
        $stmt = $pdo->prepare("INSERT INTO visitors (full_name, phone, qr_entry) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE full_name = VALUES(full_name)");
        $stmt->execute([$full_name, $phone, $phone]);
        $visitor_id = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM visitors WHERE phone = '$phone'")->fetchColumn();

        // 2. บันทึกการเข้าเยี่ยมชมบูธทันที (Record Visit)
        $insertVisit = $pdo->prepare("INSERT INTO booth_visits (visitor_id, exhibitor_id) VALUES (?, ?)");
        $insertVisit->execute([$visitor_id, $exhibitor_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}