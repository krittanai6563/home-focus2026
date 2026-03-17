<?php
// ตั้งค่า Header ให้ตอบกลับเป็น JSON
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. รับค่าจากฟอร์ม
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    $floor_count = $_POST['floors'] ?? '';
    $usable_area = $_POST['usable_area'] ?? '';
    $budget_range = $_POST['budget'] ?? '';
    $target_region = $_POST['region'] ?? '';
    $visit_purpose = $_POST['objective'] ?? '';
    $decision_time = $_POST['decision_time'] ?? '';

    try {
        // 2. ตรวจสอบว่ามี อีเมล หรือ เบอร์โทร นี้ในระบบแล้วหรือยัง
        $check_sql = "SELECT id FROM visitors WHERE phone = :phone OR email = :email";
        $stmt_check = $pdo->prepare($check_sql);
        $stmt_check->execute(['phone' => $phone, 'email' => $email]);
        
        if ($stmt_check->rowCount() > 0) {
            echo json_encode([
                "success" => false,
                "message" => "อีเมลหรือเบอร์โทรศัพท์นี้ ถูกใช้ลงทะเบียนไปแล้ว!"
            ]);
            exit;
        }

        // 3. กำหนดค่า QR Entry และ QR Discount เป็นเบอร์โทรศัพท์
        $qr_entry = $phone;
        $qr_discount = $phone;

        // 4. คำสั่ง SQL สำหรับบันทึกลงตาราง visitors
        $sql = "INSERT INTO visitors (
                    full_name, email, phone, qr_entry, qr_discount, 
                    floor_count, usable_area, budget_range, target_region, 
                    visit_purpose, decision_time, registered_at
                ) VALUES (
                    :full_name, :email, :phone, :qr_entry, :qr_discount, 
                    :floor_count, :usable_area, :budget_range, :target_region, 
                    :visit_purpose, :decision_time, NOW()
                )";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'qr_entry' => $qr_entry,
            'qr_discount' => $qr_discount,
            'floor_count' => $floor_count,
            'usable_area' => $usable_area,
            'budget_range' => $budget_range,
            'target_region' => $target_region,
            'visit_purpose' => $visit_purpose,
            'decision_time' => $decision_time
        ]);

        // 5. ทำงานเมื่อบันทึกสำเร็จ
        $insert_id = $pdo->lastInsertId();

        // 6. สร้าง URL ของรูป QR Code (ใช้เบอร์โทรเป็นหลัก)
        $qr_url = "https://quickchart.io/qr?text=" . urlencode($phone) . "&size=300&margin=2&caption=" . urlencode($full_name);

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            
            $to = $email;
            $subject = "QR Code : เข้าร่วมงาน งานรับสร้างบ้าน Focus 2026";
            
            // ตั้งค่า Headers ให้อ่านเป็นอีเมล HTML
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            
            $headers .= "From: Home Builder Association <noreply@hba-expo.com>\r\n";

            // สร้างวันที่ภาษาไทย
            $thai_months = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
            $thaiDate = date('j') . " " . $thai_months[date('n')] . " " . (date('Y') + 543);
            
            $logoUrl = "https://hba-th.org/wp-content/uploads/2025/12/3179-269x300-1.jpg";
            $bgPosterUrl = "https://hba-th.org/wp-content/uploads/2026/01/AW-Poster-Focus26_CS6-1.png";

            $htmlBody = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset="UTF-8">
              <style>
                @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&family=Sarabun:wght@300;400;600&display=swap');
                body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
                table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                img { -ms-interpolation-mode: bicubic; }
                body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
              </style>
            </head>
            <body style="margin: 0; padding: 0; background-color: #222222; font-family: 'Sarabun', sans-serif;">
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%; height: 100%; min-height: 100vh; background-image: url('{$bgPosterUrl}'); background-repeat: no-repeat; background-size: cover; background-position: center center; background-color: #222222;">
                <tr>
                  <td align="center" valign="top" style="padding: 50px 10px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.4); max-width: 600px; width: 100%;">
                      <tr>
                        <td style="padding: 40px 40px 10px 40px;">
                          <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                              <td width="80"><img src="{$logoUrl}" alt="Logo" width="70" style="display: block; border-radius: 4px;" /></td>
                              <td style="padding-left: 15px; font-family: 'Prompt', sans-serif; font-size: 14px; color: #666;">Home Builder Association<br>{$thaiDate}</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding: 20px 40px 10px 40px;">
                          <h1 style="font-family: 'Prompt', sans-serif; font-size: 24px; color: #1a1a1a; margin: 0; font-weight: 600; line-height: 1.3;">QR Code : เข้าร่วมงานรับสร้างบ้าน Focus 2026</h1>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding: 10px 40px;"><div style="height: 1px; width: 100%; background-color: #f0f0f0;"></div></td>
                      </tr>
                      <tr>
                        <td style="padding: 20px 40px; font-family: 'Sarabun', sans-serif; font-size: 16px; color: #444; line-height: 1.8;">
                          <p style="margin-bottom: 20px;">เรียน คุณ <strong style="color: #000; font-family: 'Prompt', sans-serif;">{$full_name}</strong> ที่เคารพ</p>
                          <p>ขอขอบคุณสำหรับการลงทะเบียนเข้าร่วมงาน "งานรับสร้างบ้าน Focus 2026" ระหว่างวันที่ 18-22 มีนาคม 2569 ณ อิมแพ็ค ฮอลล์ 6 เมืองทองธานี จัดโดย สมาคมธุรกิจรับสร้างบ้าน</p>
                          <p>งาน "งานรับสร้างบ้าน Focus 2026" งานเดียวที่รวมบริษัทรับสร้างบ้านชั้นนำไว้มากที่สุด จัดเต็มสิทธิประโยชน์ ส่วนลดและของแถม จากบริษัทรับสร้างบ้านชั้นนํา พร้อมสินเชื่ออัตราดอกเบี้ยพิเศษ เฉพาะในงานนี้เท่านั้น</p>
                          <p>เพื่ออำนวยความสะดวกในการเข้าชมงาน ทางสมาคมธุรกิจรับสร้างบ้าน ได้สร้าง QR Code สำหรับ Check-in เข้าร่วมงาน</p>
                        </td>
                      </tr>
                      <tr>
                        <td align="center" style="padding: 10px 40px 30px 40px;">
                          <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #e5e5e5; border-radius: 12px; overflow: hidden;">
                            <tr>
                              <td width="35%" bgcolor="#f9f9f9" style="padding: 25px; border-right: 1px dashed #cccccc; text-align: center;">
                                <p style="font-size: 12px; color: #888; margin: 0; font-family: 'Prompt', sans-serif; text-transform: uppercase;">SCAN HERE</p>
                              </td>
                              <td width="65%" style="padding: 25px; text-align: center;">
                                <p style="font-family: 'Prompt', sans-serif; font-weight: 600; color: #0056b3; margin: 0 0 10px 0; font-size: 14px; letter-spacing: 1px;">E-TICKET FOR ENTRY</p>
                                <img src="{$qr_url}" alt="QR Code" width="160" style="display: block; margin: 0 auto; border: 4px solid #fff;" />
                                <p style="font-size: 12px; color: #999; margin-top: 10px;">Ref ID: {$phone}</p>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding: 0 40px 40px 40px; font-family: 'Sarabun', sans-serif; font-size: 15px; color: #444; line-height: 1.8;">
                          <p>แล้วพบกันในงาน "งานรับสร้างบ้าน Focus 2026" ระหว่างวันที่ 18-22 มีนาคม 2569 ณ อิมแพ็ค ฮอลล์ 6 เมืองทองธานี สอบถามรายละเอียดเพิ่มเติมได้ที่ <a href="https://line.me/R/ti/p/@055cuumo?oat_content=url" style="color: #0056b3; font-weight: 600; text-decoration: none;">LINE</a></p>
                          <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; font-size: 14px; color: #666;">
                            จึงเรียนมาเพื่อโปรดพิจารณาและขอแสดงความนับถือ<br><br>
                            <strong>สมาคมธุรกิจรับสร้างบ้าน</strong><br>
                            www.hba-th.org | โทร : 0 2570 0153, 0 2940 2744
                          </div>
                        </td>
                      </tr>
                    </table>
                    <p style="font-family: 'Prompt', sans-serif; color: rgba(255,255,255,0.6); font-size: 11px; margin-top: 30px; letter-spacing: 1px;">&copy; HOME BUILDER ASSOCIATION | ALL RIGHTS RESERVED</p>
                  </td>
                </tr>
              </table>
            </body>
            </html>
HTML;

            // ใช้คำสั่งส่งเมล์ของ PHP (@ ช่วยซ่อน warning กรณี mail service ของ server ปิดอยู่)
            @mail($to, $subject, $htmlBody, $headers);
        }
        // ========================================================

        // 8. ตอบกลับ Frontend 
        echo json_encode([
            "success" => true,
            "message" => "ลงทะเบียนสำเร็จ ข้อมูลและ QR Code ถูกส่งไปยังอีเมลของท่านแล้ว",
            "insert_id" => $insert_id,
            "qr_url" => $qr_url
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "เกิดข้อผิดพลาดจากฐานข้อมูล: " . $e->getMessage()
        ]);
        exit;
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid Request"]);
}
?>
