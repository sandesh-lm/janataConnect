<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../config/db.php';
$conn = getDB();

$id     = (int)($_POST['id'] ?? 0);
$status = trim($conn->real_escape_string($_POST['status'] ?? ''));
$type   = trim($conn->real_escape_string($_POST['type'] ?? 'issue'));

$allowed_statuses = ['Pending', 'In Progress', 'Resolved', 'Active', 'Completed', 'Cancelled'];
if (!$id || !in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$table = ($type === 'token') ? 'tokens' : 'issues';
$result = $conn->query("UPDATE `$table` SET status='$status' WHERE id=$id");

echo json_encode([
    'success' => $result && $conn->affected_rows >= 0,
    'id'      => $id,
    'status'  => $status,
    'table'   => $table,
]);

$conn->close();
?>
