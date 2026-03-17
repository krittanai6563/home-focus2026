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
    <title>Take Order & Redeem - <?php echo htmlspecialchars($company_name); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        /* ---------------------------------------------------
           ดีไซน์ใหม่ โทนสีเขียว-ทอง (Green & Gold Theme)
        --------------------------------------------------- */
        :root {
            --theme-dark: #0f3a24;     /* เขียวเข้มจัด (Forest Green) */
            --theme-main: #185a3a;     /* เขียวหลัก */
            --theme-light: #248255;    /* เขียวสว่าง */
            --theme-bg: #f2f7f4;       /* พื้นหลังอมเขียวอ่อนมากๆ */
            --theme-gold: #f39c12;     /* สีทองคูปอง */
            --theme-gold-dark: #d68910;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--theme-bg);
            color: var(--theme-dark);
        }

        .font-prompt { font-family: 'Prompt', sans-serif; }

        .card-custom {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(24, 90, 58, 0.08); /* เงาอมเขียว */
        }

        .btn-action {
            border-radius: 15px;
            padding: 12px;
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-theme { background-color: var(--theme-main); color: white; border: none; }
        .btn-theme:hover { background-color: var(--theme-light); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(36, 130, 85, 0.3); }
        
        .btn-gold { background-color: var(--theme-gold); color: white; border: none; }
        .btn-gold:hover { background-color: var(--theme-gold-dark); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3); }

        .qr-box { 
            border: none !important; 
            border-radius: 25px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(24, 90, 58, 0.1); 
            background: white;
            border: 3px solid var(--theme-light) !important; /* ขอบกล้องสีเขียว */
        }

        .divider { 
            display: flex; align-items: center; text-align: center; 
            color: #7b9c8b; margin: 25px 0; font-size: 0.85rem; 
        }
        .divider::before, .divider::after { 
            content: ''; flex: 1; border-bottom: 1px solid #d1e0d7; 
        }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        .step-panel { display: none; }
        .step-panel.active { display: block; }

        .step-badge {
            background-color: rgba(36, 130, 85, 0.1);
            color: var(--theme-light);
            border: 1px solid rgba(36, 130, 85, 0.25);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 12px;
        }

        .summary-box { 
            background: #fdfaf4; /* พื้นหลังอมเหลืองทองนิดๆ */
            border: 1px solid #f9e79f;
            border-radius: 15px; 
            padding: 20px; 
        }
        
        .input-bg-light { background-color: #f0f4f2; border: 1px solid transparent; }
        .input-bg-light:focus { background-color: #e6eee9; box-shadow: none; border: 1px solid var(--theme-light); }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4 pb-5" style="max-width: 600px;">
    
    <div id="step-checkin" class="step-panel active animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <div class="step-badge font-prompt">ขั้นตอนที่ 1/3</div>
            <h4 class="font-prompt fw-bold" style="color: var(--theme-dark);">ดึงข้อมูลลูกค้าเพื่อเปิดบิล</h4>
            <p class="text-muted small">สแกนคิวอาร์โค้ดหรือค้นหาจากเบอร์โทร</p>
        </div>
        
        <div id="reader-blue" class="qr-box mb-4"></div>
        
        <div class="divider font-prompt text-uppercase">หรือค้นหาด้วยเบอร์โทรศัพท์</div>

        <div class="card card-custom border-0" style="border-top: 4px solid var(--theme-light) !important;">
            <div class="card-body p-4 text-center">
                <label class="form-label small font-prompt fw-bold text-muted text-start w-100">กรอกเบอร์โทรศัพท์ลูกค้า (กรณีสแกนไม่ได้)</label>
                <div class="input-group">
                    <span class="input-group-text input-bg-light"><i class="fas fa-phone" style="color: var(--theme-light);"></i></span>
                    <input type="tel" id="input-phone" class="form-control input-bg-light font-prompt" placeholder="ระบุเบอร์โทร 10 หลัก" maxlength="10">
                    <button class="btn btn-theme px-4 font-prompt fw-bold" onclick="checkInByPhone()">ค้นหา</button>
                </div>
            </div>
        </div>
    </div>

    <div id="step-order" class="step-panel animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <div class="step-badge font-prompt">ขั้นตอนที่ 2/3</div>
            <h4 class="font-prompt fw-bold" style="color: var(--theme-dark);">ระบุยอดจอง (Take Order)</h4>
            <p class="text-muted small">ระบุรายละเอียดสัญญาและตรวจสอบสิทธิ์ส่วนลด</p>
        </div>

        <div class="card card-custom">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label font-prompt fw-bold" style="color: var(--theme-main);">รายการปลูกสร้าง</label>
                    <input type="text" id="order_detail" class="form-control form-control-lg input-bg-light font-prompt" placeholder="แบบบ้าน / โครงการ">
                </div>
                <div class="mb-4">
                    <label class="form-label font-prompt fw-bold" style="color: var(--theme-main);">มูลค่าสัญญา (บาท)</label>
                   <input type="text" id="order_value" class="form-control form-control-lg fw-bold input-bg-light text-center" style="color: var(--theme-dark);" placeholder="0" oninput="formatNumber(this)">
                </div>
                
                <div class="divider font-prompt text-uppercase">รับสิทธิ์ส่วนลดคูปองในงาน</div>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <button class="btn btn-light btn-action w-100 py-3 font-prompt border" onclick="applyCoupon(false)">
                            ไม่ใช้คูปอง
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-gold btn-action w-100 py-3 font-prompt shadow-sm" onclick="showCouponScanner()">
                            <i class="fas fa-qrcode me-1"></i> สแกนคูปอง
                        </button>
                    </div>
                </div>

                <div id="manual-coupon-section">
                    <div class="divider font-prompt text-uppercase" style="font-size: 0.75rem; margin: 15px 0;">หรือระบุเบอร์โทรเพื่อใช้สิทธิ์ส่วนลด</div>
                    <div class="input-group">
                        <span class="input-group-text input-bg-light"><i class="fas fa-ticket-alt text-muted"></i></span>
                        <input type="tel" id="coupon-phone" class="form-control input-bg-light font-prompt" placeholder="เบอร์โทรที่ใช้รับสิทธิ์" maxlength="10">
                        <button class="btn btn-gold px-3 font-prompt fw-bold" onclick="applyCouponByPhone()">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="reader-yellow" class="qr-box mt-4" style="display:none; border-color: var(--theme-gold) !important;"></div>
        
        <div class="text-center mt-4">
            <button class="btn btn-link text-muted text-decoration-none small font-prompt" onclick="location.reload()">
                <i class="fas fa-arrow-left me-1"></i> ยกเลิกและกลับไปหน้าแรก
            </button>
        </div>
    </div>

    <div id="step-summary" class="step-panel animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <div class="step-badge font-prompt" style="background-color: var(--theme-gold); color: white; border: none;">ขั้นตอนที่ 3/3</div>
            <h4 class="font-prompt fw-bold" style="color: var(--theme-dark);">ตรวจสอบยอดและสรุปรายการ</h4>
        </div>

        <div class="card card-custom mb-4 border-0">
            <div class="card-body summary-box">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">ชื่อลูกค้า:</span>
                    <span id="sum-name" class="fw-bold font-prompt" style="color: var(--theme-dark);"></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">เบอร์โทรศัพท์:</span>
                    <span id="sum-phone" class="fw-bold font-prompt" style="color: var(--theme-dark);"></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">รายการ:</span>
                    <span id="sum-detail" class="fw-bold font-prompt" style="color: var(--theme-dark);"></span>
                </div>
                <hr style="border-color: #e5d19c;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">มูลค่าโครงการ:</span>
                    <span id="sum-value" class="fw-bold"></span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-danger">
                    <span class="small">ส่วนลดคูปอง:</span>
                    <span id="sum-discount" class="fw-bold"></span>
                </div>
                <hr style="border-color: #e5d19c;">
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mt-3" style="border-left: 5px solid var(--theme-main);">
                    <h5 class="fw-bold font-prompt mb-0" style="color: var(--theme-dark);">ยอดสุทธิ:</h5>
                    <h3 id="sum-net" class="fw-bold font-prompt mb-0" style="color: var(--theme-main);"></h3>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-center">
            <button class="btn btn-theme btn-action w-100 shadow-sm font-prompt fs-5" onclick="confirmOrder()">
                <i class="fas fa-check-circle me-2"></i> ยืนยันการทำรายการ
            </button>
        </div>
        <div class="text-center mt-3">
            <button class="btn btn-link text-muted text-decoration-none small font-prompt" onclick="switchStep('order')">
                <i class="fas fa-edit me-1"></i> กลับไปแก้ไขข้อมูล
            </button>
        </div>
    </div>
</div>

<script>
// เพิ่ม has_used_coupon ไว้ในตัวแปรเพื่อเก็บประวัติ
let orderData = { visitor_id: '', name: '', phone: '', detail: '', value: 0, discount: 0, net_value: 0, has_used_coupon: false };

const scannerBlue = new Html5QrcodeScanner("reader-blue", { fps: 15, qrbox: 250 });
scannerBlue.render(data => fetchVisitor(data));

function checkInByPhone() {
    const phoneInput = document.getElementById('input-phone').value;
    if(phoneInput.length < 9) return alert('กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง');
    fetchVisitor(phoneInput);
}

function fetchVisitor(query) {
    fetch('../api/get_visitor_info.php?qr_data=' + encodeURIComponent(query))
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // เงื่อนไขที่ 1: ดักจับถ้ายังไม่ได้เช็คอินเข้างาน (สแกนหน้าประตู)
            if (data.check_in_status === 0) {
                alert('แจ้งเตือน: ท่านยังไม่ได้เช็คอินเข้างาน (กรุณาเช็คอินที่หน้าประตูก่อนครับ)');
                return; 
            }

            // === เงื่อนไขที่ 2: ดักจับตั้งแต่หน้าแรก ถ้าลูกค้ารายนี้เคยใช้คูปองไปแล้ว ===
            if (data.has_used_coupon === true) {
                alert('คุณเคยใช้คูปองครบจำนวนไปแล้ว (ไม่สามารถเปิดบิลรับส่วนลดซ้ำได้)');
                return; // สั่ง return เพื่อบล็อกไม่ให้ข้ามไปหน้า 2
            }
            
            // ถ้าผ่านทั้ง 2 เงื่อนไข ถึงจะยอมให้ดึงข้อมูลไปเปิดบิลในหน้า 2
            orderData.visitor_id = data.id;
            orderData.name = data.name;
            orderData.phone = data.phone;
            orderData.has_used_coupon = data.has_used_coupon; 

            scannerBlue.clear();
            switchStep('order');
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error("Error Fetching Data:", err);
        alert("เกิดข้อผิดพลาดในการดึงข้อมูลจากระบบ");
    });
}

