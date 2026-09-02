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
        "INSERT INTO courses (spec_no, title, description, icon_svg, max_seats, start_date, end_date, sort_order) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $data['spec_no'],
        $data['title'],
        $data['description'],
        $data['icon_svg'],
        !empty($data['max_seats']) ? (int)$data['max_seats'] : 30,
        !empty($data['start_date']) ? $data['start_date'] : null,
        !empty($data['end_date']) ? $data['end_date'] : null,
        $data['sort_order'] ?? 0
    ]);
}

function update_course(PDO $pdo, int $id, array $data): void {
    $stmt = $pdo->prepare(
        "UPDATE courses SET spec_no = ?, title = ?, description = ?, icon_svg = ?, max_seats = ?, start_date = ?, end_date = ?, sort_order = ? 
         WHERE id = ?"
    );
    $stmt->execute([
        $data['spec_no'],
        $data['title'],
        $data['description'],
        $data['icon_svg'],
        !empty($data['max_seats']) ? (int)$data['max_seats'] : 30,
        !empty($data['start_date']) ? $data['start_date'] : null,
        !empty($data['end_date']) ? $data['end_date'] : null,
        $data['sort_order'] ?? 0,
        $id
    ]);
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

function get_user_by_id(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function username_exists(PDO $pdo, string $username): bool {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return (bool) $stmt->fetch();
}

// สมัครสมาชิกใหม่เสมอเป็น role 'user'
function register_user(
    PDO $pdo, 
    string $username, 
    string $email, 
    string $password, 
    ?string $fullname = null, 
    ?string $phone = null, 
    ?string $studentId = null
): int {
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, fullname, phone, student_id, password_hash, role) 
         VALUES (?, ?, ?, ?, ?, ?, 'user')"
    );
    $stmt->execute([
        $username,
        $email !== '' ? $email : null,
        $fullname !== '' ? $fullname : null,
        $phone !== '' ? $phone : null,
        $studentId !== '' ? $studentId : null,
        password_hash($password, PASSWORD_DEFAULT)
    ]);
    return (int) $pdo->lastInsertId();
}

function update_user_profile(PDO $pdo, int $userId, array $data): void {
    $stmt = $pdo->prepare(
        "UPDATE users SET fullname = ?, email = ?, phone = ?, student_id = ? WHERE id = ?"
    );
    $stmt->execute([
        $data['fullname'] !== '' ? $data['fullname'] : null,
        $data['email'] !== '' ? $data['email'] : null,
        $data['phone'] !== '' ? $data['phone'] : null,
        $data['student_id'] !== '' ? $data['student_id'] : null,
        $userId
    ]);
}

function update_user_password(PDO $pdo, int $userId, string $newPassword): void {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
}

// ---------- Enrollments ----------

function get_active_enrollment_count(PDO $pdo, int $courseId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status != 'cancelled'");
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn();
}

function is_course_full(PDO $pdo, int $courseId): bool {
    $course = get_course($pdo, $courseId);
    if (!$course || empty($course['max_seats']) || (int)$course['max_seats'] <= 0) {
        return false;
    }
    $activeCount = get_active_enrollment_count($pdo, $courseId);
    return $activeCount >= (int)$course['max_seats'];
}

function enroll_in_course(PDO $pdo, int $userId, int $courseId): array {
    $course = get_course($pdo, $courseId);
    if (!$course) {
        return ['ok' => false, 'message' => 'ไม่พบหลักสูตรนี้'];
    }

    // ตรวจสอบว่าเคยลงทะเบียนหรือไม่
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'cancelled') {
            // เคยยกเลิก ให้กลับมาเป็น pending ได้หากที่นั่งยังไม่เต็ม
            if (is_course_full($pdo, $courseId)) {
                return ['ok' => false, 'message' => 'ขออภัย หลักสูตรนี้ที่นั่งเต็มแล้ว'];
            }
            $upd = $pdo->prepare("UPDATE enrollments SET status = 'pending', enrolled_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$existing['id']]);
            return ['ok' => true, 'message' => 'ลงทะเบียนหลักสูตร "' . $course['title'] . '" เรียบร้อยแล้ว (รอการอนุมัติ)'];
        }
        return ['ok' => true, 'message' => 'คุณลงทะเบียนหลักสูตรนี้ไว้อยู่แล้ว (สถานะ: ' . status_label($existing['status']) . ')'];
    }

    // ตรวจสอบที่นั่งเต็มหรือไม่
    if (is_course_full($pdo, $courseId)) {
        return ['ok' => false, 'message' => 'ขออภัย หลักสูตรนี้ที่นั่งเต็มแล้ว (จำกัด ' . (int)$course['max_seats'] . ' ที่นั่ง)'];
    }

    try {
        $ins = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, status) VALUES (?, ?, 'pending')");
        $ins->execute([$userId, $courseId]);
        return ['ok' => true, 'message' => 'ลงทะเบียนหลักสูตร "' . $course['title'] . '" เรียบร้อยแล้ว (รอการอนุมัติจากแอดมิน)'];
    } catch (PDOException $e) {
        return ['ok' => false, 'message' => 'เกิดข้อผิดพลาดในการลงทะเบียน'];
    }
}

