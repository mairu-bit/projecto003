<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
}

$enrollments = get_enrollments_for_user($pdo, (int) $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>บัญชีของฉัน — แผนกวิชาช่างยนต์</title>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600&family=Sarabun:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="login-shell">
  <div class="login-box" style="width:460px;">
    <h1>สวัสดี, <?= e($_SESSION['username']) ?></h1>
    <div class="sub">คุณเข้าสู่ระบบในฐานะสมาชิกทั่วไป</div>

    <h3 style="font-family:'Chakra Petch',sans-serif; color:#fff; font-size:14px; margin:24px 0 12px;">หลักสูตรที่ลงทะเบียนไว้</h3>
    <?php if ($enrollments): ?>
      <div style="display:grid; gap:10px;">
        <?php foreach ($enrollments as $en): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border:1px solid var(--steel-line); border-radius:4px;">
          <div>
            <div style="font-size:14px; color:#fff;"><?= e($en['title']) ?></div>
            <div style="font-size:12px; color:var(--text-dim);"><?= e($en['spec_no']) ?> · ลงทะเบียนเมื่อ <?= e(date('d/m/Y', strtotime($en['enrolled_at']))) ?></div>
          </div>
          <form method="post" action="unenroll.php" onsubmit="return confirm('ยกเลิกการลงทะเบียนหลักสูตรนี้?');">
            <input type="hidden" name="course_id" value="<?= $en['course_id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">ยกเลิก</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="sub" style="margin:0;">ยังไม่ได้ลงทะเบียนหลักสูตรใด — <a href="index.php#courses" style="color:var(--orange);">ดูหลักสูตรที่เปิดสอน</a></p>
    <?php endif; ?>

    <div class="row-actions" style="margin-top:24px;">
      <a href="index.php" class="btn btn-outline">กลับหน้าเว็บไซต์</a>
      <a href="logout.php" class="btn btn-danger">ออกจากระบบ</a>
    </div>
  </div>
</div>
</body>
</html>
