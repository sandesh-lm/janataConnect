<?php
$current_admin_page = basename($_SERVER['PHP_SELF']);
function aal($page, $current) {
    $pages = ['campaigns.php','campaign_form.php','campaign_registrations.php'];
    if ($page === 'campaigns.php' && in_array($current, $pages)) return 'active';
    $pages2 = ['announcements.php','announcement_form.php'];
    if ($page === 'announcements.php' && in_array($current, $pages2)) return 'active';
    if ($page === 'users.php' && $current === 'users.php') return 'active';
    return $current === $page ? 'active' : '';
}
?>
<aside class="admin-sidebar">
  <div class="sidebar-title">Admin Panel</div>
  <nav class="admin-nav">
    <a href="dashboard.php" class="<?= aal('dashboard.php', $current_admin_page) ?>">
      <span class="icon">📊</span> Dashboard
    </a>
    <a href="announcements.php" class="<?= aal('announcements.php', $current_admin_page) ?>">
      <span class="icon">📢</span> Announcements
    </a>
    <a href="campaigns.php" class="<?= aal('campaigns.php', $current_admin_page) ?>">
      <span class="icon">🌿</span> Campaigns
    </a>
    <a href="users.php" class="<?= aal('users.php', $current_admin_page) ?>">
      <span class="icon">👥</span> Citizens
    </a>
    <a href="issues.php" class="<?= aal('issues.php', $current_admin_page) ?>">
      <span class="icon">📋</span> Issues
    </a>
    <a href="volunteers.php" class="<?= aal('volunteers.php', $current_admin_page) ?>">
      <span class="icon">🤝</span> Volunteers
    </a>
    <a href="tokens.php" class="<?= aal('tokens.php', $current_admin_page) ?>">
      <span class="icon">🎫</span> Tokens
    </a>
    <div style="border-top:1px solid rgba(255,255,255,0.1);margin:10px 0;"></div>
    <a href="../index.php" target="_blank">
      <span class="icon">🌐</span> View Public Site
    </a>
    <a href="logout.php">
      <span class="icon">🚪</span> Logout
    </a>
  </nav>
</aside>
