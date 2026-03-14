<?php
$page_title = 'Announcements';
require_once 'config/db.php';
$conn = getDB();

$tag_filter = $conn->real_escape_string($_GET['tag'] ?? '');
$where = "WHERE is_active=1" . ($tag_filter ? " AND tag='$tag_filter'" : '');
$announcements = $conn->query("SELECT * FROM announcements $where ORDER BY created_at DESC");

// Get unique tags for filter
$tags_res = $conn->query("SELECT DISTINCT tag, tag_color FROM announcements WHERE is_active=1 ORDER BY tag");
$tags = [];
while ($t = $tags_res->fetch_assoc()) $tags[] = $t;

$conn->close();
include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Announcements</span>
    </div>
    <h1>📢 Municipal Announcements</h1>
    <p>Official notices, updates, and information from Pokhara Metropolitan City.</p>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Tag filters -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:30px;align-items:center;">
      <span style="font-size:13.5px;color:#5a6a7e;font-weight:600;">Filter by:</span>
      <a href="announcements.php" style="padding:5px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
        background:<?= !$tag_filter ? 'var(--primary)' : '#f0f5ff' ?>;
        color:<?= !$tag_filter ? 'white' : 'var(--primary)' ?>;border:1.5px solid var(--primary);">
        All
      </a>
      <?php foreach ($tags as $t): ?>
      <a href="announcements.php?tag=<?= urlencode($t['tag']) ?>"
         style="padding:5px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
           background:<?= ($tag_filter == $t['tag']) ? $t['tag_color'] : $t['tag_color'].'18' ?>;
           color:<?= ($tag_filter == $t['tag']) ? 'white' : $t['tag_color'] ?>;
           border:1.5px solid <?= $t['tag_color'] ?>44;">
        <?= htmlspecialchars($t['tag']) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Announcements grid -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <?php
      $count = 0;
      while ($a = $announcements->fetch_assoc()):
        $count++;
      ?>
      <div style="background:white;border-radius:14px;padding:26px;box-shadow:0 2px 14px rgba(10,77,140,0.08);border:1.5px solid #d0dce8;transition:box-shadow 0.2s;"
           onmouseover="this.style.boxShadow='0 6px 28px rgba(10,77,140,0.14)'"
           onmouseout="this.style.boxShadow='0 2px 14px rgba(10,77,140,0.08)'">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:13px;">
          <span style="background:<?= htmlspecialchars($a['tag_color']) ?>18;color:<?= htmlspecialchars($a['tag_color']) ?>;
            font-size:11.5px;font-weight:700;padding:4px 12px;border-radius:20px;
            border:1px solid <?= htmlspecialchars($a['tag_color']) ?>33;">
            <?= htmlspecialchars($a['tag']) ?>
          </span>
          <span style="font-size:12px;color:#8a9ab0;"><?= date('M j, Y', strtotime($a['created_at'])) ?></span>
        </div>
        <h3 style="font-size:16px;font-weight:700;color:#1a2332;margin-bottom:10px;line-height:1.4;">
          <?= htmlspecialchars($a['title']) ?>
        </h3>
        <p style="font-size:13.5px;color:#5a6a7e;line-height:1.7;"><?= nl2br(htmlspecialchars($a['body'])) ?></p>
      </div>
      <?php endwhile; ?>
    </div>

    <?php if ($count === 0): ?>
    <div style="text-align:center;padding:60px 20px;color:#5a6a7e;">
      <div style="font-size:48px;margin-bottom:16px;">📭</div>
      <h3 style="font-size:18px;margin-bottom:8px;">No announcements found</h3>
      <p>Check back soon for municipal notices and updates.</p>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
