<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'แดชบอร์ด';

$courseCount   = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$labCount      = (int) $pdo->query("SELECT COUNT(*) FROM labs")->fetchColumn();
$userCount     = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pendingCount  = (int) $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'")->fetchColumn();
$totalEnrolls  = (int) $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status != 'cancelled'")->fetchColumn();
$messageCount  = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unreadCount   = count_unread_messages($pdo);

$recentEnrollments = get_all_enrollments($pdo);
$recentEnrollments = array_slice($recentEnrollments, 0, 5);

require __DIR__ . '/includes/admin_header.php';
?>

<h1>แดชบอร์ด</h1>
<div class="sub">ภาพรวมข้อมูลและการลงทะเบียนของเว็บไซต์แผนกวิชาช่างยนต์</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));">
  <div class="stat-card">
    <div class="n"><?= $courseCount ?></div>
    <div class="l">หลักสูตรที่เปิดสอน</div>
  </div>
  <div class="stat-card">
    <div class="n"><?= $userCount ?></div>
    <div class="l">สมาชิกผู้เรียนทั้งหมด</div>
  </div>
  <div class="stat-card" style="border-left:3px solid var(--yellow);">
    <div class="n" style="color:var(--yellow);"><?= $pendingCount ?></div>
    <div class="l">รออนุมัติการลงทะเบียน</div>
  </div>
  <div class="stat-card">
    <div class="n" style="color:var(--ok);"><?= $totalEnrolls ?></div>
    <div class="l">การลงทะเบียนเรียนทั้งหมด</div>
  </div>
  <div class="stat-card">
    <div class="n"><?= $messageCount ?></div>
    <div class="l">ข้อความติดต่อ<?= $unreadCount ? " (ยังไม่อ่าน $unreadCount)" : '' ?></div>
  </div>
</div>

<div class="admin-card" style="margin-bottom:20px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <h3 style="margin:0; font-family:'Chakra Petch',sans-serif; color:#fff; font-size:15px;">การลงทะเบียนล่าสุด</h3>
    <a href="enrollments.php" style="font-size:13px; color:var(--orange); text-decoration:none;">ดูทั้งหมด →</a>
  </div>
  <table>
    <thead>
      <tr><th>วันที่</th><th>ผู้เรียน</th><th>หลักสูตร</th><th>สถานะ</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($recentEnrollments as $en): ?>
      <tr>
        <td style="color:var(--text-dim); font-size:13px; white-space:nowrap;"><?= format_date_thai($en['enrolled_at']) ?></td>
        <td>
          <div style="font-weight:600; color:#fff;"><?= e($en['fullname'] ?: $en['username']) ?></div>
          <div style="font-size:12px; color:var(--text-dim);"><?= e($en['student_id'] ?: $en['username']) ?></div>
        </td>
        <td><?= e($en['spec_no']) ?> - <?= e($en['course_title']) ?></td>
        <td><?= status_badge($en['status']) ?></td>
        <td><a href="enrollments.php?q=<?= urlencode($en['username']) ?>" class="btn btn-outline btn-sm">จัดการ</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentEnrollments): ?>
        <tr><td colspan="5" style="color:var(--text-dim); text-align:center;">ยังไม่มีข้อมูลการลงทะเบียน</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="admin-card">
  <h3 style="margin-top:0; font-family:'Chakra Petch',sans-serif; color:#fff; font-size:15px;">ทางลัดจัดการระบบ</h3>
  <div class="row-actions">
    <a href="enrollments.php" class="btn btn-primary">จัดการผู้ลงทะเบียน <?= $pendingCount > 0 ? "($pendingCount)" : "" ?></a>
    <a href="course_form.php" class="btn btn-outline">+ เพิ่มหลักสูตร</a>
    <a href="lab_form.php" class="btn btn-outline">+ เพิ่มห้องปฏิบัติการ</a>
    <a href="messages.php" class="btn btn-outline">ดูข้อความติดต่อ</a>
  </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
