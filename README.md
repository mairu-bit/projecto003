# แผนกวิชาช่างยนต์ — Backend

เว็บไซต์ PHP + MySQL (Docker) พร้อมระบบแอดมินสำหรับจัดการหลักสูตร ห้องปฏิบัติการ
และดูข้อความจากฟอร์มติดต่อ

## เริ่มต้นใช้งาน

```bash
docker compose up -d --build
```

- เว็บไซต์หลัก: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (user: `root`, password: `rootpassword`)
- เข้าสู่ระบบ (admin หรือ user ใช้หน้าเดียวกัน): http://localhost:8080/login.php
- สมัครสมาชิก (สร้างบัญชี role `user` เท่านั้น): http://localhost:8080/register.php

ระบบ login ใช้ตาราง `users` ร่วมกันทั้ง admin และสมาชิกทั่วไป โดยแยกด้วยคอลัมน์ `role`
(`admin` / `user`) — หลัง login ระบบจะพาไปหน้าที่ถูกต้องให้อัตโนมัติ
(`admin` → `/admin/dashboard.php`, `user` → `/account.php`) บัญชี admin สร้างได้เฉพาะ
ผ่านฐานข้อมูลโดยตรง (ผู้ใช้ทั่วไป self-register ไม่ได้)

> ⚠️ ถ้าเคยรัน `docker compose up` มาก่อนหน้านี้แล้ว (ตาราง `admins` เดิม) ต้องรีเซ็ต
> volume ก่อนเพื่อให้ schema ใหม่ (`users` + คอลัมน์ `role`) ถูกสร้าง:
> ```bash
> docker compose down -v
> docker compose up -d --build
> ```

ฐานข้อมูลจะถูกสร้างและ seed ข้อมูลอัตโนมัติจาก `database/schema.sql`
ในการรัน `docker compose up` **ครั้งแรกเท่านั้น** (MySQL จะรันสคริปต์ใน
`/docker-entrypoint-initdb.d/` เฉพาะตอนที่ volume `db_data` ยังว่างอยู่)

หากเคยรันมาก่อนแล้วและต้องการรีเซ็ตฐานข้อมูลใหม่:

```bash
docker compose down -v
docker compose up -d --build
```

## บัญชีแอดมินเริ่มต้น

| Username | Password |
|---|---|
| `admin` | `admin123` |

⚠️ **เปลี่ยนรหัสผ่านทันทีก่อนใช้งานจริง** วิธีเปลี่ยน:

```bash
php -r "echo password_hash('รหัสผ่านใหม่', PASSWORD_DEFAULT);"
```

แล้วนำ hash ที่ได้ไปอัปเดตในตาราง `admins` ผ่าน phpMyAdmin (คอลัมน์ `password_hash`)

## โครงสร้างโปรเจกต์

```
index.php              หน้าเว็บหลัก (ดึงหลักสูตร/ห้องปฏิบัติการจาก DB)
contact_submit.php     รับข้อมูลจากฟอร์มติดต่อ บันทึกลง DB
db.php / config.php    การเชื่อมต่อฐานข้อมูล (PDO)
includes/
  functions.php        ฟังก์ชันดึง/บันทึกข้อมูลทั้งหมด (courses, labs, messages)
  header.php / footer.php
admin/
  login.php / logout.php
  dashboard.php         สรุปภาพรวม
  courses.php / course_form.php   จัดการหลักสูตร (เพิ่ม/แก้ไข/ลบ)
  labs.php / lab_form.php         จัดการห้องปฏิบัติการ (เพิ่ม/แก้ไข/ลบ)
  messages.php          ดู/ทำเครื่องหมายอ่านแล้ว/ลบ ข้อความติดต่อ
database/schema.sql     โครงสร้างตาราง + ข้อมูลตั้งต้น
```

## ตารางในฐานข้อมูล

- **users** — บัญชีผู้ใช้ทั้ง admin และสมาชิกทั่วไป แยกด้วยคอลัมน์ `role`
- **courses** — หลักสูตรที่แสดงในหน้า "หลักสูตรที่เปิดสอน"
- **labs** — ห้องปฏิบัติการที่แสดงในหน้า "ห้องปฏิบัติการ"
- **enrollments** — การลงทะเบียนเรียนของสมาชิก (เชื่อม `users` กับ `courses`, กันลงทะเบียนซ้ำด้วย unique key)
- **contact_messages** — ข้อความจากฟอร์มติดต่อ

## การลงทะเบียนหลักสูตร

- สมาชิก (role `user`) ที่ login แล้ว จะเห็นปุ่ม "ลงทะเบียนเรียน" ใต้การ์ดหลักสูตรแต่ละใบในหน้าเว็บหลัก
- ลงทะเบียนซ้ำหลักสูตรเดิมไม่ได้ (unique constraint ในฐานข้อมูล)
- ดู/ยกเลิกหลักสูตรที่ลงทะเบียนไว้ได้ที่หน้า `/account.php`
- แอดมินดูรายชื่อผู้ลงทะเบียนทั้งหมดได้ที่ `/admin/enrollments.php` และดูจำนวนผู้ลงทะเบียนต่อหลักสูตรได้ที่ `/admin/courses.php`

## หมายเหตุด้านความปลอดภัย

- ทุก query ใช้ PDO prepared statements
- รหัสผ่านแอดมินเก็บด้วย `password_hash()` / ตรวจสอบด้วย `password_verify()`
- ช่อง "ไอคอน SVG" ของหลักสูตรแก้ไขได้เฉพาะผู้ที่ login เป็นแอดมินเท่านั้น (เชื่อถือได้เทียบเท่าเนื้อหาอื่นในระบบแอดมิน)
- เปลี่ยน `MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD` ใน `docker-compose.yml` ก่อนใช้งานจริงนอกเครื่อง dev
