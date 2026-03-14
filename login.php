<?php
require_once 'config/auth.php';
if (user_logged_in()) { header('Location: my-dashboard.php'); exit; }

$page_title = 'Login';
$error = '';
$redirect = htmlspecialchars($_GET['redirect'] ?? 'my-dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $conn = getDB();

    $email    = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND is_active=1 LIMIT 1");
    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Update last login
            $conn->query("UPDATE users SET last_login=NOW() WHERE id={$user['id']}");
            login_user($user);
            $conn->close();
            $go = $_POST['redirect'] ?? 'my-dashboard.php';
            // Safety: only allow simple filenames or relative paths, no external URLs
            if (!$go || strpos($go, '//') !== false || strpos($go, 'http') === 0) {
                $go = 'my-dashboard.php';
            }
            // Strip any leading slashes/path traversal
            $go = ltrim(basename($go) !== $go ? $go : $go, '/');
            header('Location: ' . $go);
            exit;
        }
    }
    $error = 'Invalid email or password.';
    $conn->close();
}

include 'includes/header.php';
?>

<div style="min-height:70vh;display:flex;align-items:center;padding:48px 0;">
  <div class="container">
    <div style="max-width:480px;margin:0 auto;">

      <div class="form-card">
        <div class="form-card-header" style="text-align:center;padding:32px 36px 24px;">
          <div style="font-size:44px;margin-bottom:10px;">🏛️</div>
          <h2 style="font-size:22px;">Welcome Back</h2>
          <p>Sign in to your JanataConnect account</p>
        </div>
        <div class="form-body">

          <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if (isset($_GET['redirect'])): ?>
          <?php
          $page_names = [
            'report_issue.php'       => ['📋','Report a Community Issue','Log in to submit issue reports and track their resolution status in real time.'],
            'volunteer_register.php' => ['🤝','Volunteer Registration','Log in to register as a volunteer and join municipal campaigns.'],
            'token_system.php'       => ['🎫','Book Office Token','Log in to book digital queue tokens for government offices.'],
            'campaign_register.php'  => ['🌿','Campaign Registration','Log in to register for this volunteer campaign.'],
          ];
          $rp = htmlspecialchars($_GET['redirect']);
          $base_rp = basename(strtok($rp, '?'));
          if (isset($page_names[$base_rp])):
            [$ico, $ptitle, $pdesc] = $page_names[$base_rp];
          ?>
          <div style="background:#f0f5ff;border:1.5px solid #b3cef0;border-radius:10px;padding:14px 16px;margin-bottom:16px;display:flex;gap:12px;align-items:flex-start;">
            <span style="font-size:24px;"><?= $ico ?></span>
            <div>
              <strong style="font-size:14px;color:#0a4d8c;display:block;margin-bottom:3px;"><?= $ptitle ?></strong>
              <span style="font-size:13px;color:#5a6a7e;"><?= $pdesc ?></span>
            </div>
          </div>
          <?php else: ?>
          <div class="alert alert-info">🔒 Please log in to access that page.</div>
          <?php endif; ?>
          <?php endif; ?>

          <?php if (isset($_GET['registered'])): ?>
          <div class="alert alert-success">✅ Account created! Please log in.</div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <input type="hidden" name="redirect" value="<?= $redirect ?>">

            <div class="form-group" style="margin-bottom:16px;">
              <label>Email Address <span class="req">*</span></label>
              <input type="email" name="email" placeholder="your@email.com" required autofocus
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom:8px;">
              <label>Password <span class="req">*</span></label>
              <div style="position:relative;">
                <input type="password" name="password" placeholder="Your password" required id="login-pw"
                       style="padding-right:44px;">
                <button type="button" onclick="togglePw()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:#5a6a7e;" title="Show/hide password">👁️</button>
              </div>
            </div>

          </div>
          <div class="form-footer" style="flex-direction:column;gap:14px;align-items:stretch;">
            <button type="submit" class="btn btn-primary btn-full" style="font-size:15px;padding:13px;">🔐 Login</button>
            <div style="text-align:center;font-size:14px;color:#5a6a7e;">
              Don't have an account? <a href="register.php" style="color:#0a4d8c;font-weight:600;">Create one free →</a>
            </div>
          </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function togglePw() {
  const pw = document.getElementById('login-pw');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>

<?php include 'includes/footer.php'; ?>
