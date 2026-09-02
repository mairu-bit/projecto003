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
<div class="sub">จัดการรายการหลักสูตร กำหนดที่นั่ง และช่วงเวลาเรียนที่แสดงบนหน้าเว็บไซต์</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-ok">ลบหลักสูตรเรียบร้อยแล้ว</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-ok">บันทึกหลักสูตรเรียบร้อยแล้ว</div><?php endif; ?>

<div class="row-actions" style="margin-bottom:18px;">
  <a href="course_form.php" class="btn btn-primary">+ เพิ่มหลักสูตรใหม่</a>
</div>

<div class="admin-card">
  <table>
    <thead>
      <tr>
        <th>ลำดับ</th>
        <th>รหัส</th>
        <th>ชื่อหลักสูตร</th>
        <th>ช่วงเวลาเรียน</th>
        <th>ผู้ลงทะเบียน / ที่นั่ง</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($courses as $c): 
        $activeCount = get_active_enrollment_count($pdo, (int)$c['id']);
        $maxSeats = (int)($c['max_seats'] ?? 30);
        $isFull = ($maxSeats > 0 && $activeCount >= $maxSeats);
      ?>
      <tr>
        <td><?= (int)$c['sort_order'] ?></td>
        <td style="font-weight:600; color:var(--orange);"><?= e($c['spec_no']) ?></td>
        <td>
          <div style="font-weight:600; color:#fff;"><?= e($c['title']) ?></div>
          <div style="font-size:12px; color:var(--text-dim); max-width:320px;"><?= e(mb_strimwidth($c['description'] ?? '', 0, 80, '…')) ?></div>
        </td>
        <td style="color:var(--text-dim); font-size:13px; white-space:nowrap;">
          <?= format_date_range($c['start_date'], $c['end_date']) ?>
        </td>
        <td style="white-space:nowrap;">
          <a href="enrollments.php?course=<?= $c['id'] ?>" style="text-decoration:none; color:<?= $isFull ? 'var(--danger)' : 'inherit' ?>;">
            <b><?= $activeCount ?></b> / <?= $maxSeats > 0 ? $maxSeats : 'ไม่จำกัด' ?> คน
            <?php if ($isFull): ?><span class="status-badge badge-cancelled" style="font-size:10px; margin-left:4px;">เต็ม</span><?php endif; ?>
          </a>
        </td>
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
        <tr><td colspan="6" style="color:var(--text-dim); text-align:center;">ยังไม่มีหลักสูตร</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
