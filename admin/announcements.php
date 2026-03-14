<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM announcements WHERE id=$id");
    header('Location: announcements.php?msg=deleted');
    exit;
}

// Handle toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE announcements SET is_active = 1 - is_active WHERE id=$id");
    header('Location: announcements.php?msg=updated');
    exit;
}

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-header">
      <div><h1>📢 Announcements</h1><p>Manage public notices displayed on the homepage and ticker.</p></div>
      <a href="announcement_form.php" class="btn btn-primary">+ Add Announcement</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ Announcement <?= $_GET['msg'] === 'deleted' ? 'deleted' : 'updated' ?> successfully.</div>
    <?php endif; ?>

    <div class="data-table">
      <div class="table-header">
        <h3>All Announcements</h3>
        <span style="font-size:13.5px;color:#5a6a7e;"><?= $announcements->num_rows ?> total</span>
      </div>
      <table>
        <thead>
          <tr><th>Title</th><th>Tag</th><th>Ticker</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php while ($a = $announcements->fetch_assoc()): ?>
          <tr>
            <td>
              <strong style="font-size:14px;"><?= htmlspecialchars($a['title']) ?></strong><br>
              <span style="font-size:12px;color:#5a6a7e;"><?= htmlspecialchars(substr($a['body'], 0, 70)) ?>...</span>
            </td>
            <td>
              <span style="background:<?= htmlspecialchars($a['tag_color']) ?>18;color:<?= htmlspecialchars($a['tag_color']) ?>;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                <?= htmlspecialchars($a['tag']) ?>
              </span>
            </td>
            <td>
              <?= $a['is_ticker'] ? '<span style="color:#1a7a4a;font-weight:600;font-size:13px;">✅ Yes</span>' : '<span style="color:#5a6a7e;font-size:13px;">No</span>' ?>
            </td>
            <td>
              <a href="announcements.php?toggle=<?= $a['id'] ?>"
                 style="font-size:12.5px;font-weight:700;text-decoration:none;padding:4px 12px;border-radius:20px;
                   background:<?= $a['is_active'] ? '#e6f6ed' : '#fde8e6' ?>;
                   color:<?= $a['is_active'] ? '#1a7a4a' : '#c0392b' ?>;">
                <?= $a['is_active'] ? '● Active' : '○ Hidden' ?>
              </a>
            </td>
            <td style="font-size:13px;color:#5a6a7e;white-space:nowrap;"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:8px;">
                <a href="announcement_form.php?id=<?= $a['id'] ?>" style="padding:5px 12px;background:#e8f0fb;color:#0a4d8c;border-radius:6px;text-decoration:none;font-size:12.5px;font-weight:600;">✏️ Edit</a>
                <a href="announcements.php?delete=<?= $a['id'] ?>"
                   onclick="return confirm('Delete this announcement?')"
                   style="padding:5px 12px;background:#fde8e6;color:#c0392b;border-radius:6px;text-decoration:none;font-size:12.5px;font-weight:600;">🗑️ Delete</a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
