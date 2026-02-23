<?php 
require_once '../includes/auth.php'; 
require_once '../config/db_config.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน Walk-in - Home Focus 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --hba-navy: #003366; --hba-blue: #0056b3; --hba-light: #f8fafd; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--hba-light); color: var(--hba-navy); }
        .font-prompt { font-family: 'Prompt', sans-serif; }
        .card-register { border: none; border-radius: 25px; box-shadow: 0 15px 40px rgba(0, 51, 102, 0.1); }
        h3 { font-family: 'Prompt', sans-serif; font-size: 1.1rem; font-weight: 600; margin: 25px 0 15px; display: flex; align-items: center; gap: 10px; color: var(--hba-navy); }
        .form-label { font-weight: 600; font-size: 0.85rem; margin-bottom: 5px; color: #444; }
        .form-control, .form-select { border-radius: 12px; padding: 10px 15px; border: 1px solid #e0e0e0; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; background: white; padding: 15px; border-radius: 16px; border: 1px solid #e0e0e0; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-submit { background: linear-gradient(45deg, var(--hba-navy), var(--hba-blue)); color: white; border: none; border-radius: 15px; padding: 15px; font-weight: 600; width: 100%; margin-top: 30px; transition: 0.3s; }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 51, 102, 0.2); }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="card card-register animate__animated animate__fadeIn">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h2 class="font-prompt fw-bold">ลงทะเบียนผู้เยี่ยมชม</h2>
                <p class="text-muted small">บันทึกข้อมูลลูกค้าเข้าสู่ระบบลีดของบริษัท</p>
            </div>

            <form id="walkinForm" onsubmit="submitWalkIn(event)">
                
                <h3><i class="fas fa-user-circle"></i> ข้อมูลส่วนตัว</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อ - นามสกุล *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="ระบุชื่อจริงและนามสกุล" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" placeholder="example@email.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์ *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="08XXXXXXXX" required maxlength="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อายุ</label>
                        <select name="age_range" class="form-select">
                            <option value="">-- เลือกช่วงอายุ --</option>
                            <option value="ต่ำกว่า 30 ปี">ต่ำกว่า 30 ปี</option>
                            <option value="31 - 40 ปี">31 - 40 ปี</option>
                            <option value="41 - 50 ปี">41 - 50 ปี</option>
                            <option value="51 ปีขึ้นไป">51 ปีขึ้นไป</option>
                        </select>
                    </div>
                </div>

                <h3><i class="fas fa-bullhorn"></i> ช่องทางการรับรู้</h3>
                <div class="checkbox-grid">
                    <label class="checkbox-item"><input type="checkbox" name="channel[]" value="Facebook"> Facebook</label>
                    <label class="checkbox-item"><input type="checkbox" name="channel[]" value="Google"> Google</label>
                    <label class="checkbox-item"><input type="checkbox" name="channel[]" value="Youtube"> Youtube</label>
                    <label class="checkbox-item"><input type="checkbox" name="channel[]" value="Website"> Website</label>
                    <label class="checkbox-item"><input type="checkbox" name="channel[]" value="Line"> Line</label>
                    <label class="checkbox-item"><input type="checkbox" name="channel[]" value="TikTok"> TikTok</label>
                    <label class="checkbox-item" style="background: #fffbeb; border-radius: 8px;">
                        <input type="checkbox" id="chk-offline" name="channel[]" value="สื่ออื่นๆ" onchange="toggleOffline(this)"> สื่ออื่นๆ
                    </label>
                </div>

                <div id="offline-option-box" style="display: none; margin-top: 15px; padding: 20px; background: #fffbeb; border-radius: 16px; border: 1px dashed #fcd34d;">
                    <label class="form-label" style="color: #b45309;">โปรดระบุสื่อที่ท่านพบเห็น:</label>
                    <select name="offline_source" id="offline-select" class="form-select" style="border-color: #fcd34d;">
                        <option value="">-- กรุณาเลือก --</option>
                        <option value="วิทยุ">วิทยุ</option>
                        <option value="โทรทัศน์">โทรทัศน์</option>
                        <option value="ป้ายบิลบอร์ด">ป้ายบิลบอร์ด</option>
                    </select>
                </div>

                <h3><i class="fas fa-home"></i> ความต้องการเรื่องบ้าน</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ภูมิภาคที่ต้องการสร้างบ้าน</label>
                        <select name="region" class="form-select">
                            <option value="">-- เลือกภูมิภาค --</option>
                            <option value="กรุงเทพฯ และปริมณฑล">กรุงเทพฯ และปริมณฑล</option>
                            <option value="ภาคกลาง">ภาคกลาง</option>
                            <option value="ภาคเหนือ">ภาคเหนือ</option>
                            <option value="ภาคตะวันออกเฉียงเหนือ">ภาคตะวันออกเฉียงเหนือ</option>
                            <option value="ภาคใต้">ภาคใต้</option>
                            <option value="ภาคตะวันออก">ภาคตะวันออก</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">จำนวนชั้น</label>
                        <select name="floors" class="form-select">
                            <option value="">-- เลือกจำนวนชั้น --</option>
                            <option value="1 ชั้น">1 ชั้น</option>
                            <option value="2 ชั้น">2 ชั้น</option>
                            <option value="3 ชั้นขึ้นไป">3 ชั้นขึ้นไป</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">พื้นที่ใช้สอย (ตร.ม)</label>
                        <select name="usable_area" class="form-select">
                            <option value="">-- ระบุพื้นที่ใช้สอย --</option>
                            <option value="น้อยกว่า 100 ตร.ม.">น้อยกว่า 100 ตร.ม.</option>
                            <option value="101 - 200 ตร.ม.">101 - 200 ตร.ม.</option>
                            <option value="201 - 300 ตร.ม.">201 - 300 ตร.ม.</option>
                            <option value="มากกว่า 300 ตร.ม.">มากกว่า 300 ตร.ม.</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">งบประมาณการก่อสร้าง</label>
                        <select name="budget" class="form-select">
                            <option value="">-- ระบุงบประมาณ --</option>
                            <option value="ไม่เกิน 2 ล้านบาท">ไม่เกิน 2 ล้านบาท</option>
                            <option value="2 - 5 ล้านบาท">2 - 5 ล้านบาท</option>
                            <option value="5 - 10 ล้านบาท">5 - 10 ล้านบาท</option>
                            <option value="10 ล้านบาทขึ้นไป">10 ล้านบาทขึ้นไป</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วัตถุประสงค์ในการมางาน</label>
                        <select name="objective" class="form-select">
                            <option value="">-- ระบุวัตถุประสงค์ --</option>
                            <option value="มองหาบริษัทรับสร้างบ้าน">มองหาบริษัทรับสร้างบ้าน</option>
                            <option value="ดูแบบบ้าน/หาไอเดีย">ดูแบบบ้าน/หาไอเดีย</option>
                            <option value="ปรึกษาสินเชื่อ">ปรึกษาสินเชื่อ</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ระยะเวลาการตัดสินใจ</label>
                        <select name="decision_time" class="form-select">
                            <option value="">-- ระบุระยะเวลา --</option>
                            <option value="ภายใน 3 เดือน">ภายใน 3 เดือน</option>
                            <option value="ภายใน 6 เดือน">ภายใน 6 เดือน</option>
                            <option value="ภายใน 1 ปี">ภายใน 1 ปี</option>
                            <option value="ยังไม่มีกำหนด">ยังไม่มีกำหนด</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 25px; display: flex; gap: 12px; align-items: start; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <input type="checkbox" name="consent" required style="width: 20px; height: 20px; margin-top: 3px;">
                    <label style="font-size: 13px; color: #64748b; line-height: 1.6;">
                        ข้าพเจ้าได้อ่านและยอมรับ <a href="#" class="text-primary fw-bold">นโยบายความเป็นส่วนตัว</a> และยินยอมให้ประมวลผลข้อมูลเพื่อนำเสนอข่าวสารและสิทธิประโยชน์ทางการตลาด
                    </label>
                </div>

                <button type="submit" class="btn-submit font-prompt">
                    <i class="fas fa-check-circle me-2"></i> ยืนยันการลงทะเบียน
                </button>
            </form>
            <div id="responseMsg" class="mt-3"></div>
        </div>
    </div>
</div>

<script>
function toggleOffline(chk) {
    document.getElementById('offline-option-box').style.display = chk.checked ? 'block' : 'none';
}

function submitWalkIn(event) {
    event.preventDefault();
    const btn = event.target.querySelector('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...';

    const formData = new FormData(event.target);

    fetch('api/save_walkin_full.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const msgDiv = document.getElementById('responseMsg');
        if(data.success) {
            msgDiv.innerHTML = `<div class="alert alert-success border-0 rounded-4 p-3 animate__animated animate__fadeIn"><i class="fas fa-check-circle me-2"></i>ลงทะเบียนและบันทึก Lead สำเร็จ!</div>`;
            setTimeout(() => window.location.href = 'dashboard.php', 2000);
        } else {
            msgDiv.innerHTML = `<div class="alert alert-danger border-0 rounded-4 p-3">${data.message}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-2"></i> ยืนยันการลงทะเบียน';
        }
    })
    .catch(error => {
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        btn.disabled = false;
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>