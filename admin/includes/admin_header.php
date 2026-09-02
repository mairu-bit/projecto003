<?php
// ต้อง require admin_auth.php ($pdo พร้อมใช้งาน) ก่อน include ไฟล์นี้
$unread = count_unread_messages($pdo);
$pendingEnrolls = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'")->fetchColumn();
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>ผู้ดูแลระบบ</title>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="logo">แผนกวิชาช่างยนต์<small>ADMIN PANEL</small></div>
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">แดชบอร์ด</a>
    <a href="courses.php" class="<?= $current === 'courses.php' || $current === 'course_form.php' ? 'active' : '' ?>">หลักสูตร</a>
    <a href="labs.php" class="<?= $current === 'labs.php' || $current === 'lab_form.php' ? 'active' : '' ?>">ห้องปฏิบัติการ</a>
    <a href="enrollments.php" class="<?= $current === 'enrollments.php' ? 'active' : '' ?>">
      ผู้ลงทะเบียน
      <?php if ($pendingEnrolls > 0): ?><span class="badge" style="background:var(--yellow); color:#000;"><?= $pendingEnrolls ?></span><?php endif; ?>
    </a>
    <a href="messages.php" class="<?= $current === 'messages.php' ? 'active' : '' ?>">
      ข้อความติดต่อ
      <?php if ($unread > 0): ?><span class="badge"><?= $unread ?></span><?php endif; ?>
    </a>
    <a href="../index.php" target="_blank">ดูหน้าเว็บไซต์ ↗</a>
    <a href="logout.php" class="logout">ออกจากระบบ (<?= e($_SESSION['username'] ?? '') ?>)</a>
  </aside>
  <main class="admin-main">
