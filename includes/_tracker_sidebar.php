<!-- Sidebar for issue tracker — shown to logged-in users -->
<div style="background:white;border-radius:14px;border:1.5px solid #d0dce8;box-shadow:0 2px 12px rgba(10,77,140,0.07);overflow:hidden;">
  <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));padding:18px 22px;color:white;">
    <h4 style="font-size:15px;font-weight:700;">📊 Your Summary</h4>
  </div>
  <div style="padding:18px 22px;display:flex;flex-direction:column;gap:14px;">
    <?php
    $uid_s = (int)($user['id'] ?? 0);
    require_once 'config/db.php';
    $sc = getDB();
    $counts = [
        'Total'       => $sc->query("SELECT COUNT(*) as c FROM issues WHERE user_id=$uid_s")->fetch_assoc()['c'],
        'Pending'     => $sc->query("SELECT COUNT(*) as c FROM issues WHERE user_id=$uid_s AND status='Pending'")->fetch_assoc()['c'],
        'In Progress' => $sc->query("SELECT COUNT(*) as c FROM issues WHERE user_id=$uid_s AND status='In Progress'")->fetch_assoc()['c'],
        'Resolved'    => $sc->query("SELECT COUNT(*) as c FROM issues WHERE user_id=$uid_s AND status='Resolved'")->fetch_assoc()['c'],
    ];
    $sc->close();
    $col_map = ['Total'=>'#0a4d8c','Pending'=>'#e67e22','In Progress'=>'#2980b9','Resolved'=>'#1a7a4a'];
    foreach ($counts as $label => $val):
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f4f8;">
      <span style="font-size:14px;color:#5a6a7e;"><?= $label ?></span>
      <strong style="font-size:18px;color:<?= $col_map[$label] ?>;"><?= $val ?></strong>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div style="background:white;border-radius:14px;border:1.5px solid #d0dce8;padding:18px 22px;margin-top:14px;">
  <h4 style="font-size:14px;font-weight:700;margin-bottom:14px;">Quick Actions</h4>
  <div style="display:flex;flex-direction:column;gap:10px;">
    <a href="report_issue.php" class="btn btn-primary btn-full" style="font-size:13.5px;">📋 Report New Issue</a>
    <a href="my-dashboard.php" class="btn btn-full" style="background:#f0f5ff;color:#0a4d8c;border:1.5px solid #b3cef0;font-size:13.5px;">📊 Full Dashboard</a>
  </div>
</div>

<div style="background:#f0f5ff;border-radius:12px;padding:16px 18px;margin-top:14px;border:1.5px solid #b3cef0;font-size:13px;color:#1a2332;line-height:1.65;">
  💡 <strong>Tip:</strong> Issues are typically reviewed within 2–3 working days. Resolved issues are archived for 6 months.
</div>
