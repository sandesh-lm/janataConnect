<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$id = (int)($_GET['id'] ?? 0);
$editing = false;
$data = [
    'title'=>'','description'=>'','location'=>'','campaign_date'=>'',
    'campaign_time'=>'','organizer'=>'','max_volunteers'=>0,
    'category'=>'Environment','status'=>'Upcoming','is_active'=>1
];

if ($id) {
    $res = $conn->query("SELECT * FROM campaigns WHERE id=$id LIMIT 1");
    if ($res && $res->num_rows) { $data = $res->fetch_assoc(); $editing = true; }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($conn->real_escape_string($_POST['title'] ?? ''));
    $description   = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $location      = trim($conn->real_escape_string($_POST['location'] ?? ''));
    $campaign_date = $conn->real_escape_string($_POST['campaign_date'] ?? '');
    $campaign_time = trim($conn->real_escape_string($_POST['campaign_time'] ?? ''));
    $organizer     = trim($conn->real_escape_string($_POST['organizer'] ?? ''));
    $max_volunteers= (int)($_POST['max_volunteers'] ?? 0);
    $category      = $conn->real_escape_string($_POST['category'] ?? 'Environment');
    $status        = $conn->real_escape_string($_POST['status'] ?? 'Upcoming');
    $is_active     = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$description) {
        $error = 'Title and description are required.';
    } else {
        $date_val = $campaign_date ? "'$campaign_date'" : 'NULL';
        if ($editing) {
            $sql = "UPDATE campaigns SET title='$title', description='$description', location='$location',
                    campaign_date=$date_val, campaign_time='$campaign_time', organizer='$organizer',
                    max_volunteers=$max_volunteers, category='$category', status='$status', is_active=$is_active
                    WHERE id=$id";
        } else {
            $sql = "INSERT INTO campaigns (title, description, location, campaign_date, campaign_time, organizer, max_volunteers, category, status, is_active)
                    VALUES ('$title','$description','$location',$date_val,'$campaign_time','$organizer',$max_volunteers,'$category','$status',$is_active)";
        }
        if ($conn->query($sql)) {
            header('Location: campaigns.php?msg=saved');
            exit;
        } else {
            $error = 'Save failed: ' . $conn->error;
        }
    }
    $data = $_POST;
    $data['is_active'] = $is_active;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $editing ? 'Edit' : 'Add' ?> Campaign – Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div style="margin-bottom:20px;">
      <a href="campaigns.php" style="color:#5a6a7e;text-decoration:none;font-size:14px;">← Back to Campaigns</a>
    </div>

    <div class="form-card" style="max-width:860px;">
      <div class="form-card-header">
        <h2><?= $editing ? '✏️ Edit Campaign' : '🌿 Create New Campaign' ?></h2>
        <p>Citizens will be able to see and register for this campaign on the public portal.</p>
      </div>
      <div class="form-body">

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">

          <div class="form-section-title">📋 Basic Information</div>
          <div class="form-grid">
            <div class="form-group full">
              <label>Campaign Title <span class="req">*</span></label>
              <input type="text" name="title" placeholder="e.g. Phewa Lake Tree Plantation Drive" required
                     value="<?= htmlspecialchars($data['title']) ?>">
            </div>
            <div class="form-group full">
              <label>Description <span class="req">*</span></label>
              <textarea name="description" rows="5" required
                        placeholder="Describe the campaign — what volunteers will do, what to bring, goals..."><?= htmlspecialchars($data['description']) ?></textarea>
            </div>
            <div class="form-group">
              <label>Category</label>
              <select name="category">
                <?php foreach (['Environment','Sanitation','Health','Disaster Relief','Education','Infrastructure','Social Work','Other'] as $cat): ?>
                <option <?= ($data['category']==$cat) ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <?php foreach (['Upcoming','Ongoing','Completed','Cancelled'] as $s): ?>
                <option <?= ($data['status']==$s) ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-section-title">📅 Date, Time & Location</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Campaign Date</label>
              <input type="date" name="campaign_date" value="<?= htmlspecialchars($data['campaign_date'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Time</label>
              <input type="text" name="campaign_time" placeholder="e.g. 7:00 AM – 11:00 AM"
                     value="<?= htmlspecialchars($data['campaign_time']) ?>">
            </div>
            <div class="form-group full">
              <label>Location / Venue</label>
              <input type="text" name="location" placeholder="e.g. Phewa Lake Shore, Ward 6, Pokhara"
                     value="<?= htmlspecialchars($data['location']) ?>">
            </div>
          </div>

          <div class="form-section-title">👥 Organizer & Capacity</div>
          <div class="form-grid">
            <div class="form-group">
              <label>Organizer / Department</label>
              <input type="text" name="organizer" placeholder="e.g. Environment Section, Pokhara Metro"
                     value="<?= htmlspecialchars($data['organizer']) ?>">
            </div>
            <div class="form-group">
              <label>Max Volunteers</label>
              <input type="number" name="max_volunteers" min="0" placeholder="0 = unlimited"
                     value="<?= (int)($data['max_volunteers'] ?? 0) ?>">
              <small style="color:#5a6a7e;font-size:12px;">Set 0 for unlimited registrations</small>
            </div>
          </div>

          <div style="margin-top:16px;">
            <label class="checkbox-item">
              <input type="checkbox" name="is_active" <?= ($data['is_active'] ?? 1) ? 'checked' : '' ?>>
              <span style="font-size:14px;">Active — show this campaign to the public</span>
            </label>
          </div>

        </div>
        <div class="form-footer">
          <a href="campaigns.php" class="btn" style="border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <?= $editing ? '💾 Save Changes' : '🌿 Create Campaign' ?>
          </button>
        </div>
        </form>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
