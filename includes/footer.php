<?php
$base_path = isset($base_path) ? $base_path : '';
?>
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="name">🏛️ JanataConnect</div>
        <p>Official digital citizen services portal for Pokhara Metropolitan City. Connecting residents with government services for a smarter, more transparent municipality.</p>
        <div class="social-links">
          <a href="#" title="Facebook">f</a>
          <a href="#" title="Twitter">𝕏</a>
          <a href="#" title="YouTube">▶</a>
          <a href="#" title="Instagram">📷</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= $base_path ?>index.php">Home</a></li>
          <li><a href="<?= $base_path ?>report_issue.php">Report Issue</a></li>
          <li><a href="<?= $base_path ?>volunteer_register.php">Volunteer Portal</a></li>
          <li><a href="<?= $base_path ?>campaigns.php">Campaigns</a></li>
          <li><a href="<?= $base_path ?>token_system.php">Token System</a></li>
          <li><a href="<?= $base_path ?>announcements.php">Announcements</a></li>
          <li><a href="<?= $base_path ?>about.php">About Us</a></li>
          <li><a href="<?= $base_path ?>contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Services</h4>
        <ul>
          <li><a href="#">Citizenship Certificate</a></li>
          <li><a href="#">Land Registration</a></li>
          <li><a href="#">Business License</a></li>
          <li><a href="#">Tax Payment</a></li>
          <li><a href="#">Birth Registration</a></li>
          <li><a href="#">Recommendation Letter</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <ul>
          <li><a href="#">📍 Pokhara-30, Kaski, Gandaki</a></li>
          <li><a href="tel:+97761525999">📞 061-525999</a></li>
          <li><a href="mailto:info@pokharametro.gov.np">✉️ info@pokharametro.gov.np</a></li>
          <li><a href="#">🌐 pokharametro.gov.np</a></li>
          <li><a href="#">⏰ Sun-Fri: 10AM–5PM</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;width:100%">
      <span>© <?= date('Y') ?> JanataConnect – Pokhara Metropolitan City. All rights reserved.</span>
      <div style="display:flex;gap:16px;">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Use</a>
        <a href="#">Accessibility</a>
      </div>
    </div>
  </div>
</footer>

<script src="<?= $base_path ?>js/main.js"></script>
</body>
</html>
