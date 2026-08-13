<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'ห้องปฏิบัติการ';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    delete_lab($pdo, (int) $_POST['delete_id']);
    header('Location: labs.php?deleted=1');
    exit;
}

$labs = get_all_labs($pdo);
require __DIR__ . '/includes/admin_header.php';
?>

<h1>ห้องปฏิบัติการ</h1>
<div class="sub">จัดการรายการห้องปฏิบัติการที่แสดงบนหน้าเว็บไซต์</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-ok">ลบห้องปฏิบัติการเรียบร้อยแล้ว</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-ok">บันทึกห้องปฏิบัติการเรียบร้อยแล้ว</div><?php endif; ?>

<div class="row-actions" style="margin-bottom:18px;">
  <a href="lab_form.php" class="btn btn-primary">+ เพิ่มห้องปฏิบัติการใหม่</a>
</div>

<div class="admin-card">
  <table>
    <thead>
      <tr><th>ลำดับ</th><th>SHEET</th><th>ชื่อห้องปฏิบัติการ</th><th>คำอธิบาย</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($labs as $l): ?>
      <tr>
        <td><?= (int)$l['sort_order'] ?></td>
        <td><?= e($l['sheet_no']) ?></td>
        <td><?= e($l['title']) ?></td>
        <td style="max-width:360px; color:var(--text-dim);"><?= e(mb_strimwidth($l['description'] ?? '', 0, 90, '…')) ?></td>
        <td>
          <div class="row-actions">
            <a href="lab_form.php?id=<?= $l['id'] ?>" class="btn btn-outline btn-sm">แก้ไข</a>
            <form method="post" onsubmit="return confirm('ยืนยันการลบห้องปฏิบัติการนี้?');" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $l['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$labs): ?>
        <tr><td colspan="5" style="color:var(--text-dim);">ยังไม่มีห้องปฏิบัติการ</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
