<?php
// ไฟล์: api/record_visit.php
require_once '../includes/auth.php';
require_once '../config/db_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['qr_data'])) {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูล QR Code']);
    exit;
}

$qr_data = trim($_POST['qr_data']);
$exhibitor_id = $_SESSION['exhibitor_id']; 

$clean_qr = preg_replace('/[^0-9]/', '', $qr_data);
if (strlen($clean_qr) >= 9 && substr($clean_qr, 0, 1) !== '0') {
    $clean_qr = '0' . $clean_qr;
}
$clean_qr = substr($clean_qr, 0, 10);

// ==========================================
// ฟังก์ชันสำหรับส่งอีเมลคูปองส่วนลด
// ==========================================
function sendCouponEmail($toEmail, $userName, $qrDiscountData) {
    $subject = "รับสิทธิ์คูปองส่วนลด 1,000 บาท - งาน Home Focus 2026";
    
    // สร้าง QR Code ผ่าน API QuickChart
    $qrUrl = "https://quickchart.io/qr?text=" . urlencode($qrDiscountData) . "&size=300&margin=2";
    $logoUrl = "https://hba-th.org/wp-content/uploads/2025/12/3179-269x300-1.jpg";
    $bgPosterUrl = "https://hba-th.org/wp-content/uploads/2026/01/AW-Poster-Focus26_CS6-1.png";
    $thaiDate = date('d M Y');

    // โครงสร้าง HTML อีเมล (อิงจากดีไซน์เดิมของคุณ)
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&family=Sarabun:wght@400&display=swap');
            body { font-family: 'Sarabun', sans-serif; background-color: #222222; margin: 0; padding: 0; }
        </style>
    </head>
    <body style='background-color: #222222;'>
        <table width='100%' style='background-image: url({$bgPosterUrl}); background-size: cover; padding: 50px 10px;'>
            <tr>
                <td align='center'>
                    <table width='600' style='background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.4);'>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <img src='{$logoUrl}' width='70' style='display:block; margin-bottom: 20px;'>
                                <h1 style='font-family: \"Prompt\", sans-serif; font-size: 22px; color: #c6a700; margin: 0;'>
                                    ยินดีต้อนรับเข้าสู่งาน Home Focus 2026
                                </h1>
                                <hr style='border: 0; border-bottom: 1px solid #eee; margin: 20px 0;'>
                                <p>เรียน คุณ <strong>{$userName}</strong></p>
                                <p>ขอบคุณที่เช็คอินเข้าร่วมงาน! สมาคมขอมอบ <strong>คูปองส่วนลดพิเศษมูลค่า 1,000 บาท</strong> เพื่อใช้เป็นส่วนลดเพิ่มเติมในการจองสร้างบ้านกับบริษัทต่างๆ ภายในงาน (สแกนคูปองนี้ที่หน้าบูธที่ร่วมรายการ)</p>
                            </td>
                        </tr>
                        <tr>
                            <td align='center' style='padding: 0 40px 40px 40px;'>
                                <table width='100%' style='border: 2px dashed #c6a700; border-radius: 12px; background-color: #fffdf2;'>
                                    <tr>
                                        <td align='center' style='padding: 30px;'>
                                            <p style='font-family: \"Prompt\", sans-serif; font-weight: 600; color: #c6a700; font-size: 16px; margin-top: 0;'>E-COUPON DISCOUNT 1,000 THB</p>
                                            <img src='{$qrUrl}' alt='QR Discount' width='180' style='border: 4px solid #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                                            <p style='font-size: 13px; color: #666; margin-bottom: 0;'>รหัสอ้างอิง: {$qrDiscountData}</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Home Builder Association <no-reply@hba-th.org>\r\n";

    // ส่งอีเมล
    @mail($toEmail, $subject, $htmlBody, $headers);
}

try {
    // 1. ค้นหาข้อมูลลูกค้า
    $stmt = $pdo->prepare("SELECT id, full_name, phone, email, qr_discount, check_in_status FROM visitors WHERE qr_entry = ? OR phone = ?");
    $stmt->execute([$clean_qr, $clean_qr]);
    $visitor = $stmt->fetch();

    if ($visitor) {
        $visitor_id = $visitor['id'];
        $isFirstCheckin = false;
        
        // 2. ถ้าลูกค้าเพิ่งเคยเช็คอินครั้งแรก (check_in_status = 0)
        if ($visitor['check_in_status'] == 0) {
            // อัปเดตสถานะเช็คอิน
            $updateStatus = $pdo->prepare("UPDATE visitors SET check_in_status = 1 WHERE id = ?");
            $updateStatus->execute([$visitor_id]);
            
            // ส่งอีเมลถ้าลูกค้ามีอีเมลในระบบ
            if (!empty($visitor['email'])) {
                sendCouponEmail($visitor['email'], $visitor['full_name'], $visitor['qr_discount']);
            }
            $isFirstCheckin = true;
        }

        // 3. ตรวจสอบการสแกนซ้ำในบูธ (ป้องกันสแกนซ้ำภายในวันเดียวกัน)
        $checkStmt = $pdo->prepare("SELECT id FROM booth_visits WHERE visitor_id = ? AND exhibitor_id = ? AND DATE(visit_time) = CURDATE()");
        $checkStmt->execute([$visitor_id, $exhibitor_id]);
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'name' => $visitor['full_name'], 
                'phone' => $visitor['phone'],
                'message' => '✔️ คุณสแกนลูกค้ารายนี้ไปแล้ว (ลูกค้าเคยรับสิทธิ์คูปองไปแล้ว)'
            ]);
            exit;
        }

        // 4. บันทึกข้อมูลการเยี่ยมชมลงตาราง booth_visits (เก็บ Lead)
        $insertStmt = $pdo->prepare("INSERT INTO booth_visits (visitor_id, exhibitor_id) VALUES (?, ?)");
        $insertStmt->execute([$visitor_id, $exhibitor_id]);

        // กำหนดข้อความตอบกลับ
        $responseMsg = 'บันทึกข้อมูลลีดเรียบร้อยแล้ว';
        if ($isFirstCheckin) {
            $responseMsg = 'เช็คอินสำเร็จ! ระบบกำลังส่งคูปอง 1,000 บาท ไปที่อีเมลลูกค้า 📨';
        }

        echo json_encode([
            'success' => true,
            'name' => $visitor['full_name'],
            'phone' => $visitor['phone'],
            'message' => $responseMsg
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบข้อมูลลูกค้าในระบบ อาจต้องลงทะเบียน Walk-in'
        ]);
    }

} catch (PDOException $e) {
    error_log("Scan Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล']);
}
?>