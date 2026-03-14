<?php
require_once 'auth_check.php';
require_once '../config/db.php';
$conn = getDB();

$id = (int)($_GET['id'] ?? 0);
$editing = false;
$data = ['title'=>'','body'=>'','tag'=>'Notice','tag_color'=>'#0a4d8c','is_ticker'=>1,'is_active'=>1];

if ($id) {
    $res = $conn->query("SELECT * FROM announcements WHERE id=$id LIMIT 1");
    if ($res && $res->num_rows) { $data = $res->fetch_assoc(); $editing = true; }
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($conn->real_escape_string($_POST['title'] ?? ''));
    $body      = trim($conn->real_escape_string($_POST['body'] ?? ''));
    $tag       = $conn->real_escape_string($_POST['tag'] ?? 'Notice');
    $tag_color = $conn->real_escape_string($_POST['tag_color'] ?? '#0a4d8c');
    $is_ticker = isset($_POST['is_ticker']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$body) {
        $error = 'Title and body are required.';
    } else {
        if ($editing) {
            $sql = "UPDATE announcements SET title='$title', body='$body', tag='$tag', tag_color='$tag_color', is_ticker=$is_ticker, is_active=$is_active WHERE id=$id";
        } else {
            $sql = "INSERT INTO announcements (title, body, tag, tag_color, is_ticker, is_active) VALUES ('$title','$body','$tag','$tag_color',$is_ticker,$is_active)";
        }
        if ($conn->query($sql)) {
            header('Location: announcements.php?msg=saved');
            exit;
        } else {
            $error = 'Save failed. Please try again.';
        }
    }
    // Refill form on error
    $data = ['title'=>$_POST['title'],'body'=>$_POST['body'],'tag'=>$_POST['tag'],
             'tag_color'=>$_POST['tag_color'],'is_ticker'=>$is_ticker,'is_active'=>$is_active];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $editing ? 'Edit' : 'Add' ?> Announcement – Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="admin-layout">
  <?php include 'sidebar.php'; ?>

  <main class="admin-main">
    <div style="margin-bottom:20px;">
      <a href="announcements.php" style="color:#5a6a7e;text-decoration:none;font-size:14px;">← Back to Announcements</a>
    </div>

    <div class="form-card" style="max-width:780px;">
      <div class="form-card-header">
        <h2><?= $editing ? '✏️ Edit Announcement' : '+ New Announcement' ?></h2>
        <p>This will appear on the homepage and in the notice ticker.</p>
      </div>
      <div class="form-body">

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">

          <div class="form-group" style="margin-bottom:16px;">
            <label>Announcement Title <span class="req">*</span></label>
            <input type="text" name="title" placeholder="e.g. Water Supply Disruption – Ward 3" required
                   value="<?= htmlspecialchars($data['title']) ?>">
          </div>

          <div class="form-group" style="margin-bottom:16px;">
            <label>Body / Description <span class="req">*</span></label>
            <textarea name="body" rows="5" required placeholder="Full announcement text..."><?= htmlspecialchars($data['body']) ?></textarea>
          </div>

          <div class="form-grid" style="margin-bottom:16px;">
            <div class="form-group">
              <label>Tag / Category</label>
              <select name="tag" id="tag-select" onchange="syncTagColor()">
                <?php
                $tag_options = [
                  'Notice'  => '#0a4d8c',
                  'Urgent'  => '#c0392b',
                  'Event'   => '#1a7a4a',
                  'Info'    => '#e8a000',
                  'Holiday' => '#8e44ad',
                  'Health'  => '#2980b9',
                ];
                foreach ($tag_options as $t => $col): ?>
                <option value="<?= $t ?>" data-color="<?= $col ?>" <?= ($data['tag']==$t) ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Tag Colour</label>
              <div style="display:flex;align-items:center;gap:10px;">
                <input type="color" name="tag_color" id="tag-color" value="<?= htmlspecialchars($data['tag_color']) ?>"
                       style="width:48px;height:40px;padding:2px;border:1.5px solid #d0dce8;border-radius:8px;cursor:pointer;">
                <span id="color-preview" style="font-size:13px;color:#5a6a7e;">Adjust if needed</span>
              </div>
            </div>
          </div>

          <div style="display:flex;gap:24px;margin-bottom:22px;flex-wrap:wrap;">
            <label class="checkbox-item">
              <input type="checkbox" name="is_ticker" <?= $data['is_ticker'] ? 'checked' : '' ?>>
              <span style="font-size:14px;">Show in notice ticker bar</span>
            </label>
            <label class="checkbox-item">
              <input type="checkbox" name="is_active" <?= $data['is_active'] ? 'checked' : '' ?>>
              <span style="font-size:14px;">Active (visible to public)</span>
            </label>
          </div>

        </div>
        <div class="form-footer">
          <a href="announcements.php" class="btn" style="border:1.5px solid #d0dce8;color:#5a6a7e;background:white;">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <?= $editing ? '💾 Save Changes' : '📢 Publish Announcement' ?>
          </button>
        </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
const tagColors = <?= json_encode($tag_options) ?>;
function syncTagColor() {
  const sel = document.getElementById('tag-select');
  const opt = sel.options[sel.selectedIndex];
  const col = opt.getAttribute('data-color');
  document.getElementById('tag-color').value = col;
}
</script>

<?php include '../includes/footer.php'; ?>
