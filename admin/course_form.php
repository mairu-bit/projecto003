<?php
require_once __DIR__ . '/includes/admin_auth.php';

$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;
$course = $id ? get_course($pdo, $id) : null;
if ($id && !$course) {
    header('Location: courses.php');
    exit;
}
$pageTitle = $course ? 'แก้ไขหลักสูตร' : 'เพิ่มหลักสูตร';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'spec_no'     => trim($_POST['spec_no'] ?? ''),
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'icon_svg'    => trim($_POST['icon_svg'] ?? ''),
        'max_seats'   => (int) ($_POST['max_seats'] ?? 30),
        'start_date'  => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        'end_date'    => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($data['title'] === '') $errors[] = 'กรุณากรอกชื่อหลักสูตร';

    if (!$errors) {
        if ($id) {
            update_course($pdo, $id, $data);
        } else {
            create_course($pdo, $data);
        }
        header('Location: courses.php?saved=1');
        exit;
    }
    $course = array_merge($course ?? [], $data);
}

require __DIR__ . '/includes/admin_header.php';
?>

<h1><?= e($pageTitle) ?></h1>
<div class="sub">ข้อมูลนี้จะแสดงในส่วน “หลักสูตรที่เปิดสอน” บนหน้าเว็บไซต์</div>

<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="admin-card">
  <form method="post" class="form-grid">
    <div style="display:grid; grid-template-columns:1fr 2fr; gap:14px;">
      <div>
        <label>รหัสหลักสูตร (เช่น SPEC · 01)</label>
        <input type="text" name="spec_no" value="<?= e($course['spec_no'] ?? '') ?>" placeholder="SPEC · 01">
      </div>
      <div>
        <label>ชื่อหลักสูตร <span style="color:var(--orange);">*</span></label>
        <input type="text" name="title" value="<?= e($course['title'] ?? '') ?>" required>
      </div>
    </div>

    <div>
      <label>คำอธิบายหลักสูตร</label>
      <textarea name="description" rows="3" style="font-family:'Sarabun',sans-serif;"><?= e($course['description'] ?? '') ?></textarea>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
      <div>
        <label>จำนวนที่นั่งสูงสุด (คน)</label>
        <input type="number" name="max_seats" min="1" max="1000" value="<?= e((string)($course['max_seats'] ?? 30)) ?>" required>
      </div>
      <div>
        <label>วันที่เริ่มเรียน</label>
        <input type="date" name="start_date" value="<?= e($course['start_date'] ?? '') ?>">
      </div>
      <div>
        <label>วันที่สิ้นสุด</label>
        <input type="date" name="end_date" value="<?= e($course['end_date'] ?? '') ?>">
      </div>
    </div>

    <div>
      <label>ไอคอน (SVG markup ภายใน &lt;svg&gt;, viewBox 0 0 48 48) — ปล่อยว่างได้</label>
      <textarea name="icon_svg" rows="3" style="font-family:monospace;"><?= e($course['icon_svg'] ?? '') ?></textarea>
    </div>

    <div>
      <label>ลำดับการแสดงผล</label>
      <input type="number" name="sort_order" value="<?= e((string)($course['sort_order'] ?? 0)) ?>" style="width:140px;">
    </div>

    <div class="row-actions" style="margin-top:8px;">
      <button type="submit" class="btn btn-primary">บันทึกหลักสูตร</button>
      <a href="courses.php" class="btn btn-outline">ยกเลิก</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
