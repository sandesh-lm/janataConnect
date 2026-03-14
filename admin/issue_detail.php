<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: issues.php'); exit; }

$res = $conn->query("SELECT * FROM issues WHERE id=$id LIMIT 1");
if (!$res || $res->num_rows === 0) { header('Location: issues.php'); exit; }
$issue = $res->fetch_assoc();

// Handle status update from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE issues SET status='$status' WHERE id=$id");
    header("Location: issue_detail.php?id=$id&updated=1");
    exit;
}

$conn->close();
$status_colors = ['Pending' => '#e67e22', 'In Progress' => '#0a4d8c', 'Resolved' => '#1a7a4a'];
$color = $status_colors[$issue['status']] ?? '#5a6a7e';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Issue #ISS-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?> – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
      <a href="issues.php" style="color:#5a6a7e;text-decoration:none;font-size:14px;">← Back to Issues</a>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">✅ Status updated successfully.</div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

      <!-- Issue Detail Card -->
      <div style="background:white;border-radius:14px;box-shadow:var(--shadow);overflow:hidden;">
        <div style="background:linear-gradient(90deg,var(--primary),var(--primary-light));padding:22px 28px;color:white;">
          <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-bottom:4px;">ISS-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></div>
          <h2 style="font-size:19px;font-weight:700;"><?= htmlspecialchars($issue['title']) ?></h2>
          <div style="margin-top:8px;font-size:13px;color:rgba(255,255,255,0.8);">
            📁 <?= htmlspecialchars($issue['category']) ?> &nbsp;|&nbsp;
            📍 Ward <?= htmlspecialchars($issue['ward']) ?> &nbsp;|&nbsp;
            📅 <?= date('F j, Y', strtotime($issue['date'])) ?>
          </div>
        </div>

        <div style="padding:28px;">
          <h4 style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#5a6a7e;margin-bottom:10px;">Issue Description</h4>
          <p style="font-size:15px;line-height:1.75;color:#1a2332;margin-bottom:24px;"><?= nl2br(htmlspecialchars($issue['description'])) ?></p>

          <?php if ($issue['location']): ?>
          <div style="background:#f4f7fb;border-radius:8px;padding:14px 16px;margin-bottom:20px;font-size:14px;">
            📍 <strong>Location:</strong> <?= htmlspecialchars($issue['location']) ?>
          </div>
          <?php endif; ?>

          <?php if ($issue['photo']): ?>
          <div>
            <h4 style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#5a6a7e;margin-bottom:12px;">Photo Evidence</h4>
            <img src="../uploads/<?= htmlspecialchars($issue['photo']) ?>"
                 alt="Issue photo"
                 style="max-width:100%;border-radius:10px;border:1.5px solid #d0dce8;"
                 onerror="this.style.display='none'">
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sidebar Info + Actions -->
      <div style="display:flex;flex-direction:column;gap:18px;">

        <!-- Reporter Info -->
        <div style="background:white;border-radius:12px;padding:22px;box-shadow:var(--shadow);">
          <h4 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#5a6a7e;margin-bottom:16px;">👤 Reporter Details</h4>
          <div style="display:flex;flex-direction:column;gap:11px;font-size:14px;">
            <div><strong style="color:#5a6a7e;font-size:12px;display:block;">Name</strong><?= htmlspecialchars($issue['name']) ?></div>
            <div><strong style="color:#5a6a7e;font-size:12px;display:block;">Phone</strong><a href="tel:<?= htmlspecialchars($issue['phone']) ?>" style="color:#0a4d8c;"><?= htmlspecialchars($issue['phone']) ?></a></div>
            <?php if ($issue['email']): ?>
            <div><strong style="color:#5a6a7e;font-size:12px;display:block;">Email</strong><a href="mailto:<?= htmlspecialchars($issue['email']) ?>" style="color:#0a4d8c;"><?= htmlspecialchars($issue['email']) ?></a></div>
            <?php endif; ?>
            <div><strong style="color:#5a6a7e;font-size:12px;display:block;">Ward</strong>Ward <?= htmlspecialchars($issue['ward']) ?></div>
            <div><strong style="color:#5a6a7e;font-size:12px;display:block;">Submitted</strong><?= date('M j, Y \a\t g:i A', strtotime($issue['created_at'])) ?></div>
          </div>
        </div>

        <!-- Status Update -->
        <div style="background:white;border-radius:12px;padding:22px;box-shadow:var(--shadow);">
          <h4 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#5a6a7e;margin-bottom:16px;">🔄 Update Status</h4>
          <div style="background:<?= $color ?>22;border:1.5px solid <?= $color ?>44;border-radius:8px;padding:10px 14px;margin-bottom:16px;text-align:center;">
            <span style="color:<?= $color ?>;font-weight:700;font-size:14px;">Current: <?= htmlspecialchars($issue['status']) ?></span>
          </div>
          <form method="POST">
            <div class="form-group" style="margin-bottom:12px;">
              <select name="status" style="width:100%;padding:10px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
                <option <?= $issue['status']=='Pending' ? 'selected' : '' ?>>Pending</option>
                <option <?= $issue['status']=='In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option <?= $issue['status']=='Resolved' ? 'selected' : '' ?>>Resolved</option>
              </select>
            </div>
            <button type="submit" class="btn btn-blue btn-full">✓ Update Status</button>
          </form>
        </div>

        <!-- Quick Nav -->
        <div style="background:#f0f5ff;border-radius:12px;padding:18px;border:1.5px solid #b3cef0;">
          <div style="font-size:13px;color:#5a6a7e;margin-bottom:10px;">Quick Navigation</div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <?php
            // Previous / Next issue
            require_once '../config/db.php';
            $conn2 = getDB();
            $prev = $conn2->query("SELECT id FROM issues WHERE id < $id ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $next = $conn2->query("SELECT id FROM issues WHERE id > $id ORDER BY id ASC LIMIT 1")->fetch_assoc();
            $conn2->close();
            ?>
            <?php if ($prev): ?><a href="issue_detail.php?id=<?= $prev['id'] ?>" style="font-size:13.5px;color:#0a4d8c;text-decoration:none;">← Previous Issue</a><?php endif; ?>
            <?php if ($next): ?><a href="issue_detail.php?id=<?= $next['id'] ?>" style="font-size:13.5px;color:#0a4d8c;text-decoration:none;">Next Issue →</a><?php endif; ?>
            <a href="issues.php" style="font-size:13.5px;color:#5a6a7e;text-decoration:none;">📋 All Issues</a>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