function unenroll_from_course(PDO $pdo, int $userId, int $courseId): void {
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'cancelled' WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
}

function update_enrollment_status(PDO $pdo, int $enrollmentId, string $status): bool {
    $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }
    $stmt = $pdo->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $enrollmentId]);
}

function delete_enrollment(PDO $pdo, int $enrollmentId): void {
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->execute([$enrollmentId]);
}

function is_user_enrolled(PDO $pdo, int $userId, int $courseId): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ? AND status != 'cancelled'");
    $stmt->execute([$userId, $courseId]);
    return (bool) $stmt->fetchColumn();
}

function get_user_enrollment(PDO $pdo, int $userId, int $courseId): ?array {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// คืนค่า map [course_id => status] ของ user คนนี้
function get_user_enrolled_map(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT course_id, status FROM enrollments WHERE user_id = ?");
    $stmt->execute([$userId]);
    $map = [];
    while ($row = $stmt->fetch()) {
        $map[(int)$row['course_id']] = $row['status'];
    }
    return $map;
}

function get_enrollments_for_user(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare(
        "SELECT e.id, e.status, e.enrolled_at, e.updated_at, c.id AS course_id, c.spec_no, c.title, c.max_seats, c.start_date, c.end_date
         FROM enrollments e
         JOIN courses c ON c.id = e.course_id
         WHERE e.user_id = ?
         ORDER BY e.enrolled_at DESC"
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_enrollment_count_for_course(PDO $pdo, int $courseId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status != 'cancelled'");
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn();
}

// สำหรับหน้าแอดมิน: รายชื่อผู้ลงทะเบียนทั้งหมด พร้อม Filter และ Search
function get_all_enrollments(PDO $pdo, ?int $courseFilter = null, ?string $statusFilter = null, ?string $search = null): array {
    $sql = "SELECT e.id, e.status, e.enrolled_at, e.updated_at,
                   u.id AS user_id, u.username, u.fullname, u.phone, u.student_id, u.email,
                   c.id AS course_id, c.title AS course_title, c.spec_no, c.max_seats, c.start_date, c.end_date
            FROM enrollments e
            JOIN users u   ON u.id = e.user_id
            JOIN courses c ON c.id = e.course_id
            WHERE 1=1";
    $params = [];

    if ($courseFilter) {
        $sql .= " AND e.course_id = ?";
        $params[] = $courseFilter;
    }
    if ($statusFilter && in_array($statusFilter, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
        $sql .= " AND e.status = ?";
        $params[] = $statusFilter;
    }
    if ($search !== null && trim($search) !== '') {
        $kw = '%' . trim($search) . '%';
        $sql .= " AND (u.username LIKE ? OR u.fullname LIKE ? OR u.student_id LIKE ? OR u.phone LIKE ? OR u.email LIKE ?)";
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
    }

    $sql .= " ORDER BY e.enrolled_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
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

// ---------- UI & Formatting Helpers ----------

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key, array $source = []): string {
    return e($source[$key] ?? '');
}

function status_label(string $status): string {
    switch ($status) {
        case 'confirmed': return 'อนุมัติแล้ว';
        case 'pending':   return 'รอการอนุมัติ';
        case 'completed': return 'ผ่านการอบรม';
        case 'cancelled': return 'ยกเลิกแล้ว';
        default:          return $status;
    }
}

function status_badge(string $status): string {
    $label = status_label($status);
    $class = 'badge-pending';
    if ($status === 'confirmed') $class = 'badge-confirmed';
    elseif ($status === 'completed') $class = 'badge-completed';
    elseif ($status === 'cancelled') $class = 'badge-cancelled';
    return '<span class="status-badge ' . $class . '">' . e($label) . '</span>';
}

function format_date_thai(?string $date): string {
    if (!$date) return '-';
    $time = strtotime($date);
    if (!$time) return '-';
    $day = date('j', $time);
    $monthNames = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $month = $monthNames[(int)date('n', $time)] ?? '';
    $year = (int)date('Y', $time) + 543;
    return "{$day} {$month} {$year}";
}

function format_date_range(?string $startDate, ?string $endDate): string {
    if (!$startDate && !$endDate) return 'ตามตารางภาคเรียน';
    if ($startDate && $endDate) {
        return format_date_thai($startDate) . ' — ' . format_date_thai($endDate);
    }
    if ($startDate) return 'เริ่ม ' . format_date_thai($startDate);
    return 'สิ้นสุด ' . format_date_thai($endDate);
}
