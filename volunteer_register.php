<?php
require_once 'config/auth.php';
require_login('volunteer_register.php');
$__logged_user = current_user();

$page_title = 'Volunteer Registration';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $conn = getDB();

    $name           = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $dob            = $conn->real_escape_string($_POST['dob'] ?? '');
    $gender         = $conn->real_escape_string($_POST['gender'] ?? '');
    $citizenship_no = trim($conn->real_escape_string($_POST['citizenship_no'] ?? ''));
    $phone          = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $email          = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $address        = trim($conn->real_escape_string($_POST['address'] ?? ''));
    $ward           = $conn->real_escape_string($_POST['ward'] ?? '');
    $skills         = isset($_POST['skills']) ? implode(', ', array_map([$conn, 'real_escape_string'], $_POST['skills'])) : '';
    $other_skills   = trim($conn->real_escape_string($_POST['other_skills'] ?? ''));
    $avail_days     = $conn->real_escape_string($_POST['avail_days'] ?? '');
    $avail_time     = $conn->real_escape_string($_POST['avail_time'] ?? '');
    $has_exp        = isset($_POST['has_experience']) ? 1 : 0;
    $prev_org       = trim($conn->real_escape_string($_POST['prev_organization'] ?? ''));
    $emerg_name     = trim($conn->real_escape_string($_POST['emergency_name'] ?? ''));
    $emerg_phone    = trim($conn->real_escape_string($_POST['emergency_phone'] ?? ''));
    $agreed         = isset($_POST['agreed']) ? 1 : 0;

    if (!$name || !$phone || !$ward || !$agreed) {
        $error = 'Please fill in all required fields and agree to the terms.';
    } else {
        $uid_v = (int)($_SESSION['user_id'] ?? 0);
        $sql = "INSERT INTO volunteers (user_id, name, dob, gender, citizenship_no, phone, email, address, ward, skills, other_skills, availability_days, availability_time, has_experience, prev_organization, emergency_contact, emergency_phone, agreed)
                VALUES ($uid_v, '$name', " . ($dob ? "'$dob'" : 'NULL') . ", '$gender', '$citizenship_no', '$phone', '$email', '$address', '$ward', '$skills', '$other_skills', '$avail_days', '$avail_time', $has_exp, '$prev_org', '$emerg_name', '$emerg_phone', $agreed)";
        if ($conn->query($sql)) {
            $success = true;
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
    $conn->close();
}

include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Volunteer Portal</span>
    </div>
    <h1>🤝 Volunteer Registration Portal</h1>
    <p>Register to join community campaigns and make a positive impact in Pokhara.</p>
  </div>
</div>

<div class="form-container">
  <div class="container">

    <?php if ($success): ?>
    <div class="success-card">
      <div class="success-icon">🎉</div>
      <h2>Registration Successful!</h2>
      <p>Thank you for registering as a volunteer with Pokhara Metropolitan City.</p>
      <div class="info-box">
        <strong>What happens next?</strong><br>
        Our volunteer coordinator will contact you via phone or email when a campaign matching your skills and availability is announced. Please keep your phone accessible.
      </div>
      <p style="font-size:13px;margin-top:12px;color:#5a6a7e;">Upcoming campaigns: Tree Plantation Drive (this Sunday), Road Cleaning (Ward 3 & 7 – next week), Health Camp (Ward 10 – Month end)</p>
      <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap;">
        <a href="index.php" class="btn btn-blue">Back to Home</a>
        <a href="token_system.php" class="btn btn-outline" style="border-color:#d0dce8;color:#1a2332;">Book a Token</a>
      </div>
    </div>

    <?php else: ?>
    <div class="form-card">
      <div class="form-card-header">
        <h2>🤝 Volunteer Registration Form</h2>
        <p>Join hundreds of active community volunteers across 33 wards of Pokhara.</p>
      </div>
      <div class="form-body">

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>

          <div class="form-section-title">👤 Personal Information</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name <span class="req">*</span></label>
              <input type="text" name="name" placeholder="As per citizenship" required value="<?= htmlspecialchars($_POST['name'] ?? $__logged_user['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Date of Birth</label>
              <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <option value="">Select Gender</option>
                <option value="Male" <?= (($_POST['gender'] ?? '') == 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (($_POST['gender'] ?? '') == 'Female') ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= (($_POST['gender'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label>Citizenship Number (Optional)</label>
              <input type="text" name="citizenship_no" placeholder="XX-XX-XX-XXXXX" value="<?= htmlspecialchars($_POST['citizenship_no'] ?? '') ?>">
            </div>
          </div>

          <div class="form-section-title">📞 Contact Details</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Phone Number <span class="req">*</span></label>
              <input type="tel" name="phone" placeholder="98XXXXXXXX" required value="<?= htmlspecialchars($_POST['phone'] ?? $__logged_user['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" placeholder="volunteer@email.com" value="<?= htmlspecialchars($_POST['email'] ?? $__logged_user['email'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Permanent Address</label>
              <input type="text" name="address" placeholder="VDC/Municipality, District, Province" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
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

          <div class="form-section-title">📅 Availability</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Available Days</label>
              <div class="checkbox-group" style="flex-direction:column;">
                <?php foreach (['Weekdays', 'Weekends', 'Anytime'] as $day): ?>
                <label class="checkbox-item">
                  <input type="radio" name="avail_days" value="<?= $day ?>" <?= (($_POST['avail_days'] ?? '') == $day) ? 'checked' : '' ?>>
                  <?= $day ?>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="form-group">
              <label>Available Time</label>
              <div class="checkbox-group" style="flex-direction:column;">
                <?php foreach (['Morning (6AM–12PM)', 'Afternoon (12PM–6PM)', 'Evening (6PM–9PM)'] as $t): ?>
                <label class="checkbox-item">
                  <input type="radio" name="avail_time" value="<?= $t ?>" <?= (($_POST['avail_time'] ?? '') == $t) ? 'checked' : '' ?>>
                  <?= $t ?>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="form-section-title">🎯 Skills & Experience</div>
          <div class="form-group full">
            <label>Your Skills (Select all that apply)</label>
            <div class="checkbox-group">
              <?php
              $skill_options = ['First Aid', 'Teaching', 'Event Management', 'Environmental Work', 'Technology / IT', 'Photography', 'Social Work', 'Construction', 'Driving', 'Cooking'];
              foreach ($skill_options as $skill):
                $checked = isset($_POST['skills']) && in_array($skill, $_POST['skills']) ? 'checked' : '';
              ?>
              <label class="checkbox-item">
                <input type="checkbox" name="skills[]" value="<?= $skill ?>" <?= $checked ?>>
                <?= $skill ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="form-group full">
            <label>Other Skills</label>
            <input type="text" name="other_skills" placeholder="Any other relevant skills..." value="<?= htmlspecialchars($_POST['other_skills'] ?? '') ?>">
          </div>

          <div class="form-grid" style="margin-top:16px;">
            <div class="form-group full">
              <label class="checkbox-item">
                <input type="checkbox" name="has_experience" <?= isset($_POST['has_experience']) ? 'checked' : '' ?>>
                <span>I have volunteered before</span>
              </label>
            </div>
            <div class="form-group full" id="prev-org-wrap">
              <label>Previous Organization / Campaign</label>
              <input type="text" name="prev_organization" placeholder="Name of organization or campaign" value="<?= htmlspecialchars($_POST['prev_organization'] ?? '') ?>">
            </div>
          </div>

          <div class="form-section-title">🆘 Emergency Contact</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Emergency Contact Name</label>
              <input type="text" name="emergency_name" placeholder="Full name" value="<?= htmlspecialchars($_POST['emergency_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Emergency Phone</label>
              <input type="tel" name="emergency_phone" placeholder="98XXXXXXXX" value="<?= htmlspecialchars($_POST['emergency_phone'] ?? '') ?>">
            </div>
          </div>

          <div style="margin-top:22px;padding:16px;background:#f4f7fb;border-radius:10px;">
            <label class="checkbox-item">
              <input type="checkbox" name="agreed" required <?= isset($_POST['agreed']) ? 'checked' : '' ?>>
              <span style="font-size:13.5px;line-height:1.55;">I agree to the volunteer terms and conditions. I understand that volunteering is unpaid and I commit to participating responsibly in assigned campaigns. I consent to my contact details being used by Pokhara Metropolitan City for volunteer coordination purposes.</span>
            </label>
          </div>

        </div><!-- form-body -->

        <div class="form-footer">
          <span style="font-size:13px;color:#5a6a7e;">🤝 Join 1,200+ active volunteers in Pokhara</span>
          <button type="submit" class="btn btn-primary">🤝 Register as Volunteer</button>
        </div>

        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
