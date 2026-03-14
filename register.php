<?php
require_once 'config/auth.php';
if (user_logged_in()) { header('Location: my-dashboard.php'); exit; }

$page_title = 'Create Account';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $conn = getDB();

    $full_name = trim($conn->real_escape_string($_POST['full_name'] ?? ''));
    $email     = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $phone     = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $ward      = $conn->real_escape_string($_POST['ward'] ?? '');
    $address   = trim($conn->real_escape_string($_POST['address'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (!$full_name || !$email || !$phone || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check email duplicate
        $dup = $conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1");
        if ($dup && $dup->num_rows > 0) {
            $error = 'An account with this email already exists. <a href="login.php" style="color:#0a4d8c;">Login instead →</a>';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql  = "INSERT INTO users (full_name, email, phone, ward, address, password)
                     VALUES ('$full_name','$email','$phone','$ward','$address','$hash')";
            if ($conn->query($sql)) {
                $new_id = $conn->insert_id;
                $user_row = $conn->query("SELECT * FROM users WHERE id=$new_id LIMIT 1")->fetch_assoc();
                login_user($user_row);
                header('Location: my-dashboard.php?welcome=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
    $conn->close();
}

include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Create Account</span>
    </div>
    <h1>👤 Create Your Account</h1>
    <p>Register to track your issues, manage tokens, and access personalised services.</p>
  </div>
</div>

<div class="form-container">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;max-width:900px;margin:0 auto;">

      <div class="form-card">
        <div class="form-card-header">
          <h2>👤 Citizen Registration</h2>
          <p>All information is kept confidential and used only for municipal services.</p>
        </div>
        <div class="form-body">

          <?php if ($error): ?>
          <div class="alert alert-error">⚠️ <?= $error ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>

            <div class="form-section-title">Personal Information</div>
            <div class="form-grid">
              <div class="form-group full">
                <label>Full Name <span class="req">*</span></label>
                <input type="text" name="full_name" placeholder="As per citizenship" required
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Email Address <span class="req">*</span></label>
                <input type="email" name="email" placeholder="your@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Phone Number <span class="req">*</span></label>
                <input type="tel" name="phone" placeholder="98XXXXXXXX" required
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Ward Number</label>
                <select name="ward">
                  <option value="">Select your ward</option>
                  <?php for ($i=1;$i<=33;$i++): ?>
                  <option value="<?= $i ?>" <?= (($_POST['ward']??'')==$i)?'selected':'' ?>>Ward <?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" placeholder="Your locality / tole"
                       value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
              </div>
            </div>

            <div class="form-section-title">Set Password</div>
            <div class="form-grid">
              <div class="form-group">
                <label>Password <span class="req">*</span></label>
                <input type="password" name="password" placeholder="Minimum 6 characters" required id="pw">
              </div>
              <div class="form-group">
                <label>Confirm Password <span class="req">*</span></label>
                <input type="password" name="confirm_password" placeholder="Re-enter password" required id="pw2">
              </div>
              <div class="form-group full" id="pw-match" style="display:none;margin-top:-8px;">
                <span style="font-size:13px;color:#c0392b;">⚠️ Passwords do not match</span>
              </div>
            </div>

            <div style="margin-top:18px;">
              <label class="checkbox-item">
                <input type="checkbox" required>
                <span style="font-size:13.5px;">I agree to the <a href="#" style="color:#0a4d8c;">Terms of Service</a> and <a href="#" style="color:#0a4d8c;">Privacy Policy</a></span>
              </label>
            </div>

          </div>
          <div class="form-footer">
            <span style="font-size:13px;color:#5a6a7e;">Already have an account? <a href="login.php" style="color:#0a4d8c;">Login →</a></span>
            <button type="submit" class="btn btn-primary">✅ Create Account</button>
          </div>
          </form>
        </div>

      <!-- Benefits sidebar -->
      <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:90px;">
        <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;border-radius:14px;padding:26px;">
          <h3 style="font-size:17px;font-weight:700;margin-bottom:16px;">✨ Why Register?</h3>
          <?php
          $benefits = [
            ['📋','Track all your reported issues and see real-time status updates'],
            ['🎫','View all your token bookings in one place'],
            ['🌿','Manage your campaign registrations'],
            ['🔔','Get updates when your issue status changes'],
            ['📊','Personal dashboard with your activity history'],
            ['⚡','Pre-filled forms — no need to re-enter your details'],
          ];
          foreach ($benefits as $b): ?>
          <div style="display:flex;gap:12px;margin-bottom:13px;align-items:flex-start;">
            <span style="font-size:20px;flex-shrink:0;"><?= $b[0] ?></span>
            <span style="font-size:13.5px;color:rgba(255,255,255,0.85);line-height:1.55;"><?= $b[1] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="background:#f0f5ff;border-radius:12px;padding:18px;border:1.5px solid #b3cef0;font-size:13.5px;color:#1a2332;line-height:1.65;">
          🔒 Your data is secure and only used for Pokhara Metropolitan City services.
        </div>
      </div>

    </div>
  </div>
</div>

<script>
document.getElementById('pw2').addEventListener('input', function() {
  const match = document.getElementById('pw-match');
  match.style.display = (this.value && this.value !== document.getElementById('pw').value) ? 'block' : 'none';
});
</script>

<?php include 'includes/footer.php'; ?>
