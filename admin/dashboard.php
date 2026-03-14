<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$issues_count      = $conn->query("SELECT COUNT(*) as c FROM issues")->fetch_assoc()['c'];
$pending_count     = $conn->query("SELECT COUNT(*) as c FROM issues WHERE status='Pending'")->fetch_assoc()['c'];
$volunteers_count  = $conn->query("SELECT COUNT(*) as c FROM volunteers")->fetch_assoc()['c'];
$tokens_count      = $conn->query("SELECT COUNT(*) as c FROM tokens")->fetch_assoc()['c'];
$campaigns_count   = $conn->query("SELECT COUNT(*) as c FROM campaigns WHERE is_active=1")->fetch_assoc()['c'];
$users_count       = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active=1")->fetch_assoc()['c'];
$campaign_regs     = $conn->query("SELECT COUNT(*) as c FROM campaign_registrations")->fetch_assoc()['c'];
$announcements_count = $conn->query("SELECT COUNT(*) as c FROM announcements WHERE is_active=1")->fetch_assoc()['c'];

$recent_issues      = $conn->query("SELECT * FROM issues ORDER BY created_at DESC LIMIT 5");
$recent_tokens      = $conn->query("SELECT * FROM tokens ORDER BY created_at DESC LIMIT 5");
$upcoming_campaigns = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM campaign_registrations cr WHERE cr.campaign_id=c.id) as reg_count FROM campaigns c WHERE c.status IN ('Upcoming','Ongoing') AND c.is_active=1 ORDER BY c.campaign_date ASC LIMIT 4");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - JanataConnect</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="admin-layout">
  <?php include 'sidebar.php'; ?>
  <main class="admin-main">
    <div class="admin-header">
      <div><h1>Dashboard</h1><p>Welcome back, <?php echo htmlspecialchars($admin_name); ?>! Here is today's overview.</p></div>
      <div style="font-size:13.5px;color:#5a6a7e;"><?php echo date('l, F j, Y'); ?></div>
    </div>

    <div class="stats-row">
      <div class="stat-box"><div class="icon blue">📋</div><div><div class="val"><?php echo $issues_count; ?></div><div class="lbl">Total Issues</div></div></div>
      <div class="stat-box"><div class="icon orange">⏳</div><div><div class="val"><?php echo $pending_count; ?></div><div class="lbl">Pending Issues</div></div></div>
      <div class="stat-box"><div class="icon green">🤝</div><div><div class="val"><?php echo $volunteers_count; ?></div><div class="lbl">Volunteers</div></div></div>
      <div class="stat-box"><div class="icon blue">🎫</div><div><div class="val"><?php echo $tokens_count; ?></div><div class="lbl">Tokens</div></div></div>
    </div>

    <div class="stats-row" style="margin-bottom:28px;">
      <div class="stat-box"><div class="icon green">🌿</div><div><div class="val"><?php echo $campaigns_count; ?></div><div class="lbl">Active Campaigns</div></div></div>
      <div class="stat-box"><div class="icon blue">👥</div><div><div class="val"><?php echo $campaign_regs; ?></div><div class="lbl">Campaign Regs</div></div></div>
      <div class="stat-box"><div class="icon orange">📢</div><div><div class="val"><?php echo $announcements_count; ?></div><div class="lbl">Announcements</div></div></div>
      <div class="stat-box"><div class="icon blue">👥</div><div><div class="val"><?php echo $users_count; ?></div><div class="lbl">Registered Citizens</div></div></div>
    </div>

    <div style="display:flex;gap:12px;margin-bottom:28px;flex-wrap:wrap;">
      <a href="announcement_form.php" class="btn btn-primary">+ Add Announcement</a>
      <a href="campaign_form.php" class="btn btn-blue">+ Create Campaign</a>
      <a href="issues.php" class="btn" style="background:white;border:1.5px solid #d0dce8;color:#1a2332;">Review Issues (<?php echo $pending_count; ?> pending)</a>
    </div>

    <?php if ($upcoming_campaigns->num_rows > 0): ?>
    <div class="data-table" style="margin-bottom:24px;">
      <div class="table-header">
        <h3>🌿 Upcoming Campaigns</h3>
        <a href="campaigns.php" class="btn btn-blue" style="padding:7px 14px;font-size:13px;">Manage All</a>
      </div>
      <table><thead><tr><th>Campaign</th><th>Category</th><th>Date</th><th>Registered</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php while ($c = $upcoming_campaigns->fetch_assoc()): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($c['title']); ?></strong><br><span style="font-size:12px;color:#5a6a7e;"><?php echo htmlspecialchars(substr($c['location'],0,40)); ?></span></td>
          <td style="font-size:13px;"><?php echo htmlspecialchars($c['category']); ?></td>
          <td style="font-size:13px;"><?php echo $c['campaign_date'] ? date('M j, Y', strtotime($c['campaign_date'])) : 'TBA'; ?></td>
          <td><strong style="color:#0a4d8c;"><?php echo $c['reg_count']; ?></strong><?php echo $c['max_volunteers']>0 ? ' / '.$c['max_volunteers'] : ''; ?></td>
          <td><span class="badge badge-new"><?php echo htmlspecialchars($c['status']); ?></span></td>
          <td><a href="campaign_registrations.php?campaign_id=<?php echo $c['id']; ?>" style="font-size:13px;color:#0a4d8c;text-decoration:none;">View</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody></table>
    </div>
    <?php endif; ?>

    <div class="data-table" style="margin-bottom:24px;">
      <div class="table-header"><h3>Recent Issues</h3><a href="issues.php" class="btn btn-blue" style="padding:7px 14px;font-size:13px;">View All</a></div>
      <table><thead><tr><th>ID</th><th>Name</th><th>Ward</th><th>Category</th><th>Title</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php while ($row = $recent_issues->fetch_assoc()): ?>
        <tr>
          <td style="font-size:12px;color:#5a6a7e;">ISS-<?php echo str_pad($row['id'],4,'0',STR_PAD_LEFT); ?></td>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td>Ward <?php echo htmlspecialchars($row['ward']); ?></td>
          <td style="font-size:12.5px;"><?php echo htmlspecialchars($row['category']); ?></td>
          <td><a href="issue_detail.php?id=<?php echo $row['id']; ?>" style="color:#0a4d8c;text-decoration:none;"><?php echo htmlspecialchars(substr($row['title'],0,35)); ?>...</a></td>
          <td><?php echo date('M j', strtotime($row['date'])); ?></td>
          <td><span class="badge badge-<?php echo strtolower(str_replace(' ','-',$row['status'])); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
        </tr>
        <?php endwhile; ?>
      </tbody></table>
    </div>

    <div class="data-table">
      <div class="table-header"><h3>Recent Tokens</h3><a href="tokens.php" class="btn btn-blue" style="padding:7px 14px;font-size:13px;">View All</a></div>
      <table><thead><tr><th>Token</th><th>Name</th><th>Office</th><th>Service</th><th>Date</th></tr></thead>
      <tbody>
        <?php while ($row = $recent_tokens->fetch_assoc()): ?>
        <tr>
          <td><strong style="color:#0a4d8c;"><?php echo htmlspecialchars($row['token_number']); ?></strong></td>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td style="font-size:13px;"><?php echo htmlspecialchars(substr($row['office'],0,28)); ?>...</td>
          <td style="font-size:13px;"><?php echo htmlspecialchars($row['service']); ?></td>
          <td><?php echo date('M j', strtotime($row['date'])); ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody></table>
    </div>
  </main>
</div>
<?php include '../includes/footer.php'; ?>
