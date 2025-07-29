<?php
session_start();
require_once '../../src/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /trials/copilot/my-php-project/public/login.php');
    exit;
}

$adminName = $_SESSION['user_name'] ?? 'Admin';
$pdo = getPDO();

// Handle pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$course = $_GET['course'] ?? '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($course)) {
    $where[] = "course_name = :course";
    $params[':course'] = $course;
}

$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSQL");
$stmt->execute($params);
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

$sql = "SELECT id, first_name, last_name, email, course_name, created_at, profile_picture 
        FROM users $whereSQL 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Bind search and course if set
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
if (!empty($course)) {
    $stmt->bindValue(':course', $course, PDO::PARAM_STR);
}

// Bind limit and offset
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Admin Panel</a>
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

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?= htmlspecialchars($adminName) ?></h2>
    </div>

    <h4>👥 Registered Users</h4>

    <form class="row g-3 mb-3" method="GET">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search by email or name" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" id="courseFilter" name="course">
                <option value="">All Courses</option>
                <option value="PHP" <?= $course === 'PHP' ? 'selected' : '' ?>>PHP</option>
                <option value="JavaScript" <?= $course === 'JavaScript' ? 'selected' : '' ?>>JavaScript</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Search</button>
        </div>
        <div class="col-md-3">
            <a href="export_users.php?search=<?= urlencode($search) ?>&course=<?= urlencode($course) ?>" class="btn btn-success w-100">Export CSV</a>
            
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>UID</th>
            <th>Image</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Course Name</th>
            <th>Registered At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="userTableBody">
        <?php foreach ($users as $index => $user): ?>
            <!-- <tr>
                <td><?= $offset + $index + 1 ?></td>
                <td><?= $user['id'] ?></td>
                <td>
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" class="img-thumbnail" style="width: 50px; height: 50px;">
                    <?php else: ?>
                        <img src="/trials/copilot/my-php-project/public/images/new_image.png" alt="Default" class="img-thumbnail" style="width: 50px; height: 50px;">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['course_name'] ?? 'N/A') ?></td>
                <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                <td>
                    <button class="btn btn-info btn-sm view-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-user='<?= json_encode($user) ?>'>View</button>
                </td>
            </tr> -->

            <tr class="clickable-row" data-user-id="<?= $user['id'] ?>">
              <td><?= $offset + $index + 1 ?></td>
              <td><?= $user['id'] ?></td>
              <td>
                  <?php if (!empty($user['profile_picture'])): ?>
                      <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile" class="img-thumbnail" style="width: 50px; height: 50px;">
                  <?php else: ?>
                      <img src="/trials/copilot/my-php-project/public/images/new_image.png" alt="Default" class="img-thumbnail" style="width: 50px; height: 50px;">
                  <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['course_name'] ?? 'N/A') ?></td>
              <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
              <td>
                  <!-- Optional: leave the button for now or remove if using row-click -->
                  <button class="btn btn-info btn-sm view-btn">View</button>
              </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>

    <script>
$(document).ready(function () {
    function fetchFilteredUsers() {
        const search = $('#searchInput').val();
        const course = $('#courseFilter').val();

        $.get('admin.php', { search, course, ajax: 1 }, function (response) {
            $('#userTableBody').html(response);
        });
    }

    $('#searchInput').on('input', function () {
        fetchFilteredUsers();
    });

    $('#courseFilter').on('change', function () {
        fetchFilteredUsers();
    });
});
</script>
<script>
$(document).ready(function() {
  $(document).on('click', '.clickable-row', function() {
    let userId = $(this).data('user-id');

    $.ajax({
      url: '/trials/copilot/my-php-project/ajax/fetch_user.php',
      method: 'POST',
      data: { id: userId },
      dataType: 'json',
      success: function(user) {
        if (user) {
          $('#viewModal #viewName').text(user.first_name + ' ' + user.last_name);
          $('#viewModal #viewEmail').text(user.email);
          $('#viewModal #viewCourse').text(user.course_name || 'N/A');
          $('#viewModal #viewProfile').attr('src', user.profile_picture || '/trials/copilot/my-php-project/public/images/new_image.png');

          $('#viewModal').modal('show');
        } else {
          alert('User not found.');
        }
      },
      error: function() {
        alert('Failed to fetch user data.');
      }
    });
  });
});
</script>



    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&course=<?= urlencode($course) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <hr>
    <div class="text-muted">Logged in as admin | at <?= date('Y-m-d H:i') ?></div>
