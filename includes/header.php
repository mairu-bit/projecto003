<?php
// หมายเหตุ: session_start() ต้องถูกเรียกจากไฟล์บนสุด (เช่น index.php) ก่อนมี output ใดๆ
$loggedIn = !empty($_SESSION['user_id']);
$isAdmin  = $loggedIn && ($_SESSION['role'] ?? null) === 'admin';
?>
<header>
  <div class="brand">
    <span class="brand-mark"></span>
    <div>
      แผนกวิชาช่างยนต์
      <small>AUTOMOTIVE TECHNOLOGY DEPT.</small>
    </div>
  </div>
  <nav>
    <ul>
      <li><a href="#about">เกี่ยวกับแผนก</a></li>
      <li><a href="#courses">หลักสูตร</a></li>
      <li><a href="#labs">ห้องปฏิบัติการ</a></li>
      <li><a href="#contact">ติดต่อ</a></li>
      <?php if ($loggedIn): ?>
        <li><a href="<?= $isAdmin ? 'admin/dashboard.php' : 'account.php' ?>"><?= e($_SESSION['username']) ?></a></li>
        <li><a href="logout.php">ออกจากระบบ</a></li>
      <?php else: ?>
        <li><a href="login.php">เข้าสู่ระบบ</a></li>
        <li><a href="register.php">สมัครสมาชิก</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
