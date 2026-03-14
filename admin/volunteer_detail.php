<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: volunteers.php'); exit; }

$res = $conn->query("SELECT * FROM volunteers WHERE id=$id LIMIT 1");
if (!$res || $res->num_rows === 0) { header('Location: volunteers.php'); exit; }
$v = $res->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteer – <?= htmlspecialchars($v['name']) ?> – JanataConnect Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div style="margin-bottom:20px;">
      <a href="volunteers.php" style="color:#5a6a7e;text-decoration:none;font-size:14px;">← Back to Volunteers</a>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

      <div style="background:white;border-radius:14px;box-shadow:var(--shadow);overflow:hidden;">
        <div style="background:linear-gradient(90deg,#1a7a4a,#24a062);padding:22px 28px;color:white;">
          <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-bottom:4px;">VOL-<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></div>
          <h2 style="font-size:22px;font-weight:700;"><?= htmlspecialchars($v['name']) ?></h2>
          <div style="font-size:13px;color:rgba(255,255,255,0.8);margin-top:6px;">
            <?= htmlspecialchars($v['gender'] ?? '') ?>
            <?php if ($v['dob']): ?> &nbsp;|&nbsp; DOB: <?= date('M j, Y', strtotime($v['dob'])) ?><?php endif; ?>
            &nbsp;|&nbsp; Ward <?= htmlspecialchars($v['ward'] ?? '—') ?>
          </div>
        </div>

        <div style="padding:28px;">
          <!-- Skills -->
          <?php if ($v['skills']): ?>
          <h4 style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#5a6a7e;margin-bottom:12px;">🎯 Skills</h4>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:22px;">
            <?php foreach (explode(', ', $v['skills']) as $skill): ?>
            <span style="background:#e8f0fb;color:#0a4d8c;padding:5px 13px;border-radius:20px;font-size:13px;font-weight:500;"><?= htmlspecialchars($skill) ?></span>
            <?php endforeach; ?>
            <?php if ($v['other_skills']): ?>
            <span style="background:#f4f7fb;color:#5a6a7e;padding:5px 13px;border-radius:20px;font-size:13px;"><?= htmlspecialchars($v['other_skills']) ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Availability -->
          <h4 style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#5a6a7e;margin-bottom:12px;">📅 Availability</h4>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px;">
            <div style="background:#f4f7fb;border-radius:8px;padding:14px;text-align:center;">
              <div style="font-size:11px;color:#5a6a7e;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:5px;">Days</div>
              <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($v['availability_days'] ?? '—') ?></div>
            </div>
            <div style="background:#f4f7fb;border-radius:8px;padding:14px;text-align:center;">
              <div style="font-size:11px;color:#5a6a7e;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:5px;">Time</div>
              <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($v['availability_time'] ?? '—') ?></div>
            </div>
          </div>

          <!-- Experience -->
          <?php if ($v['has_experience']): ?>
          <h4 style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#5a6a7e;margin-bottom:10px;">📋 Previous Experience</h4>
          <div style="background:#e6f6ed;border-radius:8px;padding:14px 16px;margin-bottom:22px;font-size:14px;">
            ✅ Has volunteered before
            <?php if ($v['prev_organization']): ?> — <strong><?= htmlspecialchars($v['prev_organization']) ?></strong><?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Address -->
          <?php if ($v['address']): ?>
          <div style="font-size:14px;color:#5a6a7e;">📍 <?= htmlspecialchars($v['address']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Contact Sidebar -->
      <div style="display:flex;flex-direction:column;gap:18px;">
        <div style="background:white;border-radius:12px;padding:22px;box-shadow:var(--shadow);">
          <h4 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#5a6a7e;margin-bottom:16px;">📞 Contact</h4>
          <div style="display:flex;flex-direction:column;gap:12px;font-size:14px;">
            <div>
              <strong style="font-size:12px;color:#5a6a7e;display:block;">Phone</strong>
              <a href="tel:<?= htmlspecialchars($v['phone']) ?>" style="color:#0a4d8c;font-weight:600;"><?= htmlspecialchars($v['phone']) ?></a>
            </div>
            <?php if ($v['email']): ?>
            <div>
              <strong style="font-size:12px;color:#5a6a7e;display:block;">Email</strong>
              <a href="mailto:<?= htmlspecialchars($v['email']) ?>" style="color:#0a4d8c;"><?= htmlspecialchars($v['email']) ?></a>
            </div>
            <?php endif; ?>
            <?php if ($v['citizenship_no']): ?>
            <div>
              <strong style="font-size:12px;color:#5a6a7e;display:block;">Citizenship #</strong>
              <?= htmlspecialchars($v['citizenship_no']) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($v['emergency_contact'] || $v['emergency_phone']): ?>
        <div style="background:#fff8f0;border-radius:12px;padding:22px;box-shadow:var(--shadow);border:1.5px solid #ffd0a0;">
          <h4 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#e67e22;margin-bottom:14px;">🆘 Emergency Contact</h4>
          <?php if ($v['emergency_contact']): ?><div style="font-size:14px;font-weight:600;margin-bottom:6px;"><?= htmlspecialchars($v['emergency_contact']) ?></div><?php endif; ?>
          <?php if ($v['emergency_phone']): ?><a href="tel:<?= htmlspecialchars($v['emergency_phone']) ?>" style="color:#e67e22;font-size:14px;"><?= htmlspecialchars($v['emergency_phone']) ?></a><?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="background:#f4f7fb;border-radius:10px;padding:16px;font-size:13px;color:#5a6a7e;">
          Registered: <?= date('F j, Y \a\t g:i A', strtotime($v['created_at'])) ?>
        </div>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
