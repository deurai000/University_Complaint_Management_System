<?php

require_once __DIR__ . "/../includes/complaints.php";
requireAdmin($conn);
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$fileId = filter_input(INPUT_GET, "file", FILTER_VALIDATE_INT);
$query = $conn->prepare("SELECT f.file_path, f.file_name" . COMPLAINT_FROM
    . " JOIN complaint_attachments f ON f.complaint_id = c.id
        WHERE c.id = ? AND f.id = ?");
$query->execute([$id, $fileId]);
$file = $query->fetch();
if (!$file) {
    complaintNotFound();
}

$directory = realpath(__DIR__ . "/../uploads/complaints");
$path = realpath(__DIR__ . "/../" . $file["file_path"]);
if (!$directory || !$path || dirname($path) !== $directory || !is_file($path) || !is_readable($path)) {
    complaintNotFound();
}
session_write_close();
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($file["file_name"]));
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, no-store");
header("Content-Length: " . filesize($path));
readfile($path);
