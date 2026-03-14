<?php
require_once 'config/auth.php';
require_login();
require_once 'config/db.php';

$user = current_user();
$uid  = (int)$user['id'];
$conn = getDB();

// Fetch user's data
$my_issues  = $conn->query("SELECT * FROM issues WHERE user_id=$uid ORDER BY created_at DESC");
$my_tokens  = $conn->query("SELECT * FROM tokens WHERE user_id=$uid ORDER BY date DESC LIMIT 10");
$my_campregs = $conn->query("SELECT cr.*, c.title as camp_title, c.campaign_date, c.location, c.status as camp_status
    FROM campaign_registrations cr
    JOIN campaigns c ON c.id = cr.campaign_id
    WHERE cr.user_id=$uid ORDER BY cr.created_at DESC");
$my_volunteer = $conn->query("SELECT * FROM volunteers WHERE user_id=$uid ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// Stats
$total_issues   = $my_issues->num_rows;
$pending_issues = $conn->query("SELECT COUNT(*) as c FROM issues WHERE user_id=$uid AND status='Pending'")->fetch_assoc()['c'];
$resolved       = $conn->query("SELECT COUNT(*) as c FROM issues WHERE user_id=$uid AND status='Resolved'")->fetch_assoc()['c'];
$total_tokens   = $my_tokens->num_rows;

$conn->close();

$page_title = 'My Dashboard';
include 'includes/header.php';

$status_styles = [
    'Pending'     => ['bg'=>'#fff3e0','color'=>'#e67e22','label'=>'⏳ Pending'],
    'In Progress' => ['bg'=>'#e8f0fb','color'=>'#0a4d8c','label'=>'🔧 In Progress'],
    'Resolved'    => ['bg'=>'#e6f6ed','color'=>'#1a7a4a','label'=>'✅ Resolved'],
];
?>

<div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;padding:36px 0;">
  <div class="container">
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
      <!-- Avatar -->
      <div style="width:64px;height:64px;border-radius:50%;background:<?= avatar_color($user['full_name']) ?>;
                  border:3px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;
                  font-size:24px;font-weight:700;color:white;flex-shrink:0;">
        <?= strtoupper(substr($user['full_name'],0,1)) ?>
      </div>
      <div>
        <div style="font-size:12px;color:rgba(255,255,255,0.65);margin-bottom:3px;text-transform:uppercase;letter-spacing:0.8px;">My Dashboard</div>
        <h1 style="font-size:24px;font-weight:700;margin-bottom:3px;">
          Welcome back, <?= htmlspecialchars(first_name($user['full_name'])) ?>! 👋
        </h1>
        <div style="font-size:13.5px;color:rgba(255,255,255,0.75);">
          <?= htmlspecialchars($user['email']) ?>
          <?php if ($user['ward']): ?> &nbsp;·&nbsp; Ward <?= htmlspecialchars($user['ward']) ?><?php endif; ?>
        </div>
      </div>
      <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="report_issue.php" class="btn btn-primary" style="font-size:13.5px;padding:9px 18px;">📋 Report Issue</a>
        <a href="token_system.php" class="btn btn-outline" style="font-size:13.5px;padding:9px 18px;">🎫 Book Token</a>
      </div>
    </div>
  </div>
</div>

<?php if (isset($_GET['welcome'])): ?>
<div style="background:#e6f6ed;border-bottom:2px solid #b3e6c8;padding:14px 0;text-align:center;font-size:14.5px;color:#1a7a4a;font-weight:600;">
  🎉 Welcome to JanataConnect! Your account has been created successfully.
</div>
<?php endif; ?>

<div style="padding:40px 0;background:#f4f7fb;min-height:60vh;">
  <div class="container">

    <!-- Stats row -->
    <div class="stats-row" style="margin-bottom:32px;">
      <div class="stat-box">
        <div class="icon blue">📋</div>
        <div><div class="val"><?= $total_issues ?></div><div class="lbl">Issues Reported</div></div>
      </div>
      <div class="stat-box">
        <div class="icon orange">⏳</div>
        <div><div class="val"><?= $pending_issues ?></div><div class="lbl">Pending</div></div>
      </div>
      <div class="stat-box">
        <div class="icon green">✅</div>
        <div><div class="val"><?= $resolved ?></div><div class="lbl">Resolved</div></div>
      </div>
      <div class="stat-box">
        <div class="icon blue">🎫</div>
        <div><div class="val"><?= $total_tokens ?></div><div class="lbl">Tokens Booked</div></div>
      </div>
    </div>

    <!-- Tab nav -->
    <div style="display:flex;gap:4px;border-bottom:2px solid #d0dce8;margin-bottom:24px;overflow-x:auto;">
      <?php
      $tabs = [
        'issues'    => '📋 My Issues ('.$total_issues.')',
        'tokens'    => '🎫 My Tokens',
        'campaigns' => '🌿 My Campaigns',
        'profile'   => '👤 My Profile',
      ];
      $active_tab = $_GET['tab'] ?? 'issues';
      foreach ($tabs as $key => $label): ?>
      <a href="my-dashboard.php?tab=<?= $key ?>"
         style="padding:12px 20px;text-decoration:none;font-size:14px;font-weight:600;white-space:nowrap;
                border-bottom:3px solid <?= $active_tab===$key ? 'var(--primary)' : 'transparent' ?>;
                margin-bottom:-2px;
                color:<?= $active_tab===$key ? 'var(--primary)' : '#5a6a7e' ?>;
                background:<?= $active_tab===$key ? 'white' : 'transparent' ?>;
                border-radius:8px 8px 0 0;transition:all 0.2s;">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- ── MY ISSUES TAB ─────────────────────────────────────── -->
    <?php if ($active_tab === 'issues'): ?>

    <?php if ($total_issues === 0): ?>
    <div style="background:white;border-radius:14px;padding:56px;text-align:center;border:1.5px solid #d0dce8;">
      <div style="font-size:52px;margin-bottom:16px;">📋</div>
      <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;color:#1a2332;">No issues reported yet</h3>
      <p style="color:#5a6a7e;margin-bottom:24px;">Spotted a problem in your area? Report it and track its resolution here.</p>
      <a href="report_issue.php" class="btn btn-primary">📋 Report Your First Issue</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <?php
      // Reset pointer
      $my_issues->data_seek(0);
      while ($iss = $my_issues->fetch_assoc()):
        $ss = $status_styles[$iss['status']] ?? $status_styles['Pending'];
      ?>
      <div style="background:white;border-radius:12px;border:1.5px solid #d0dce8;box-shadow:0 2px 8px rgba(10,77,140,0.06);overflow:hidden;">
        <div style="display:grid;grid-template-columns:1fr auto;gap:16px;padding:20px 24px;align-items:start;">
          <div>
            <div style="display:flex;gap:10px;margin-bottom:10px;align-items:center;flex-wrap:wrap;">
              <span style="background:<?= $ss['bg'] ?>;color:<?= $ss['color'] ?>;font-size:12px;font-weight:700;padding:4px 12px;border-radius:20px;">
                <?= $ss['label'] ?>
              </span>
              <span style="background:#f0f5ff;color:#0a4d8c;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;">
                <?= htmlspecialchars($iss['category']) ?>
              </span>
              <span style="font-size:12px;color:#8a9ab0;">ISS-<?= str_pad($iss['id'],4,'0',STR_PAD_LEFT) ?></span>
            </div>
            <h3 style="font-size:16px;font-weight:700;color:#1a2332;margin-bottom:6px;"><?= htmlspecialchars($iss['title']) ?></h3>
            <p style="font-size:13.5px;color:#5a6a7e;line-height:1.6;margin-bottom:10px;">
              <?= htmlspecialchars(substr($iss['description'],0,180)) ?><?= strlen($iss['description'])>180?'...':'' ?>
            </p>
            <div style="display:flex;gap:18px;font-size:12.5px;color:#8a9ab0;flex-wrap:wrap;">
              <span>📍 Ward <?= htmlspecialchars($iss['ward']) ?><?= $iss['location'] ? ' · '.htmlspecialchars(substr($iss['location'],0,40)) : '' ?></span>
              <span>📅 Reported <?= date('M j, Y', strtotime($iss['date'])) ?></span>
            </div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <?php if ($iss['photo']): ?>
            <a href="uploads/<?= htmlspecialchars($iss['photo']) ?>" target="_blank">
              <img src="uploads/<?= htmlspecialchars($iss['photo']) ?>"
                   style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1.5px solid #d0dce8;"
                   onerror="this.style.display='none'" alt="Issue photo">
            </a>
            <?php endif; ?>
          </div>
        </div>
        <!-- Status progress bar -->
        <div style="background:#f4f7fb;border-top:1px solid #d0dce8;padding:10px 24px;display:flex;align-items:center;gap:16px;">
          <?php
          $steps    = ['Pending','In Progress','Resolved'];
          $step_now = array_search($iss['status'], $steps);
          foreach ($steps as $i => $step):
            $done    = $i <= $step_now;
            $current = $i === $step_now;
          ?>
          <div style="display:flex;align-items:center;gap:6px;">
            <div style="width:20px;height:20px;border-radius:50%;background:<?= $done?($current?'var(--primary)':'var(--green)'):'#d0dce8' ?>;
                        display:flex;align-items:center;justify-content:center;font-size:10px;color:white;font-weight:700;flex-shrink:0;">
              <?= $done ? ($current ? ($step_now==2?'✓':'●') : '✓') : '' ?>
            </div>
            <span style="font-size:12px;font-weight:<?= $current?'700':'500' ?>;color:<?= $done?($current?'var(--primary)':'var(--green)'):'#b0bac5' ?>;">
              <?= $step ?>
            </span>
          </div>
          <?php if ($i < 2): ?>
          <div style="flex:1;height:2px;background:<?= $i<$step_now?'var(--green)':'#d0dce8' ?>;border-radius:2px;"></div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- ── MY TOKENS TAB ─────────────────────────────────────── -->
    <?php elseif ($active_tab === 'tokens'): ?>

    <?php if ($my_tokens->num_rows === 0): ?>
    <div style="background:white;border-radius:14px;padding:56px;text-align:center;border:1.5px solid #d0dce8;">
      <div style="font-size:52px;margin-bottom:16px;">🎫</div>
      <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">No tokens booked yet</h3>
      <p style="color:#5a6a7e;margin-bottom:24px;">Book a digital token to avoid long queues at government offices.</p>
      <a href="token_system.php" class="btn btn-blue">🎫 Book a Token</a>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
      <?php while ($tok = $my_tokens->fetch_assoc()):
        $tok_date    = strtotime($tok['date']);
        $is_upcoming = $tok_date >= strtotime('today');
      ?>
      <div style="background:white;border-radius:12px;border:1.5px solid <?= $is_upcoming?'var(--primary)':'#d0dce8' ?>;box-shadow:0 2px 8px rgba(10,77,140,0.07);overflow:hidden;">
        <div style="background:<?= $is_upcoming?'linear-gradient(135deg,var(--primary-dark),var(--primary))':'#f4f7fb' ?>;padding:18px 20px;text-align:center;">
          <div style="font-size:11px;color:<?= $is_upcoming?'rgba(255,255,255,0.65)':'#8a9ab0' ?>;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Token Number</div>
          <div style="font-size:36px;font-weight:700;color:<?= $is_upcoming?'#f5c842':'#1a2332' ?>;letter-spacing:2px;"><?= htmlspecialchars($tok['token_number']) ?></div>
        </div>
        <div style="padding:16px 20px;font-size:13.5px;display:flex;flex-direction:column;gap:8px;">
          <div><strong style="color:#5a6a7e;font-size:11.5px;display:block;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Office</strong><?= htmlspecialchars($tok['office']) ?></div>
          <div><strong style="color:#5a6a7e;font-size:11.5px;display:block;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Service</strong><?= htmlspecialchars($tok['service']) ?></div>
          <div style="display:flex;gap:14px;">
            <div><strong style="color:#5a6a7e;font-size:11.5px;display:block;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Date</strong><?= date('M j, Y', $tok_date) ?></div>
            <div><strong style="color:#5a6a7e;font-size:11.5px;display:block;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Slot</strong><?= strpos($tok['time_slot'],'Morning')!==false ? 'Morning' : 'Afternoon' ?></div>
          </div>
          <?php if ($is_upcoming): ?>
          <div style="background:#e8f0fb;border-radius:6px;padding:8px 12px;font-size:12.5px;color:#0a4d8c;margin-top:4px;">
            📅 Upcoming — please arrive on time
          </div>
          <?php else: ?>
          <div style="background:#f4f7fb;border-radius:6px;padding:8px 12px;font-size:12.5px;color:#8a9ab0;margin-top:4px;">
            Past appointment
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- ── MY CAMPAIGNS TAB ──────────────────────────────────── -->
    <?php elseif ($active_tab === 'campaigns'): ?>

    <?php if ($my_campregs->num_rows === 0): ?>
    <div style="background:white;border-radius:14px;padding:56px;text-align:center;border:1.5px solid #d0dce8;">
      <div style="font-size:52px;margin-bottom:16px;">🌿</div>
      <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">No campaign registrations yet</h3>
      <p style="color:#5a6a7e;margin-bottom:24px;">Join a volunteer campaign and contribute to your community.</p>
      <a href="campaigns.php" class="btn btn-blue">🌿 Browse Campaigns</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <?php while ($cr = $my_campregs->fetch_assoc()):
        $camp_past = strtotime($cr['campaign_date']) < strtotime('today');
      ?>
      <div style="background:white;border-radius:12px;border:1.5px solid #d0dce8;padding:20px 24px;box-shadow:0 2px 8px rgba(10,77,140,0.06);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
          <div>
            <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
              <span style="background:<?= $camp_past?'#f4f7fb':'#e8f0fb' ?>;color:<?= $camp_past?'#5a6a7e':'#0a4d8c' ?>;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">
                <?= $camp_past ? 'Completed' : htmlspecialchars($cr['camp_status']) ?>
              </span>
            </div>
            <h3 style="font-size:16px;font-weight:700;color:#1a2332;margin-bottom:8px;"><?= htmlspecialchars($cr['camp_title']) ?></h3>
            <div style="display:flex;gap:16px;font-size:13px;color:#5a6a7e;flex-wrap:wrap;">
              <?php if ($cr['campaign_date']): ?>
              <span>📅 <?= date('l, M j, Y', strtotime($cr['campaign_date'])) ?></span>
              <?php endif; ?>
              <?php if ($cr['location']): ?>
              <span>📍 <?= htmlspecialchars($cr['location']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div style="background:<?= $camp_past?'#f4f7fb':'#e6f6ed' ?>;border-radius:8px;padding:10px 16px;text-align:center;flex-shrink:0;">
            <div style="font-size:22px;"><?= $camp_past?'🏅':'⏳' ?></div>
            <div style="font-size:11.5px;color:#5a6a7e;margin-top:4px;font-weight:600;"><?= $camp_past?'Participated':'Registered' ?></div>
          </div>
        </div>
        <?php if ($cr['skills']): ?>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #eef2f7;font-size:13px;color:#5a6a7e;">
          <strong>Skills offered:</strong> <?= htmlspecialchars($cr['skills']) ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- ── MY PROFILE TAB ────────────────────────────────────── -->
    <?php elseif ($active_tab === 'profile'): ?>
    <?php
    require_once 'config/db.php';
    $pconn = getDB();
    $full_user = $pconn->query("SELECT * FROM users WHERE id=$uid LIMIT 1")->fetch_assoc();

    $profile_success = '';
    $profile_error   = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $fn   = trim($pconn->real_escape_string($_POST['full_name']??''));
        $ph   = trim($pconn->real_escape_string($_POST['phone']??''));
        $wd   = $pconn->real_escape_string($_POST['ward']??'');
        $addr = trim($pconn->real_escape_string($_POST['address']??''));
        if ($fn && $ph) {
            $pconn->query("UPDATE users SET full_name='$fn',phone='$ph',ward='$wd',address='$addr' WHERE id=$uid");
            $_SESSION['user_name']  = $fn;
            $_SESSION['user_phone'] = $ph;
            $_SESSION['user_ward']  = $wd;
            $full_user = $pconn->query("SELECT * FROM users WHERE id=$uid LIMIT 1")->fetch_assoc();
            $profile_success = 'Profile updated successfully!';
        } else { $profile_error = 'Name and phone are required.'; }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $cur  = $_POST['current_password'] ?? '';
        $newp = $_POST['new_password'] ?? '';
        $conf = $_POST['confirm_new'] ?? '';
        if (!password_verify($cur, $full_user['password'])) {
            $profile_error = 'Current password is incorrect.';
        } elseif (strlen($newp) < 6) {
            $profile_error = 'New password must be at least 6 characters.';
        } elseif ($newp !== $conf) {
            $profile_error = 'New passwords do not match.';
        } else {
            $hash = password_hash($newp, PASSWORD_BCRYPT);
            $pconn->query("UPDATE users SET password='$hash' WHERE id=$uid");
            $profile_success = 'Password changed successfully!';
        }
    }
    $pconn->close();
    ?>

    <?php if ($profile_success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($profile_success) ?></div><?php endif; ?>
    <?php if ($profile_error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($profile_error) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

      <!-- Update profile form -->
      <div style="background:white;border-radius:14px;border:1.5px solid #d0dce8;overflow:hidden;">
        <div style="background:linear-gradient(90deg,var(--primary),var(--primary-light));padding:18px 24px;color:white;">
          <h3 style="font-size:16px;font-weight:700;">👤 Edit Profile</h3>
        </div>
        <div style="padding:24px;">
          <form method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div style="display:flex;flex-direction:column;gap:14px;">
              <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required value="<?= htmlspecialchars($full_user['full_name']) ?>">
              </div>
              <div class="form-group">
                <label>Email <small style="color:#5a6a7e;">(cannot change)</small></label>
                <input type="email" value="<?= htmlspecialchars($full_user['email']) ?>" disabled style="background:#f4f7fb;color:#8a9ab0;">
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" required value="<?= htmlspecialchars($full_user['phone']) ?>">
              </div>
              <div class="form-group">
                <label>Ward</label>
                <select name="ward">
                  <option value="">Select ward</option>
                  <?php for ($i=1;$i<=33;$i++): ?>
                  <option value="<?= $i ?>" <?= $full_user['ward']==$i?'selected':'' ?>>Ward <?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Address / Locality</label>
                <input type="text" name="address" value="<?= htmlspecialchars($full_user['address']??'') ?>">
              </div>
              <button type="submit" class="btn btn-blue btn-full">💾 Save Changes</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Change password -->
      <div style="background:white;border-radius:14px;border:1.5px solid #d0dce8;overflow:hidden;">
        <div style="background:linear-gradient(90deg,#5a6a7e,#8a9ab0);padding:18px 24px;color:white;">
          <h3 style="font-size:16px;font-weight:700;">🔐 Change Password</h3>
        </div>
        <div style="padding:24px;">
          <form method="POST">
            <input type="hidden" name="change_password" value="1">
            <div style="display:flex;flex-direction:column;gap:14px;">
              <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required placeholder="Your current password">
              </div>
              <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="Minimum 6 characters">
              </div>
              <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_new" required placeholder="Re-enter new password">
              </div>
              <button type="submit" class="btn btn-full" style="background:#5a6a7e;color:white;">🔐 Update Password</button>
            </div>
          </form>
        </div>
        <!-- Account info -->
        <div style="padding:0 24px 24px;">
          <div style="background:#f4f7fb;border-radius:8px;padding:14px;font-size:13px;color:#5a6a7e;margin-top:4px;">
            <div style="margin-bottom:5px;">📅 Member since <?= date('F j, Y', strtotime($full_user['created_at'])) ?></div>
            <?php if ($full_user['last_login']): ?>
            <div>🕐 Last login <?= date('M j, Y \a\t g:i A', strtotime($full_user['last_login'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
