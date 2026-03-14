<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$search = trim($conn->real_escape_string($_GET['search'] ?? ''));
$where = $search ? "WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR ward LIKE '%$search%' OR skills LIKE '%$search%'" : '';

$volunteers = $conn->query("SELECT * FROM volunteers $where ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteers – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-header">
      <div><h1>🤝 Volunteers</h1><p>All registered community volunteers.</p></div>
    </div>

    <form method="GET" style="display:flex;gap:12px;margin-bottom:22px;">
      <input type="text" name="search" placeholder="🔍 Search by name, phone, ward, skill..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex:1;padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
      <button type="submit" class="btn btn-blue" style="padding:9px 20px;">Search</button>
      <a href="volunteers.php" class="btn" style="padding:9px 16px;border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Reset</a>
    </form>

    <div class="data-table">
      <div class="table-header">
        <h3>Registered Volunteers</h3>
        <span style="font-size:13.5px;color:#5a6a7e;"><?= $volunteers->num_rows ?> volunteers</span>
      </div>
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Contact</th><th>Ward</th><th>Skills</th><th>Availability</th><th>Registered</th></tr>
        </thead>
        <tbody>
          <?php while ($row = $volunteers->fetch_assoc()): ?>
          <tr>
            <td style="font-size:12px;color:#5a6a7e;">VOL-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
            <td>
              <a href="volunteer_detail.php?id=<?= $row['id'] ?>" style="text-decoration:none;color:inherit;">
                <strong><?= htmlspecialchars($row['name']) ?></strong>
              </a><br>
              <span style="font-size:12px;color:#5a6a7e;"><?= htmlspecialchars($row['gender'] ?? '') ?></span>
            </td>
            <td>
              <?= htmlspecialchars($row['phone']) ?><br>
              <span style="font-size:12px;color:#5a6a7e;"><?= htmlspecialchars($row['email'] ?? '') ?></span>
            </td>
            <td>Ward <?= htmlspecialchars($row['ward'] ?? '—') ?></td>
            <td style="font-size:13px;max-width:180px;">
              <?php
              if ($row['skills']) {
                $skills = explode(', ', $row['skills']);
                foreach (array_slice($skills, 0, 3) as $s) {
                  echo '<span style="display:inline-block;background:#e8f0fb;color:#0a4d8c;padding:2px 8px;border-radius:12px;font-size:11.5px;margin:2px;">' . htmlspecialchars($s) . '</span>';
                }
                if (count($skills) > 3) echo '<span style="font-size:11.5px;color:#5a6a7e;"> +' . (count($skills) - 3) . ' more</span>';
              } else { echo '—'; }
              ?>
            </td>
            <td>
              <?php if ($row['availability_days']): ?>
              <span style="font-size:13px;"><?= htmlspecialchars($row['availability_days']) ?></span><br>
              <span style="font-size:12px;color:#5a6a7e;"><?= htmlspecialchars($row['availability_time'] ?? '') ?></span>
              <?php else: echo '—'; endif; ?>
            </td>
            <td style="font-size:13px;"><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
