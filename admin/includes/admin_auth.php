<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'admin') {
    header('Location: login.php');
    exit;
}
// ramet  ส่วน backend
