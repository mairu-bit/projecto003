<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$courses = get_all_courses($pdo);
$labs    = get_all_labs($pdo);

$isUser         = !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? null) === 'user';
$userEnrolledMap = $isUser ? get_user_enrolled_map($pdo, (int) $_SESSION['user_id']) : [];
$enrollFlash    = $_SESSION['enroll_flash'] ?? null;
unset($_SESSION['enroll_flash']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แผนกวิชาช่างยนต์ — AUTOMOTIVE TECHNOLOGY DEPT.</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Status Badges */
.status-badge{
  display:inline-block; font-size:11.5px; font-weight:600; padding:3px 9px; border-radius:12px;
  text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap;
}
.badge-pending{ background:rgba(251,191,36,0.12); color:#fbbf24; border:1px solid rgba(251,191,36,0.35); }
.badge-confirmed{ background:rgba(63,191,114,0.12); color:#3fbf72; border:1px solid rgba(63,191,114,0.35); }
.badge-completed{ background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.35); }
.badge-cancelled{ background:rgba(255,90,68,0.1); color:#ff5a44; border:1px solid rgba(255,90,68,0.3); }

.course-meta {
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px dashed rgba(255,255,255,0.1);
  display: grid;
  gap: 6px;
  font-size: 12.5px;
  color: var(--text-dim);
}
.course-meta-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero blueprint-grid">
  <div class="wrap">
    <div class="hero-inner">
      <div>
        <div class="eyebrow">DWG. NO. ME-101 &nbsp;/&nbsp; แผนกช่างยนต์</div>
        <h1>
          ผลิตช่างฝีมือ<br>
          พร้อมทำงานจริง<br>
          <span class="accent">ตั้งแต่วันแรกที่ลงมือ</span>
        </h1>
        <p class="hero-desc">
          หลักสูตรที่ยึดของจริงเป็นครู ฝึกปฏิบัติในห้องปฏิบัติการมาตรฐาน
          ตั้งแต่เครื่องยนต์ ระบบไฟฟ้า ไปจนถึงระบบส่งกำลัง
          โดยครูผู้สอนที่ผ่านงานช่างยนต์มาโดยตรง
        </p>
        <div class="btn-row">
          <a href="#courses" class="btn btn-primary">ดูหลักสูตรทั้งหมด</a>
          <a href="#contact" class="btn btn-outline">ติดต่อสอบถาม</a>
        </div>
        <div class="spec-strip">
          <div><div class="n"><?= count($courses) ?></div><div class="l">สาขาวิชาที่เปิดสอน</div></div>
          <div><div class="n">100%</div><div class="l">ฝึกกับเครื่องยนต์จริง</div></div>
          <div><div class="n">ปวช. / ปวส.</div><div class="l">ระดับที่เปิดรับ</div></div>
        </div>
      </div>

      <div class="diagram-panel">
        <div class="panel-head">
          <span>SEC. A-A — SINGLE CYLINDER</span>
          <span class="live">LIVE SIMULATION</span>
        </div>
        <svg id="engine-svg" viewBox="0 0 400 470" xmlns="http://www.w3.org/2000/svg">
          <line x1="150" y1="55" x2="250" y2="55" stroke="#5c6068" stroke-width="1" stroke-dasharray="4 3"/>
          <line x1="150" y1="48" x2="150" y2="62" stroke="#5c6068" stroke-width="1"/>
          <line x1="250" y1="48" x2="250" y2="62" stroke="#5c6068" stroke-width="1"/>
          <text x="200" y="40" fill="#8b8f99" font-family="Chakra Petch, sans-serif" font-size="11" text-anchor="middle">⌀ 100 mm BORE</text>

          <line x1="272" y1="115" x2="272" y2="225" stroke="#5c6068" stroke-width="1" stroke-dasharray="4 3"/>
          <line x1="265" y1="115" x2="279" y2="115" stroke="#5c6068" stroke-width="1"/>
          <line x1="265" y1="225" x2="279" y2="225" stroke="#5c6068" stroke-width="1"/>
          <text x="333" y="174" fill="#8b8f99" font-family="Chakra Petch, sans-serif" font-size="11" text-anchor="middle" transform="rotate(90 333,174)">STROKE 110 mm</text>

          <rect x="150" y="70" width="100" height="210" rx="4" fill="none" stroke="#3a3d45" stroke-width="2"/>

          <g id="piston">
            <rect x="160" y="115" width="80" height="46" rx="3" fill="#2a2d34" stroke="#ff6b1a" stroke-width="1.5"/>
            <line x1="160" y1="130" x2="240" y2="130" stroke="#3a3d45" stroke-width="1"/>
            <line x1="160" y1="146" x2="240" y2="146" stroke="#3a3d45" stroke-width="1"/>
          </g>

          <line id="rod" x1="200" y1="161" x2="200" y2="285" stroke="#8b8f99" stroke-width="6" stroke-linecap="round"/>

          <circle cx="200" cy="340" r="56" fill="#1a1c21" stroke="#3a3d45" stroke-width="2"/>
          <circle cx="200" cy="340" r="56" fill="none" stroke="#ff6b1a" stroke-width="1" stroke-dasharray="2 6" opacity="0.5"/>
          <g id="flywheel-spokes" stroke="#3a3d45" stroke-width="2">
            <line x1="200" y1="294" x2="200" y2="386"/>
            <line x1="154" y1="340" x2="246" y2="340"/>
            <line x1="167" y1="307" x2="233" y2="373"/>
            <line x1="167" y1="373" x2="233" y2="307"/>
          </g>
          <circle cx="200" cy="340" r="7" fill="#16171c" stroke="#5c6068" stroke-width="1.5"/>
          <circle id="crankpin" cx="200" cy="285" r="8" fill="#ff6b1a"/>

          <rect x="120" y="410" width="160" height="14" rx="2" fill="#1a1c21" stroke="#3a3d45" stroke-width="1.5"/>
        </svg>
        <div class="diagram-caption">4-STROKE · CRANK–SLIDER MOTION MODEL</div>
      </div>
    </div>
  </div>
</section>

<section id="about">
  <div class="wrap">
    <div class="about-grid">
      <div class="about-copy">
        <div class="eyebrow">เกี่ยวกับแผนก</div>
        <h2 style="font-size:clamp(24px,2.8vw,32px); color:#fff; margin-bottom:22px;">เรียนจากของจริง<br>ไม่ใช่แค่ในตำรา</h2>
        <p>
          แผนกวิชาช่างยนต์มุ่งผลิตนักเรียนนักศึกษาที่มีทักษะฝีมือตรงตามความต้องการของสถานประกอบการ
          ผ่านการฝึกปฏิบัติจริงคู่ขนานไปกับความรู้ภาคทฤษฎี ตั้งแต่โครงสร้างเครื่องยนต์ไปจนถึง
          เทคโนโลยียานยนต์สมัยใหม่
        </p>
        <ul class="about-list">
          <li><span class="tick">01</span> ฝึกปฏิบัติกับเครื่องยนต์และอุปกรณ์จริงในทุกรายวิชา</li>
          <li><span class="tick">02</span> ครูผู้สอนมีประสบการณ์ตรงจากภาคอุตสาหกรรมยานยนต์</li>
          <li><span class="tick">03</span> เตรียมความพร้อมสู่การทำงานและการศึกษาต่อระดับสูง</li>
        </ul>
      </div>

      <div class="torque-card">
        <h3>โครงสร้างหลักสูตร</h3>
        <div class="gauge-row"><span class="label">ทฤษฎีเครื่องยนต์และกลไก</span><span class="val">ปวช.1–3</span></div>
        <div class="gauge-row"><span class="label">ปฏิบัติงานซ่อมบำรุง</span><span class="val">ทุกภาคเรียน</span></div>
        <div class="gauge-row"><span class="label">ระบบไฟฟ้า–อิเล็กทรอนิกส์ยานยนต์</span><span class="val">ปวช.2 ขึ้นไป</span></div>
        <div class="gauge-row"><span class="label">ฝึกงานสถานประกอบการ</span><span class="val">ปวช.3 / ปวส.2</span></div>
      </div>
    </div>
  </div>
</section>

<section id="courses" class="blueprint-grid">
  <div class="wrap">
    <div class="section-head">
      <h2>หลักสูตรที่เปิดสอน</h2>
      <p>สี่กลุ่มวิชาหลักที่ครอบคลุมงานช่างยนต์ตั้งแต่ต้นจนถึงระบบยานยนต์สมัยใหม่</p>
    </div>
    <?php if ($enrollFlash): ?>
      <p style="margin-bottom:24px; padding:12px 16px; border-radius:4px; font-size:14px; max-width:560px;
                background:<?= $enrollFlash['ok'] ? 'rgba(63,191,114,0.1)' : 'rgba(255,90,68,0.1)' ?>; 
                border:1px solid <?= $enrollFlash['ok'] ? 'rgba(63,191,114,0.35)' : 'rgba(255,90,68,0.35)' ?>; 
                color:<?= $enrollFlash['ok'] ? '#7fe0a0' : '#ff7b6b' ?>;">
        <?= e($enrollFlash['message']) ?>
      </p>
    <?php endif; ?>
    <div class="course-grid">
      <?php foreach ($courses as $course): 
        $cid = (int)$course['id'];
        $activeCount = get_active_enrollment_count($pdo, $cid);
        $maxSeats = (int)($course['max_seats'] ?? 30);
        $isFull = ($maxSeats > 0 && $activeCount >= $maxSeats);
        $userStatus = $userEnrolledMap[$cid] ?? null;
      ?>
      <div class="course-card">
        <div class="course-num"><?= e($course['spec_no']) ?></div>
        <svg class="course-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><?= $course['icon_svg'] /* trusted markup, edited only via admin */ ?></svg>
        <h3><?= e($course['title']) ?></h3>
        <p><?= e($course['description']) ?></p>

        <!-- Course Meta: Dates & Seat Quotas -->
        <div class="course-meta">
          <div class="course-meta-item">
            <span>📅 ช่วงเวลา:</span>
            <span style="color:#d8d9de;"><?= format_date_range($course['start_date'], $course['end_date']) ?></span>
          </div>
          <div class="course-meta-item">
            <span>👥 ที่นั่ง:</span>
            <span style="color:<?= $isFull ? 'var(--danger)' : '#d8d9de' ?>; font-weight:500;">
              <?= $activeCount ?> / <?= $maxSeats > 0 ? $maxSeats : 'ไม่จำกัด' ?> ที่นั่ง
              <?php if ($isFull): ?><span style="color:var(--danger); font-size:11px;">(เต็ม)</span><?php endif; ?>
            </span>
          </div>
        </div>

        <!-- Action / Status -->
        <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
          <?php if ($userStatus && $userStatus !== 'cancelled'): ?>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:12.5px; color:var(--text-dim);">สถานะ:</span>
              <?= status_badge($userStatus) ?>
            </div>
            <a href="account.php?tab=courses" style="font-size:12px; color:var(--orange);">ดูในบัญชี →</a>
          <?php elseif ($isUser): ?>
            <?php if ($isFull): ?>
              <button disabled class="btn btn-outline" style="border:none; padding:9px 16px; font-size:13px; opacity:0.5; cursor:not-allowed; background:rgba(255,255,255,0.05); color:var(--text-dim);">ที่นั่งเต็มแล้ว</button>
            <?php else: ?>
              <form method="post" action="enroll.php" style="margin:0;">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <button type="submit" class="btn btn-primary" style="border:none; cursor:pointer; padding:9px 18px; font-size:13px;">ลงทะเบียนเรียน</button>
              </form>
            <?php endif; ?>
          <?php elseif (empty($_SESSION['user_id'])): ?>
            <a href="login.php" style="display:inline-block; font-size:13px; color:var(--orange);">เข้าสู่ระบบเพื่อลงทะเบียน →</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!$courses): ?>
        <p style="color:var(--text-dim);">ยังไม่มีข้อมูลหลักสูตร</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section id="labs">
  <div class="wrap">
    <div class="section-head">
      <h2>ห้องปฏิบัติการ</h2>
      <p>พื้นที่ฝึกปฏิบัติที่จำลองสภาพงานจริงจากสถานประกอบการ</p>
    </div>
    <div class="lab-sheet">
      <?php foreach ($labs as $lab): ?>
      <div class="lab-row">
        <div class="sheet-no">SHEET<b><?= e($lab['sheet_no']) ?></b></div>
        <h3><?= e($lab['title']) ?></h3>
        <p><?= e($lab['description']) ?></p>
      </div>
      <?php endforeach; ?>
      <?php if (!$labs): ?>
        <p style="color:var(--text-dim);">ยังไม่มีข้อมูลห้องปฏิบัติการ</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/script.js"></script>

</body>
</html>
