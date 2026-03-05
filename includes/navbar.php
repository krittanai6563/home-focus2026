<?php
// ตรวจสอบและตั้งค่ารูปโปรไฟล์
$nav_company_name = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : 'ผู้ประกอบการ';
$nav_profile_img = (!empty($_SESSION['profile_img']) && $_SESSION['profile_img'] !== 'default-profile.png') 
                   ? $_SESSION['profile_img'] 
                   : 'https://www.allthaievent.com/images/event/31199.jpg';
?>
<style>
    :root {
        --nav-dark: #001529;
        --nav-navy: #002347;
        --nav-blue: #0056b3;
        --nav-sky: #00a8ff;
    }

    /* 1. Glassmorphism Design (โปร่งแสงและเบลอ) */
    .modern-navbar {
        background: rgba(0, 35, 71, 0.85); 
        backdrop-filter: blur(12px); 
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        transition: all 0.3s ease;
    }
    
    .navbar-brand {
        font-family: 'Prompt', sans-serif;
        font-weight: 600;
        color: #ffffff !important;
        letter-spacing: 0.5px;
        font-size: 1.3rem;
    }

    /* 2. เมนูพร้อม Animated Underline */
    .modern-nav-link {
        font-family: 'Prompt', sans-serif;
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 0.95rem;
        font-weight: 400;
        padding: 8px 16px !important;
        margin: 0 4px;
        position: relative;
        transition: color 0.3s ease;
    }

    .modern-nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: var(--nav-sky);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 2px;
    }

    .modern-nav-link:hover, .modern-nav-link.active {
        color: #ffffff !important;
    }

    .modern-nav-link:hover::after, .modern-nav-link.active::after {
        width: 70%; 
    }

    /* 3. กรอบโปรไฟล์แบบแคปซูล (Pill Profile) */
    .profile-pill {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50px;
        padding: 6px 6px 6px 20px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .profile-pill:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .profile-img-modern {
        width: 38px;
        height: 38px;
        object-fit: cover;
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    /* 4. Dropdown Menu โค้งมนและนุ่มนวล */
    .modern-dropdown {
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 35, 71, 0.15);
        padding: 12px 0;
        min-width: 220px;
        animation: dropFade 0.3s ease forwards;
    }

    @keyframes dropFade {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modern-dropdown-item {
        font-family: 'Sarabun', sans-serif;
        padding: 10px 24px;
        color: var(--nav-navy);
        font-weight: 500;
        transition: 0.2s;
        border-left: 3px solid transparent;
    }

    .modern-dropdown-item:hover {
        background-color: #f0f5fa;
        color: var(--nav-blue);
        border-left-color: var(--nav-sky);
    }

    .navbar-toggler { border: none; padding: 5px; }
    .navbar-toggler:focus { box-shadow: none; }
</style>

<nav class="navbar navbar-expand-lg modern-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-cube me-2 text-info" style="color: var(--nav-sky) !important;"></i> 
            HBA <span class="fw-light">FOCUS</span>
        </a>
        
        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <i class="fas fa-bars fs-4"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 mt-3 mt-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link modern-nav-link active" href="dashboard.php">
                        <i class="fas fa-chart-pie me-1 opacity-75"></i> แดชบอร์ด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link modern-nav-link" href="scan_visitor.php">
                        <i class="fas fa-qrcode me-1 opacity-75"></i> สแกนลีด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link modern-nav-link" href="redeem_order.php">
                        <i class="fas fa-ticket-alt me-1 opacity-75"></i> รับออเดอร์
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link modern-nav-link" href="register_walkin.php">
                        <i class="fas fa-user-plus me-1 opacity-75"></i> ลูกค้า Walk-in
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link modern-nav-link" href="index.php">
                        <i class="fas fa-file-alt me-1 opacity-75"></i> รายงาน
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center mt-3 mt-lg-0">
                <div class="dropdown">
                    <a class="profile-pill" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="text-end me-3 d-none d-lg-block">
                            <div class="font-prompt fw-semibold text-white lh-1" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($nav_company_name); ?>
                            </div>
                            <small style="color: var(--nav-sky); font-size: 0.7rem;">Exhibitor Account</small>
                        </div>
                        <img src="<?php echo htmlspecialchars($nav_profile_img); ?>" alt="Profile" class="profile-img-modern">
                        <span class="d-lg-none ms-3 font-prompt fw-bold text-white"><?php echo htmlspecialchars($nav_company_name); ?></span>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end modern-dropdown mt-lg-3">
                        <li class="d-lg-none">
                            <h6 class="dropdown-header font-prompt text-muted" style="font-size: 0.8rem;">ผู้ประกอบการ</h6>
                        </li>
                        <li>
                            <a class="dropdown-item modern-dropdown-item" href="#">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3 text-center" style="width: 35px; height: 35px; line-height: 18px;">
                                        <i class="fas fa-cog text-muted"></i>
                                    </div>
                                    ตั้งค่าบัญชี
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-2 opacity-10"></li>
                        <li>
                            <a class="dropdown-item modern-dropdown-item text-danger" href="logout.php">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3 text-center" style="width: 35px; height: 35px; line-height: 18px;">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </div>
                                    <span class="fw-bold">ออกจากระบบ</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>