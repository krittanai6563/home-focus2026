<?php 
// ถอยหลัง 1 ระดับเพื่อออกจาก assets ไปหาโฟลเดอร์หลัก
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';

$company_name = $_SESSION['company_name'];
$profile_img = $_SESSION['profile_img'] ?: 'default-profile.png';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Order & Redeem - <?php echo $company_name; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        :root {
            --hba-gold: #f39c12;
            --hba-dark-gold: #d35400;
            --hba-navy: #003366;
            --hba-light: #fdfaf4;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--hba-light);
            color: var(--hba-navy);
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }
        .step-panel { display: none; }
        .step-panel.active { display: block; }

        .card-custom {
            border: none;
            border-radius: 25px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.1);
        }

        .qr-box { 
            border: none !important; 
            border-radius: 25px; 
            overflow: hidden; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            background: white;
        }

        .btn-gold { background: var(--hba-gold); color: white; border: none; border-radius: 15px; padding: 12px; font-weight: 600; transition: all 0.3s; }
        .btn-gold:hover { background: var(--hba-dark-gold); color: white; transform: translateY(-2px); }
        
        .divider { 
            display: flex; 
            align-items: center; 
            text-align: center; 
            color: #888; 
            margin: 25px 0; 
            font-size: 0.8rem; 
        }
        .divider::before, .divider::after { 
            content: ''; 
            flex: 1; 
            border-bottom: 1px solid #ddd; 
        }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        .summary-box { 
            background: #fff9f0; 
            border: 1px solid #ffeeba;
            border-radius: 20px; 
            padding: 25px; 
        }

        .step-badge {
            background-color: var(--hba-gold);
            color: white;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4 pb-5">
    
    <div id="step-checkin" class="step-panel active animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <div class="step-badge font-prompt">ขั้นตอนที่ 1/3</div>
            <h4 class="font-prompt fw-bold text-navy">Check-in @ Booth</h4>
            <p class="text-muted small">สแกนคิวอาร์ (สีฟ้า) หรือค้นหาเบอร์โทรลูกค้า</p>
        </div>
        
        <div id="reader-blue" class="qr-box mb-4"></div>
        
        <div class="divider font-prompt text-uppercase">หรือค้นหาด้วยเบอร์โทรศัพท์</div>

        <div class="card card-custom">
            <div class="card-body p-4 text-center">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                    <input type="tel" id="input-phone" class="form-control bg-light border-0" placeholder="ระบุเบอร์โทรศัพท์ลูกค้า" maxlength="10">
                    <button class="btn btn-gold px-4 font-prompt" onclick="checkInByPhone()">ค้นหา</button>
                </div>
            </div>
        </div>
    </div>

    <div id="step-order" class="step-panel animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <div class="step-badge font-prompt">ขั้นตอนที่ 2/3</div>
            <h4 class="font-prompt fw-bold text-navy">Take Order</h4>
            <p class="text-muted small">ระบุรายละเอียดสัญญาและตรวจสอบสิทธิ์ส่วนลด</p>
        </div>

        <div class="card card-custom">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label font-prompt fw-bold">รายการปลูกสร้าง</label>
                    <input type="text" id="order_detail" class="form-control form-control-lg border-light bg-light font-prompt" placeholder="แบบบ้าน/โครงการ">
                </div>
                <div class="mb-4">
                    <label class="form-label font-prompt fw-bold">มูลค่าสัญญา (บาท)</label>
                    <input type="number" id="order_value" class="form-control form-control-lg fw-bold text-primary border-light bg-light text-center" placeholder="0.00">
                </div>
                
                <div class="divider font-prompt text-uppercase">ส่วนลดจองในงาน</div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <button class="btn btn-outline-secondary btn-action w-100 py-3 font-prompt" onclick="applyCoupon(false)">
                            ไม่ใช้คูปอง
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-gold btn-action w-100 py-3 font-prompt" onclick="showCouponScanner()">
                            สแกนคูปอง (สีเหลือง)
                        </button>
                    </div>
                </div>

                <div id="manual-coupon-section">
                    <div class="divider font-prompt text-uppercase" style="font-size: 0.7rem; margin: 15px 0;">หรือระบุเบอร์โทรเพื่อใช้สิทธิ์</div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-ticket-alt text-muted"></i></span>
                        <input type="tel" id="coupon-phone" class="form-control bg-light border-0" placeholder="เบอร์โทรที่ใช้รับสิทธิ์" maxlength="10">
                        <button class="btn btn-gold px-3 font-prompt" onclick="applyCouponByPhone()">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="reader-yellow" class="qr-box mt-4" style="display:none;"></div>
    </div>

    <div id="step-summary" class="step-panel animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <div class="step-badge font-prompt bg-success">ขั้นตอนที่ 3/3</div>
            <h4 class="font-prompt fw-bold">สรุปรายการ</h4>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-body summary-box">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">ชื่อลูกค้า:</span>
                    <span id="sum-name" class="fw-bold font-prompt text-navy"></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">เบอร์โทรศัพท์:</span>
                    <span id="sum-phone" class="fw-bold font-prompt"></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">มูลค่าโครงการ:</span>
                    <span id="sum-value" class="fw-bold"></span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-danger">
                    <span>ส่วนลดคูปอง:</span>
                    <span id="sum-discount" class="fw-bold"></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold font-prompt mb-0">มูลค่าสุทธิ:</h5>
                    <h4 id="sum-net" class="fw-bold font-prompt text-success mb-0"></h4>
                </div>
            </div>
        </div>

        <button class="btn btn-gold w-100 py-3 shadow-sm font-prompt" onclick="confirmOrder()">
            <i class="fas fa-check-circle me-2"></i> ยืนยันการบันทึกสำเร็จ
        </button>
        <div class="text-center mt-3">
            <button class="btn btn-link text-muted text-decoration-none small font-prompt" onclick="location.reload()">
                ยกเลิกและเริ่มใหม่
            </button>
        </div>
    </div>
</div>

<script>
let orderData = { visitor_id: '', name: '', phone: '', detail: '', value: 0, discount: 0 };

const scannerBlue = new Html5QrcodeScanner("reader-blue", { fps: 15, qrbox: 250 });
scannerBlue.render(data => fetchVisitor(data));

function checkInByPhone() {
    const phoneInput = document.getElementById('input-phone').value;
    if(phoneInput.length < 9) return alert('กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง');
    fetchVisitor(phoneInput);
}

function fetchVisitor(query) {
    fetch('../api/get_visitor_info.php?query=' + encodeURIComponent(query))
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            orderData.visitor_id = data.id;
            orderData.name = data.name;
            orderData.phone = data.phone;
            scannerBlue.clear();
            switchStep('order');
        } else alert(data.message);
    });
}

