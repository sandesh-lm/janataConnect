<?php
require_once 'config/auth.php';
require_login('report_issue.php');

$page_title = 'Report Community Issue';
$success = false;
$error = '';
$issue_id = '';
$__logged_user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $conn = getDB();

    $name     = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $phone    = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $email    = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $ward     = trim($conn->real_escape_string($_POST['ward'] ?? ''));
    $category = trim($conn->real_escape_string($_POST['category'] ?? ''));
    $title    = trim($conn->real_escape_string($_POST['title'] ?? ''));
    $description = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $location = trim($conn->real_escape_string($_POST['location'] ?? ''));
    $date     = date('Y-m-d');

    // Validate required
    if (!$name || !$phone || !$ward || !$category || !$title || !$description) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle photo upload
        $photo = '';
        if (!empty($_FILES['photo']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['photo']['size'] < 5000000) {
                $photo = 'issue_' . time() . '_' . rand(100,999) . '.' . $ext;
                $upload_path = UPLOAD_DIR . $photo;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo = ''; // silently fail upload
                }
            } else {
                $error = 'Invalid file type or file too large (max 5MB). Allowed: JPG, PNG, GIF, WEBP.';
            }
        }

        if (!$error) {
            $user_id_val = (int)($_SESSION['user_id'] ?? 0);
            $sql = "INSERT INTO issues (user_id, name, phone, email, ward, category, title, description, location, photo, date, status)
                    VALUES ($user_id_val, '$name', '$phone', '$email', '$ward', '$category', '$title', '$description', '$location', '$photo', '$date', 'Pending')";
            if ($conn->query($sql)) {
                $issue_id = 'ISS-' . str_pad($conn->insert_id, 4, '0', STR_PAD_LEFT);
                $success = true;
            } else {
                $error = 'Database error. Please try again.';
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
      <a href="index.php">Home</a>
      <span>›</span>
      <span>Report Issue</span>
    </div>
    <h1>📋 Report a Community Issue</h1>
    <p>Help us improve your ward by reporting problems that need municipal attention.</p>
  </div>
</div>

<div class="form-container">
  <div class="container">

    <?php if ($success): ?>
    <div class="success-card">
      <div class="success-icon">✅</div>
      <h2>Issue Reported Successfully!</h2>
      <p>Your issue has been submitted to the Pokhara Metropolitan City office.</p>
      <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:white;border-radius:12px;padding:20px 26px;margin:20px 0;text-align:center;">
        <div style="font-size:11px;color:rgba(255,255,255,0.65);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Your Reference ID</div>
        <div style="font-size:36px;font-weight:700;letter-spacing:3px;color:#f5c842;"><?= htmlspecialchars($issue_id) ?></div>
        <div style="font-size:13px;color:rgba(255,255,255,0.7);margin-top:6px;">Save this ID to track your issue status</div>
      </div>
      <div class="info-box">
        <strong>Status:</strong> <span style="color:#e67e22;font-weight:600;">⏳ Pending Review</span><br>
        <strong>Date Submitted:</strong> <?= date('F j, Y') ?><br>
        <strong>Expected Review:</strong> Within 2–3 working days
      </div>
      <p style="font-size:13px;margin-top:10px;color:#5a6a7e;">A municipal officer will review your report and assign a field team. You can track the status anytime using your Reference ID.</p>
      <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap;">
        <a href="track-issue.php" class="btn btn-primary">🔍 Track This Issue</a>
        <a href="report_issue.php" class="btn btn-blue">Report Another Issue</a>
        <?php if (user_logged_in()): ?>
        <a href="my-dashboard.php?tab=issues" class="btn btn-outline" style="border-color:#d0dce8;color:#1a2332;">📊 My Dashboard</a>
        <?php endif; ?>
      </div>
    </div>

    <?php else: ?>
    <div class="form-card">
      <div class="form-card-header">
        <h2>📋 Community Issue Report Form</h2>
        <p>All starred (*) fields are required. Your report helps the municipality prioritize repairs.</p>
      </div>
      <div class="form-body">

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>

          <div class="form-section-title">👤 Reporter Information</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name <span class="req">*</span></label>
              <input type="text" name="name" placeholder="Ramesh Bahadur Thapa" required value="<?= htmlspecialchars($_POST['name'] ?? $__logged_user['full_name'] ?? '') ?>">
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
              <label>Ward Number <span class="req">*</span></label>
              <select name="ward" required>
                <option value="">Select Ward</option>
                <?php for ($i = 1; $i <= 33; $i++): ?>
                <option value="<?= $i ?>" <?= (($_POST['ward'] ?? '') == $i) ? 'selected' : '' ?>>Ward <?= $i ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <div class="form-section-title">🔍 Issue Details</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Issue Category <span class="req">*</span></label>
              <select name="category" required>
                <option value="">Select Category</option>
                <option value="Road Problem" <?= (($_POST['category'] ?? '') == 'Road Problem') ? 'selected' : '' ?>>🛣️ Road Problem</option>
                <option value="Waste Management" <?= (($_POST['category'] ?? '') == 'Waste Management') ? 'selected' : '' ?>>🗑️ Waste Management</option>
                <option value="Street Light" <?= (($_POST['category'] ?? '') == 'Street Light') ? 'selected' : '' ?>>💡 Street Light</option>
                <option value="Water Supply" <?= (($_POST['category'] ?? '') == 'Water Supply') ? 'selected' : '' ?>>🚰 Water Supply</option>
                <option value="Drainage" <?= (($_POST['category'] ?? '') == 'Drainage') ? 'selected' : '' ?>>🌊 Drainage</option>
                <option value="Public Property" <?= (($_POST['category'] ?? '') == 'Public Property') ? 'selected' : '' ?>>🏗️ Public Property</option>
                <option value="Noise Pollution" <?= (($_POST['category'] ?? '') == 'Noise Pollution') ? 'selected' : '' ?>>🔊 Noise Pollution</option>
                <option value="Other" <?= (($_POST['category'] ?? '') == 'Other') ? 'selected' : '' ?>>📌 Other</option>
              </select>
            </div>
            <div class="form-group">
              <label>Issue Title <span class="req">*</span></label>
              <input type="text" name="title" placeholder="Brief title of the problem" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Issue Description <span class="req">*</span></label>
              <textarea name="description" rows="5" placeholder="Describe the issue in detail — how long it has existed, impact on community, etc." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
              <label>📍 Location / Address</label>
              <input type="text" name="location" placeholder="e.g. Near New Road, Ward 5, Pokhara" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
            </div>
          </div>

          <div class="form-section-title">📸 Photo Evidence</div>
          <div class="form-group full">
            <label>Upload Photo of Issue</label>
            <div class="file-upload-area" onclick="document.getElementById('photo').click()">
              <input type="file" name="photo" id="photo" accept="image/*">
              <div class="upload-icon">📷</div>
              <p id="file-label">Click to upload a photo (JPG, PNG, max 5MB)</p>
              <p style="font-size:12px;margin-top:4px;color:#8a9ab0;">Photo evidence helps speed up resolution</p>
            </div>
          </div>

          <div style="margin-top:20px;">
            <label class="checkbox-item" style="cursor:pointer;">
              <input type="checkbox" required style="width:16px;height:16px;">
              <span style="font-size:13.5px;">I confirm that the information provided is accurate and I am reporting this issue in good faith.</span>
            </label>
          </div>

        </div><!-- form-body -->

        <div class="form-footer">
          <span style="font-size:13px;color:#5a6a7e;">📌 Your report will be assigned a unique ID for tracking.</span>
          <button type="submit" class="btn btn-primary">
            📋 Submit Issue Report
          </button>
        </div>

        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
