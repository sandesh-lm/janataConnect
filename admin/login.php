<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/db.php';
    $conn = getDB();

    $username = trim($conn->real_escape_string($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM admin_users WHERE username = '$username' LIMIT 1";
    $res = $conn->query($sql);

    if ($res && $res->num_rows === 1) {
        $admin = $res->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid username or password.';
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – JanataConnect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:linear-gradient(135deg,#073a6b 0%,#0a4d8c 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;">
  <div style="background:white;border-radius:18px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
    <div style="background:linear-gradient(135deg,#073a6b,#0a4d8c);padding:36px 36px 28px;text-align:center;color:white;">
      <div style="font-size:48px;margin-bottom:12px;">🏛️</div>
      <h1 style="font-size:22px;font-weight:700;margin-bottom:4px;">JanataConnect</h1>
      <p style="font-size:13px;color:rgba(255,255,255,0.7);">Admin Portal – Pokhara Metropolitan City</p>
    </div>
    <div style="padding:32px 36px 36px;">
      <h2 style="font-size:18px;font-weight:700;margin-bottom:22px;color:#1a2332;">Admin Login</h2>

      <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if (isset($_GET['logged_out'])): ?>
      <div class="alert alert-info" style="background:#e8f0fb;color:#0a4d8c;border:1px solid #b3cef0;">✅ You have been logged out successfully.</div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group" style="margin-bottom:16px;">
          <label>Username</label>
          <input type="text" name="username" placeholder="Enter admin username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:24px;">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-blue btn-full" style="font-size:15px;padding:13px;">🔐 Login to Admin Panel</button>
      </form>

      <div style="margin-top:20px;text-align:center;">
        <a href="../index.php" style="color:#5a6a7e;font-size:13.5px;text-decoration:none;">← Back to JanataConnect</a>
      </div>

      <!-- <div style="margin-top:20px;padding:12px;background:#f4f7fb;border-radius:8px;font-size:12.5px;color:#5a6a7e;text-align:center;">
        Demo credentials: <strong>admin</strong> / <strong>admin123</strong>
      </div> -->
    </div>
  </div>
</body>
</html>
