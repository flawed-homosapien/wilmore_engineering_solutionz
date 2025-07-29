<?php
require_once '../../src/db.php';
$pdo = getPDO();

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=users_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Course Name', 'Created At']);

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users";
if ($search) {
    $sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [$row['id'], $row['first_name'], $row['last_name'], $row['email'], $row['course_name'], $row['created_at']]);
}
fclose($output);
exit;
