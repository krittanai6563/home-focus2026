<?php 
require '../includes/auth.php'; 
require '../config/db_config.php';

$company_name = $_SESSION['company_name'];
$profile_img = $_SESSION['profile_img'] ?: 'default-profile.png';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Lead - <?php echo $company_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --hba-dark: #001a33;
            --hba-navy: #003366;
            --hba-blue: #0056b3;
            --hba-light: #f8fafd;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--hba-light);
            color: var(--hba-dark);
        }

        .navbar { 
            background: linear-gradient(135deg, var(--hba-dark) 0%, var(--hba-navy) 100%);
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .navbar-brand { font-family: 'Prompt', sans-serif; font-weight: 600; font-size: 1.2rem; }
        .profile-thumb { width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(255,255,255,0.2); }

        #reader { 
            border: none !important; 
            border-radius: 25px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.1); 
            background: white;
        }
        
        .card-custom {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.05);
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }
        
        .btn-action {
            border-radius: 15px;
            padding: 12px;
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-navy { background-color: var(--hba-navy); color: white; }
        .btn-navy:hover { background-color: var(--hba-blue); color: white; transform: translateY(-2px); }
        
        .btn-warning-custom { background-color: #ffc107; color: #212529; }
        .btn-warning-custom:hover { background-color: #e0a800; transform: translateY(-2px); }

        .divider { display: flex; align-items: center; text-align: center; color: #888; margin: 30px 0; font-size: 0.9rem; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ddd; }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        #scan-result { display: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container">
        <div class="navbar-brand d-flex align-items-center">
            <img src="assets/img/<?php echo $profile_img; ?>" class="rounded-circle profile-thumb me-2" alt="Profile">
            <span class="d-none d-sm-inline">สแกนเก็บ Lead</span>
        </div>
        <a href="dashboard.php" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="fas fa-arrow-left"></i> กลับ
        </a>
    </div>
</nav>

<div class="container py-4 pb-5">
    
    <div class="text-center mb-4">
        <h5 class="font-prompt fw-bold text-navy">กล้องสแกนคิวอาร์ (สีฟ้า)</h5>
        <p class="text-muted small">วางคิวอาร์โค้ดให้อยู่ในกรอบเพื่อบันทึกข้อมูล</p>
    </div>

    <div id="reader" class="mb-4"></div>

    <div id="scan-result" class="card card-custom animate__animated animate__fadeIn">
        <div class="card-body text-center py-5">
            <div id="result-icon" class="mb-3"></div>
            <h4 id="visitor-name" class="font-prompt fw-bold mb-1 text-navy"></h4>
            <p id="visitor-phone" class="text-muted mb-4"></p>
            <div id="status-msg" class="alert py-2 mb-4"></div>

            <div id="no-data-action" class="mb-4 d-none text-center">
                <p class="text-danger small mb-3">ไม่พบข้อมูลในฐานข้อมูล กรุณาลงทะเบียนใหม่</p>
                <a href="register_walkin.php" class="btn btn-warning-custom btn-action w-100 shadow-sm mb-2">
                    <i class="fas fa-user-plus me-2"></i>ลงทะเบียนลูกค้า Walk-in
                </a>
            </div>

            <button onclick="resetScanner()" class="btn btn-navy w-100 btn-action shadow-sm">สแกน/ค้นหาคนต่อไป</button>
        </div>
    </div>

    <div id="manual-section">
        <div class="divider font-prompt text-uppercase">หรือค้นหาด้วยเบอร์โทรศัพท์</div>

        <div class="card card-custom">
            <div class="card-body p-4">
                <label class="form-label small font-prompt fw-bold text-muted">กรอกเบอร์โทรศัพท์ลูกค้า (กรณีสแกนไม่ได้)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                    <input type="tel" id="manual-phone" class="form-control bg-light border-0" placeholder="ระบุเบอร์โทร 10 หลัก" maxlength="10">
                    <button onclick="manualSearch()" class="btn btn-navy px-4 font-prompt">ค้นหา</button>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-muted" style="font-size: 0.7rem;">Official Scan Portal - Home Focus 2026</p>
    </div>
</div>

<script>
let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
    fps: 15, 
    qrbox: {width: 250, height: 250},
    aspectRatio: 1.0 
});

function onScanSuccess(decodedText) {
    processData(decodedText);
}

function manualSearch() {
    const phone = document.getElementById('manual-phone').value;
    if(phone.length < 9) {
        alert('กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง');
        return;
    }
    processData(phone);
}

function processData(qrData) {
    html5QrcodeScanner.pause();
    document.getElementById('reader').style.display = 'none';
    document.getElementById('manual-section').style.display = 'none';
    
    fetch('../api/record_visit.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'qr_data=' + encodeURIComponent(qrData)
    })
    .then(response => response.json())
    .then(data => {
        const resCard = document.getElementById('scan-result');
        resCard.style.display = 'block';
        
        if(data.success) {
            document.getElementById('result-icon').innerHTML = '<i class="fas fa-check-circle text-success fa-4x"></i>';
            document.getElementById('visitor-name').innerText = data.name;
            document.getElementById('visitor-phone').innerText = 'เบอร์โทร: ' + data.phone;
            document.getElementById('status-msg').className = 'alert alert-success py-2 font-prompt';
            document.getElementById('status-msg').innerText = 'บันทึกข้อมูลลีดเรียบร้อยแล้ว';
            
            document.getElementById('no-data-action').classList.add('d-none');
        } else {
            document.getElementById('result-icon').innerHTML = '<i class="fas fa-times-circle text-danger fa-4x"></i>';
            document.getElementById('visitor-name').innerText = 'ไม่พบข้อมูล';
            document.getElementById('visitor-phone').innerText = '';
            document.getElementById('status-msg').className = 'alert alert-danger py-2 font-prompt';
            document.getElementById('status-msg').innerText = data.message;

            // แสดงส่วนปุ่ม Walk-in ทันทีเมื่อไม่พบข้อมูล
            document.getElementById('no-data-action').classList.remove('d-none');
        }
    });
}

function resetScanner() {
    document.getElementById('scan-result').style.display = 'none';
    document.getElementById('reader').style.display = 'block';
    document.getElementById('manual-section').style.display = 'block';
    document.getElementById('manual-phone').value = '';
    html5QrcodeScanner.resume();
}

html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>