<?php
session_start(); // ✅ Start the session before using $_SESSION

require_once '../../src/db.php';
$pdo = getPDO(); // ✅ Initialize the $pdo connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, email, bio, course_name, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['first_name']); ?> | User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .profile-card, .activity-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
        }
        .sidebar {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .welcome-banner {
            background-color: #f35a1e;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">User Account</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="#">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Settings</a>
        </li>
      </ul>
      <a href="/trials/copilot/my-php-project/public/logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</nav>


<div class="container-fluid mt-4">
    <div class="row mb-3">
        <!-- Sidebar -->
        <div class="col-md-3 sidebar text-center">
            <div class="">
                
            </div>
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" class="img-thumbnail profile-img mb-3">
                  <?php else: ?>
                      <img src="/trials/copilot/my-php-project/public/images/new_image.png" alt="Default" class="img-thumbnail profile-img mb-3">
                  <?php endif; ?>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($user['bio']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($user['course_name'] ?? 'Not set'); ?></p>
                <hr>
                <a href="edit_profile.php" class="btn btn-outline-primary btn-sm d-block mb-2">Edit Profile</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="main-section">
                <div class="welcome-banner">
                    <h4>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?> <?php echo htmlspecialchars($user['last_name']); ?>!</h4>
                  
                    <p>Great to see you. Keep building your career with new skills.</p>
                </div>

                <div class="row mt-4 mb-4">
                    <div class="col-12">
                        <div class="activity-card">
                            <h5 class="mb-3">Course Progress</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">✅ Completed profile setup</li>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 30%;">
                                        30%
                                    </div>
                                </div>
                            </ul>

                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card profile-card">
                            <div class="card-body">
                                <h5 class="card-title">Your Course</h5>
                                <p class="card-text">You're currently enrolled in <strong><?php echo htmlspecialchars($user['course_name'] ?? 'Not selected'); ?></strong>.</p>
                                <br>
                                <a href="select_course.php" class="btn btn-sm btn-primary">Choose/Change Course</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card profile-card">
                            <div class="card-body">
                                <h5 class="card-title">Upload Profile Picture</h5>
                                <form action="upload_profile_pic.php" method="POST" enctype="multipart/form-data">
                                    <input type="file" name="profile_picture" class="form-control mb-2" accept="image/*" required>
                                    <button type="submit" class="btn btn-sm btn-success">Upload</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 mb-4">
                    <div class="col-12">
                        <div class="activity-card">
                            <h5 class="mb-3">Course Progress</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">✅ Completed profile setup</li>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 30%;">
                                        30%
                                    </div>
                                </div>
                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Future widgets like Recent Activity, Progress, etc. -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="activity-card">
                            <h5 class="mb-3">Your Activity</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">✅ Completed profile setup</li>
                                <li class="mb-2">📘 Enrolled in: <?php echo htmlspecialchars($user['course_name'] ?? 'None'); ?></li>
                                <li class="mb-2">📥 Last login: <?php echo date('Y-m-d H:i'); ?> (mock)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="d-flex flex-column min-vh-50">
  <!-- Your content here -->
  <footer class="mt-auto bg-dark text-white text-center py-3">
    <div class="container">
      <span>&copy; <span id="year"></span> - Crafted by <a href="#">#BuildWithRoi</a></span>
    </div>
  </footer>



</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script >
const yearElement = document.getElementById('year');
const currentYear = new Date().getFullYear();
yearElement.textContent = currentYear;

</script>
</body>
</html>