function applyCoupon(use) {
    orderData.discount = use ? 10000 : 0;
    switchStep('summary');
}

function showCouponScanner() {
    document.getElementById('reader-yellow').style.display = 'block';
    const scannerYellow = new Html5QrcodeScanner("reader-yellow", { fps: 15, qrbox: 250 });
    scannerYellow.render(qr => {
        orderData.discount = 10000; // ส่วนลด 10,000 บาท
        scannerYellow.clear();
        switchStep('summary');
    });
}

function applyCouponByPhone() {
    const phoneInput = document.getElementById('coupon-phone').value;
    if(phoneInput.length < 9) return alert('กรุณากรอกเบอร์โทรศัพท์');
    
    // ตรวจสอบสิทธิ์ว่าตรงกับลูกค้าที่ Check-in หรือไม่
    if(phoneInput === orderData.phone) {
        orderData.discount = 10000;
        alert('ยืนยันสิทธิ์ส่วนลด 10,000 บาท');
        switchStep('summary');
    } else {
        alert('เบอร์โทรศัพท์ไม่ตรงกับข้อมูล Check-in');
    }
}

function switchStep(id) {
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step-' + id).classList.add('active');
    if(id === 'summary') renderSummary();
}

function renderSummary() {
    orderData.detail = document.getElementById('order_detail').value;
    orderData.value = parseFloat(document.getElementById('order_value').value) || 0;
    document.getElementById('sum-name').innerText = orderData.name;
    document.getElementById('sum-phone').innerText = 'เบอร์โทร: ' + orderData.phone;
    document.getElementById('sum-detail').innerText = orderData.detail || 'ไม่ระบุ';
    document.getElementById('sum-value').innerText = orderData.value.toLocaleString() + ' บาท';
    document.getElementById('sum-discount').innerText = '-' + orderData.discount.toLocaleString() + ' บาท';
    document.getElementById('sum-net').innerText = (orderData.value - orderData.discount).toLocaleString() + ' บาท';
}

function confirmOrder() {
    fetch('../api/save_transaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(orderData)
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('บันทึกยอดจองเรียบร้อยแล้ว!');
            window.location.href = '../dashboard.php';
        } else alert(data.message);
    });
}
</script>
</body>
</html>