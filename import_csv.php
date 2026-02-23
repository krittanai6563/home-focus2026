<?php
// เชื่อมต่อฐานข้อมูล
require_once 'config/db_config.php';

$filename = "home-focus2026 - New Form (3).csv";

if (!file_exists($filename)) {
    die("<h3 style='color:red;'>ไม่พบไฟล์ CSV: $filename</h3>");
}

echo "<h2>กำลังอัปเดตข้อมูลเชิงลึกจากไฟล์ CSV...</h2>";

function cleanPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) >= 9 && substr($phone, 0, 1) !== '0') {
        $phone = '0' . $phone;
    }
    return substr($phone, 0, 10);
}

if (($handle = fopen($filename, "r")) !== FALSE) {
    fgetcsv($handle, 10000, ","); // ข้าม Header
    
    $update_count = 0;
    $error_count = 0;

    $stmt = $pdo->prepare("
        UPDATE visitors 
        SET 
            budget_range = ?, 
            usable_area = ?, 
            floor_count = ?, 
            target_region = ?, 
            visit_purpose = ?
        WHERE phone = ?
    ");

    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        
        $phone = cleanPhone($data[12]);
        if (strlen($phone) < 9) continue; // ถ้าเบอร์ไม่ถูกต้อง ให้ข้าม
        
        // แมปข้อมูลตามคอลัมน์ที่คุณระบุ
        $visitPurpose = !empty($data[4]) ? trim($data[4]) : null;  // ช่อง E (Index 4)
        $budgetRange = !empty($data[10]) ? trim($data[10]) : null; // ช่อง K (Index 10)
        $usableArea = !empty($data[16]) ? trim($data[16]) : null;  // ช่อง Q (Index 16)
        $floorCount = !empty($data[18]) ? trim($data[18]) : null;  // ช่อง S (Index 18)
        
        // -------------------------------------------------------------
        // จุดที่แก้ไข: ดึงข้อมูลทำเลจากช่อง AB (Index 27) เท่านั้น
        // -------------------------------------------------------------
        $targetRegion = !empty($data[27]) ? trim($data[27]) : null;

        try {
            // ทำการอัปเดตข้อมูล
            $stmt->execute([
                $budgetRange, 
                $usableArea, 
                $floorCount, 
                $targetRegion, 
                $visitPurpose, 
                $phone // ตัวเชื่อม WHERE phone = ?
            ]);

            // เช็คว่ามีแถวถูกอัปเดตจริงๆ ไหม
            if ($stmt->rowCount() > 0) {
                $update_count++;
            }

        } catch (PDOException $e) {
            echo "<span style='color:red;'>Error (เบอร์: $phone): " . $e->getMessage() . "</span><br>";
            $error_count++;
        }
    }
    
    fclose($handle);

    echo "<hr>";
    echo "<h3 style='color: green;'>✅ อัปเดตข้อมูลเชิงลึกสำเร็จ: $update_count รายการ</h3>";
    if ($error_count > 0) {
        echo "<h3 style='color: red;'>❌ เกิดข้อผิดพลาด: $error_count รายการ</h3>";
    }
    echo "<br><br><a href='admin_dashboard.php' style='padding: 10px 20px; background: #002366; color: white; text-decoration: none; border-radius: 5px;'>กลับไปยังหน้า Dashboard สมาคม</a>";
    
} else {
    echo "<h3 style='color:red;'>ไม่สามารถเปิดอ่านไฟล์ CSV ได้</h3>";
}
?>