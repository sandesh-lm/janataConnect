<?php
$page_title = 'About Us';
include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>About</span>
    </div>
    <h1>🏛️ About JanataConnect</h1>
    <p>Learn about our mission to digitize and improve citizen services in Pokhara.</p>
  </div>
</div>

<section class="section section-alt">
  <div class="container">
    <div class="about-grid">
      <div class="about-content">
        <div class="section-tag">Our Mission</div>
        <h2>Bridging Citizens and Municipal Government</h2>
        <p>JanataConnect is the official digital citizen services platform of Pokhara Metropolitan City, developed to make local government services more accessible, transparent, and efficient for all 500,000+ residents of Pokhara.</p>
        <p>Launched as part of the Smart Pokhara Digital Governance Initiative, this platform empowers citizens to report community issues, volunteer for public campaigns, and access government services without lengthy queue visits.</p>
        <ul class="features-list">
          <li><div class="check">✓</div><span>Issue tracking with real-time status updates</span></li>
          <li><div class="check">✓</div><span>Volunteer management across 33 wards</span></li>
          <li><div class="check">✓</div><span>Digital token system for 7+ government offices</span></li>
          <li><div class="check">✓</div><span>Mobile-friendly access for all citizens</span></li>
          <li><div class="check">✓</div><span>Transparent public service delivery tracking</span></li>
        </ul>
      </div>
      <div>
        <div style="background:linear-gradient(135deg,#073a6b,#0a4d8c);border-radius:18px;padding:40px;color:white;text-align:center;">
          <div style="font-size:60px;margin-bottom:16px;">🏛️</div>
          <h3 style="font-size:22px;font-weight:700;margin-bottom:8px;">Pokhara Metropolitan City</h3>
          <p style="color:rgba(255,255,255,0.75);font-size:14px;margin-bottom:24px;">Gandaki Province, Nepal</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <?php
            $facts = [
              ['500,000+', 'Population'],
              ['33', 'Wards'],
              ['464.24 km²', 'Area'],
              ['1,500+', 'Staff'],
            ];
            foreach ($facts as $f): ?>
            <div style="background:rgba(255,255,255,0.1);border-radius:10px;padding:16px;">
              <div style="font-size:24px;font-weight:700;color:#f5c842;"><?= $f[0] ?></div>
              <div style="font-size:12.5px;color:rgba(255,255,255,0.7);margin-top:4px;"><?= $f[1] ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Our Team</div>
      <h2>Key Municipal Departments</h2>
      <p>JanataConnect is jointly operated by multiple departments of Pokhara Metropolitan City.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
      <?php
      $depts = [
        ['🏗️', 'Infrastructure Department', 'Handles road repairs, drainage, and public construction projects across all 33 wards.'],
        ['🌿', 'Environment Section', 'Manages waste collection, tree plantation, and environmental awareness campaigns.'],
        ['💡', 'Urban Utilities', 'Oversees street lights, water supply, electricity, and urban facility management.'],
        ['📋', 'Citizen Services', 'Processes all documentation including recommendations, certificates, and registrations.'],
        ['🤝', 'Community Outreach', 'Coordinates volunteers, community programs, and social welfare campaigns.'],
        ['💻', 'IT & Digital Services', 'Manages digital platforms including JanataConnect and online service portals.'],
      ];
      foreach ($depts as $d): ?>
      <div style="background:white;border-radius:12px;padding:26px;border:1.5px solid #d0dce8;box-shadow:0 2px 10px rgba(10,77,140,0.07);">
        <div style="font-size:32px;margin-bottom:14px;"><?= $d[0] ?></div>
        <h4 style="font-size:15px;font-weight:700;margin-bottom:8px;"><?= $d[1] ?></h4>
        <p style="font-size:13.5px;color:#5a6a7e;line-height:1.65;"><?= $d[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
