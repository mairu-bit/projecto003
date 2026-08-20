<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'แดชบอร์ด';

$courseCount  = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$labCount     = (int) $pdo->query("SELECT COUNT(*) FROM labs")->fetchColumn();
$messageCount = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unreadCount  = count_unread_messages($pdo);

require __DIR__ . '/includes/admin_header.php';
?>

<h1>แดชบอร์ด</h1>
<div class="sub">ภาพรวมข้อมูลของเว็บไซต์แผนกวิชาช่างยนต์</div>

<div class="stat-grid">
  <div class="stat-card"><div class="n"><?= $courseCount ?></div><div class="l">หลักสูตรทั้งหมด</div></div>
  <div class="stat-card"><div class="n"><?= $labCount ?></div><div class="l">ห้องปฏิบัติการทั้งหมด</div></div>
  <div class="stat-card"><div class="n"><?= $messageCount ?></div><div class="l">ข้อความติดต่อทั้งหมด<?= $unreadCount ? " (ยังไม่อ่าน $unreadCount)" : '' ?></div></div>
</div>

<div class="admin-card">
  <h3 style="margin-top:0; font-family:'Chakra Petch',sans-serif; color:#fff; font-size:15px;">ทางลัด</h3>
  <div class="row-actions">
    <a href="course_form.php" class="btn btn-primary">+ เพิ่มหลักสูตร</a>
    <a href="lab_form.php" class="btn btn-outline">+ เพิ่มห้องปฏิบัติการ</a>
    <a href="messages.php" class="btn btn-outline">ดูข้อความติดต่อ</a>
  </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
