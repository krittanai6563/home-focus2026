<?php 
require_once '../config/db_config.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนเข้าร่วมงาน - Home Focus 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --hba-navy: #003366; --hba-blue: #0056b3; --hba-light: #f8fafd; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--hba-light); color: var(--hba-navy); }
        .font-prompt { font-family: 'Prompt', sans-serif; }
        .card-register { border: none; border-radius: 25px; box-shadow: 0 15px 40px rgba(0, 51, 102, 0.1); margin-top: 2rem; }
        h3 { font-family: 'Prompt', sans-serif; font-size: 1.1rem; font-weight: 600; margin: 25px 0 15px; display: flex; align-items: center; gap: 10px; color: var(--hba-navy); }
        .form-label { font-weight: 600; font-size: 0.85rem; margin-bottom: 5px; color: #444; }
        .form-control, .form-select { border-radius: 12px; padding: 10px 15px; border: 1px solid #e0e0e0; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; background: white; padding: 15px; border-radius: 16px; border: 1px solid #e0e0e0; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-submit { background: linear-gradient(45deg, var(--hba-navy), var(--hba-blue)); color: white; border: none; border-radius: 15px; padding: 15px; font-weight: 600; width: 100%; margin-top: 30px; transition: 0.3s; }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 51, 102, 0.2); }
        .popup-content { text-align: left; font-size: 0.95rem; line-height: 1.6; color: #333; }
    </style>
</head>
<body>

<div class="container py-2 pb-5">
    <div class="card card-register animate__animated animate__fadeIn">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h2 class="font-prompt fw-bold" style="color: var(--hba-navy);">ลงทะเบียนเข้าร่วมงาน FOCUS 2026</h2>
                <p class="text-muted small">กรุณากรอกข้อมูลเพื่อรับสิทธิพิเศษและ QR Code สำหรับสแกนเข้างาน</p>
            </div>

            <form id="walkinForm" onsubmit="submitWalkIn(event)" novalidate>
                
                <h3><i class="fas fa-user-circle"></i> ข้อมูลส่วนตัว</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อ - นามสกุล *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="ระบุชื่อจริงและนามสกุล">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อีเมล *</label>
                        <input type="email" name="email" class="form-control" placeholder="example@email.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์ *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="08XXXXXXXX" maxlength="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อายุ *</label>
                        <select name="age_range" class="form-select">
                            <option value="">-- เลือกช่วงอายุ --</option>
                            <option value="ต่ำกว่า 30 ปี">ต่ำกว่า 30 ปี</option>
                            <option value="31 - 40 ปี">31 - 40 ปี</option>
                            <option value="41 - 50 ปี">41 - 50 ปี</option>
                            <option value="51 ปีขึ้นไป">51 ปีขึ้นไป</option>
                        </select>
                    </div>
                </div>

                <h3><i class="fas fa-bullhorn"></i> ช่องทางการรับรู้ *</h3>
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
                        <label class="form-label">ภูมิภาคที่ต้องการสร้างบ้าน *</label>
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
                        <label class="form-label">จำนวนชั้น *</label>
                        <select name="floors" class="form-select">
                            <option value="">-- เลือกจำนวนชั้น --</option>
                            <option value="1 ชั้น">1 ชั้น</option>
                            <option value="2 ชั้น">2 ชั้น</option>
                            <option value="3 ชั้นขึ้นไป">3 ชั้นขึ้นไป</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">พื้นที่ใช้สอย (ตร.ม) *</label>
                        <select name="usable_area" class="form-select">
                            <option value="">-- ระบุพื้นที่ใช้สอย --</option>
                            <option value="น้อยกว่า 100 ตร.ม.">น้อยกว่า 100 ตร.ม.</option>
                            <option value="101 - 200 ตร.ม.">101 - 200 ตร.ม.</option>
                            <option value="201 - 300 ตร.ม.">201 - 300 ตร.ม.</option>
                            <option value="มากกว่า 300 ตร.ม.">มากกว่า 300 ตร.ม.</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">งบประมาณการก่อสร้าง *</label>
                        <select name="budget" class="form-select">
                            <option value="">-- ระบุงบประมาณ --</option>
                            <option value="ไม่เกิน 2 ล้านบาท">ไม่เกิน 2 ล้านบาท</option>
                            <option value="2 - 5 ล้านบาท">2 - 5 ล้านบาท</option>
                            <option value="5 - 10 ล้านบาท">5 - 10 ล้านบาท</option>
                            <option value="10 ล้านบาทขึ้นไป">10 ล้านบาทขึ้นไป</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วัตถุประสงค์ในการมางาน *</label>
                        <select name="objective" class="form-select">
                            <option value="">-- ระบุวัตถุประสงค์ --</option>
                            <option value="มองหาบริษัทรับสร้างบ้าน">มองหาบริษัทรับสร้างบ้าน</option>
                            <option value="ดูแบบบ้าน/หาไอเดีย">ดูแบบบ้าน/หาไอเดีย</option>
                            <option value="ปรึกษาสินเชื่อ">ปรึกษาสินเชื่อ</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ระยะเวลาการตัดสินใจ *</label>
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
                    <input type="checkbox" name="consent" id="consent" style="width: 20px; height: 20px; margin-top: 3px;">
                    <label for="consent" style="font-size: 13px; color: #64748b; line-height: 1.6; cursor: pointer;">
                        ข้าพเจ้าได้อ่านและยอมรับ <a href="#" onclick="showPrivacyPolicy(event)" class="text-primary fw-bold" style="text-decoration: underline;">นโยบายความเป็นส่วนตัว (Privacy Policy)</a> และยินยอมให้ประมวลผลข้อมูลเพื่อนำเสนอข่าวสารและสิทธิประโยชน์ทางการตลาด *
                    </label>
                </div>
                
                <button type="submit" class="btn-submit font-prompt">
                    <i class="fas fa-check-circle me-2"></i> ยืนยันการลงทะเบียน
                </button>
            </form>
        </div>
    </div>
</div>

<script>

function toggleOffline(chk) {
    document.getElementById('offline-option-box').style.display = chk.checked ? 'block' : 'none';
}

// ฟังก์ชันแสดง Popup นโยบายความเป็นส่วนตัว
function showPrivacyPolicy(event) {
    event.preventDefault();
    const policyHTML = `
        <div style="text-align: left; font-size: 0.9rem; line-height: 1.7; color: #444; max-height: 55vh; overflow-y: auto; padding-right: 15px; font-family: 'Sarabun', sans-serif;">
            <p>สมาคมธุรกิจรับสร้างบ้าน ซึ่งต่อไปนี้เรียกว่า “สมาคมฯ” อาจเก็บรวบรวมข้อมูลส่วนบุคคลของท่าน โดยจัดทำเป็นนโยบายความเป็นส่วนตัว (Privacy Policy) ซึ่งจะแจ้งวิธีที่เราจัดการกับข้อมูลส่วนบุคคลของท่าน ดังต่อไปนี้</p>
            <p>“ข้อมูลส่วนบุคคล” คือ ข้อมูลเกี่ยวกับบุคคลซึ่งทำให้สามารถระบุตัวบุคคลนั้นได้ไม่ว่าทางตรงหรือทางอ้อม แต่ไม่รวมถึงข้อมูลของผู้ถึงแก่กรรมโดยเฉพาะ</p>
            
            <strong style="color: #003366;">1. การเก็บรวบรวมข้อมูลส่วนบุคคล</strong>
            <p>สมาคมฯ มีการจัดเก็บข้อมูลส่วนบุคคลของท่านผ่านทางเว็บไซต์ของสมาคมฯ ที่ชอบด้วยกฎหมาย โดยจัดเก็บข้อมูลเท่าที่จำเป็นตามวัตถุประสงค์ของสมาคมฯ จึงจำเป็นที่ต้องแจ้งให้ท่านทราบและขอความยินยอมก่อนเก็บรวบรวมข้อมูลส่วนบุคคลดังกล่าว โดยสมาคมฯ จะเก็บรักษาข้อมูลเหล่านั้นไว้เป็นความลับ การเก็บข้อมูลจากแหล่งอื่นที่ไม่ใช่จากท่านโดยตรง สมาคมฯ จะแจ้งการจัดเก็บให้ท่านทราบไม่เกิน 30 วัน หลังจากการจัดเก็บ<br>
            ท่านสามารถเลือกว่าจะให้ข้อมูลส่วนบุคคลแก่สมาคมฯ หรือไม่ก็ได้ แต่อย่างไรก็ตาม โปรดทราบว่าหากท่านไม่ให้ข้อมูลส่วนบุคคลแก่สมาคมฯ อาจมีบางบริการที่สมาคมฯ ไม่สามารถให้บริการแก่ท่านได้หากปราศจากข้อมูลส่วนบุคคลของท่าน</p>

            <strong style="color: #003366;">2. ข้อมูลที่สมาคมฯ เก็บรวบรวม</strong>
            <p>สมาคมธุรกิจรับสร้างบ้าน อาจเก็บรวบรวมข้อมูลส่วนบุคคลของท่านผ่านหลายช่องทาง เช่น<br>
            2.1 เมื่อท่านติดต่อสอบถามข้อมูล สมาคมฯ อาจขอข้อมูลเกี่ยวกับท่าน เช่น ชื่อ อีเมล เบอร์โทรศัพท์ เป็นต้น<br>
            2.2 สมาคมฯ อาจจัดเก็บบันทึกข้อมูลการเข้าออกเว็บไซต์ (Log Files) ของท่าน โดยจะจัดเก็บข้อมูลดังนี้ หมายเลขไอพี (IP Address) หรือ เวลาการเข้าใช้งาน เป็นต้น<br>
            2.3 คุกกี้ เว็บไซต์ของสมาคมฯ อาจใช้คุกกี้ในบางกรณี คุกกี้ คือไฟล์ข้อมูลขนาดเล็กที่จัดเก็บข้อมูลซึ่งแลกเปลี่ยนระหว่างคอมพิวเตอร์ของท่านและเว็บไซต์ของเรา สมาคมฯ ใช้คุกกี้เฉพาะเพื่อการจัดเก็บข้อมูลที่อาจเป็นประโยชน์ต่อท่านในครั้งถัดไปที่ท่านกลับมาเยี่ยมชมเว็บไซต์ของสมาคมฯ</p>

            <strong style="color: #003366;">3. ระยะเวลาการเก็บข้อมูลส่วนบุคคล</strong>
            <p>สมาคมฯ จะจัดเก็บข้อมูลส่วนบุคคลเกี่ยวกับท่าน เป็นระยะเวลา 3 ปี นับจากวันที่เก็บข้อมูล ตามวัตถุประสงค์ที่ได้แจ้งให้ท่านทราบ โดยมีมาตรการรักษาความปลอดภัยตามสมควรเพื่อป้องกันการเข้าถึง การรวบรวม การใช้ การเปิดเผย การทำสำเนา การดัดแปลง การกำจัดข้อมูล หรือความเสี่ยงในลักษณะเดียวกันโดยไม่ได้รับอนุญาต</p>

            <strong style="color: #003366;">4. วัตถุประสงค์การเก็บข้อมูลส่วนบุคคล</strong>
            <p>ข้อมูลส่วนบุคคลของท่านตามที่สมาคมฯ ได้จัดเก็บ สมาคมฯ จะไม่นำข้อมูลส่วนบุคคลของท่าน ไปดำเนินการอื่นนอกเหนือไปจากวัตถุประสงค์ที่สมาคมฯ ระบุไว้ โดยทางสมาคมฯ เก็บรวบรวมข้อมูลเพื่อวัตถุประสงค์ดังต่อไปนี้<br>
            4.1 เพื่อให้บริการหรือตอบคำถามตามที่ท่านร้องขอ<br>
            4.2 เพื่อรวบรวมข้อมูลเป็นฐานข้อมูลของสมาคมฯ หรือข้อมูลเชิงสถิติเกี่ยวกับจำนวนผู้เยี่ยมชมเว็บไซต์<br>
            4.3 เพื่อรวบรวมข้อมูลเป็นฐานข้อมูลของสมาคมฯ ใช้สำหรับการส่งประชาสัมพันธ์กิจกรรมทางการตลาดของสมาคมฯ<br>
            4.4 การกระทำอื่นที่ท่านให้ความยินยอม<br>
            สมาคมฯ จะใช้ข้อมูลส่วนบุคคลของท่านตามที่ท่านได้ให้มา เพื่อดำเนินการตามความประสงค์ ในการให้บริการตามธุรกรรมระหว่างท่านกับสมาคมฯ โดยทางสมาคมฯ จะไม่นำข้อมูลส่วนบุคคลที่ได้รับจากท่านเพื่อวัตถุประสงค์อื่นใดนอกเหนือจากวัตถุประสงค์ที่ระบุไว้ขณะเก็บรวบรวมข้อมูลดังกล่าว</p>

            <strong style="color: #003366;">5. การเปิดเผยข้อมูลส่วนบุคคล</strong>
            <p>สมาคมฯ จะไม่นำข้อมูลส่วนบุคคลของท่านไปเปิดเผยแก่บุคคลภายนอก เว้นแต่กรณีดังนี้<br>
            5.1 ได้รับความยินยอมจากเจ้าของข้อมูล<br>
            5.2 คำสั่งศาล พนักงานเจ้าหน้าที่ หรือ กฎหมาย ให้เปิดเผยข้อมูลดังกล่าว<br>
            โปรดทราบว่า สมาคมฯ อาจส่งต่อข้อมูลส่วนบุคคลของท่านระหว่างพันธมิตรของสมาคมฯ ด้วยกัน เพื่อการให้บริการแก่ท่านอย่างมีประสิทธิภาพและบรรลุตามวัตถุประสงค์</p>

            <strong style="color: #003366;">6. การรักษาความมั่งคงปลอดภัย</strong>
            <p>ทางสมาคมฯ มีมาตรการในการรักษาความมั่นคงปลอดภัยของข้อมูลส่วนบุคคลที่เหมาะสมเพื่อป้องกันข้อมูลส่วนบุคคลของท่าน ดังนี้<br>
            6.1 สมาคมฯ ป้องกันมิให้มีการ เข้าถึง ทำลาย ใช้ ดัดแปลง แก้ไข หรือเปิดเผยข้อมูลส่วนบุคคลโดยไม่ได้รับอนุญาต<br>
            6.2 สมาคมฯ จำกัดคนเข้าถึงข้อมูลส่วนบุคคล สำหรับบุคคลที่เกี่ยวข้องและจำเป็น<br>
            6.3 ในกรณีที่ สมาคมฯ ว่าจ้างบริษัทอื่น ๆ เพื่อให้บริการในนามของสมาคมฯ จะมีการลงนามในข้อตกลงรักษาข้อมูล<br>
            6.4 การเข้ารหัส สมาคมฯ ใช้การเข้ารหัสแบบ Secure Sockets Layer (SSL)</p>

            <strong style="color: #003366;">7. สิทธิของเจ้าของข้อมูล</strong>
            <p>เจ้าของข้อมูลส่วนบุคคลมีสิทธิดังต่อไปนี้ โดยสามารถแจ้งให้ทางสมาคมฯ ทราบเป็นลายลักษณ์อักษร หรือผ่านทางอีเมล info@hba-th.org เพื่อแจ้งความประสงค์ดังกล่าว<br>
            7.1 สิทธิเพิกถอนความยินยอม<br>
            7.2 สิทธิในการขอเข้าถึงและขอรับสำเนาข้อมูลส่วนบุคคล<br>
            7.3 สิทธิขอให้เปิดเผยถึงการได้มาซึ่งข้อมูลส่วนบุคคลที่ไม่ได้ให้ความยินยอม<br>
            7.4 สิทธิคัดค้านการเก็บรวบรวม ใช้ หรือ เปิดเผยข้อมูลส่วนบุคคล<br>
            7.5 สิทธิขอให้ลบ ทำลาย หรือทำให้ไม่สามารถระบุตัวบุคคล<br>
            7.6 สิทธิขอให้ระงับการใช้ข้อมูลส่วนบุคคล<br>
            7.7 สิทธิขอให้แก้ไขข้อมูลส่วนบุคคลให้ถูกต้อง<br>
            7.8 สิทธิขอให้ห้ามหรือคัดค้านประมวลผลข้อมูลส่วนบุคคล<br>
            7.9 สิทธิขอให้โอนย้ายข้อมูลส่วนบุคคล</p>

            <strong style="color: #003366;">8. ช่องทางการติดต่อ</strong>
            <p>สมาคมธุรกิจรับสร้างบ้าน<br>
            เลขที่ 2 ซอยลาดปลาเค้า 10 แขวงลาดพร้าว เขตลาดพร้าว กรุงเทพฯ 10230<br>
            โทรศัพท์ : 0-2570-0153, 0-2940-2744<br>
            อีเมล : info@hba-th.org <br>
            เว็บไซต์ : http://www.hba-th.org</p>
            <p style="font-size: 0.8rem; color: #888;">วันที่แก้ไขล่าสุด 23 กรกฎาคม 2568</p>
        </div>
    `;

    Swal.fire({
        title: '<span style="font-family: \'Prompt\', sans-serif; color: #003366; font-size: 1.3rem;">นโยบายความเป็นส่วนตัว (Privacy Policy)</span>',
        html: policyHTML,
        width: '750px',
        confirmButtonText: '<i class="fas fa-check"></i> รับทราบและปิดหน้าต่าง',
        confirmButtonColor: '#003366'
    });
}

// ฟังก์ชันสำหรับดาวน์โหลด QR Code
function downloadQRCode(url, filename) {
    const btn = document.getElementById('btn-download-qr');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังเตรียมไฟล์...';
    btn.disabled = true;

    fetch(url)
        .then(response => response.blob())
        .then(blob => {
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
            
            btn.innerHTML = '<i class="fas fa-check"></i> โหลดสำเร็จ';
            setTimeout(() => { 
                btn.innerHTML = originalText; 
                btn.disabled = false; 
            }, 2000);
        })
        .catch(error => {
            window.open(url, '_blank');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

function submitWalkIn(event) {
    event.preventDefault();
    const form = event.target;
    
    let missingFields = [];

    if (!form.full_name.value.trim()) missingFields.push('ชื่อ - นามสกุล');
    if (!form.email.value.trim()) missingFields.push('อีเมล');
    if (!form.phone.value.trim()) missingFields.push('เบอร์โทรศัพท์');
    if (!form.age_range.value) missingFields.push('อายุ');

    // ตรวจสอบช่องทางการรับรู้ (ต้องเลือกอย่างน้อย 1 อัน)
    const channels = form.querySelectorAll('input[name="channel[]"]:checked');
    if (channels.length === 0) {
        missingFields.push('ช่องทางการรับรู้ (กรุณาเลือกอย่างน้อย 1 ข้อ)');
    } else {
        const isOfflineChecked = document.getElementById('chk-offline').checked;
        if (isOfflineChecked && !form.offline_source.value) {
            missingFields.push('ระบุสื่ออื่นๆ ที่ท่านพบเห็น');
        }
    }

    if (!form.region.value) missingFields.push('ภูมิภาคที่ต้องการสร้างบ้าน');
    if (!form.floors.value) missingFields.push('จำนวนชั้น');
    if (!form.usable_area.value) missingFields.push('พื้นที่ใช้สอย');
    if (!form.budget.value) missingFields.push('งบประมาณการก่อสร้าง');
    if (!form.objective.value) missingFields.push('วัตถุประสงค์ในการมางาน');
    if (!form.decision_time.value) missingFields.push('ระยะเวลาการตัดสินใจ');

    // ตรวจสอบการกดยอมรับนโยบาย
    if (!form.consent.checked) {
        missingFields.push('กดยอมรับนโยบายความเป็นส่วนตัว');
    }

    if (missingFields.length > 0) {
        let errorHtml = '<div style="text-align: left; font-size: 0.95rem; font-family: \'Sarabun\', sans-serif;"><p style="color: #d32f2f; margin-bottom: 10px;"><b>กรุณากรอกข้อมูลในช่องต่อไปนี้ให้ครบถ้วน:</b></p><ul style="padding-left: 20px; line-height: 1.8;">';
        missingFields.forEach(field => {
            errorHtml += `<li>${field}</li>`;
        });
        errorHtml += '</ul></div>';

        Swal.fire({
            icon: 'warning',
            title: '<span style="font-family: \'Prompt\';">ข้อมูลไม่ครบถ้วน</span>',
            html: errorHtml,
            confirmButtonColor: '#003366',
            confirmButtonText: 'กลับไปแก้ไข'
        });
        return; 
    }

  
    const btn = form.querySelector('.btn-submit');
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังบันทึกข้อมูลและสร้าง E-Ticket...';

    const formData = new FormData(form);
    const userEmail = formData.get('email');

    fetch('../api/save_walkin_full.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const rawText = await response.text(); 
        try { return JSON.parse(rawText); } 
        catch (err) { throw new Error(rawText); }
    })
    .then(data => {
        if(data.success) {
            const qrImage = data.qr_url ? data.qr_url : 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=FOCUS2026-'+data.insert_id;
            const fileName = 'FOCUS2026-Ticket-' + (data.insert_id || 'QR') + '.png';

            const popupHtml = `
                <div style="background: #fff; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow: hidden; text-align: left; position: relative; margin-bottom: 15px;">
                    
                    <div style="background: linear-gradient(135deg, #003366, #0056b3); color: white; padding: 25px 20px; text-align: center; position: relative;">
                        <h3 style="margin: 0; font-family: 'Prompt', sans-serif; font-size: 1.5rem; color: #fff; letter-spacing: 1px;">E-TICKET</h3>
                        <p style="margin: 5px 0 0; font-size: 0.95rem; color: #e2e8f0; font-family: 'Prompt', sans-serif;">งานรับสร้างบ้าน FOCUS 2026</p>
                    </div>

                    <div style="padding: 25px 25px 15px; font-family: 'Sarabun', sans-serif; font-size: 0.95rem; color: #333; line-height: 1.6;">
                        <p style="text-align: center; font-weight: 600; color: #003366; font-size: 1.1rem; margin-bottom: 15px; font-family: 'Prompt', sans-serif;">
                            ขอขอบพระคุณที่ให้เกียรติเข้าร่วมงาน
                        </p>
                        <p style="text-align: center; margin-bottom: 20px; color: #444;">
                            งานที่รวบรวมบริษัทรับสร้างบ้านชั้นนำของประเทศ<br>
                            พร้อมแบบบ้านหลากหลายสไตล์ วัสดุก่อสร้าง<br>
                            และผู้เชี่ยวชาญที่พร้อมให้คำปรึกษาเรื่องการสร้างบ้านอย่างครบวงจร
                        </p>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px;">
                            <p style="font-weight: bold; margin-bottom: 15px; color: #003366; font-family: 'Prompt', sans-serif;">ภายในงานคุณจะได้พบกับ</p>
                            
                            <div style="margin-bottom: 12px; line-height: 1.4;">
                                <b style="color: #1e293b;"><i class="fas fa-home text-primary"></i> Luxury Home</b><br>
                                <span style="font-size: 0.85rem; color: #64748b;">สัมผัสแบบบ้านหรู ดีไซน์ใหม่ล่าสุด พร้อมแนวคิดสถาปัตยกรรมและฟังก์ชันการอยู่อาศัยระดับพรีเมียม</span>
                            </div>
                            
                            <div style="margin-bottom: 12px; line-height: 1.4;">
                                <b style="color: #1e293b;"><i class="fas fa-comments text-primary"></i> Home Builder Consultation</b><br>
                                <span style="font-size: 0.85rem; color: #64748b;">พูดคุยกับบริษัทรับสร้างบ้านโดยตรง รับคำปรึกษาเกี่ยวกับการออกแบบ ฟังก์ชัน และงบประมาณในการปลูกสร้าง</span>
                            </div>
                            
                            <div style="margin-bottom: 12px; line-height: 1.4;">
                                <b style="color: #1e293b;"><i class="fas fa-building-columns text-primary"></i> Finance & Consultation</b><br>
                                <span style="font-size: 0.85rem; color: #64748b;">พบกับสถาบันการเงินชั้นนำ พร้อมให้คำแนะนำเรื่องสินเชื่อเพื่อการปลูกสร้างบ้าน และการวางแผนงบประมาณ</span>
                            </div>
                            
                            <div style="line-height: 1.4;">
                                <b style="color: #1e293b;"><i class="fas fa-gift text-primary"></i> Promotion & Privilege</b><br>
                                <span style="font-size: 0.85rem; color: #64748b;">พบกับข้อเสนอพิเศษภายในงาน พร้อมสิทธิประโยชน์สำหรับผู้ที่จองปลูกสร้างบ้านในช่วงจัดงาน</span>
                            </div>
                        </div>

                        <p style="text-align: center; font-size: 0.9rem; color: #003366; font-style: italic; margin-bottom: 0;">
                            ขอให้ท่านเพลิดเพลินกับการเยี่ยมชมงาน และหวังเป็นอย่างยิ่งว่างานนี้จะช่วยให้ท่านเห็นภาพ <br><strong>“บ้านในแบบของคุณ และบ้านที่สะท้อนตัวตนของคุณ”</strong><br>ได้อย่างชัดเจนยิ่งขึ้น
                        </p>
                    </div>

                    <div style="position: relative; text-align: center; margin-top: 5px;">
                        <div style="border-top: 2px dashed #cbd5e1; margin: 0 25px;"></div>
                        <div style="position: absolute; top: -15px; left: -15px; width: 30px; height: 30px; background: rgba(0,51,102,0.9); border-radius: 50%;"></div>
                        <div style="position: absolute; top: -15px; right: -15px; width: 30px; height: 30px; background: rgba(0,51,102,0.9); border-radius: 50%;"></div>
                    </div>

                    <div style="padding: 25px 20px 20px; text-align: center; background: #f8fafc;">
                        <p style="font-family: 'Prompt', sans-serif; font-weight: 600; color: #003366; margin-bottom: 10px; font-size: 1.1rem;">
                            SCAN TICKET สำหรับเข้างาน
                        </p>
                        
                        <div style="background: #fff; padding: 10px; border: 1px solid #e2e8f0; border-radius: 15px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                            <img src="${qrImage}" alt="QR Code" style="width: 170px; height: 170px; display: block;">
                        </div>

                        <div style="margin-top: 15px;">
                            <button id="btn-download-qr" type="button" onclick="downloadQRCode('${qrImage}', '${fileName}')" style="background: #0056b3; color: white; border: none; padding: 8px 25px; border-radius: 25px; font-family: 'Prompt', sans-serif; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 86, 179, 0.2); transition: 0.2s;">
                                <i class="fas fa-download me-1"></i> เซฟรูปตั๋วลงเครื่อง
                            </button>
                        </div>

                        <div style="margin-top: 15px; font-size: 0.85rem; color: #d32f2f; font-family: 'Prompt', sans-serif;">
                            <i class="fas fa-camera"></i> <strong>หรือแคปหน้าจอนี้เพื่อใช้สแกนเข้างาน</strong>
                        </div>
                        
                        ${userEmail ? `
                        <div style="margin-top: 8px; font-size: 0.85rem; color: #2e7d32;">
                            <i class="fas fa-envelope-circle-check"></i> ข้อมูลและตั๋วถูกส่งไปยังอีเมล <strong>${userEmail}</strong> แล้ว
                        </div>` : ''}
                    </div>

                    <div style="background: #003366; color: #fff; padding: 18px 20px; text-align: center; font-size: 0.9rem; font-family: 'Prompt', sans-serif;">
                        <strong>📍 งานรับสร้างบ้าน FOCUS 2026</strong><br>
                        <span style="font-size: 0.85rem; opacity: 0.9;">18–22 มีนาคม 2569 | ณ อิมแพ็ค ฮอลล์ 6 เมืองทองธานี</span><br>
                        <div style="opacity: 0.7; font-size: 0.75rem; margin-top: 8px; font-family: 'Sarabun', sans-serif;">ขอแสดงความนับถือ สมาคมธุรกิจรับสร้างบ้าน</div>
                    </div>
                    
                </div>
            `;

            Swal.fire({
                html: popupHtml,
                showConfirmButton: true,
                confirmButtonText: '<i class="fas fa-home"></i> ปิดและกลับสู่หน้าหลัก',
                confirmButtonColor: '#0056b3',
                width: '650px',
                background: 'transparent', 
                padding: '0',              
                allowOutsideClick: false,
                backdrop: `rgba(0,51,102,0.9)` 
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload(); 
                }
            });

        } else {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: data.message,
                confirmButtonColor: '#003366'
            });
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'ตรวจสอบพบข้อผิดพลาด',
            html: `<div style="text-align: left; font-size: 0.8rem; background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;">
                    <b>ข้อความจากเซิร์ฟเวอร์:</b><br>${error.message}
                   </div>`,
            confirmButtonColor: '#003366'
        });
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
