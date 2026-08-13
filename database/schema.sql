-- ==========================================================
-- Schema: แผนกวิชาช่างยนต์ (Automotive Dept.) website backend
-- ==========================================================

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    email         VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS courses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    spec_no     VARCHAR(20)  NOT NULL,          -- e.g. "SPEC · 01"
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    icon_svg    TEXT,                            -- inner SVG markup (paths/shapes only)
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS labs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sheet_no    VARCHAR(10)  NOT NULL,          -- e.g. "01"
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    sort_order  INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS enrollments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    course_id    INT NOT NULL,
    enrolled_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_course (user_id, course_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(255) NOT NULL,
    phone      VARCHAR(50),
    email      VARCHAR(255),
    message    TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------
-- Seed data: matches what was previously hardcoded in index.php
-- ----------------------------------------------------------

INSERT INTO courses (spec_no, title, description, icon_svg, sort_order) VALUES
('SPEC · 01', 'เครื่องยนต์แก๊สโซลีน',
 'โครงสร้าง การทำงาน และการซ่อมบำรุงเครื่องยนต์เบนซินตั้งแต่พื้นฐานถึงขั้นวิเคราะห์ปัญหา',
 '<rect x="16" y="10" width="16" height="14" rx="2"/><path d="M20 24v6M28 24v6"/><rect x="17" y="30" width="14" height="4" rx="1"/><path d="M24 34v6"/><circle cx="24" cy="40" r="4"/>', 1),
('SPEC · 02', 'เครื่องยนต์ดีเซล',
 'ระบบฉีดเชื้อเพลิง แรงอัด และการบำรุงรักษาเครื่องยนต์ดีเซลที่ใช้ในยานยนต์และเครื่องจักรกล',
 '<path d="M18 8h6l2 6h-10z"/><path d="M20 14v6"/><rect x="12" y="20" width="16" height="18" rx="2"/><path d="M16 26h8M16 31h8"/>', 2),
('SPEC · 03', 'ระบบไฟฟ้ายานยนต์',
 'วงจรไฟฟ้ารถยนต์ แบตเตอรี่ ระบบสตาร์ท-ชาร์จ และการวินิจฉัยด้วยเครื่องมือวัดสมัยใหม่',
 '<path d="M26 6 14 26h8l-2 16 14-22h-8z" stroke-linejoin="round"/>', 3),
('SPEC · 04', 'ระบบส่งกำลังและช่วงล่าง',
 'คลัตช์ เกียร์ เพลาขับ ระบบเบรกและช่วงล่าง ทั้งภาคทฤษฎีและการถอด-ประกอบจริง',
 '<circle cx="18" cy="30" r="6"/><circle cx="34" cy="18" r="4"/><path d="M22 26l8-6M22 33l14 3"/>', 4);

INSERT INTO labs (sheet_no, title, description, sort_order) VALUES
('01', 'ห้องปฏิบัติการเครื่องยนต์เบนซิน–ดีเซล',
 'เครื่องยนต์ตัดผ่าและเครื่องยนต์สภาพใช้งานจริง สำหรับฝึกถอด ประกอบ และวิเคราะห์อาการเสีย', 1),
('02', 'ห้องปฏิบัติการไฟฟ้ายานยนต์',
 'ชุดฝึกวงจรไฟฟ้ารถยนต์และเครื่องมือวัดสำหรับวินิจฉัยระบบไฟฟ้า-อิเล็กทรอนิกส์', 2),
('03', 'ห้องปฏิบัติการระบบส่งกำลัง',
 'ชุดเกียร์ คลัตช์ และเพลาขับสำหรับฝึกการถอดประกอบและตรวจซ่อมระบบส่งกำลัง', 3),
('04', 'ลานฝึกซ่อมบำรุงยานยนต์',
 'พื้นที่ปฏิบัติงานพร้อมลิฟต์ยกรถและเครื่องมือช่างมาตรฐาน สำหรับงานซ่อมบำรุงตามสภาพจริง', 4);

-- Default admin login: username "admin" / password "admin123"
-- ⚠️ เปลี่ยนรหัสผ่านทันทีหลังติดตั้งจริง (ดูวิธีใน README)
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2b$10$8GO08IUYj0LFxqh5m1ppIOvxKU5aryzMl2/Fx7eBQ2pEBt/pm.wNa', 'admin');
