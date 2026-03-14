<?php
require_once 'config/auth.php';
require_login('token_system.php');
$__logged_user = current_user();

$page_title = 'Government Office Token System';
$success = false;
$error = '';
$token_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';
    $conn = getDB();

    $name      = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $phone     = trim($conn->real_escape_string($_POST['phone'] ?? ''));
    $email     = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $office    = $conn->real_escape_string($_POST['office'] ?? '');
    $service   = $conn->real_escape_string($_POST['service'] ?? '');
    $date      = $conn->real_escape_string($_POST['date'] ?? '');
    $time_slot = $conn->real_escape_string($_POST['time_slot'] ?? '');

    if (!$name || !$phone || !$office || !$service || !$date || !$time_slot) {
        $error = 'Please fill in all required fields.';
    } elseif (strtotime($date) < strtotime('today')) {
        $error = 'Please select a future date.';
    } else {
        // Generate token number: letter prefix + sequential number
        $prefix_map = [
            'Pokhara Metropolitan Office' => 'A',
            'Ward Office 1' => 'B',
            'Ward Office 8' => 'C',
            'Ward Office 17' => 'D',
            'Land Revenue Office Pokhara' => 'E',
            'District Administration Office Kaski' => 'F',
            'Malpot Office Pokhara' => 'G',
            'Pokhara Sub-Metropolitan Office' => 'H',
        ];
        $prefix = $prefix_map[$_POST['office']] ?? 'T';

        // Count existing tokens for this office on this date
        $count_sql = "SELECT COUNT(*) as cnt FROM tokens WHERE office='$office' AND date='$date'";
        $res = $conn->query($count_sql);
        $row = $res->fetch_assoc();
        $count = ($row['cnt'] ?? 0) + 1;
        $token_number = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);

        $uid_t = (int)($_SESSION['user_id'] ?? 0);
        $sql = "INSERT INTO tokens (user_id, name, phone, email, office, service, date, time_slot, token_number)
                VALUES ($uid_t, '$name', '$phone', '$email', '$office', '$service', '$date', '$time_slot', '$token_number')";

        if ($conn->query($sql)) {
            $success = true;
            $token_data = [
                'token_number' => $token_number,
                'name'         => $_POST['name'],
                'office'       => $_POST['office'],
                'service'      => $_POST['service'],
                'date'         => date('l, F j, Y', strtotime($date)),
                'time_slot'    => $time_slot,
                'booking_id'   => 'TKN-' . str_pad($conn->insert_id, 4, '0', STR_PAD_LEFT),
            ];
        } else {
            $error = 'Booking failed. Please try again.';
        }
    }
    $conn->close();
}

include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Token System</span>
    </div>
    <h1>🎫 Government Office Token System</h1>
    <p>Book your digital queue token online and skip the long waiting lines at government offices.</p>
  </div>
</div>

