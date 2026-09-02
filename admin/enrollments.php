<?php
require_once __DIR__ . '/includes/admin_auth.php';
$pageTitle = 'จัดการผู้ลงทะเบียนหลักสูตร';

$successMsg = null;
$errorMsg = null;

// เปลี่ยนสถานะการลงทะเบียน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $enrollId = (int)($_POST['enrollment_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    if ($enrollId && in_array($newStatus, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
        update_enrollment_status($pdo, $enrollId, $newStatus);
        $successMsg = 'อัปเดตสถานะการลงทะเบียนเรียบร้อยแล้วเป็น "' . status_label($newStatus) . '"';
    }
}

// ลบรายการลงทะเบียน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $enrollId = (int)($_POST['enrollment_id'] ?? 0);
    if ($enrollId) {
        delete_enrollment($pdo, $enrollId);
        $successMsg = 'ลบรายการลงทะเบียนเรียบร้อยแล้ว';
    }
}

$courseFilter = !empty($_GET['course']) ? (int)$_GET['course'] : null;
$statusFilter = !empty($_GET['status']) ? trim($_GET['status']) : null;
$search       = isset($_GET['q']) ? trim($_GET['q']) : null;

$enrollments = get_all_enrollments($pdo, $courseFilter, $statusFilter, $search);
$allCourses  = get_all_courses($pdo);

// สรุปยอดตามสถานะ
$pendingCount   = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'")->fetchColumn();
$confirmedCount = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'confirmed'")->fetchColumn();
$completedCount = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'")->fetchColumn();
$cancelledCount = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'cancelled'")->fetchColumn();

require __DIR__ . '/includes/admin_header.php';
?>

<h1>จัดการผู้ลงทะเบียนหลักสูตร</h1>
<div class="sub">ตรวจสอบรายชื่อผู้เรียน อนุมัติการลงทะเบียน และติดตามสถานะการเรียน</div>

<?php if ($successMsg): ?><div class="alert alert-ok"><?= e($successMsg) ?></div><?php endif; ?>
<?php if ($errorMsg): ?><div class="alert alert-error"><?= e($errorMsg) ?></div><?php endif; ?>

<!-- Quick Stat Cards -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr); margin-bottom:20px;">
  <div class="stat-card" style="border-left:4px solid var(--yellow);">
    <div class="n" style="color:var(--yellow);"><?= $pendingCount ?></div>
    <div class="l">รอการอนุมัติ (Pending)</div>
  </div>
  <div class="stat-card" style="border-left:4px solid var(--ok);">
    <div class="n" style="color:var(--ok);"><?= $confirmedCount ?></div>
    <div class="l">อนุมัติแล้ว (Confirmed)</div>
  </div>
  <div class="stat-card" style="border-left:4px solid var(--blue);">
    <div class="n" style="color:var(--blue);"><?= $completedCount ?></div>
    <div class="l">ผ่านการอบรม (Completed)</div>
  </div>
  <div class="stat-card" style="border-left:4px solid var(--danger);">
    <div class="n" style="color:var(--danger);"><?= $cancelledCount ?></div>
    <div class="l">ยกเลิกแล้ว (Cancelled)</div>
  </div>
</div>