function formatNumber(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (val !== '') {
        input.value = parseInt(val, 10).toLocaleString('th-TH');
    } else {
        input.value = '';
    }
}

function validateOrderInput() {
    const detail = document.getElementById('order_detail').value.trim();
    const rawValue = document.getElementById('order_value').value.replace(/,/g, '');
    const val = parseFloat(rawValue);
    
    if(!detail) {
        alert("กรุณาระบุรายการปลูกสร้าง/แบบบ้าน");
        document.getElementById('order_detail').focus();
        return false;
    }
    if(isNaN(val) || val <= 0) {
        alert("กรุณาระบุมูลค่าสัญญาให้ถูกต้อง");
        document.getElementById('order_value').focus();
        return false;
    }
    return true;
}

function applyCoupon(use) {
    if(!validateOrderInput()) return; 
    
    if(use) {
        // ดักจับถ้าเคยกดใช้คูปองไปแล้ว
        if (orderData.has_used_coupon === true) {
            alert('คุณเคยใช้คูปองครบจำนวนไปแล้ว');
            return;
        }
        orderData.discount = 10000;
    } else {
        orderData.discount = 0;
    }
    switchStep('summary');
}

function showCouponScanner() {
    if(!validateOrderInput()) return; 
    
    // === ดักจับ: กรณีสแกนคิวอาร์คูปองสีเหลือง ===
    if (orderData.has_used_coupon === true) {
        alert('คุณเคยใช้คูปองครบจำนวนไปแล้ว');
        return;
    }
    
    document.getElementById('reader-yellow').style.display = 'block';
    document.getElementById('reader-yellow').scrollIntoView({ behavior: 'smooth' });
    
    const scannerYellow = new Html5QrcodeScanner("reader-yellow", { fps: 15, qrbox: 250 });
    scannerYellow.render(qr => {
        orderData.discount = 10000;
        scannerYellow.clear();
        document.getElementById('reader-yellow').style.display = 'none';
        switchStep('summary');
    });
}

