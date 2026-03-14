<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$campaign_id = (int)($_GET['campaign_id'] ?? 0);
if (!$campaign_id) { header('Location: campaigns.php'); exit; }

$campaign_res = $conn->query("SELECT * FROM campaigns WHERE id=$campaign_id LIMIT 1");
if (!$campaign_res || !$campaign_res->num_rows) { header('Location: campaigns.php'); exit; }
$campaign = $campaign_res->fetch_assoc();

// Handle delete registration
if (isset($_GET['delete_reg'])) {
    $rid = (int)$_GET['delete_reg'];
    $conn->query("DELETE FROM campaign_registrations WHERE id=$rid AND campaign_id=$campaign_id");
    header("Location: campaign_registrations.php?campaign_id=$campaign_id&msg=deleted");
    exit;
}

$search = trim($conn->real_escape_string($_GET['search'] ?? ''));
$where = "WHERE campaign_id=$campaign_id" . ($search ? " AND (name LIKE '%$search%' OR phone LIKE '%$search%')" : '');
$regs = $conn->query("SELECT * FROM campaign_registrations $where ORDER BY created_at ASC");
$total = $conn->query("SELECT COUNT(*) as c FROM campaign_registrations WHERE campaign_id=$campaign_id")->fetch_assoc()['c'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrations – <?= htmlspecialchars($campaign['title']) ?></title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div style="margin-bottom:16px;">
      <a href="campaigns.php" style="color:#5a6a7e;text-decoration:none;font-size:14px;">← Back to Campaigns</a>
    </div>

    <div class="admin-header">
      <div>
        <h1>👥 Campaign Registrations</h1>
        <p style="color:#5a6a7e;font-size:14px;margin-top:4px;">
          <strong><?= htmlspecialchars($campaign['title']) ?></strong>
          <?php if ($campaign['campaign_date']): ?>
          — <?= date('M j, Y', strtotime($campaign['campaign_date'])) ?>
          <?php endif; ?>
        </p>
      </div>
      <div style="text-align:right;">
        <div style="font-size:28px;font-weight:700;color:#0a4d8c;"><?= $total ?></div>
        <div style="font-size:13px;color:#5a6a7e;">
          <?= $campaign['max_volunteers'] > 0 ? "/ {$campaign['max_volunteers']} max" : 'registrations' ?>
        </div>
      </div>
    </div>

    <?php if ($campaign['max_volunteers'] > 0): ?>
    <div style="background:white;border-radius:10px;padding:14px 18px;margin-bottom:22px;border:1.5px solid #d0dce8;display:flex;align-items:center;gap:16px;">
      <div style="flex:1;background:#e8f0fb;border-radius:8px;height:10px;overflow:hidden;">
        <div style="background:var(--primary);height:100%;width:<?= min(100, round($total/$campaign['max_volunteers']*100)) ?>%;border-radius:8px;transition:width 0.3s;"></div>
      </div>
      <span style="font-size:13.5px;font-weight:600;color:#0a4d8c;white-space:nowrap;">
        <?= $total ?> / <?= $campaign['max_volunteers'] ?> (<?= round($total/$campaign['max_volunteers']*100) ?>%)
      </span>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ Registration removed.</div>
    <?php endif; ?>

    <form method="GET" style="display:flex;gap:12px;margin-bottom:20px;">
      <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">
      <input type="text" name="search" placeholder="🔍 Search by name or phone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
             style="flex:1;padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
      <button type="submit" class="btn btn-blue" style="padding:9px 20px;">Search</button>
      <a href="campaign_registrations.php?campaign_id=<?= $campaign_id ?>" class="btn" style="padding:9px 16px;border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Reset</a>
    </form>

    <div class="data-table">
      <div class="table-header">
        <h3>Registered Participants</h3>
        <span style="font-size:13.5px;color:#5a6a7e;"><?= $regs->num_rows ?> shown</span>
      </div>
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Contact</th><th>Ward</th><th>Skills</th><th>Message</th><th>Registered</th><th></th></tr>
        </thead>
        <tbody>
          <?php $i = 0; while ($r = $regs->fetch_assoc()): $i++; ?>
          <tr>
            <td style="color:#5a6a7e;font-size:13px;"><?= $i ?></td>
            <td><strong style="font-size:14px;"><?= htmlspecialchars($r['name']) ?></strong></td>
            <td>
              <a href="tel:<?= htmlspecialchars($r['phone']) ?>" style="color:#0a4d8c;font-size:13.5px;"><?= htmlspecialchars($r['phone']) ?></a>
              <?php if ($r['email']): ?><br><a href="mailto:<?= htmlspecialchars($r['email']) ?>" style="color:#5a6a7e;font-size:12px;"><?= htmlspecialchars($r['email']) ?></a><?php endif; ?>
            </td>
            <td><?= $r['ward'] ? 'Ward '.$r['ward'] : '—' ?></td>
            <td style="font-size:13px;color:#5a6a7e;"><?= htmlspecialchars($r['skills'] ?: '—') ?></td>
            <td style="font-size:13px;color:#5a6a7e;max-width:160px;"><?= htmlspecialchars($r['message'] ?: '—') ?></td>
            <td style="font-size:12.5px;color:#5a6a7e;white-space:nowrap;"><?= date('M j, g:i A', strtotime($r['created_at'])) ?></td>
            <td>
              <a href="campaign_registrations.php?campaign_id=<?= $campaign_id ?>&delete_reg=<?= $r['id'] ?>"
                 onclick="return confirm('Remove this registration?')"
                 style="font-size:12px;color:#c0392b;text-decoration:none;padding:4px 10px;background:#fde8e6;border-radius:6px;">Remove</a>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if ($i === 0): ?>
          <tr><td colspan="8" style="text-align:center;padding:40px;color:#5a6a7e;">No registrations yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
