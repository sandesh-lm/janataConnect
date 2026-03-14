<?php
// Shared header include
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base_path = $is_admin ? '../' : '';

// Load user auth (for nav state) — only on public pages
if (!$is_admin) {
    require_once $base_path . 'config/auth.php';
    $__user = user_logged_in() ? current_user() : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? $page_title . ' – JanataConnect' : 'JanataConnect – Pokhara Metropolitan City' ?></title>
  <meta name="description" content="JanataConnect – Official citizen service portal for Pokhara Metropolitan City, Nepal">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="<?= $base_path ?>css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏛️</text></svg>">
</head>
<body>

<!-- Notice Bar - live from DB -->
<?php
// Only fetch ticker if DB config is available
$_ticker_items = [];
$_ticker_path = $is_admin ? '../config/db.php' : 'config/db.php';
if (file_exists($_ticker_path)) {
    require_once $_ticker_path;
    try {
        $_tc = getDB();
        $_tr = $_tc->query("SELECT title FROM announcements WHERE is_active=1 AND is_ticker=1 ORDER BY created_at DESC LIMIT 8");
        if ($_tr) { while ($_row = $_tr->fetch_assoc()) $_ticker_items[] = htmlspecialchars($_row['title']); }
        $_tc->close();
    } catch (Exception $e) { /* silently fallback */ }
}
if (empty($_ticker_items)) {
    $_ticker_items = ['Welcome to JanataConnect – Official Portal of Pokhara Metropolitan City',
                      'Report community issues, register as volunteer, and book office tokens online',
                      'Sun–Fri: 10AM–5PM | 📞 061-525999 | pokharametro.gov.np'];
}
$_ticker_text = implode(' &nbsp;|&nbsp; ', array_merge($_ticker_items, $_ticker_items));
?>
<div class="notice-bar">
  <div class="container">
    <span class="notice-tag">📢 Notice</span>
    <div class="notice-ticker">
      <div class="notice-ticker-inner">
        <span><?= $_ticker_text ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-top">
    <div class="container">
      <span>🏛️ Government of Nepal | Gandaki Province | Kaski District</span>
      <div style="display:flex;align-items:center;gap:4px;">
        <a href="#" style="font-size:12.5px;">NP | EN</a>
        <?php if (!$is_admin): ?>
          <?php if ($__user): ?>
            <!-- Logged-in user dropdown -->
            <div class="user-nav-menu">
              <div class="user-nav-trigger">
                <div class="user-avatar-sm" style="background:<?= avatar_color($__user['full_name']) ?>;">
                  <?= strtoupper(substr($__user['full_name'],0,1)) ?>
                </div>
                <span><?= htmlspecialchars(first_name($__user['full_name'])) ?></span>
                <span style="font-size:10px;opacity:0.7;">▼</span>
              </div>
              <div class="user-nav-dropdown">
                <div class="user-nav-dropdown-header">
                  <div class="uname"><?= htmlspecialchars($__user['full_name']) ?></div>
                  <div class="uemail"><?= htmlspecialchars($__user['email']) ?></div>
                </div>
                <a href="<?= $base_path ?>my-dashboard.php"><span class="di">📊</span> My Dashboard</a>
                <a href="<?= $base_path ?>my-dashboard.php?tab=issues"><span class="di">📋</span> My Issues</a>
                <a href="<?= $base_path ?>my-dashboard.php?tab=tokens"><span class="di">🎫</span> My Tokens</a>
                <a href="<?= $base_path ?>my-dashboard.php?tab=campaigns"><span class="di">🌿</span> My Campaigns</a>
                <a href="<?= $base_path ?>my-dashboard.php?tab=profile"><span class="di">⚙️</span> Profile Settings</a>
                <a href="<?= $base_path ?>user-logout.php" class="logout-link"><span class="di">🚪</span> Logout</a>
              </div>
            </div>
          <?php else: ?>
            <a href="<?= $base_path ?>login.php" style="font-size:13px;padding:5px 12px;border:1.5px solid rgba(255,255,255,0.4);border-radius:6px;color:white;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">Login</a>
            <a href="<?= $base_path ?>register.php" style="font-size:13px;padding:5px 14px;background:var(--accent);border-radius:6px;color:white;text-decoration:none;font-weight:600;transition:all 0.2s;" onmouseover="this.style.background='var(--accent-light)'" onmouseout="this.style.background='var(--accent)'">Register</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= $base_path ?>../index.php" style="font-size:12.5px;">Public Site</a>
        <?php endif; ?>
        <a href="<?= $base_path ?>contact.php" style="font-size:12.5px;">Help</a>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="nav-main">
      <a href="<?= $base_path ?>index.php" class="nav-brand">
        <div class="nav-logo">🏛️</div>
        <div class="nav-brand-text">
          <div class="name">JanataConnect</div>
          <div class="sub">Pokhara Metropolitan City</div>
        </div>
      </a>
      <button class="nav-toggle" aria-label="Toggle menu">☰</button>
      <ul class="nav-links">
        <li><a href="<?= $base_path ?>index.php" <?= ($current_page=='index.php') ? 'class="active"' : '' ?>>Home</a></li>
        <?php if (!$is_admin && isset($__user) && $__user): ?>
        <li><a href="<?= $base_path ?>my-dashboard.php" <?= ($current_page=='my-dashboard.php') ? 'class="active"' : '' ?>>My Dashboard</a></li>
        <?php endif; ?>
        <li><a href="<?= $base_path ?>report_issue.php" <?= ($current_page=='report_issue.php') ? 'class="active"' : '' ?>>Report Issue</a></li>
        <li><a href="<?= $base_path ?>track-issue.php" <?= ($current_page=='track-issue.php') ? 'class="active"' : '' ?>>Track Issue</a></li>
        <li><a href="<?= $base_path ?>volunteer_register.php" <?= ($current_page=='volunteer_register.php') ? 'class="active"' : '' ?>>Volunteer Portal</a></li>
        <li><a href="<?= $base_path ?>campaigns.php" <?= ($current_page=='campaigns.php'||$current_page=='campaign_register.php') ? 'class="active"' : '' ?>>Campaigns</a></li>
        <li><a href="<?= $base_path ?>token_system.php" <?= ($current_page=='token_system.php') ? 'class="active"' : '' ?>>Token System</a></li>
        <li><a href="<?= $base_path ?>announcements.php" <?= ($current_page=='announcements.php') ? 'class="active"' : '' ?>>Notices</a></li>
        <li><a href="<?= $base_path ?>about.php" <?= ($current_page=='about.php') ? 'class="active"' : '' ?>>About</a></li>
        <li><a href="<?= $base_path ?>contact.php" <?= ($current_page=='contact.php') ? 'class="active"' : '' ?>>Contact</a></li>
      </ul>
    </div>
  </div>
</nav>
