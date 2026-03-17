<?php
require '../config/db_config.php';

// ตั้งค่าข้อมูลที่ต้องการ
$user = 'superadmin';      // แนะนำให้ใช้ชื่อนี้สำหรับสิทธิ์สูงสุด
$pass = '1234';            // รหัสผ่านที่ต้องการ
$company = 'ผู้ดูแลระบบสูงสุด'; // ชื่อบริษัท/หน่วยงาน
$role = 'superadmin';      // เปลี่ยนเป็น superadmin เพื่อให้เข้าถึงเมนูที่คุณต้องการได้ครบ

// เข้ารหัสผ่านให้ถูกต้องตามมาตรฐานความปลอดภัย
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

try {
    // เตรียมคำสั่ง SQL
    $sql = "INSERT INTO exhibitors (company_name, username, password_hash, role) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            role = VALUES(role),
            company_name = VALUES(company_name)";

    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$company, $user, $hashed_password, $role])) {
        echo "<h3>ดำเนินการเรียบร้อยแล้ว!</h3>";
        echo "<b>Username:</b> $user <br>";
        echo "<b>Password:</b> $pass <br>";
        echo "<b>Role:</b> $role <br><br>";
        echo "ลองกลับไป <a href='login.php'>Login</a> อีกครั้งครับ";
    }
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>