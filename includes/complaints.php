<?php

require_once __DIR__ . "/../config/database.php";

const COMPLAINT_STATUSES = [
    "pending" => "Pending",
    "in_progress" => "In progress",
    "resolved" => "Resolved",
    "rejected" => "Rejected",
];

// Assignment rows are retained as history. The newest assignment identifies the current staff member.
const COMPLAINT_FROM = " FROM complaints c
    JOIN categories cat ON cat.id = c.category_id
    JOIN users student ON student.id = c.student_id
    LEFT JOIN complaint_assignments assignment ON assignment.id = (
        SELECT a.id FROM complaint_assignments a WHERE a.complaint_id = c.id
        ORDER BY a.assigned_at DESC, a.id DESC LIMIT 1
    )
    LEFT JOIN users staff ON staff.id = assignment.staff_id";

function requireAdmin(PDO $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION["user_id"])) {
        header("Location: ../auth/admin_login.php");
        exit;
    }
    $query = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
    $query->execute([$_SESSION["user_id"]]);
    $user = $query->fetch();
    if (!$user || $user["role"] !== ($_SESSION["role"] ?? null) || $user["role"] !== "admin") {
        http_response_code(403);
        exit("You do not have access to this page.");
    }
    return $user;
}

function findComplaint(PDO $conn, int $id)
{
    $query = $conn->prepare("SELECT c.*, cat.category_name, student.full_name AS student_name,
            assignment.staff_id, staff.full_name AS staff_name,
            (SELECT COALESCE(MAX(u.id), 0) FROM complaint_updates u WHERE u.complaint_id = c.id) AS version"
        . COMPLAINT_FROM . " WHERE c.id = ?");
    $query->execute([$id]);
    return $query->fetch();
}

function complaintNotFound(): void
{
    http_response_code(404);
    exit("Complaint or attachment unavailable.");
}
