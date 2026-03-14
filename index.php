<?php
$page_title = 'Home';
require_once 'config/auth.php';
$__home_user = user_logged_in() ? current_user() : null;
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-badge">
          <span>🇳🇵</span> Official Portal – Pokhara Metropolitan City
        </div>
        <h1>JanataConnect – Connecting Citizens with <span>Municipal Services</span></h1>
        <p>Report problems in your area, volunteer for community campaigns, and book government office appointments online — fast, easy, and paperless.</p>
        <div class="hero-actions">
          <?php if ($__home_user): ?>
            <a href="my-dashboard.php" class="btn btn-primary">📊 My Dashboard</a>
            <a href="report_issue.php" class="btn btn-outline">📋 Report an Issue</a>
          <?php else: ?>
            <a href="register.php" class="btn btn-primary">✅ Create Free Account</a>
            <a href="login.php" class="btn btn-outline">🔐 Login</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="hero-stats">
        <div class="stat-card">
          <div class="num">2,840+</div>
          <div class="label">Issues Reported</div>
        </div>
        <div class="stat-card">
          <div class="num">1,205+</div>
          <div class="label">Volunteers</div>
        </div>
        <div class="stat-card">
          <div class="num">8,600+</div>
          <div class="label">Tokens Issued</div>
        </div>
        <div class="stat-card">
          <div class="num">35</div>
          <div class="label">Wards Covered</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Three Portal Cards -->
<section class="section section-alt" id="portals">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Our Services</div>
      <h2>Citizen Services at Your Fingertips</h2>
      <p>Access all three municipal service portals directly — no office visit required for initial submissions.</p>
    </div>
    <div class="cards-grid">

      <div class="portal-card">
        <div class="card-icon">📋</div>
        <h3>Report Community Issues</h3>
        <p>Spotted a broken road, overflowing drain, or non-functional street light? Report it directly to the municipality with photos and location details.</p>
        <?php if ($__home_user): ?>
        <a href="report_issue.php" class="btn btn-blue">Report an Issue →</a>
        <?php else: ?>
        <a href="login.php?redirect=report_issue.php" class="btn btn-blue">🔐 Login to Report →</a>
        <?php endif; ?>
      </div>

      <div class="portal-card">
        <div class="card-icon">🤝</div>
        <h3>Volunteer Registration</h3>
        <p>Join community-driven campaigns like tree plantation, road cleaning, health camps, and disaster relief. Make a real difference in your neighbourhood.</p>
        <?php if ($__home_user): ?>
        <a href="volunteer_register.php" class="btn btn-blue">Register as Volunteer →</a>
        <?php else: ?>
        <a href="login.php?redirect=volunteer_register.php" class="btn btn-blue">🔐 Login to Register →</a>
        <?php endif; ?>
      </div>

      <div class="portal-card">
        <div class="card-icon">🎫</div>
        <h3>Government Office Token</h3>
        <p>Book your digital queue token for government offices online. Skip the line, choose your time slot, and visit only when your number is called.</p>
        <?php if ($__home_user): ?>
        <a href="token_system.php" class="btn btn-blue">Book a Token →</a>
        <?php else: ?>
        <a href="login.php?redirect=token_system.php" class="btn btn-blue">🔐 Login to Book →</a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<!-- How It Works -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">How It Works</div>
      <h2>Simple Steps to Get Help</h2>
      <p>Our streamlined process ensures your concerns reach the right team quickly.</p>
    </div>
    <div class="steps-grid">
      <div class="step-item">
        <div class="step-num">1</div>
        <h4>Submit Your Request</h4>
        <p>Fill out the simple online form with your details and the issue or service needed.</p>
      </div>
      <div class="step-item">
        <div class="step-num">2</div>
        <h4>Get Confirmation</h4>
        <p>Receive an instant acknowledgement with your submission reference number.</p>
      </div>
      <div class="step-item">
        <div class="step-num">3</div>
        <h4>Team Review</h4>
        <p>The responsible municipal department reviews and assigns your request to field officers.</p>
      </div>
      <div class="step-item">
        <div class="step-num">4</div>
        <h4>Resolution Update</h4>
        <p>Get notified when your issue is resolved or your appointment is confirmed.</p>
      </div>
    </div>
  </div>
</section>

