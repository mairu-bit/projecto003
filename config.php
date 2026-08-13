<?php
// อ่านค่าจาก Environment Variables ก่อน ถ้าไม่มีให้ใช้ค่าเริ่มต้น
// (ค่าเริ่มต้นตรงกับ docker-compose.yml ปัจจุบัน จึงยังทำงานได้ทันทีแบบไม่ต้องแก้อะไร)
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'my_database');
define('DB_USER', getenv('DB_USER') ?: 'dbuser');
define('DB_PASS', getenv('DB_PASS') ?: 'dbpassword');

// ตั้งค่า session/timezone กลาง
date_default_timezone_set('Asia/Bangkok');