<div class="form-container">
  <div class="container">

    <?php if ($success): ?>
    <!-- Token Confirmation -->
    <div class="success-card" style="max-width:600px;">
      <div class="success-icon">✅</div>
      <h2>Token Booked Successfully!</h2>
      <p>Your appointment has been confirmed. Please arrive on time.</p>

      <div class="token-display">
        <div class="token-label">Your Token Number</div>
        <div class="token-num"><?= htmlspecialchars($token_data['token_number']) ?></div>
        <div style="font-size:13px;color:rgba(255,255,255,0.65);margin-top:10px;"><?= htmlspecialchars($token_data['office']) ?></div>
      </div>

      <div class="info-box">
        <strong>Booking ID:</strong> <?= htmlspecialchars($token_data['booking_id']) ?><br>
        <strong>Name:</strong> <?= htmlspecialchars($token_data['name']) ?><br>
        <strong>Service:</strong> <?= htmlspecialchars($token_data['service']) ?><br>
        <strong>Date:</strong> <?= htmlspecialchars($token_data['date']) ?><br>
        <strong>Time Slot:</strong> <?= htmlspecialchars($token_data['time_slot']) ?>
      </div>

      <div class="alert alert-info">
        ℹ️ Please bring your original documents and this booking reference. Arrive 10 minutes before your time slot.
      </div>

      <div style="display:flex;gap:12px;justify-content:center;margin-top:20px;flex-wrap:wrap;">
        <a href="token_system.php" class="btn btn-blue">Book Another Token</a>
        <a href="index.php" class="btn btn-outline" style="border-color:#d0dce8;color:#1a2332;">Back to Home</a>
      </div>
    </div>

    <?php else: ?>

    <!-- Info Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:32px;">
      <div style="background:white;border-radius:10px;padding:18px;border:1.5px solid #d0dce8;display:flex;align-items:center;gap:14px;">
        <div style="font-size:30px;">⏰</div>
        <div><strong style="font-size:14px;display:block;">Save Your Time</strong><span style="font-size:13px;color:#5a6a7e;">No more waiting in physical queues for hours</span></div>
      </div>
      <div style="background:white;border-radius:10px;padding:18px;border:1.5px solid #d0dce8;display:flex;align-items:center;gap:14px;">
        <div style="font-size:30px;">📱</div>
        <div><strong style="font-size:14px;display:block;">Book Online</strong><span style="font-size:13px;color:#5a6a7e;">Reserve your slot from home or office easily</span></div>
      </div>
      <div style="background:white;border-radius:10px;padding:18px;border:1.5px solid #d0dce8;display:flex;align-items:center;gap:14px;">
        <div style="font-size:30px;">🎫</div>
        <div><strong style="font-size:14px;display:block;">Instant Token</strong><span style="font-size:13px;color:#5a6a7e;">Get your token number immediately on booking</span></div>
      </div>
    </div>

    <div class="form-card">
      <div class="form-card-header">
        <h2>🎫 Token Booking Form</h2>
        <p>Book your visit slot at any government office in Pokhara Metropolitan City.</p>
      </div>
      <div class="form-body">

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>

          <div class="form-section-title">👤 Visitor Information</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Full Name <span class="req">*</span></label>
              <input type="text" name="name" placeholder="As per citizenship" required value="<?= htmlspecialchars($_POST['name'] ?? $__logged_user['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Phone Number <span class="req">*</span></label>
              <input type="tel" name="phone" placeholder="98XXXXXXXX" required value="<?= htmlspecialchars($_POST['phone'] ?? $__logged_user['phone'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Email Address</label>
              <input type="email" name="email" placeholder="confirmation@email.com" value="<?= htmlspecialchars($_POST['email'] ?? $__logged_user['email'] ?? '') ?>">
            </div>
          </div>

          <div class="form-section-title">🏢 Appointment Details</div>
          <div class="form-grid">
            <div class="form-group full">
              <label>Select Government Office <span class="req">*</span></label>
              <select name="office" id="office" required onchange="updateServices()">
                <option value="">-- Select Office --</option>
                <optgroup label="Metropolitan Offices">
                  <option value="Pokhara Metropolitan Office" <?= (($_POST['office'] ?? '') == 'Pokhara Metropolitan Office') ? 'selected' : '' ?>>Pokhara Metropolitan Office</option>
                  <option value="Pokhara Sub-Metropolitan Office" <?= (($_POST['office'] ?? '') == 'Pokhara Sub-Metropolitan Office') ? 'selected' : '' ?>>Pokhara Sub-Metropolitan Office</option>
                </optgroup>
                <optgroup label="Ward Offices">
                  <?php for ($i = 1; $i <= 33; $i++): ?>
                  <option value="Ward Office <?= $i ?>" <?= (($_POST['office'] ?? '') == "Ward Office $i") ? 'selected' : '' ?>>Ward Office <?= $i ?></option>
                  <?php endfor; ?>
                </optgroup>
                <optgroup label="District Offices">
                  <option value="Land Revenue Office Pokhara" <?= (($_POST['office'] ?? '') == 'Land Revenue Office Pokhara') ? 'selected' : '' ?>>Land Revenue Office Pokhara</option>
                  <option value="District Administration Office Kaski" <?= (($_POST['office'] ?? '') == 'District Administration Office Kaski') ? 'selected' : '' ?>>District Administration Office Kaski</option>
                  <option value="Malpot Office Pokhara" <?= (($_POST['office'] ?? '') == 'Malpot Office Pokhara') ? 'selected' : '' ?>>Malpot Office Pokhara</option>
                  <option value="Inland Revenue Office Pokhara" <?= (($_POST['office'] ?? '') == 'Inland Revenue Office Pokhara') ? 'selected' : '' ?>>Inland Revenue Office Pokhara</option>
                </optgroup>
              </select>
            </div>
            <div class="form-group full">
              <label>Select Service Type <span class="req">*</span></label>
              <select name="service" required>
                <option value="">-- Select Service --</option>
                <optgroup label="Citizenship Services">
                  <option value="Citizenship Certificate – New" <?= (($_POST['service'] ?? '') == 'Citizenship Certificate – New') ? 'selected' : '' ?>>Citizenship Certificate – New</option>
                  <option value="Citizenship Certificate – Renewal" <?= (($_POST['service'] ?? '') == 'Citizenship Certificate – Renewal') ? 'selected' : '' ?>>Citizenship Certificate – Renewal</option>
                </optgroup>
                <optgroup label="Land & Property">
                  <option value="Land Registration" <?= (($_POST['service'] ?? '') == 'Land Registration') ? 'selected' : '' ?>>Land Registration</option>
                  <option value="Land Ownership Certificate" <?= (($_POST['service'] ?? '') == 'Land Ownership Certificate') ? 'selected' : '' ?>>Land Ownership Certificate</option>
                  <option value="Land Transfer" <?= (($_POST['service'] ?? '') == 'Land Transfer') ? 'selected' : '' ?>>Land Transfer</option>
                </optgroup>
                <optgroup label="Tax & Finance">
                  <option value="Tax Payment" <?= (($_POST['service'] ?? '') == 'Tax Payment') ? 'selected' : '' ?>>Tax Payment</option>
                  <option value="Property Tax Assessment" <?= (($_POST['service'] ?? '') == 'Property Tax Assessment') ? 'selected' : '' ?>>Property Tax Assessment</option>
                  <option value="PAN Registration" <?= (($_POST['service'] ?? '') == 'PAN Registration') ? 'selected' : '' ?>>PAN Registration</option>
                </optgroup>
                <optgroup label="Business">
                  <option value="Business Registration" <?= (($_POST['service'] ?? '') == 'Business Registration') ? 'selected' : '' ?>>Business Registration</option>
                  <option value="Business Renewal" <?= (($_POST['service'] ?? '') == 'Business Renewal') ? 'selected' : '' ?>>Business Renewal</option>
                </optgroup>
                <optgroup label="Documents">
                  <option value="Recommendation Letter" <?= (($_POST['service'] ?? '') == 'Recommendation Letter') ? 'selected' : '' ?>>Recommendation Letter</option>
                  <option value="Birth Certificate" <?= (($_POST['service'] ?? '') == 'Birth Certificate') ? 'selected' : '' ?>>Birth Certificate</option>
                  <option value="Marriage Certificate" <?= (($_POST['service'] ?? '') == 'Marriage Certificate') ? 'selected' : '' ?>>Marriage Certificate</option>
                  <option value="Death Certificate" <?= (($_POST['service'] ?? '') == 'Death Certificate') ? 'selected' : '' ?>>Death Certificate</option>
                  <option value="Migration Certificate" <?= (($_POST['service'] ?? '') == 'Migration Certificate') ? 'selected' : '' ?>>Migration Certificate</option>
                </optgroup>
                <option value="Other" <?= (($_POST['service'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label>Select Date <span class="req">*</span></label>
              <input type="date" name="date" id="date" required data-future="true" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Select Time Slot <span class="req">*</span></label>
              <select name="time_slot" required>
                <option value="">Select Time Slot</option>
                <option value="Morning (10:00 AM – 1:00 PM)" <?= (($_POST['time_slot'] ?? '') == 'Morning (10:00 AM – 1:00 PM)') ? 'selected' : '' ?>>🌤️ Morning (10:00 AM – 1:00 PM)</option>
                <option value="Afternoon (1:00 PM – 4:00 PM)" <?= (($_POST['time_slot'] ?? '') == 'Afternoon (1:00 PM – 4:00 PM)') ? 'selected' : '' ?>>☀️ Afternoon (1:00 PM – 4:00 PM)</option>
              </select>
            </div>
          </div>

          <div class="alert alert-info" style="margin-top:16px;">
            ℹ️ Government offices are open <strong>Sunday to Friday, 10:00 AM – 5:00 PM</strong>. Closed on Saturdays and public holidays.
          </div>

        </div>

        <div class="form-footer">
          <span style="font-size:13px;color:#5a6a7e;">🎫 Token number will be generated immediately</span>
          <button type="submit" class="btn btn-primary">🎫 Book My Token</button>
        </div>

        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
