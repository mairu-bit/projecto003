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
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $old = ['username' => $username, 'email' => $email];

    if ($username === '' || mb_strlen($username) < 3) $errors[] = 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'อีเมลไม่ถูกต้อง';
    if (mb_strlen($password) < 6) $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    if ($password !== $confirm) $errors[] = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
    if (!$errors && username_exists($pdo, $username)) $errors[] = 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว';

    if (!$errors) {
        $userId = register_user($pdo, $username, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['username']  = $username;
        $_SESSION['role']      = 'user';
        header('Location: account.php');
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
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600&family=Sarabun:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="login-shell">
  <form class="login-box" method="post" style="width:380px;">
    <h1>สมัครสมาชิก</h1>
    <div class="sub">แผนกวิชาช่างยนต์</div>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <div class="form-grid">
      <div>
        <label for="username">ชื่อผู้ใช้</label>
        <input type="text" id="username" name="username" value="<?= e($old['username'] ?? '') ?>" required autofocus>
      </div>
      <div>
        <label for="email">อีเมล (ถ้ามี)</label>
        <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>">
      </div>
      <div>
        <label for="password">รหัสผ่าน</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div>
        <label for="confirm_password">ยืนยันรหัสผ่าน</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">สมัครสมาชิก</button>
    </div>
    <div class="sub" style="margin-top:16px;">มีบัญชีอยู่แล้ว? <a href="login.php" style="color:var(--orange);">เข้าสู่ระบบ</a></div>
  </form>
</div>
</body>
</html>
