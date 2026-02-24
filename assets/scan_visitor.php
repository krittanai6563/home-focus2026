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
    <title>Scan Lead - <?php echo htmlspecialchars($company_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        /* ---------------------------------------------------
           ดีไซน์ดั้งเดิมของคุณ (หน้าสแกน & ค้นหา)
        --------------------------------------------------- */
        :root {
            --hba-dark: #001a33;
            --hba-navy: #003366;
            --hba-blue: #0056b3;
            --hba-sky: #00a8ff; /* เพิ่มสีฟ้าสำหรับไอคอน */
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

        #preview-section, #scan-result { display: none; }

        /* ---------------------------------------------------
           ดีไซน์ใหม่เพิ่มเติม (เฉพาะกล่องแสดงข้อมูล Grid)
        --------------------------------------------------- */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 25px;
            text-align: left;
        }
        .info-box {
            background: #f8fafd;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.2s;
        }
        .info-box:hover {
            border-color: var(--hba-sky);
            background: #f0f7ff;
        }
        .info-label { font-size: 0.75rem; color: #64748b; margin-bottom: 3px; font-weight: 600; text-transform: uppercase; }
        .info-value { font-size: 0.9rem; color: var(--hba-navy); font-weight: 600; }
        .info-icon { color: var(--hba-sky); font-size: 1.1rem; width: 24px; text-align: center; margin-right: 5px; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4 pb-5" style="max-width: 600px;">
    
    <div class="text-center mb-4">
        <h5 class="font-prompt fw-bold text-navy">กล้องสแกนคิวอาร์ (สีฟ้า)</h5>
        <p class="text-muted small">วางคิวอาร์โค้ดให้อยู่ในกรอบเพื่อค้นหาข้อมูลลูกค้า</p>
    </div>

    <div id="reader" class="mb-4"></div>

    <div id="preview-section" class="card card-custom animate__animated animate__fadeIn mb-4" style="border-top: 4px solid var(--hba-sky);">
        <div class="card-body text-center py-4 px-sm-4">
            
            <div class="d-flex align-items-center justify-content-center mb-3">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 60px; height: 60px;">
                    <i class="fas fa-user-check fa-2x"></i>
                </div>
            </div>
            
            <h4 id="preview-name" class="font-prompt fw-bold text-navy mb-1"></h4>
            <p id="preview-phone" class="text-muted mb-4 font-prompt bg-light d-inline-block px-3 py-1 rounded-pill small"></p>
            
            <div id="preview-details" class="info-grid font-prompt">
                </div>
            
            <input type="hidden" id="current-qr-data">
            
            <div class="d-flex gap-2 justify-content-center">
                <button onclick="confirmCheckIn()" class="btn btn-success btn-action w-50 shadow-sm">
                    <i class="fas fa-save me-1"></i> ยืนยันบันทึก
                </button>
                <button onclick="resetScanner()" class="btn btn-outline-secondary btn-action w-50 shadow-sm">
                    <i class="fas fa-times me-1"></i> ยกเลิก
                </button>
            </div>
        </div>
    </div>

    <div id="scan-result" class="card card-custom animate__animated animate__fadeIn">
        <div class="card-body text-center py-5">
            <div id="result-icon" class="mb-3"></div>
            <h4 id="visitor-name" class="font-prompt fw-bold mb-1 text-navy"></h4>
            <div id="status-msg" class="alert py-2 mb-4 font-prompt rounded-pill fw-bold"></div>

            <div id="no-data-action" class="mb-4 d-none text-center">
                <p class="text-danger small mb-3">ไม่พบข้อมูลลูกค้าในระบบ อาจเป็นลูกค้าใหม่</p>
                <a href="register_walkin.php" class="btn btn-warning-custom btn-action w-100 shadow-sm mb-2">
                    <i class="fas fa-user-plus me-2"></i>ลงทะเบียนลูกค้า Walk-in
                </a>
            </div>

            <button onclick="resetScanner()" class="btn btn-navy w-100 btn-action shadow-sm">สแกนคนต่อไป</button>
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

</div>

<script>
let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
    fps: 15, 
    qrbox: {width: 250, height: 250},
    aspectRatio: 1.0 
});

function onScanSuccess(decodedText) { processData(decodedText); }

function manualSearch() {
    const phone = document.getElementById('manual-phone').value;
    if(phone.length < 9) { alert('กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง'); return; }
    processData(phone);
}

function processData(qrData) {
    try { html5QrcodeScanner.pause(); } catch (err) {}

    document.getElementById('reader').style.display = 'none';
    document.getElementById('manual-section').style.display = 'none';
    document.getElementById('scan-result').style.display = 'none';
    
    document.getElementById('preview-section').style.display = 'block';
    document.getElementById('preview-name').innerHTML = '<i class="fas fa-circle-notch fa-spin text-primary"></i> กำลังดึงข้อมูล...';
    document.getElementById('preview-phone').innerText = '';
    document.getElementById('preview-details').innerHTML = '';
    
    fetch('../api/get_visitor_info.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'qr_data=' + encodeURIComponent(qrData)
    })
    .then(async response => {
        const isJson = response.headers.get('content-type')?.includes('application/json');
        if (!response.ok || !isJson) throw new Error('ระบบขัดข้อง: Server ไม่ตอบสนองเป็น JSON');
        return await response.json();
    })
    .then(data => {
        if(data.success) {
            document.getElementById('preview-name').innerText = data.name;
            document.getElementById('preview-phone').innerHTML = `<i class="fas fa-mobile-alt me-1 text-primary"></i> ${data.phone}`;
            document.getElementById('current-qr-data').value = qrData;
            
            // นำข้อมูลมาเรียงในกริดดีไซน์สวยๆ
            document.getElementById('preview-details').innerHTML = `
                <div class="info-box">
                    <div class="info-label">งบประมาณ</div>
                    <div class="info-value"><i class="fas fa-coins info-icon"></i>${data.budget || '-'}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">ทำเล / โซน</div>
                    <div class="info-value"><i class="fas fa-map-marker-alt info-icon"></i>${data.region || '-'}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">พื้นที่ใช้สอย</div>
                    <div class="info-value"><i class="fas fa-expand-arrows-alt info-icon"></i>${data.area || '-'}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">จำนวนชั้น</div>
                    <div class="info-value"><i class="fas fa-layer-group info-icon"></i>${data.floor || '-'}</div>
                </div>
            `;
        } else {
            document.getElementById('preview-section').style.display = 'none';
            showResultCard(false, 'ไม่พบข้อมูลลูกค้า', data.message);
            document.getElementById('no-data-action').classList.remove('d-none');
        }
    })
    .catch(error => { alert(error.message); resetScanner(); });
}

