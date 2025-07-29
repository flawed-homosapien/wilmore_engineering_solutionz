<?php
session_start();
require_once '../src/db.php';

// if (!is_logged_in() || $_SESSION['role'] !== 'alpha_admin') {
//     header('Location: login.php');
//     exit;
// }

$stmt = $pdo->query(
    "SELECT a.*, u.first_name, u.last_name FROM admin_actions a
     JOIN users u ON a.admin_id = u.id
     ORDER BY a.created_at DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Activity Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
    <h1>Admin Activity Log</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Admin</th>
                <th>Action</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['action_type']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>