function applyCouponByPhone() {
    if(!validateOrderInput()) return; 
    
    const phoneInput = document.getElementById('coupon-phone').value;
    if(phoneInput.length < 9) return alert('กรุณากรอกเบอร์โทรศัพท์สำหรับรับสิทธิ์ส่วนลด');
    
    if(phoneInput !== orderData.phone) {
        return alert('เบอร์โทรศัพท์ไม่ตรงกับข้อมูลลูกค้ารายนี้ที่ทำการ Check-in ไว้');
    }

    // === ดักจับ: กรณีกรอกเบอร์โทรเพื่อรับคูปอง ===
    if (orderData.has_used_coupon === true) {
        alert('คุณเคยใช้คูปองครบจำนวนไปแล้ว');
        return;
    }
    
    orderData.discount = 10000;
    switchStep('summary');
}

function switchStep(id) {
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step-' + id).classList.add('active');
    if(id === 'summary') renderSummary();
}

function renderSummary() {
    orderData.detail = document.getElementById('order_detail').value.trim();
    
    const rawValue = document.getElementById('order_value').value.replace(/,/g, '');
    orderData.value = parseFloat(rawValue) || 0;
    orderData.net_value = orderData.value - orderData.discount;
    
    document.getElementById('sum-name').innerText = orderData.name;
    document.getElementById('sum-phone').innerText = orderData.phone;
    document.getElementById('sum-detail').innerText = orderData.detail || 'ไม่ระบุ';
    
    document.getElementById('sum-value').innerText = orderData.value.toLocaleString() + ' บาท';
    document.getElementById('sum-discount').innerText = '-' + orderData.discount.toLocaleString() + ' บาท';
    document.getElementById('sum-net').innerText = orderData.net_value.toLocaleString() + ' บาท';
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
            window.location.href = 'dashboard.php';
        } else {
            alert(data.message);
        }
    });
}
</script>
</body>
</html>