function resetScanner() {
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('scan-result').style.display = 'none';
    document.getElementById('reader').style.display = 'block';
    document.getElementById('manual-section').style.display = 'block';
    document.getElementById('manual-phone').value = '';
    try { html5QrcodeScanner.resume(); } catch (err) {}
}

function confirmCheckIn() {
    const qrData = document.getElementById('current-qr-data').value;
    document.getElementById('preview-section').style.display = 'none';
    
    fetch('../api/record_visit.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'qr_data=' + encodeURIComponent(qrData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showResultCard(true, data.name, data.message || 'จัดเก็บ Lead สำเร็จ!');
            document.getElementById('no-data-action').classList.add('d-none');
        } else {
            showResultCard(false, data.name || 'ผิดพลาด', data.message);
        }
    });
}

function showResultCard(isSuccess, name, msg) {
    const resCard = document.getElementById('scan-result');
    resCard.style.display = 'block';
    
    const icon = isSuccess ? '<i class="fas fa-check-circle text-success fa-4x shadow-sm rounded-circle bg-white"></i>' 
                           : '<i class="fas fa-exclamation-circle text-danger fa-4x shadow-sm rounded-circle bg-white"></i>';
    const alertClass = isSuccess ? 'alert alert-success' : 'alert alert-danger';
    
    document.getElementById('result-icon').innerHTML = icon;
    document.getElementById('visitor-name').innerText = name;
    document.getElementById('status-msg').className = alertClass + ' py-2 font-prompt rounded-pill fw-bold';
    document.getElementById('status-msg').innerText = msg;
}

html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>