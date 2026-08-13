<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'หลักสูตร';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    delete_course($pdo, (int) $_POST['delete_id']);
    header('Location: courses.php?deleted=1');
    exit;
}

$courses = get_all_courses($pdo);
require __DIR__ . '/includes/admin_header.php';
?>

<h1>หลักสูตร</h1>
<div class="sub">จัดการรายการหลักสูตรที่แสดงบนหน้าเว็บไซต์</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-ok">ลบหลักสูตรเรียบร้อยแล้ว</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-ok">บันทึกหลักสูตรเรียบร้อยแล้ว</div><?php endif; ?>

<div class="row-actions" style="margin-bottom:18px;">
  <a href="course_form.php" class="btn btn-primary">+ เพิ่มหลักสูตรใหม่</a>
</div>

<div class="admin-card">
  <table>
    <thead>
      <tr><th>ลำดับ</th><th>รหัส</th><th>ชื่อหลักสูตร</th><th>คำอธิบาย</th><th>ผู้ลงทะเบียน</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= (int)$c['sort_order'] ?></td>
        <td><?= e($c['spec_no']) ?></td>
        <td><?= e($c['title']) ?></td>
        <td style="max-width:360px; color:var(--text-dim);"><?= e(mb_strimwidth($c['description'] ?? '', 0, 90, '…')) ?></td>
        <td><?= get_enrollment_count_for_course($pdo, (int)$c['id']) ?> คน</td>
        <td>
          <div class="row-actions">
            <a href="course_form.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">แก้ไข</a>
            <form method="post" onsubmit="return confirm('ยืนยันการลบหลักสูตรนี้?');" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$courses): ?>
        <tr><td colspan="6" style="color:var(--text-dim);">ยังไม่มีหลักสูตร</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
