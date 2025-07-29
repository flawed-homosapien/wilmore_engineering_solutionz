<?php
require_once '../src/db.php';

$cert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare(
    "SELECT c.*, u.first_name, u.last_name, u.email 
     FROM certificates c 
     JOIN users u ON c.user_id = u.id 
     WHERE c.id = ?"
);
$stmt->execute([$cert_id]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert) {
    echo "<h2>Certificate not found.</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Certificate Verification</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h1>Certificate Verification</h1>
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']) ?></h3>
            <p class="card-text"><strong>Course:</strong> <?= htmlspecialchars($cert['course']) ?></p>
            <p class="card-text"><strong>Issue Date:</strong> <?= htmlspecialchars($cert['issue_date']) ?></p>
            <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($cert['email']) ?></p>
            <a href="<?= htmlspecialchars($cert['pdf_path']) ?>" class="btn btn-success" download>Download Certificate (PDF)</a>
        </div>
    </div>
    <div>
        <img src="<?= htmlspecialchars($cert['qr_code_path']) ?>" alt="QR Code" style="max-width:200px;">
    </div>
</body>
</html>