<!-- Announcements - live from DB -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Latest Notices</div>
      <h2>Municipal Announcements</h2>
      <p>Official notices and updates from Pokhara Metropolitan City.</p>
    </div>
    <?php
    require_once 'config/db.php';
    $aconn = getDB();
    $notices_res = $aconn->query("SELECT * FROM announcements WHERE is_active=1 ORDER BY created_at DESC LIMIT 4");
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <?php while ($n = $notices_res->fetch_assoc()): ?>
      <div style="background:white;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(10,77,140,0.08);border:1.5px solid #d0dce8;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <span style="background:<?= htmlspecialchars($n['tag_color']) ?>22;color:<?= htmlspecialchars($n['tag_color']) ?>;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;">
            <?= htmlspecialchars($n['tag']) ?>
          </span>
          <span style="font-size:12px;color:#5a6a7e;"><?= date('M j, Y', strtotime($n['created_at'])) ?></span>
        </div>
        <h4 style="font-size:15.5px;font-weight:700;margin-bottom:8px;"><?= htmlspecialchars($n['title']) ?></h4>
        <p style="font-size:13.5px;color:#5a6a7e;line-height:1.65;"><?= htmlspecialchars(substr($n['body'],0,160)) ?><?= strlen($n['body'])>160 ? '...' : '' ?></p>
      </div>
      <?php endwhile; $aconn->close(); ?>
    </div>
    <div style="text-align:center;margin-top:24px;">
      <a href="announcements.php" class="btn btn-blue">View All Announcements &#8594;</a>
    </div>
  </div>
</section>

<!-- Upcoming Campaigns teaser - live from DB -->
<?php
$cconn = getDB();
$camp_res = $cconn->query("SELECT c.*, (SELECT COUNT(*) FROM campaign_registrations cr WHERE cr.campaign_id=c.id) as reg_count FROM campaigns c WHERE c.status IN ('Upcoming','Ongoing') AND c.is_active=1 ORDER BY c.campaign_date ASC LIMIT 3");
$cat_icons2 = ['Environment'=>'🌿','Sanitation'=>'🧹','Health'=>'🏥','Disaster Relief'=>'🆘','Education'=>'📚','Infrastructure'=>'🏗️','Social Work'=>'🤝'];
if ($camp_res && $camp_res->num_rows > 0): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Get Involved</div>
      <h2>Upcoming Volunteer Campaigns</h2>
      <p>Join municipality-organised campaigns and make a real difference in your community.</p>
    </div>
    <div class="cards-grid">
      <?php while ($c = $camp_res->fetch_assoc()):
        $icon = $cat_icons2[$c['category']] ?? '📌';
        $slots = $c['max_volunteers']>0 ? $c['max_volunteers'] - $c['reg_count'] : null;
        $full  = $slots !== null && $slots <= 0;
      ?>
      <div style="background:white;border-radius:16px;padding:28px;border:1.5px solid #d0dce8;box-shadow:0 2px 12px rgba(10,77,140,0.07);display:flex;flex-direction:column;">
        <div style="font-size:36px;margin-bottom:14px;"><?= $icon ?></div>
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
          <span style="background:#e8f0fb;color:#0a4d8c;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;"><?= htmlspecialchars($c['category']) ?></span>
          <?php if ($slots !== null): ?>
          <span style="background:<?= $full?'#fde8e6':'#e6f6ed' ?>;color:<?= $full?'#c0392b':'#1a7a4a' ?>;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;">
            <?= $full ? 'Full' : $slots.' slots left' ?>
          </span>
          <?php endif; ?>
        </div>
        <h3 style="font-size:16.5px;font-weight:700;margin-bottom:8px;color:#1a2332;"><?= htmlspecialchars($c['title']) ?></h3>
        <p style="font-size:13.5px;color:#5a6a7e;line-height:1.65;flex:1;margin-bottom:14px;"><?= htmlspecialchars(substr($c['description'],0,120)) ?>...</p>
        <div style="font-size:12.5px;color:#5a6a7e;margin-bottom:16px;">
          <?php if ($c['campaign_date']): ?><div>📅 <?= date('M j, Y', strtotime($c['campaign_date'])) ?></div><?php endif; ?>
          <?php if ($c['location']): ?><div style="margin-top:4px;">📍 <?= htmlspecialchars(substr($c['location'],0,45)) ?></div><?php endif; ?>
        </div>
        <?php if (!$full): ?>
        <a href="campaign_register.php?id=<?= $c['id'] ?>" class="btn btn-blue" style="justify-content:center;">🤝 Register Now</a>
        <?php else: ?>
        <span class="btn" style="background:#f4f7fb;color:#5a6a7e;border:1.5px solid #d0dce8;justify-content:center;cursor:not-allowed;">Registration Full</span>
        <?php endif; ?>
      </div>
      <?php endwhile; $cconn->close(); ?>
    </div>
    <div style="text-align:center;margin-top:28px;">
      <a href="campaigns.php" class="btn btn-outline" style="border-color:#0a4d8c;color:#0a4d8c;background:white;">View All Campaigns &#8594;</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<section class="section" style="background:linear-gradient(135deg,#073a6b,#0a4d8c);color:white;text-align:center;">
  <div class="container">
    <h2 style="font-size:28px;font-weight:700;margin-bottom:12px;">Be the Change in Your Community</h2>
    <p style="color:rgba(255,255,255,0.78);font-size:15.5px;max-width:520px;margin:0 auto 28px;">Every issue reported helps us build a better Pokhara. Join thousands of active citizens making a difference.</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="report_issue.php" class="btn btn-primary">📋 Report an Issue</a>
      <a href="campaigns.php" class="btn btn-outline">🌿 View Campaigns</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
