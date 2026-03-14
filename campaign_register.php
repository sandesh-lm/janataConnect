<?php
require_once 'config/auth.php';
require_login('campaigns.php');
$__logged_user = current_user();
require_once 'config/db.php';
$conn = getDB();

$campaign_id = (int)($_GET['id'] ?? 0);
if (!$campaign_id) { header('Location: campaigns.php'); exit; }

// Fetch campaign
$res = $conn->query("SELECT c.*,
    (SELECT COUNT(*) FROM campaign_registrations cr WHERE cr.campaign_id = c.id) as reg_count
    FROM campaigns c WHERE c.id=$campaign_id AND c.is_active=1 LIMIT 1");

if (!$res || $res->num_rows === 0) { header('Location: campaigns.php'); exit; }
$campaign = $res->fetch_assoc();

$is_full = $campaign['max_volunteers'] > 0 && $campaign['reg_count'] >= $campaign['max_volunteers'];
$is_open = in_array($campaign['status'], ['Upcoming', 'Ongoing']);

$page_title = 'Register – ' . $campaign['title'];
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_open && !$is_full) {
    $name    = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $phone   = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $email   = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $ward    = $conn->real_escape_string($_POST['ward'] ?? '');
    $skills  = trim($conn->real_escape_string($_POST['skills'] ?? ''));
    $message = trim($conn->real_escape_string($_POST['message'] ?? ''));

    if (!$name || !$phone) {
        $error = 'Please fill in your name and phone number.';
    } else {
        // Check duplicate registration
        $dup = $conn->query("SELECT id FROM campaign_registrations WHERE campaign_id=$campaign_id AND phone='$phone' LIMIT 1");
        if ($dup && $dup->num_rows > 0) {
            $error = 'You have already registered for this campaign with this phone number.';
        } else {
            $uid_cr = (int)($_SESSION['user_id'] ?? 0);
            $sql = "INSERT INTO campaign_registrations (user_id, campaign_id, name, phone, email, ward, skills, message)
                    VALUES ($uid_cr, $campaign_id, '$name', '$phone', '$email', '$ward', '$skills', '$message')";
            if ($conn->query($sql)) {
                $success = true;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$conn->close();
include 'includes/header.php';

$status_colors = [
    'Upcoming' => '#0a4d8c',
    'Ongoing'  => '#1a7a4a',
    'Completed'=> '#5a6a7e',
    'Cancelled'=> '#c0392b',
];
$sc = $status_colors[$campaign['status']] ?? '#0a4d8c';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span>
      <a href="campaigns.php">Campaigns</a> <span>›</span>
      <span>Register</span>
    </div>
    <h1>🤝 Campaign Registration</h1>
    <p>Register your participation for this volunteer campaign.</p>
  </div>
</div>

<div class="form-container">
  <div class="container">

    <?php if ($success): ?>
    <div class="success-card">
      <div class="success-icon">🎉</div>
      <h2>Registration Successful!</h2>
      <p>You have been registered for:</p>
      <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;border-radius:12px;padding:22px 26px;margin:20px 0;text-align:left;">
        <div style="font-size:18px;font-weight:700;margin-bottom:10px;"><?= htmlspecialchars($campaign['title']) ?></div>
        <?php if ($campaign['campaign_date']): ?>
        <div style="font-size:13.5px;color:rgba(255,255,255,0.8);margin-bottom:5px;">📅 <?= date('l, F j, Y', strtotime($campaign['campaign_date'])) ?></div>
        <?php endif; ?>
        <?php if ($campaign['campaign_time']): ?>
        <div style="font-size:13.5px;color:rgba(255,255,255,0.8);margin-bottom:5px;">⏰ <?= htmlspecialchars($campaign['campaign_time']) ?></div>
        <?php endif; ?>
        <?php if ($campaign['location']): ?>
        <div style="font-size:13.5px;color:rgba(255,255,255,0.8);">📍 <?= htmlspecialchars($campaign['location']) ?></div>
        <?php endif; ?>
      </div>
      <div class="info-box">
        The campaign organizer will contact you if there are any updates or changes. Please arrive 10 minutes early on the day of the campaign.
      </div>
      <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap;">
        <a href="campaigns.php" class="btn btn-blue">View More Campaigns</a>
        <a href="index.php" class="btn btn-outline" style="border-color:#d0dce8;color:#1a2332;">Back to Home</a>
      </div>
    </div>

    <?php elseif (!$is_open): ?>
    <div class="success-card">
      <div class="success-icon">🔒</div>
      <h2>Registration Closed</h2>
      <p>This campaign is no longer accepting registrations.</p>
      <a href="campaigns.php" class="btn btn-blue" style="margin-top:20px;">View Other Campaigns</a>
    </div>

    <?php elseif ($is_full): ?>
    <div class="success-card">
      <div class="success-icon">🔴</div>
      <h2>Campaign Full</h2>
      <p>This campaign has reached its maximum volunteer capacity.</p>
      <a href="campaigns.php" class="btn btn-blue" style="margin-top:20px;">View Other Campaigns</a>
    </div>

    <?php else: ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start;">

      <!-- Registration Form -->
      <div class="form-card">
        <div class="form-card-header">
          <h2>🤝 Register for Campaign</h2>
          <p>Fill in your details to confirm your participation.</p>
        </div>
        <div class="form-body">

          <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>

            <div class="form-section-title">👤 Your Information</div>
            <div class="form-grid">
              <div class="form-group">
                <label>Full Name <span class="req">*</span></label>
                <input type="text" name="name" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['name'] ?? $__logged_user['full_name'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Phone Number <span class="req">*</span></label>
                <input type="tel" name="phone" placeholder="98XXXXXXXX" required value="<?= htmlspecialchars($_POST['phone'] ?? $__logged_user['phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? $__logged_user['email'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Ward Number</label>
                <select name="ward">
                  <option value="">Select Ward</option>
                  <?php for ($i = 1; $i <= 33; $i++): ?>
                  <option value="<?= $i ?>" <?= (($_POST['ward'] ?? '') == $i) ? 'selected' : '' ?>>Ward <?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="form-group full">
                <label>Relevant Skills / Experience</label>
                <input type="text" name="skills" placeholder="e.g. First Aid, Driving, Teaching..." value="<?= htmlspecialchars($_POST['skills'] ?? '') ?>">
              </div>
              <div class="form-group full">
                <label>Message (Optional)</label>
                <textarea name="message" rows="3" placeholder="Any questions or things you'd like us to know..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>
            </div>

          </div>
          <div class="form-footer">
            <span style="font-size:13px;color:#5a6a7e;">
              <?php
              $slots_left = $campaign['max_volunteers'] > 0 ? $campaign['max_volunteers'] - $campaign['reg_count'] : null;
              if ($slots_left !== null) echo "🟢 {$slots_left} slots remaining";
              ?>
            </span>
            <button type="submit" class="btn btn-primary">🤝 Confirm Registration</button>
          </div>
          </form>
        </div>

      <!-- Campaign Info Sidebar -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="background:white;border-radius:14px;box-shadow:var(--shadow);overflow:hidden;">
          <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));padding:22px 24px;color:white;">
            <div style="font-size:11.5px;color:rgba(255,255,255,0.65);margin-bottom:5px;text-transform:uppercase;letter-spacing:0.8px;">Campaign Details</div>
            <h3 style="font-size:17px;font-weight:700;line-height:1.35;"><?= htmlspecialchars($campaign['title']) ?></h3>
          </div>
          <div style="padding:20px 24px;display:flex;flex-direction:column;gap:13px;font-size:13.5px;">
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <span style="width:20px;flex-shrink:0;">📅</span>
              <div>
                <strong style="display:block;font-size:11.5px;color:#5a6a7e;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Date</strong>
                <?= $campaign['campaign_date'] ? date('l, F j, Y', strtotime($campaign['campaign_date'])) : 'TBA' ?>
              </div>
            </div>
            <?php if ($campaign['campaign_time']): ?>
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <span style="width:20px;flex-shrink:0;">⏰</span>
              <div>
                <strong style="display:block;font-size:11.5px;color:#5a6a7e;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Time</strong>
                <?= htmlspecialchars($campaign['campaign_time']) ?>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($campaign['location']): ?>
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <span style="width:20px;flex-shrink:0;">📍</span>
              <div>
                <strong style="display:block;font-size:11.5px;color:#5a6a7e;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Location</strong>
                <?= htmlspecialchars($campaign['location']) ?>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($campaign['organizer']): ?>
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <span style="width:20px;flex-shrink:0;">👤</span>
              <div>
                <strong style="display:block;font-size:11.5px;color:#5a6a7e;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Organizer</strong>
                <?= htmlspecialchars($campaign['organizer']) ?>
              </div>
            </div>
            <?php endif; ?>
            <div style="border-top:1px solid #d0dce8;padding-top:13px;display:flex;justify-content:space-between;">
              <span style="color:#5a6a7e;">Registered</span>
              <strong><?= $campaign['reg_count'] ?><?= $campaign['max_volunteers'] > 0 ? ' / '.$campaign['max_volunteers'] : '' ?></strong>
            </div>
          </div>
        </div>

        <div style="background:#f0f5ff;border-radius:12px;padding:18px;border:1.5px solid #b3cef0;font-size:13.5px;color:#1a2332;line-height:1.65;">
          ℹ️ <strong>What to bring:</strong> Comfortable clothes, water bottle, and enthusiasm! All tools and equipment will be provided by the municipality.
        </div>
      </div>

    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
