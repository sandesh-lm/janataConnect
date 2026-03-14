<?php
$page_title = 'Volunteer Campaigns';
require_once 'config/auth.php';
$__camp_user = user_logged_in() ? current_user() : null;
require_once 'config/db.php';
$conn = getDB();

$cat_filter = $conn->real_escape_string($_GET['category'] ?? '');
$where = "WHERE is_active=1" . ($cat_filter ? " AND category='$cat_filter'" : '');
$campaigns = $conn->query("SELECT c.*, 
    (SELECT COUNT(*) FROM campaign_registrations cr WHERE cr.campaign_id = c.id) as reg_count
    FROM campaigns c $where ORDER BY campaign_date ASC");

$cats_res = $conn->query("SELECT DISTINCT category FROM campaigns WHERE is_active=1 ORDER BY category");
$categories = [];
while ($cat = $cats_res->fetch_assoc()) $categories[] = $cat['category'];

$conn->close();

$cat_icons = [
    'Environment' => '🌿',
    'Sanitation' => '🧹',
    'Health' => '🏥',
    'Disaster Relief' => '🆘',
    'Education' => '📚',
    'Infrastructure' => '🏗️',
    'Social Work' => '🤝',
];

$status_colors = [
    'Upcoming' => ['bg' => '#e8f0fb', 'text' => '#0a4d8c'],
    'Ongoing'  => ['bg' => '#e6f6ed', 'text' => '#1a7a4a'],
    'Completed'=> ['bg' => '#f4f7fb', 'text' => '#5a6a7e'],
    'Cancelled'=> ['bg' => '#fde8e6', 'text' => '#c0392b'],
];

include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Volunteer Campaigns</span>
    </div>
    <h1>🌿 Volunteer Campaigns</h1>
    <p>Join community-driven campaigns organized by Pokhara Metropolitan City. Make a real difference.</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Category filters -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:32px;align-items:center;">
      <span style="font-size:13.5px;color:#5a6a7e;font-weight:600;">Category:</span>
      <a href="campaigns.php" style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
        background:<?= !$cat_filter ? 'var(--primary)' : '#f0f5ff' ?>;
        color:<?= !$cat_filter ? 'white' : 'var(--primary)' ?>;border:1.5px solid var(--primary)40;">All</a>
      <?php foreach ($categories as $cat): ?>
      <a href="campaigns.php?category=<?= urlencode($cat) ?>"
         style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
           background:<?= ($cat_filter==$cat) ? 'var(--primary)' : '#f0f5ff' ?>;
           color:<?= ($cat_filter==$cat) ? 'white' : 'var(--primary)' ?>;
           border:1.5px solid rgba(10,77,140,0.25);">
        <?= ($cat_icons[$cat] ?? '📌') . ' ' . htmlspecialchars($cat) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Campaign cards -->
    <div style="display:flex;flex-direction:column;gap:22px;">
      <?php
      $count = 0;
      while ($c = $campaigns->fetch_assoc()):
        $count++;
        $sc = $status_colors[$c['status']] ?? $status_colors['Upcoming'];
        $icon = $cat_icons[$c['category']] ?? '📌';
        $slots_left = $c['max_volunteers'] > 0 ? $c['max_volunteers'] - $c['reg_count'] : null;
        $is_full = $slots_left !== null && $slots_left <= 0;
        $is_open = $c['status'] === 'Upcoming' || $c['status'] === 'Ongoing';
      ?>
      <div style="background:white;border-radius:16px;border:1.5px solid #d0dce8;box-shadow:0 2px 14px rgba(10,77,140,0.07);overflow:hidden;display:grid;grid-template-columns:1fr auto;">
        <div style="padding:28px 30px;">
          <!-- Header row -->
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap;">
            <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;font-size:12px;font-weight:700;padding:4px 12px;border-radius:20px;">
              <?= htmlspecialchars($c['status']) ?>
            </span>
            <span style="background:#f0f5ff;color:#0a4d8c;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;">
              <?= $icon ?> <?= htmlspecialchars($c['category']) ?>
            </span>
            <?php if ($slots_left !== null): ?>
            <span style="font-size:12px;color:<?= $is_full ? '#c0392b' : '#1a7a4a' ?>;font-weight:600;">
              <?= $is_full ? '🔴 Full' : "🟢 {$slots_left} slots left" ?>
            </span>
            <?php endif; ?>
          </div>

          <h3 style="font-size:19px;font-weight:700;color:#1a2332;margin-bottom:10px;">
            <?= htmlspecialchars($c['title']) ?>
          </h3>
          <p style="font-size:14px;color:#5a6a7e;line-height:1.7;margin-bottom:18px;">
            <?= nl2br(htmlspecialchars(substr($c['description'], 0, 280))) ?><?= strlen($c['description']) > 280 ? '...' : '' ?>
          </p>

          <!-- Meta row -->
          <div style="display:flex;gap:22px;flex-wrap:wrap;font-size:13px;color:#5a6a7e;margin-bottom:20px;">
            <?php if ($c['campaign_date']): ?>
            <span>📅 <?= date('l, F j, Y', strtotime($c['campaign_date'])) ?></span>
            <?php endif; ?>
            <?php if ($c['campaign_time']): ?>
            <span>⏰ <?= htmlspecialchars($c['campaign_time']) ?></span>
            <?php endif; ?>
            <?php if ($c['location']): ?>
            <span>📍 <?= htmlspecialchars($c['location']) ?></span>
            <?php endif; ?>
            <?php if ($c['organizer']): ?>
            <span>👤 <?= htmlspecialchars($c['organizer']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Action buttons -->
          <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <?php if ($is_open && !$is_full): ?>
            <?php if ($__camp_user): ?>
            <a href="campaign_register.php?id=<?= $c['id'] ?>" class="btn btn-primary">
              🤝 Register for this Campaign
            </a>
            <?php else: ?>
            <a href="login.php?redirect=campaign_register.php%3Fid%3D<?= $c['id'] ?>" class="btn btn-primary">
              🔐 Login to Register
            </a>
            <?php endif; ?>
            <?php elseif ($is_full): ?>
            <span class="btn" style="background:#f4f7fb;color:#5a6a7e;cursor:not-allowed;border:1.5px solid #d0dce8;">
              🔴 Registration Full
            </span>
            <?php else: ?>
            <span class="btn" style="background:#f4f7fb;color:#5a6a7e;cursor:not-allowed;border:1.5px solid #d0dce8;">
              Registration Closed
            </span>
            <?php endif; ?>
            <span style="display:flex;align-items:center;font-size:13px;color:#5a6a7e;">
              👥 <?= $c['reg_count'] ?> registered<?= $c['max_volunteers'] > 0 ? ' / ' . $c['max_volunteers'] . ' max' : '' ?>
            </span>
          </div>
        </div>

        <!-- Colored sidebar -->
        <div style="width:8px;background:linear-gradient(180deg,var(--primary),var(--primary-light));"></div>
      </div>
      <?php endwhile; ?>
    </div>

    <?php if ($count === 0): ?>
    <div style="text-align:center;padding:60px 20px;color:#5a6a7e;">
      <div style="font-size:48px;margin-bottom:16px;">🌱</div>
      <h3 style="font-size:18px;margin-bottom:8px;">No campaigns available right now</h3>
      <p>Check back soon — new campaigns are added regularly by the municipality.</p>
      <a href="volunteer_register.php" class="btn btn-blue" style="margin-top:20px;">Register as General Volunteer</a>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
