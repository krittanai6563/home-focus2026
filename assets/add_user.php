<?php
require '../config/db_config.php';

$user = 'test';
$pass = '1234';
$company = 'ทดสอบระบบ';
$role = 'exhibitor';

// เข้ารหัสผ่านให้ถูกต้องตามมาตรฐานความปลอดภัย
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO exhibitors (company_name, username, password_hash, role) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)";

$stmt = $pdo->prepare($sql);
if($stmt->execute([$company, $user, $hashed_password, $role])) {
    echo "อัปเดตรหัสผ่านสำหรับ user: $user เรียบร้อยแล้ว! <br> ลองกลับไป Login ด้วยรหัส 1234 อีกครั้งครับ";
}
?>