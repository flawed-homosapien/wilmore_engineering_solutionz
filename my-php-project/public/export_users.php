<?php
require_once '../../src/db.php';
if (!$conn) {
    die('Database connection not established.');
}


header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=users.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Created At']);

$sql = "SELECT id, first_name, last_name, email, created_at FROM users ORDER BY id ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;
