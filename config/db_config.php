<?php
// 1. ตั้งค่าเขตเวลาเป็นประเทศไทย (ICT / GMT+7)
date_default_timezone_set('Asia/Bangkok'); 

// 2. ข้อมูลการเชื่อมต่อฐานข้อมูล
$host = "localhost"; 
$db   = "hba_expo_2026";
$user = "root";     
$pass = ""; 
$charset = "utf8mb4";

try {
    // 3. สร้างการเชื่อมต่อผ่าน PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // กรณีเชื่อมต่อไม่สำเร็จ
    die("Database Connection Failed: " . $e->getMessage());
}
?>