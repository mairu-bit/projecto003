<?php
// ข้อความแจ้งผลหลังส่งฟอร์ม (ถูก set โดย contact_submit.php ผ่าน session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$flash = $_SESSION['contact_flash'] ?? null;
unset($_SESSION['contact_flash']);
?>
<section id="contact" class="contact-band">
  <div class="wrap">
    <div class="contact-grid">
      <div>
        <h2>สนใจสมัครเรียน<br>หรือสอบถามข้อมูล?</h2>
        <p>ทีมแนะแนวของแผนกวิชาช่างยนต์ยินดีให้ข้อมูลหลักสูตร การรับสมัคร และการเยี่ยมชมห้องปฏิบัติการ</p>

        <?php if ($flash): ?>
          <p style="margin-top:18px; padding:12px 16px; border-radius:4px; font-size:14px;
                    background:<?= $flash['ok'] ? 'rgba(60,180,90,0.12)' : 'rgba(255,80,60,0.12)' ?>;
                    border:1px solid <?= $flash['ok'] ? 'rgba(60,180,90,0.4)' : 'rgba(255,80,60,0.4)' ?>;
                    color:<?= $flash['ok'] ? '#7fe0a0' : '#ff9d8f' ?>;">
            <?= e($flash['message']) ?>
          </p>
        <?php endif; ?>

        <form action="contact_submit.php" method="post" style="margin-top:20px; display:grid; gap:12px; max-width:420px;">
          <input type="text" name="full_name" placeholder="ชื่อ-นามสกุล" required
                 style="padding:12px 14px; border-radius:3px; border:1px solid var(--steel-line); background:var(--graphite-800); color:var(--paper); font-family:'Sarabun',sans-serif;">
          <input type="tel" name="phone" placeholder="เบอร์โทรศัพท์"
                 style="padding:12px 14px; border-radius:3px; border:1px solid var(--steel-line); background:var(--graphite-800); color:var(--paper); font-family:'Sarabun',sans-serif;">
          <input type="email" name="email" placeholder="อีเมล"
                 style="padding:12px 14px; border-radius:3px; border:1px solid var(--steel-line); background:var(--graphite-800); color:var(--paper); font-family:'Sarabun',sans-serif;">
          <textarea name="message" placeholder="ข้อความ / สอบถามข้อมูล" rows="4" required
                    style="padding:12px 14px; border-radius:3px; border:1px solid var(--steel-line); background:var(--graphite-800); color:var(--paper); font-family:'Sarabun',sans-serif; resize:vertical;"></textarea>
          <button type="submit" class="btn btn-primary" style="justify-self:start; border:none; cursor:pointer;">ส่งข้อความ</button>
        </form>
      </div>
      <div class="contact-col">
        <h4>ติดต่อแผนก</h4>
        <a href="tel:0000000000">โทร. 0-0000-0000</a>
        <a href="mailto:auto-dept@example.ac.th">auto-dept@example.ac.th</a>
        <p>อาคารปฏิบัติการช่างยนต์ ชั้น 1</p>
      </div>
      <div class="contact-col">
        <h4>เวลาทำการ</h4>
        <p>จันทร์–ศุกร์ 08:30–16:30 น.</p>
        <p>เว้นวันหยุดราชการ</p>
      </div>
    </div>
    <div class="title-block">
      <span><b>DEPT.</b> ช่างยนต์</span>
      <span><b>DWG NO.</b> ME-101 REV. A</span>
      <span><b>SCALE</b> N.T.S.</span>
      <span>© <span id="year"></span> แผนกวิชาช่างยนต์</span>
    </div>
  </div>
</section>
