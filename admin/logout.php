<?php
// ออกจากระบบรวมอยู่ที่ /logout.php แล้ว (ใช้ session เดียวกันทั้ง admin และ user)
header('Location: ../logout.php');
exit;
