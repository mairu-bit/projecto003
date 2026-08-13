<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['role'] ?? null) === 'admin') {
    // แอดมินไม่ลงทะเบียนเรียน กันการกดผิดจากบัญชีแอดมิน
    header('Location: admin/dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['course_id'])) {
    header('Location: index.php#courses');
    exit;
}

$courseId = (int) $_POST['course_id'];
$course   = get_course($pdo, $courseId);

if (!$course) {
    $_SESSION['enroll_flash'] = ['ok' => false, 'message' => 'ไม่พบหลักสูตรนี้'];
    header('Location: index.php#courses');
    exit;
}

$success = enroll_in_course($pdo, (int) $_SESSION['user_id'], $courseId);

$_SESSION['enroll_flash'] = $success
    ? ['ok' => true, 'message' => 'ลงทะเบียนหลักสูตร "' . $course['title'] . '" เรียบร้อยแล้ว']
    : ['ok' => true, 'message' => 'คุณลงทะเบียนหลักสูตรนี้ไว้อยู่แล้ว'];

header('Location: index.php#courses');
exit;
