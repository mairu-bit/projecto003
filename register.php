<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $fullname   = trim($_POST['fullname'] ?? '');
    $studentId  = trim($_POST['student_id'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    $old = [
        'username'   => $username,
        'fullname'   => $fullname,
        'student_id' => $studentId,
        'phone'      => $phone,
        'email'      => $email
    ];

    if ($username === '' || mb_strlen($username) < 3) {
        $errors[] = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
    }
    if ($fullname === '') {
        $errors[] = 'กรุณากรอกชื่อ-นามสกุลจริง';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    }
    if ($password !== $confirm) {
        $errors[] = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
    }
    if (!$errors && username_exists($pdo, $username)) {
        $errors[] = 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว กรุณาเลือกชื่ออื่น';
    }

    if (!$errors) {
        $userId = register_user($pdo, $username, $email, $password, $fullname, $phone, $studentId);
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['username']  = $username;
        $_SESSION['fullname']  = $fullname;
        $_SESSION['role']      = 'user';
        header('Location: account.php?welcome=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สมัครสมาชิก — แผนกวิชาช่างยนต์</title>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600&family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="login-shell" style="padding:40px 16px;">
  <form class="login-box" method="post" style="width:440px; max-width:100%;">
    <h1>สมัครสมาชิกผู้เรียน</h1>
    <div class="sub">แผนกวิชาช่างยนต์ วิทยาลัยเทคนิค</div>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <div class="form-grid">
      <div>
        <label for="fullname">ชื่อ - นามสกุลจริง <span style="color:var(--orange);">*</span></label>
        <input type="text" id="fullname" name="fullname" value="<?= e($old['fullname'] ?? '') ?>" placeholder="เช่น นายสมชาย ใจดี" required autofocus>
      </div>
      <div>
        <label for="student_id">รหัสนักศึกษา / รหัสผู้เรียน (ถ้ามี)</label>
        <input type="text" id="student_id" name="student_id" value="<?= e($old['student_id'] ?? '') ?>" placeholder="เช่น 66301010001">
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label for="phone">เบอร์โทรศัพท์</label>
          <input type="tel" id="phone" name="phone" value="<?= e($old['phone'] ?? '') ?>" placeholder="0812345678">
        </div>
        <div>
          <label for="email">อีเมล</label>
          <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" placeholder="user@example.com">
        </div>
      </div>
      <div>
        <label for="username">ชื่อผู้ใช้ (สำหรับ Login) <span style="color:var(--orange);">*</span></label>
        <input type="text" id="username" name="username" value="<?= e($old['username'] ?? '') ?>" placeholder="ตัวอักษรภาษาอังกฤษหรือตัวเลข" required>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label for="password">รหัสผ่าน <span style="color:var(--orange);">*</span></label>
          <input type="password" id="password" name="password" placeholder="อย่างน้อย 6 ตัว" required>
        </div>
        <div>
          <label for="confirm_password">ยืนยันรหัสผ่าน <span style="color:var(--orange);">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="ตรงกับรหัสผ่าน" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">สมัครสมาชิก</button>
    </div>
    <div class="sub" style="margin-top:16px;">มีบัญชีอยู่แล้ว? <a href="login.php" style="color:var(--orange);">เข้าสู่ระบบที่นี่</a></div>
  </form>
</div>
</body>
</html>
