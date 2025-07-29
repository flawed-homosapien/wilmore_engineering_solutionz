<?php
session_start();
require_once '../src/db.php';
require_once '../src/auth.php';

// Only allow admins
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $course = trim($_POST['course']);
    $issue_date = $_POST['issue_date'];
    $admin_id = $_SESSION['user_id'];

    // Basic validation
    if (!$user_id || !$course || !$issue_date) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: admin_dashboard.php');
        exit;
    }

    // Insert certificate record
    $stmt = $pdo->prepare("INSERT INTO certificates (user_id, course, issue_date, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $course, $issue_date, $admin_id]);
    $cert_id = $pdo->lastInsertId();

    // Log admin action
    $action = "Assigned certificate (ID: $cert_id) to user (ID: $user_id)";
    $stmt = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_type, target_id, target_type, description) VALUES (?, 'assign_certificate', ?, 'certificate', ?)");
    $stmt->execute([$admin_id, $cert_id, $action]);

    // --- Generate QR Code ---
    require_once '../vendor/autoload.php';
    $qrDir = "../cert_qr/";
    if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);
    $qrFile = $qrDir . "cert_" . $cert_id . ".png";
    $qrLink = "https://wilmoreengineeringsolutionz.org/public/certificate_view.php?id=" . $cert_id; // Adjust domain/path
    \QRcode::png($qrLink, $qrFile, QR_ECLEVEL_L, 4);

    // --- Generate PDF Certificate ---
    $pdfDir = "../cert_pdfs/";
    if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
    $pdfFile = $pdfDir . "cert_" . $cert_id . ".pdf";

    // Fetch user info for certificate
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $html = "
        <h1>Certificate of Completion</h1>
        <p>This certifies that <strong>{$user['first_name']} {$user['last_name']}</strong> has completed the course <strong>{$course}</strong> on <strong>{$issue_date}</strong>.</p>
        <img src='{$qrFile}' alt='QR Code'>
    ";

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    file_put_contents($pdfFile, $dompdf->output());

    // --- Update certificate record with file paths ---
    $stmt = $pdo->prepare("UPDATE certificates SET pdf_path = ?, qr_code_path = ? WHERE id = ?");
    $stmt->execute([$pdfFile, $qrFile, $cert_id]);

    // --- Send Email with PHPMailer ---
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userEmail = $stmt->fetchColumn();

    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->setFrom('no-reply@yourdomain.com', 'Certificate System');
    $mail->addAddress($userEmail);
    $mail->Subject = "Your Certificate";
    $mail->Body = "Congratulations! Your certificate is attached.";
    $mail->addAttachment($pdfFile);

    if (!$mail->send()) {
        $_SESSION['error'] = "Certificate assigned, but email failed to send.";
    } else {
        $_SESSION['success'] = "Certificate assigned and emailed successfully!";
    }

    header('Location: admin_dashboard.php');
    exit;
} else {
    header('Location: admin_dashboard.php');
    exit;
}