</div>



    <!-- View Modal -->
              <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <img id="viewProfile" src="" alt="Profile Picture" class="img-thumbnail mb-3" width="100">
                <p><strong>Name:</strong> <span id="viewName"></span></p>
                <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                <p><strong>Course:</strong> <span id="viewCourse"></span></p>
              </div>
              <div class="modal-footer">
                <button id="editBtn" class="btn btn-warning">Edit</button>
                <button id="deleteBtn" class="btn btn-danger">Delete</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>



    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="edit_user.php" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit User</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-2">
                        <label>First Name</label>
                        <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="editLastName" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="delete_user.php" class="modal-content">
                <div class="modal-header"><h5 class="modal-title text-danger">Confirm Rejection</h5></div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this user?</p>
                    <input type="hidden" name="id" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="submit">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <!-- <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="delete_user.php" class="modal-content">
                <div class="modal-header"><h5 class="modal-title text-danger">Confirm Delete</h5></div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <input type="hidden" name="id" id="deleteUserId">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="submit">Delete</button>
                </div>
            </form>
        </div>
    </div> -->

      <footer class="mt-auto bg-dark text-white text-center py-3 fixed-bottom">
    <div class="container">
      <span>&copy; <span id="year"></span> - Crafted by <a href="#">#BuildWithRoi</a></span>
    </div>
  </footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const user = JSON.parse(btn.getAttribute('data-user'));

    document.getElementById('viewImage').src = user.profile_picture || '/trials/copilot/my-php-project/public/images/default.png';
    document.getElementById('viewFullName').textContent = user.first_name + ' ' + user.last_name;
    document.getElementById('viewEmail').textContent = user.email;
    document.getElementById('viewCourse').textContent = user.course_name || 'N/A';
    document.getElementById('viewCreatedAt').textContent = user.created_at;

    // Pass values to Edit and Delete buttons inside modal
    document.getElementById('editId').value = user.id;
    document.getElementById('editFirstName').value = user.first_name;
    document.getElementById('editLastName').value = user.last_name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('deleteUserId').value = user.id;
  });
});

    // Edit Profile
    // document.querySelectorAll('.edit-btn').forEach(btn => {
    //     btn.addEventListener('click', () => {
    //         const user = JSON.parse(btn.getAttribute('data-user'));
    //         document.getElementById('editId').value = user.id;
    //         document.getElementById('editFirstName').value = user.first_name;
    //         document.getElementById('editLastName').value = user.last_name;
    //         document.getElementById('editEmail').value = user.email;
    //     });
    // });

  

    // Delete Profile
    // document.querySelectorAll('.delete-btn').forEach(btn => {
    //     btn.addEventListener('click', () => {
    //         const userId = btn.getAttribute('data-user-id');
    //         document.getElementById('deleteUserId').value = userId;
    //     });
    // });

    const yearElement = document.getElementById('year');
    const currentYear = new Date().getFullYear();
    yearElement.textContent = currentYear;
</script>
  <script>
  $('#editForm').submit(function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    
    $.ajax({
      type: 'POST',
      url: 'edit.php', // Make sure this is the correct path
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        // Optional: You can log or parse the response
        $('#editModal').modal('hide');
        alert('User updated successfully!');
        location.reload(); // Optional: Reload to reflect changes in the table
      },
      error: function () {
        alert('Failed to update user.');
      }
    });
  });
</script>
</body>
</html>
