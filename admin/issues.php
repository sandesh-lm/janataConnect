<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE issues SET status='$status' WHERE id=$id");
    header('Location: issues.php?updated=1');
    exit;
}

$search = trim($conn->real_escape_string($_GET['search'] ?? ''));
$filter = $conn->real_escape_string($_GET['filter'] ?? '');

$where = '';
if ($search) $where .= " AND (name LIKE '%$search%' OR title LIKE '%$search%' OR ward LIKE '%$search%')";
if ($filter) $where .= " AND status='$filter'";

$issues = $conn->query("SELECT * FROM issues WHERE 1=1 $where ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Issues – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-header">
      <div><h1>📋 Community Issues</h1><p>Review and update status of reported issues.</p></div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">✅ Issue status updated successfully.</div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:12px;margin-bottom:22px;flex-wrap:wrap;">
      <input type="text" name="search" placeholder="🔍 Search by name, title, ward..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex:1;min-width:200px;padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
      <select name="filter" style="padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
        <option value="">All Statuses</option>
        <option value="Pending" <?= (($_GET['filter'] ?? '') == 'Pending') ? 'selected' : '' ?>>Pending</option>
        <option value="In Progress" <?= (($_GET['filter'] ?? '') == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
        <option value="Resolved" <?= (($_GET['filter'] ?? '') == 'Resolved') ? 'selected' : '' ?>>Resolved</option>
      </select>
      <button type="submit" class="btn btn-blue" style="padding:9px 20px;">Filter</button>
      <a href="issues.php" class="btn" style="padding:9px 16px;border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Reset</a>
    </form>

    <div class="data-table">
      <div class="table-header">
        <h3>All Issue Reports</h3>
        <span style="font-size:13.5px;color:#5a6a7e;"><?= $issues->num_rows ?> records found</span>
      </div>
      <table>
        <thead>
          <tr><th>ID</th><th>Reporter</th><th>Ward</th><th>Category</th><th>Title</th><th>Date</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php while ($row = $issues->fetch_assoc()): ?>
          <tr>
            <td style="font-size:12px;color:#5a6a7e;white-space:nowrap;">ISS-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td>
              <strong style="font-size:14px;"><?= htmlspecialchars($row['name']) ?></strong><br>
              <span style="font-size:12px;color:#5a6a7e;"><?= htmlspecialchars($row['phone']) ?></span>
            </td>
            <td>Ward <?= htmlspecialchars($row['ward']) ?></td>
            <td><span style="font-size:12.5px;"><?= htmlspecialchars($row['category']) ?></span></td>
            <td>
              <a href="issue_detail.php?id=<?= $row['id'] ?>" style="color:#0a4d8c;text-decoration:none;font-weight:500;"><?= htmlspecialchars(substr($row['title'], 0, 35)) ?><?= strlen($row['title']) > 35 ? '...' : '' ?></a>
              <?php if ($row['photo']): ?>
              <br><a href="../uploads/<?= htmlspecialchars($row['photo']) ?>" target="_blank" style="font-size:11.5px;color:#5a6a7e;">📷 View Photo</a>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;"><?= date('M j, Y', strtotime($row['date'])) ?></td>
            <td>
              <span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>" id="badge-<?= $row['id'] ?>">
                <?= htmlspecialchars($row['status']) ?>
              </span>
            </td>
            <td>
              <form method="POST" style="display:flex;gap:6px;align-items:center;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="update_status" value="1">
                <select name="status" id="status-<?= $row['id'] ?>" style="padding:5px 8px;border:1.5px solid #d0dce8;border-radius:6px;font-size:12.5px;font-family:inherit;">
                  <option <?= $row['status']=='Pending' ? 'selected' : '' ?>>Pending</option>
                  <option <?= $row['status']=='In Progress' ? 'selected' : '' ?>>In Progress</option>
                  <option <?= $row['status']=='Resolved' ? 'selected' : '' ?>>Resolved</option>
                </select>
                <button type="submit" style="padding:5px 10px;background:#0a4d8c;color:white;border:none;border-radius:6px;font-size:12px;cursor:pointer;">✓</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
