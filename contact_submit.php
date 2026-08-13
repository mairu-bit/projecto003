<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contact');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$email     = trim($_POST['email'] ?? '');
$message   = trim($_POST['message'] ?? '');

$errors = [];
if ($full_name === '') $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
if ($message === '')   $errors[] = 'กรุณากรอกข้อความ';
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'อีเมลไม่ถูกต้อง';

if ($errors) {
    $_SESSION['contact_flash'] = ['ok' => false, 'message' => implode(' / ', $errors)];
    header('Location: index.php#contact');
    exit;
}

try {
    create_contact_message($pdo, [
        'full_name' => $full_name,
        'phone'     => $phone !== '' ? $phone : null,
        'email'     => $email !== '' ? $email : null,
        'message'   => $message,
    ]);
    $_SESSION['contact_flash'] = ['ok' => true, 'message' => 'ส่งข้อความเรียบร้อยแล้ว ทางแผนกจะติดต่อกลับโดยเร็วที่สุด'];
} catch (PDOException $e) {
    $_SESSION['contact_flash'] = ['ok' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง'];
}

header('Location: index.php#contact');
exit;
