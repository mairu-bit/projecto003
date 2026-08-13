<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'account.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = find_user_by_username($pdo, $username);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'account.php'));
        exit;
    }
    $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ — แผนกวิชาช่างยนต์</title>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600&family=Sarabun:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="login-shell">
  <form class="login-box" method="post">
    <h1>เข้าสู่ระบบ</h1>
    <div class="sub">แผนกวิชาช่างยนต์</div>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <div class="form-grid">
      <div>
        <label for="username">ชื่อผู้ใช้</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>
      <div>
        <label for="password">รหัสผ่าน</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">เข้าสู่ระบบ</button>
    </div>
    <div class="sub" style="margin-top:16px;">ยังไม่มีบัญชี? <a href="register.php" style="color:var(--orange);">สมัครสมาชิก</a></div>
  </form>
</div>
</body>
</html>
