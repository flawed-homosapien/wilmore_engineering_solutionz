<?php
// user.php
require_once '../../src/db.php';
session_start();
$pdo = getPDO(); // ✅ Initialize the $pdo connection


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT first_name, last_name, email, course_name, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stats = [
    'courses_taken' => 3,
    'certificates_earned' => 2,
    'tasks_completed' => 18
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f8;
        }
        .sidebar {
            height: 100vh;
            background-color: #fff;
            border-right: 1px solid #ddd;
        }
        .profile-pic {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .nav-tabs .nav-link.active {
            background-color: #e9ecef;
            border-bottom: 2px solid #0d6efd;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar d-flex flex-column p-3">
            <img src="<?= $user['profile_picture'] ?? 'default.png' ?>" class="profile-pic mb-3" alt="Profile Picture">
            <h4><?= htmlspecialchars($user['first_name']) ?></h4>
            <p class="text-muted">Enrolled in: <?= htmlspecialchars($user['course_name']) ?></p>
            <hr>
            <a href="#" class="btn btn-outline-primary w-100 mb-2">Dashboard</a>
            <a href="#" class="btn btn-outline-secondary w-100">Logout</a>
        </div>

        <div class="col-md-9 p-4">
            <ul class="nav nav-tabs mb-4" id="dashboardTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Settings</button>
                </li>
            </ul>

            <div class="tab-content" id="dashboardTabContent">
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <h6>Courses Taken</h6>
                                <p><?= $stats['courses_taken'] ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <h6>Certificates Earned</h6>
                                <p><?= $stats['certificates_earned'] ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <h6>Tasks Completed</h6>
                                <p><?= $stats['tasks_completed'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Course Progress</h5>
                        <div class="mb-2">HTML Basics
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 70%">70%</div>
                            </div>
                        </div>
                        <div class="mb-2">CSS Fundamentals
                            <div class="progress">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 45%">45%</div>
                            </div>
                        </div>
                        <div class="mb-2">JavaScript Intro
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 30%">30%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <h5>Account Settings</h5>
                    <form action="update_profile.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
