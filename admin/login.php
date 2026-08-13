<?php
// เข้าสู่ระบบรวมอยู่ที่ /login.php แล้ว (ใช้ตาราง users เดียวกันทั้ง admin และ user)
// ถ้า login เป็น role admin ระบบจะพากลับมาที่ admin/dashboard.php ให้เอง
header('Location: ../login.php');
exit;