<!-- Filter Bar -->
<form method="get" class="filter-bar">
  <div style="flex:1; min-width:200px;">
    <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="ค้นหา: ชื่อ, รหัสนักศึกษา, เบอร์โทร, username" style="width:100%;">
  </div>
  <div>
    <select name="course" onchange="this.form.submit()">
      <option value="">-- ทุกหลักสูตร --</option>
      <?php foreach ($allCourses as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $courseFilter === (int)$c['id'] ? 'selected' : '' ?>>
          <?= e($c['spec_no']) ?> - <?= e($c['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <select name="status" onchange="this.form.submit()">
      <option value="">-- ทุกสถานะ --</option>
      <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>รอการอนุมัติ (Pending)</option>
      <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>อนุมัติแล้ว (Confirmed)</option>
      <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>ผ่านการอบรม (Completed)</option>
      <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>ยกเลิกแล้ว (Cancelled)</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
  <?php if ($courseFilter || $statusFilter || $search): ?>
    <a href="enrollments.php" class="btn btn-outline btn-sm">ล้างตัวกรอง</a>
  <?php endif; ?>
</form>

<div class="admin-card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <div style="font-size:13px; color:var(--text-dim);">พบข้อมูลทั้งหมด <b><?= count($enrollments) ?></b> รายการ</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>วันที่ลงทะเบียน</th>
        <th>ข้อมูลผู้เรียน</th>
        <th>การติดต่อ</th>
        <th>หลักสูตร</th>
        <th>สถานะ</th>
        <th>จัดการสถานะ</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($enrollments as $en): ?>
      <tr>
        <td style="white-space:nowrap; color:var(--text-dim); font-size:13px;">
          <?= format_date_thai($en['enrolled_at']) ?>
          <div style="font-size:11.5px; color:var(--text-dimmer);"><?= date('H:i น.', strtotime($en['enrolled_at'])) ?></div>
        </td>
        <td>
          <div style="font-weight:600; color:#fff; font-size:14.5px;"><?= e($en['fullname'] ?: $en['username']) ?></div>
          <div style="font-size:12px; color:var(--text-dim);">
            Username: <b style="color:#d8d9de;"><?= e($en['username']) ?></b>
            <?php if (!empty($en['student_id'])): ?>
              · รหัส: <span style="color:var(--orange);"><?= e($en['student_id']) ?></span>
            <?php endif; ?>
          </div>
        </td>
        <td style="font-size:13px;">
          <?php if (!empty($en['phone'])): ?>
            <div>📞 <a href="tel:<?= e($en['phone']) ?>" style="color:#fff; text-decoration:none;"><?= e($en['phone']) ?></a></div>
          <?php endif; ?>
          <?php if (!empty($en['email'])): ?>
            <div style="color:var(--text-dim); font-size:12px;">✉️ <?= e($en['email']) ?></div>
          <?php endif; ?>
          <?php if (empty($en['phone']) && empty($en['email'])): ?>
            <span style="color:var(--text-dimmer);">-</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="font-size:12px; color:var(--orange); font-weight:600; font-family:'Chakra Petch',sans-serif;"><?= e($en['spec_no']) ?></div>
          <div style="font-weight:500; color:#fff; font-size:13.5px;"><?= e($en['course_title']) ?></div>
        </td>
        <td>
          <?= status_badge($en['status']) ?>
        </td>
        <td>
          <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
            <!-- Dropdown Change Status -->
            <form method="post" style="margin:0; display:inline-flex; align-items:center; gap:4px;">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="enrollment_id" value="<?= $en['id'] ?>">
              <select name="status" onchange="this.form.submit()" style="padding:4px 8px; font-size:12.5px; width:auto; border-radius:3px;">
                <option value="pending" <?= $en['status'] === 'pending' ? 'selected' : '' ?>>⏳ รออนุมัติ</option>
                <option value="confirmed" <?= $en['status'] === 'confirmed' ? 'selected' : '' ?>>✅ อนุมัติ</option>
                <option value="completed" <?= $en['status'] === 'completed' ? 'selected' : '' ?>>🎓 ผ่านการอบรม</option>
                <option value="cancelled" <?= $en['status'] === 'cancelled' ? 'selected' : '' ?>>❌ ยกเลิก</option>
              </select>
            </form>

            <form method="post" onsubmit="return confirm('ยืนยันการลบรายการลงทะเบียนนี้ถาวร?');" style="margin:0; display:inline;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="enrollment_id" value="<?= $en['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px; font-size:11.5px;" title="ลบถาวร">🗑️</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$enrollments): ?>
        <tr><td colspan="6" style="color:var(--text-dim); text-align:center; padding:30px;">ไม่พบรายการผู้ลงทะเบียนที่ตรงกับเงื่อนไข</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
