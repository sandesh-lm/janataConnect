<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

// Toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE users SET is_active = 1 - is_active WHERE id=$id");
    header('Location: users.php?msg=updated');
    exit;
}

$search = trim($conn->real_escape_string($_GET['search'] ?? ''));
$where  = $search ? "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'" : '';

$users = $conn->query("SELECT u.*,
    (SELECT COUNT(*) FROM issues i WHERE i.user_id=u.id) as issue_count,
    (SELECT COUNT(*) FROM tokens t WHERE t.user_id=u.id) as token_count,
    (SELECT COUNT(*) FROM campaign_registrations cr WHERE cr.user_id=u.id) as camp_count
    FROM users u $where ORDER BY u.created_at DESC");

$total_users   = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users  = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active=1")->fetch_assoc()['c'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-header">
      <div>
        <h1>👥 Registered Users</h1>
        <p>Citizens registered on JanataConnect — <?= $active_users ?> active / <?= $total_users ?> total.</p>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ User status updated.</div>
    <?php endif; ?>

    <form method="GET" style="display:flex;gap:12px;margin-bottom:22px;">
      <input type="text" name="search" placeholder="🔍 Search by name, email or phone..."
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
             style="flex:1;padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
      <button type="submit" class="btn btn-blue" style="padding:9px 20px;">Search</button>
      <a href="users.php" class="btn" style="padding:9px 16px;border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Reset</a>
    </form>

    <div class="data-table">
      <div class="table-header">
        <h3>All Citizens</h3>
        <span style="font-size:13.5px;color:#5a6a7e;"><?= $users->num_rows ?> shown</span>
      </div>
      <table>
        <thead>
          <tr><th>User</th><th>Contact</th><th>Ward</th><th>Issues</th><th>Tokens</th><th>Campaigns</th><th>Joined</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php while ($u = $users->fetch_assoc()): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:50%;background:#0a4d8c;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white;flex-shrink:0;">
                  <?= strtoupper(substr($u['full_name'],0,1)) ?>
                </div>
                <div>
                  <strong style="font-size:14px;"><?= htmlspecialchars($u['full_name']) ?></strong>
                  <?php if ($u['last_login']): ?>
                  <div style="font-size:11.5px;color:#8a9ab0;">Last login <?= date('M j', strtotime($u['last_login'])) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <div style="font-size:13.5px;"><?= htmlspecialchars($u['email']) ?></div>
              <div style="font-size:12.5px;color:#5a6a7e;"><?= htmlspecialchars($u['phone']) ?></div>
            </td>
            <td><?= $u['ward'] ? 'Ward '.$u['ward'] : '—' ?></td>
            <td>
              <a href="issues.php?search=<?= urlencode($u['full_name']) ?>" style="color:#0a4d8c;font-weight:700;text-decoration:none;">
                <?= $u['issue_count'] ?>
              </a>
            </td>
            <td style="font-weight:600;color:#1a2332;"><?= $u['token_count'] ?></td>
            <td style="font-weight:600;color:#1a2332;"><?= $u['camp_count'] ?></td>
            <td style="font-size:13px;color:#5a6a7e;white-space:nowrap;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <a href="users.php?toggle=<?= $u['id'] ?>"
                 style="font-size:12.5px;font-weight:700;text-decoration:none;padding:4px 12px;border-radius:20px;
                   background:<?= $u['is_active'] ? '#e6f6ed' : '#fde8e6' ?>;
                   color:<?= $u['is_active'] ? '#1a7a4a' : '#c0392b' ?>;">
                <?= $u['is_active'] ? '● Active' : '○ Suspended' ?>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
