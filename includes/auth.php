<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ถ้าไม่มีรหัส ID ใน Session ให้ดีดกลับไปหน้า Login ทันที
if (!isset($_SESSION['exhibitor_id'])) {
    header("Location: login.php");
    exit;
}
?>