<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'ผู้ลงทะเบียนหลักสูตร';

$enrollments = get_all_enrollments($pdo);
require __DIR__ . '/includes/admin_header.php';
?>

<h1>ผู้ลงทะเบียนหลักสูตร</h1>
<div class="sub">รายชื่อสมาชิกที่ลงทะเบียนเรียนแต่ละหลักสูตร</div>

<div class="admin-card">
  <table>
    <thead>
      <tr><th>วันที่ลงทะเบียน</th><th>ผู้ใช้</th><th>อีเมล</th><th>หลักสูตร</th></tr>
    </thead>
    <tbody>
      <?php foreach ($enrollments as $en): ?>
      <tr>
        <td style="white-space:nowrap; color:var(--text-dim);"><?= e(date('d/m/Y H:i', strtotime($en['enrolled_at']))) ?></td>
        <td><?= e($en['username']) ?></td>
        <td style="color:var(--text-dim);"><?= e($en['email'] ?? '-') ?></td>
        <td><?= e($en['spec_no']) ?> — <?= e($en['course_title']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$enrollments): ?>
        <tr><td colspan="4" style="color:var(--text-dim);">ยังไม่มีผู้ลงทะเบียน</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
