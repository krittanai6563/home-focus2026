<?php
session_start(); // เริ่มต้น session เพื่อเข้าถึงข้อมูลเดิม

// 1. ล้างตัวแปร session ทั้งหมด
$_SESSION = array();

// 2. ถ้ามีการใช้คุกกี้สำหรับ session ให้ลบออกด้วย
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. ทำลาย session ทั้งหมดในฝั่งเซิร์ฟเวอร์
session_destroy();

// 4. ส่งผู้ใช้งานกลับไปที่หน้า Login
header("Location: login.php");
exit;
?>