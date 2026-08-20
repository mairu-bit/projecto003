<?php
require_once __DIR__ . '/includes/admin_auth.php';

$id  = isset($_GET['id']) ? (int) $_GET['id'] : null;
$lab = $id ? get_lab($pdo, $id) : null;
if ($id && !$lab) {
    header('Location: labs.php');
    exit;
}
$pageTitle = $lab ? 'แก้ไขห้องปฏิบัติการ' : 'เพิ่มห้องปฏิบัติการ';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'sheet_no'    => trim($_POST['sheet_no'] ?? ''),
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($data['title'] === '') $errors[] = 'กรุณากรอกชื่อห้องปฏิบัติการ';

    if (!$errors) {
        if ($id) {
            update_lab($pdo, $id, $data);
        } else {
            create_lab($pdo, $data);
        }
        header('Location: labs.php?saved=1');
        exit;
    }
    $lab = array_merge($lab ?? [], $data);
}

require __DIR__ . '/includes/admin_header.php';
?>

<h1><?= e($pageTitle) ?></h1>
<div class="sub">ข้อมูลนี้จะแสดงในส่วน “ห้องปฏิบัติการ” บนหน้าเว็บไซต์</div>

<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<div class="admin-card">
  <form method="post" class="form-grid">
    <div>
      <label>เลข SHEET (เช่น 01)</label>
      <input type="text" name="sheet_no" value="<?= e($lab['sheet_no'] ?? '') ?>">
    </div>
    <div>
      <label>ชื่อห้องปฏิบัติการ</label>
      <input type="text" name="title" value="<?= e($lab['title'] ?? '') ?>" required>
    </div>
    <div>
      <label>คำอธิบาย</label>
      <textarea name="description" rows="3" style="font-family:'Sarabun',sans-serif;"><?= e($lab['description'] ?? '') ?></textarea>
    </div>
    <div>
      <label>ลำดับการแสดงผล</label>
      <input type="number" name="sort_order" value="<?= e((string)($lab['sort_order'] ?? 0)) ?>">
    </div>
    <div class="row-actions">
      <button type="submit" class="btn btn-primary">บันทึก</button>
      <a href="labs.php" class="btn btn-outline">ยกเลิก</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
