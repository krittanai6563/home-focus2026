<?php
// ตรวจสอบว่ามี Session หรือยัง (ป้องกัน Error ถ้าลืม start ในหน้าหลัก)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$profile_img = $_SESSION['profile_img'] ?: 'default-profile.png';
$company_name = $_SESSION['company_name'];
?>
<nav class="navbar navbar-dark sticky-top" style="background: linear-gradient(135deg, #001a33 0%, #003366 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 12px 0;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center font-prompt" href="dashboard.php">
            <img src="assets/img/<?php echo $profile_img; ?>" class="rounded-circle me-2 border border-white" style="width: 35px; height: 35px; object-fit: cover;">
            <span style="font-size: 1.1rem;"><?php echo $company_name; ?></span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" style="background-color: #001a33; color: white;">
            <div class="offcanvas-header border-bottom border-secondary">
                <h5 class="offcanvas-title font-prompt">เมนูการใช้งาน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="dashboard.php"><i class="fas fa-home me-2"></i> หน้าหลัก (Dashboard)</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="assets/scan_visitor.php"><i class="fas fa-qrcode me-2"></i> สแกนเก็บ Lead (QR ฟ้า)</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="register_walkin.php"><i class="fas fa-user-plus me-2"></i> ลงทะเบียน Walk-in</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="redeem_order.php"><i class="fas fa-ticket-alt me-2"></i> รับออเดอร์ (QR เหลือง)</a>
                    </li>
                    <hr class="border-secondary">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>