<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['course_id'])) {
    header('Location: account.php');
    exit;
}

unenroll_from_course($pdo, (int) $_SESSION['user_id'], (int) $_POST['course_id']);

header('Location: account.php');
exit;
