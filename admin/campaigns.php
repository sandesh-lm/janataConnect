<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM campaigns WHERE id=$id");
    header('Location: campaigns.php?msg=deleted');
    exit;
}

// Handle toggle
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE campaigns SET is_active = 1 - is_active WHERE id=$id");
    header('Location: campaigns.php?msg=updated');
    exit;
}

$campaigns = $conn->query("SELECT c.*, 
    (SELECT COUNT(*) FROM campaign_registrations cr WHERE cr.campaign_id = c.id) as reg_count
    FROM campaigns c ORDER BY campaign_date ASC");
$conn->close();

$status_colors = [
    'Upcoming' => ['#e8f0fb','#0a4d8c'],
    'Ongoing'  => ['#e6f6ed','#1a7a4a'],
    'Completed'=> ['#f4f7fb','#5a6a7e'],
    'Cancelled'=> ['#fde8e6','#c0392b'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Campaigns – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-header">
      <div><h1>🌿 Volunteer Campaigns</h1><p>Create and manage community volunteer campaigns.</p></div>
      <a href="campaign_form.php" class="btn btn-primary">+ Add Campaign</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ Campaign <?= $_GET['msg'] === 'deleted' ? 'deleted' : 'updated' ?> successfully.</div>
    <?php endif; ?>

    <div style="display:flex;flex-direction:column;gap:16px;">
      <?php while ($c = $campaigns->fetch_assoc()):
        $sc = $status_colors[$c['status']] ?? $status_colors['Upcoming'];
      ?>
      <div style="background:white;border-radius:14px;border:1.5px solid #d0dce8;box-shadow:var(--shadow);padding:22px 26px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;">
          <div style="flex:1;">
            <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;flex-wrap:wrap;">
              <span style="background:<?= $sc[0] ?>;color:<?= $sc[1] ?>;font-size:12px;font-weight:700;padding:3px 12px;border-radius:20px;">
                <?= htmlspecialchars($c['status']) ?>
              </span>
              <span style="background:#f0f5ff;color:#0a4d8c;font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;">
                <?= htmlspecialchars($c['category']) ?>
              </span>
              <?php if (!$c['is_active']): ?>
              <span style="background:#f4f7fb;color:#5a6a7e;font-size:12px;font-weight:600;padding:3px 12px;border-radius:20px;">Hidden</span>
              <?php endif; ?>
            </div>
            <h3 style="font-size:17px;font-weight:700;margin-bottom:6px;"><?= htmlspecialchars($c['title']) ?></h3>
            <div style="display:flex;gap:18px;font-size:13px;color:#5a6a7e;flex-wrap:wrap;">
              <?php if ($c['campaign_date']): ?>
              <span>📅 <?= date('M j, Y', strtotime($c['campaign_date'])) ?></span>
              <?php endif; ?>
              <?php if ($c['location']): ?>
              <span>📍 <?= htmlspecialchars(substr($c['location'],0,40)) ?></span>
              <?php endif; ?>
              <span>👥 <strong style="color:#0a4d8c;"><?= $c['reg_count'] ?></strong><?= $c['max_volunteers'] > 0 ? ' / '.$c['max_volunteers'] : '' ?> registered</span>
            </div>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <a href="campaign_registrations.php?campaign_id=<?= $c['id'] ?>"
               style="padding:7px 14px;background:#e6f6ed;color:#1a7a4a;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
               👥 View Registrations (<?= $c['reg_count'] ?>)
            </a>
            <a href="campaign_form.php?id=<?= $c['id'] ?>"
               style="padding:7px 14px;background:#e8f0fb;color:#0a4d8c;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
               ✏️ Edit
            </a>
            <a href="campaigns.php?toggle=<?= $c['id'] ?>"
               style="padding:7px 14px;background:#f4f7fb;color:#5a6a7e;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
               <?= $c['is_active'] ? '👁️ Hide' : '👁️ Show' ?>
            </a>
            <a href="campaigns.php?delete=<?= $c['id'] ?>"
               onclick="return confirm('Delete this campaign and all its registrations?')"
               style="padding:7px 14px;background:#fde8e6;color:#c0392b;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
               🗑️ Delete
            </a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
