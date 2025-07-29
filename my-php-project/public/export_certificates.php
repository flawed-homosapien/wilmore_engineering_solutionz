<?php
session_start();
require_once '../src/db.php';
require_once '../src/auth.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="certificates.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Course', 'Issue Date']);

$stmt = $pdo->query(
    "SELECT c.id, u.first_name, u.last_name, u.email, c.course, c.issue_date
     FROM certificates c
     JOIN users u ON c.user_id = u.id"
);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
exit;