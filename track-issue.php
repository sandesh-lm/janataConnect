<?php
require_once 'config/auth.php';
require_once 'config/db.php';

$page_title = 'Issue Status Tracker';
$conn = getDB();
$issue = null;
$error = '';

// If user is logged in, show their issues list by default
$user = user_logged_in() ? current_user() : null;

// Check for specific issue lookup
$lookup_id  = trim($_GET['id'] ?? '');
$lookup_phone = trim($_GET['phone'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($lookup_id && $lookup_phone)) {
    $lid   = $lookup_id   ?: trim($_POST['issue_id'] ?? '');
    $lphone = $lookup_phone ?: trim($_POST['phone'] ?? '');

    // Strip ISS- prefix if present
    $lid = preg_replace('/^ISS-0*/i', '', $lid);
    $lid = (int)$lid;

    if (!$lid || !$lphone) {
        $error = 'Please enter both your Issue ID and phone number.';
    } else {
        $esc_phone = $conn->real_escape_string($lphone);
        $res = $conn->query("SELECT * FROM issues WHERE id=$lid AND phone='$esc_phone' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $issue = $res->fetch_assoc();
        } else {
            $error = 'No issue found matching that ID and phone number. Please double-check your details.';
        }
    }
}

// For logged-in users, also fetch their recent issues
$user_issues = [];
if ($user) {
    $uid = (int)$user['id'];
    $ui_res = $conn->query("SELECT * FROM issues WHERE user_id=$uid ORDER BY created_at DESC LIMIT 20");
    while ($r = $ui_res->fetch_assoc()) $user_issues[] = $r;
}

$conn->close();
include 'includes/header.php';

$status_styles = [
    'Pending'     => ['bg'=>'#fff3e0','color'=>'#e67e22','icon'=>'⏳','step'=>0],
    'In Progress' => ['bg'=>'#e8f0fb','color'=>'#0a4d8c','icon'=>'🔧','step'=>1],
    'Resolved'    => ['bg'=>'#e6f6ed','color'=>'#1a7a4a','icon'=>'✅','step'=>2],
];

function renderIssueCard($iss, $status_styles) {
    $ss    = $status_styles[$iss['status']] ?? $status_styles['Pending'];
    $steps = ['Pending','In Progress','Resolved'];
    $step  = $ss['step'];
    ob_start();
    ?>
    <div style="background:white;border-radius:14px;border:1.5px solid #d0dce8;box-shadow:0 2px 12px rgba(10,77,140,0.07);overflow:hidden;margin-bottom:16px;">
      <div style="padding:22px 26px 18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
          <div>
            <div style="display:flex;gap:10px;margin-bottom:10px;align-items:center;flex-wrap:wrap;">
              <span style="background:<?= $ss['bg'] ?>;color:<?= $ss['color'] ?>;font-size:12.5px;font-weight:700;padding:4px 14px;border-radius:20px;">
                <?= $ss['icon'] . ' ' . htmlspecialchars($iss['status']) ?>
              </span>
              <span style="background:#f0f5ff;color:#0a4d8c;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;">
                <?= htmlspecialchars($iss['category']) ?>
              </span>
              <span style="font-size:12px;color:#8a9ab0;font-family:monospace;">ISS-<?= str_pad($iss['id'],4,'0',STR_PAD_LEFT) ?></span>
            </div>
            <h3 style="font-size:17px;font-weight:700;color:#1a2332;margin-bottom:8px;"><?= htmlspecialchars($iss['title']) ?></h3>
            <p style="font-size:13.5px;color:#5a6a7e;line-height:1.7;margin-bottom:10px;"><?= nl2br(htmlspecialchars($iss['description'])) ?></p>
            <div style="display:flex;gap:20px;font-size:12.5px;color:#8a9ab0;flex-wrap:wrap;">
              <span>📍 Ward <?= htmlspecialchars($iss['ward']) ?><?= $iss['location'] ? ' · '.htmlspecialchars(substr($iss['location'],0,40)) : '' ?></span>
              <span>📅 Reported <?= date('F j, Y', strtotime($iss['date'])) ?></span>
            </div>
          </div>
          <?php if ($iss['photo']): ?>
          <a href="uploads/<?= htmlspecialchars($iss['photo']) ?>" target="_blank" style="flex-shrink:0;">
            <img src="uploads/<?= htmlspecialchars($iss['photo']) ?>"
                 style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:1.5px solid #d0dce8;"
                 onerror="this.style.display='none'" alt="Issue photo">
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Progress tracker -->
      <div style="background:#f8fafd;border-top:1px solid #e8eef5;padding:16px 26px;">
        <div style="font-size:11.5px;color:#8a9ab0;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;font-weight:600;">Resolution Progress</div>
        <div style="display:flex;align-items:center;gap:0;">
          <?php foreach ($steps as $i => $s):
            $done    = $i <= $step;
            $current = $i === $step;
            $colors  = ['#e67e22','#0a4d8c','#1a7a4a'];
            $col     = $done ? $colors[min($step,2)] : '#d0dce8';
          ?>
          <div style="display:flex;align-items:center;flex:<?= $i<2?'1':'0' ?>;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
              <div style="width:32px;height:32px;border-radius:50%;background:<?= $done?$col:'#eef2f7' ?>;
                          border:2px solid <?= $done?$col:'#d0dce8' ?>;
                          display:flex;align-items:center;justify-content:center;
                          font-size:13px;color:white;font-weight:700;flex-shrink:0;
                          box-shadow:<?= $current?'0 0 0 4px '.$col.'22':'' ?>;">
                <?php if ($done && !$current): ?>✓<?php elseif ($current): ?>●<?php else: ?><?= $i+1 ?><?php endif; ?>
              </div>
              <span style="font-size:11.5px;font-weight:<?= $current?'700':'500' ?>;
                           color:<?= $done?$col:'#b0bac5' ?>;white-space:nowrap;">
                <?= $s ?>
              </span>
            </div>
            <?php if ($i < 2): ?>
            <div style="flex:1;height:3px;background:<?= $i<$step?$col:'#e8eef5' ?>;
                        margin:0 4px;margin-bottom:22px;border-radius:3px;"></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($iss['status'] === 'Resolved'): ?>
        <div style="background:#e6f6ed;border-radius:8px;padding:10px 14px;margin-top:14px;font-size:13.5px;color:#1a7a4a;font-weight:600;">
          ✅ This issue has been resolved by the Pokhara Metropolitan City team. Thank you for reporting!
        </div>
        <?php elseif ($iss['status'] === 'In Progress'): ?>
        <div style="background:#e8f0fb;border-radius:8px;padding:10px 14px;margin-top:14px;font-size:13.5px;color:#0a4d8c;">
          🔧 A municipal officer has been assigned to address this issue. Work is in progress.
        </div>
        <?php else: ?>
        <div style="background:#fff3e0;border-radius:8px;padding:10px 14px;margin-top:14px;font-size:13.5px;color:#e67e22;">
          ⏳ Your issue has been received and is awaiting review by the relevant department.
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Issue Tracker</span>
    </div>
    <h1>🔍 Issue Status Tracker</h1>
    <p>Track the real-time status of your reported community issues.</p>
  </div>
</div>

<div style="padding:44px 0;background:#f4f7fb;min-height:60vh;">
  <div class="container">

    <?php if ($user && count($user_issues) > 0): ?>
    <!-- Logged-in: show their issues automatically -->
    <div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
          <h2 style="font-size:20px;font-weight:700;">Your Reported Issues (<?= count($user_issues) ?>)</h2>
          <a href="report_issue.php" class="btn btn-primary" style="font-size:13.5px;padding:9px 18px;">+ Report New Issue</a>
        </div>
        <?php foreach ($user_issues as $iss): ?>
          <?= renderIssueCard($iss, $status_styles) ?>
        <?php endforeach; ?>
      </div>
      <div style="position:sticky;top:90px;">
        <?php include 'includes/_tracker_sidebar.php'; ?>
      </div>
    </div>

    <?php else: ?>
    <!-- Guest: lookup form -->
    <div style="max-width:720px;margin:0 auto;">

      <?php if ($issue): ?>
      <!-- Found issue -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="font-size:20px;font-weight:700;">Issue Found</h2>
        <a href="track-issue.php" style="font-size:13.5px;color:#5a6a7e;text-decoration:none;">← Search another</a>
      </div>
      <?= renderIssueCard($issue, $status_styles) ?>

      <?php else: ?>
      <!-- Lookup form -->
      <div class="form-card" style="max-width:560px;margin:0 auto 32px;">
        <div class="form-card-header">
          <h2>🔍 Look Up Your Issue</h2>
          <p>Enter your Issue Reference ID and the phone number you used when reporting.</p>
        </div>
        <div class="form-body">
          <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="form-grid">
              <div class="form-group">
                <label>Issue Reference ID <span class="req">*</span></label>
                <input type="text" name="issue_id" placeholder="e.g. ISS-0042 or just 42" required
                       value="<?= htmlspecialchars($_POST['issue_id'] ?? $lookup_id) ?>">
              </div>
              <div class="form-group">
                <label>Phone Number Used <span class="req">*</span></label>
                <input type="tel" name="phone" placeholder="98XXXXXXXX" required
                       value="<?= htmlspecialchars($_POST['phone'] ?? $lookup_phone) ?>">
              </div>
            </div>
          </div>
          <div class="form-footer">
            <span style="font-size:13px;color:#5a6a7e;">Your reference ID was shown after submission.</span>
            <button type="submit" class="btn btn-primary">🔍 Track Issue</button>
          </div>
          </form>
        </div>
      </div>

      <!-- Login prompt -->
      <div class="login-gate">
        <h3>Track All Your Issues at Once</h3>
        <p>Create a free account to automatically track all issues you report — no manual lookup needed. See real-time status updates from your personal dashboard.</p>
        <div class="gate-actions">
          <a href="register.php" class="btn btn-primary">✅ Create Free Account</a>
          <a href="login.php?redirect=track-issue.php" class="btn btn-outline">🔐 Login</a>
        </div>
      </div>
      <?php endif; ?>

    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
