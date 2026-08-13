<?php
/**
 * ฟังก์ชันกลางสำหรับดึง/บันทึกข้อมูลจากฐานข้อมูล
 * ต้อง require __DIR__ . '/../db.php' ($pdo) ก่อนเรียกใช้ไฟล์นี้
 */

// ---------- Courses ----------

function get_all_courses(PDO $pdo): array {
    return $pdo->query("SELECT * FROM courses ORDER BY sort_order ASC, id ASC")->fetchAll();
}

function get_course(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function create_course(PDO $pdo, array $data): void {
    $stmt = $pdo->prepare(
        "INSERT INTO courses (spec_no, title, description, icon_svg, sort_order) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$data['spec_no'], $data['title'], $data['description'], $data['icon_svg'], $data['sort_order']]);
}

function update_course(PDO $pdo, int $id, array $data): void {
    $stmt = $pdo->prepare(
        "UPDATE courses SET spec_no = ?, title = ?, description = ?, icon_svg = ?, sort_order = ? WHERE id = ?"
    );
    $stmt->execute([$data['spec_no'], $data['title'], $data['description'], $data['icon_svg'], $data['sort_order'], $id]);
}

function delete_course(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
}

// ---------- Labs ----------

function get_all_labs(PDO $pdo): array {
    return $pdo->query("SELECT * FROM labs ORDER BY sort_order ASC, id ASC")->fetchAll();
}

function get_lab(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM labs WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function create_lab(PDO $pdo, array $data): void {
    $stmt = $pdo->prepare(
        "INSERT INTO labs (sheet_no, title, description, sort_order) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$data['sheet_no'], $data['title'], $data['description'], $data['sort_order']]);
}

function update_lab(PDO $pdo, int $id, array $data): void {
    $stmt = $pdo->prepare(
        "UPDATE labs SET sheet_no = ?, title = ?, description = ?, sort_order = ? WHERE id = ?"
    );
    $stmt->execute([$data['sheet_no'], $data['title'], $data['description'], $data['sort_order'], $id]);
}

function delete_lab(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare("DELETE FROM labs WHERE id = ?");
    $stmt->execute([$id]);
}

// ---------- Users (combined admin/user accounts) ----------

function find_user_by_username(PDO $pdo, string $username): ?array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function username_exists(PDO $pdo, string $username): bool {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return (bool) $stmt->fetch();
}

// สมัครสมาชิกใหม่เสมอเป็น role 'user' (สร้างบัญชี admin ได้เฉพาะจากฐานข้อมูลโดยตรงเท่านั้น)
function register_user(PDO $pdo, string $username, string $email, string $password): int {
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')"
    );
    $stmt->execute([$username, $email !== '' ? $email : null, password_hash($password, PASSWORD_DEFAULT)]);
    return (int) $pdo->lastInsertId();
}

// ---------- Enrollments ----------

function enroll_in_course(PDO $pdo, int $userId, int $courseId): bool {
    try {
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->execute([$userId, $courseId]);
        return true;
    } catch (PDOException $e) {
        // ลงทะเบียนซ้ำ (unique key ชนกัน) ถือว่าลงทะเบียนอยู่แล้ว ไม่ใช่ error ร้ายแรง
        return false;
    }
}

function unenroll_from_course(PDO $pdo, int $userId, int $courseId): void {
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
}

function is_user_enrolled(PDO $pdo, int $userId, int $courseId): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    return (bool) $stmt->fetchColumn();
}

// คืนค่า array ของ course_id ทั้งหมดที่ user คนนี้ลงทะเบียนไว้ (ใช้เช็คสถานะปุ่มบนหน้าเว็บได้เร็ว)
function get_enrolled_course_ids(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
    $stmt->execute([$userId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function get_enrollments_for_user(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare(
        "SELECT e.id, e.enrolled_at, c.id AS course_id, c.spec_no, c.title
         FROM enrollments e
         JOIN courses c ON c.id = e.course_id
         WHERE e.user_id = ?
         ORDER BY e.enrolled_at DESC"
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_enrollment_count_for_course(PDO $pdo, int $courseId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn();
}

// สำหรับหน้าแอดมิน: รายชื่อผู้ลงทะเบียนทั้งหมด พร้อมชื่อคอร์สและผู้ใช้
function get_all_enrollments(PDO $pdo): array {
    return $pdo->query(
        "SELECT e.id, e.enrolled_at, u.username, u.email, c.title AS course_title, c.spec_no
         FROM enrollments e
         JOIN users u   ON u.id = e.user_id
         JOIN courses c ON c.id = e.course_id
         ORDER BY e.enrolled_at DESC"
    )->fetchAll();
}

// ---------- Contact messages ----------

function create_contact_message(PDO $pdo, array $data): void {
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages (full_name, phone, email, message) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$data['full_name'], $data['phone'], $data['email'], $data['message']]);
}

function get_all_messages(PDO $pdo): array {
    return $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
}

function count_unread_messages(PDO $pdo): int {
    return (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
}

function mark_message_read(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
}

function delete_message(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
}

// ---------- Small helpers ----------

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key, array $source = []): string {
    return e($source[$key] ?? '');
}
