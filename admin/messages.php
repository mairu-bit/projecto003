<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'ข้อความติดต่อ';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        delete_message($pdo, (int) $_POST['delete_id']);
    } elseif (isset($_POST['read_id'])) {
        mark_message_read($pdo, (int) $_POST['read_id']);
    }
    header('Location: messages.php');
    exit;
}

$messages = get_all_messages($pdo);
require __DIR__ . '/includes/admin_header.php';
?>

<h1>ข้อความติดต่อ</h1>
<div class="sub">ข้อความจากฟอร์ม “สนใจสมัครเรียนหรือสอบถามข้อมูล” บนหน้าเว็บไซต์</div>

<div class="admin-card">
  <table>
    <thead>
      <tr><th>วันที่</th><th>ชื่อ</th><th>ติดต่อกลับ</th><th>ข้อความ</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($messages as $m): ?>
      <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
        <td style="white-space:nowrap; color:var(--text-dim);"><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></td>
        <td><?= e($m['full_name']) ?><?= $m['is_read'] ? '' : ' <span style="color:var(--orange); font-size:11px;">● ใหม่</span>' ?></td>
        <td style="color:var(--text-dim);">
          <?php if ($m['phone']): ?><?= e($m['phone']) ?><br><?php endif; ?>
          <?php if ($m['email']): ?><?= e($m['email']) ?><?php endif; ?>
        </td>
        <td style="max-width:340px;"><?= nl2br(e($m['message'])) ?></td>
        <td>
          <div class="row-actions">
            <?php if (!$m['is_read']): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="read_id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn btn-outline btn-sm">ทำเครื่องหมายว่าอ่านแล้ว</button>
            </form>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('ยืนยันการลบข้อความนี้?');" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$messages): ?>
        <tr><td colspan="5" style="color:var(--text-dim);">ยังไม่มีข้อความติดต่อ</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
