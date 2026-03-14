<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$search = trim($conn->real_escape_string($_GET['search'] ?? ''));
$date_filter = $conn->real_escape_string($_GET['date'] ?? '');

$where = 'WHERE 1=1';
if ($search) $where .= " AND (name LIKE '%$search%' OR token_number LIKE '%$search%' OR office LIKE '%$search%')";
if ($date_filter) $where .= " AND date='$date_filter'";

$tokens = $conn->query("SELECT * FROM tokens $where ORDER BY created_at DESC");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tokens – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div class="admin-header">
      <div><h1>🎫 Token Bookings</h1><p>All digital queue token bookings.</p></div>
    </div>

    <form method="GET" style="display:flex;gap:12px;margin-bottom:22px;flex-wrap:wrap;">
      <input type="text" name="search" placeholder="🔍 Search by name, token, office..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex:1;min-width:180px;padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
      <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" style="padding:9px 14px;border:1.5px solid #d0dce8;border-radius:8px;font-size:14px;font-family:inherit;">
      <button type="submit" class="btn btn-blue" style="padding:9px 20px;">Filter</button>
      <a href="tokens.php" class="btn" style="padding:9px 16px;border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Reset</a>
    </form>

    <div class="data-table">
      <div class="table-header">
        <h3>All Token Bookings</h3>
        <span style="font-size:13.5px;color:#5a6a7e;"><?= $tokens->num_rows ?> records</span>
      </div>
      <table>
        <thead>
          <tr><th>Token #</th><th>Visitor</th><th>Office</th><th>Service</th><th>Date</th><th>Time Slot</th><th>Booked</th></tr>
        </thead>
        <tbody>
          <?php while ($row = $tokens->fetch_assoc()): ?>
          <tr>
            <td><strong style="color:#0a4d8c;font-size:15px;"><?= htmlspecialchars($row['token_number']) ?></strong></td>
            <td>
              <strong style="font-size:14px;"><?= htmlspecialchars($row['name']) ?></strong><br>
              <span style="font-size:12px;color:#5a6a7e;"><?= htmlspecialchars($row['phone']) ?></span>
            </td>
            <td style="font-size:13px;max-width:160px;"><?= htmlspecialchars($row['office']) ?></td>
            <td style="font-size:13px;"><?= htmlspecialchars($row['service']) ?></td>
            <td style="white-space:nowrap;"><?= date('M j, Y', strtotime($row['date'])) ?></td>
            <td style="font-size:13px;"><?= htmlspecialchars($row['time_slot']) ?></td>
            <td style="font-size:12.5px;color:#5a6a7e;"><?= date('M j, g:i A', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
