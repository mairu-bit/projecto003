<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$user = get_user_by_id($pdo, $userId);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'courses';
if (!in_array($tab, ['courses', 'profile', 'password'], true)) {
    $tab = 'courses';
}

$errors = [];
$successMsg = null;

if (isset($_SESSION['flash_msg'])) {
    if ($_SESSION['flash_msg']['type'] === 'ok') {
        $successMsg = $_SESSION['flash_msg']['text'];
    } else {
        $errors[] = $_SESSION['flash_msg']['text'];
    }
    unset($_SESSION['flash_msg']);
}

if (isset($_GET['welcome'])) {
    $successMsg = 'ยินดีต้อนรับเข้าสู่ระบบแผนกวิชาช่างยนต์!';
}

// จัดการการส่งฟอร์มแก้ไขโปรไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $tab = 'profile';
    $fullname  = trim($_POST['fullname'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if ($fullname === '') {
        $errors[] = 'กรุณากรอกชื่อ-นามสกุลจริง';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    if (!$errors) {
        update_user_profile($pdo, $userId, [
            'fullname'   => $fullname,
            'student_id' => $studentId,
            'phone'      => $phone,
            'email'      => $email
        ]);
        $_SESSION['fullname'] = $fullname;
        $user = get_user_by_id($pdo, $userId);
        $successMsg = 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว';
    }
}

// จัดการการเปลี่ยนรหัสผ่าน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $tab = 'password';
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $cfmPass = $_POST['confirm_password'] ?? '';

    if (!password_verify($oldPass, $user['password_hash'])) {
        $errors[] = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
    }
    if (mb_strlen($newPass) < 6) {
        $errors[] = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร';
    }
    if ($newPass !== $cfmPass) {
        $errors[] = 'รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน';
    }

    if (!$errors) {
        update_user_password($pdo, $userId, $newPass);
        $user = get_user_by_id($pdo, $userId);
        $successMsg = 'เปลี่ยนรหัสผ่านใหม่เรียบร้อยแล้ว';
    }
}

$enrollments = get_enrollments_for_user($pdo, $userId);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>บัญชีของฉัน — แผนกวิชาช่างยนต์</title>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body style="background:var(--graphite-950); padding:30px 16px;">

<div style="max-width:760px; margin:0 auto;">

  <!-- Header Card -->
  <div class="admin-card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
    <div>
      <div style="font-size:12px; color:var(--orange); font-family:'Chakra Petch',sans-serif; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">สมาชิกแผนกช่างยนต์</div>
      <h1 style="font-family:'Chakra Petch',sans-serif; font-size:22px; color:#fff; margin:0;"><?= e($user['fullname'] ?: $user['username']) ?></h1>
      <div style="font-size:13px; color:var(--text-dim); margin-top:4px;">
        ชื่อผู้ใช้: <b style="color:#fff;"><?= e($user['username']) ?></b>
        <?php if (!empty($user['student_id'])): ?>
          · รหัส: <b style="color:#fff;"><?= e($user['student_id']) ?></b>
        <?php endif; ?>
      </div>
    </div>
    <div class="row-actions">
      <a href="index.php" class="btn btn-outline btn-sm">🌐 หน้าเว็บไซต์หลัก</a>
      <a href="logout.php" class="btn btn-danger btn-sm">ออกจากระบบ</a>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="alert alert-ok"><?= e($successMsg) ?></div>
  <?php endif; ?>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
  <?php endforeach; ?>

  <!-- Navigation Tabs -->
  <div class="nav-tabs">
    <a href="account.php?tab=courses" class="nav-tab <?= $tab === 'courses' ? 'active' : '' ?>">📚 หลักสูตรที่ลงทะเบียน (<?= count($enrollments) ?>)</a>
    <a href="account.php?tab=profile" class="nav-tab <?= $tab === 'profile' ? 'active' : '' ?>">👤 ข้อมูลส่วนตัว</a>
    <a href="account.php?tab=password" class="nav-tab <?= $tab === 'password' ? 'active' : '' ?>">🔒 เปลี่ยนรหัสผ่าน</a>
  </div>

  <!-- TAB 1: Enrolled Courses -->
  <?php if ($tab === 'courses'): ?>
    <div class="admin-card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h2 style="font-family:'Chakra Petch',sans-serif; color:#fff; font-size:16px; margin:0;">รายการหลักสูตรที่ลงทะเบียน</h2>
        <a href="index.php#courses" class="btn btn-primary btn-sm">+ ลงทะเบียนหลักสูตรเพิ่ม</a>
      </div>

      <?php if ($enrollments): ?>
        <div style="display:grid; gap:14px;">
          <?php foreach ($enrollments as $en): ?>
          <div style="border:1px solid var(--steel-line); border-radius:6px; padding:16px; background:var(--graphite-800);">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:10px;">
              <div>
                <span style="font-size:11px; color:var(--orange); font-family:'Chakra Petch',sans-serif; font-weight:600;"><?= e($en['spec_no']) ?></span>
                <h3 style="font-size:16px; color:#fff; margin:2px 0 6px;"><?= e($en['title']) ?></h3>
                <div style="font-size:12.5px; color:var(--text-dim); display:flex; flex-wrap:wrap; gap:12px;">
                  <span>📅 ช่วงเวลาเรียน: <b style="color:#d8d9de;"><?= format_date_range($en['start_date'], $en['end_date']) ?></b></span>
                  <span>🕒 วันที่ลงทะเบียน: <?= format_date_thai($en['enrolled_at']) ?></span>
                </div>
              </div>
              <div>
                <?= status_badge($en['status']) ?>
              </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding-top:10px; border-top:1px solid var(--steel-line); font-size:12.5px;">
              <div style="color:var(--text-dimmer);">
                <?php if ($en['status'] === 'pending'): ?>
                  ℹ️ อยู่ระหว่างรอการตรวจสอบและอนุมัติจากผู้ดูแลระบบ
                <?php elseif ($en['status'] === 'confirmed'): ?>
                  ✅ ได้รับการอนุมัติแล้ว สามารถเข้าเรียนตามกำหนดการ
                <?php elseif ($en['status'] === 'completed'): ?>
                  🎓 ผ่านการอบรมหลักสูตรเรียบร้อยแล้ว
                <?php elseif ($en['status'] === 'cancelled'): ?>
                  ❌ รายการนี้ถูกยกเลิกแล้ว
                <?php endif; ?>
              </div>

              <?php if (in_array($en['status'], ['pending', 'confirmed'], true)): ?>
              <form method="post" action="unenroll.php" onsubmit="return confirm('ยืนยันการยกเลิกการลงทะเบียนหลักสูตร <?= e($en['title']) ?>?');" style="margin:0;">
                <input type="hidden" name="course_id" value="<?= $en['course_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">ยกเลิกการลงทะเบียน</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div style="text-align:center; padding:32px 16px; color:var(--text-dim);">
          <div style="font-size:32px; margin-bottom:8px;">📚</div>
          <p style="margin:0 0 16px;">คุณยังไม่ได้ลงทะเบียนหลักสูตรใด</p>
          <a href="index.php#courses" class="btn btn-primary">ดูหลักสูตรที่เปิดสอนและลงทะเบียน</a>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- TAB 2: Edit Profile -->
  <?php if ($tab === 'profile'): ?>
    <div class="admin-card">
      <h2 style="font-family:'Chakra Petch',sans-serif; color:#fff; font-size:16px; margin:0 0 16px;">แก้ไขข้อมูลส่วนตัว</h2>
      <form method="post" class="form-grid">
        <input type="hidden" name="action" value="update_profile">
        
        <div>
          <label for="username_readonly">ชื่อผู้ใช้ (Login Username)</label>
          <input type="text" id="username_readonly" value="<?= e($user['username']) ?>" disabled style="opacity:0.6; cursor:not-allowed;">
        </div>

        <div>
          <label for="fullname">ชื่อ - นามสกุลจริง <span style="color:var(--orange);">*</span></label>
          <input type="text" id="fullname" name="fullname" value="<?= e($user['fullname'] ?? '') ?>" required>
        </div>

        <div>
          <label for="student_id">รหัสนักศึกษา / รหัสประจำตัวผู้เรียน</label>
          <input type="text" id="student_id" name="student_id" value="<?= e($user['student_id'] ?? '') ?>" placeholder="เช่น 66301010001">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
          <div>
            <label for="phone">เบอร์โทรศัพท์</label>
            <input type="tel" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="เช่น 0812345678">
          </div>
          <div>
            <label for="email">อีเมล</label>
            <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>" placeholder="user@example.com">
          </div>
        </div>

        <div class="row-actions" style="margin-top:8px;">
          <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <!-- TAB 3: Change Password -->
  <?php if ($tab === 'password'): ?>
    <div class="admin-card">
      <h2 style="font-family:'Chakra Petch',sans-serif; color:#fff; font-size:16px; margin:0 0 16px;">เปลี่ยนรหัสผ่าน</h2>
      <form method="post" class="form-grid">
        <input type="hidden" name="action" value="change_password">

        <div>
          <label for="old_password">รหัสผ่านปัจจุบัน <span style="color:var(--orange);">*</span></label>
          <input type="password" id="old_password" name="old_password" required>
        </div>

        <div>
          <label for="new_password">รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร) <span style="color:var(--orange);">*</span></label>
          <input type="password" id="new_password" name="new_password" required>
        </div>

        <div>
          <label for="confirm_password">ยืนยันรหัสผ่านใหม่ <span style="color:var(--orange);">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="row-actions" style="margin-top:8px;">
          <button type="submit" class="btn btn-primary">เปลี่ยนรหัสผ่าน</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

</div>

</body>
</html>
