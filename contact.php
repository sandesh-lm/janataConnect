<?php
$page_title = 'Contact Us';
include 'includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> <span>›</span> <span>Contact</span>
    </div>
    <h1>📞 Contact Us</h1>
    <p>Get in touch with Pokhara Metropolitan City offices or the JanataConnect support team.</p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-info-card">
        <h3>🏛️ Municipal Office Contacts</h3>
        <div class="contact-item">
          <div class="ci-icon">📍</div>
          <div class="ci-text">
            <strong>Address</strong>
            <span>Pokhara-30, Bagar, Kaski District<br>Gandaki Province, Nepal</span>
          </div>
        </div>
        <div class="contact-item">
          <div class="ci-icon">📞</div>
          <div class="ci-text">
            <strong>Main Office</strong>
            <span>061-525999 / 061-523022</span>
          </div>
        </div>
        <div class="contact-item">
          <div class="ci-icon">✉️</div>
          <div class="ci-text">
            <strong>Email</strong>
            <span>info@pokharametro.gov.np</span>
          </div>
        </div>
        <div class="contact-item">
          <div class="ci-icon">⏰</div>
          <div class="ci-text">
            <strong>Office Hours</strong>
            <span>Sunday – Friday: 10:00 AM – 5:00 PM<br>Saturday & Public Holidays: Closed</span>
          </div>
        </div>
        <div class="contact-item">
          <div class="ci-icon">🌐</div>
          <div class="ci-text">
            <strong>Website</strong>
            <span>www.pokharametro.gov.np</span>
          </div>
        </div>

        <hr style="border:none;border-top:1px solid #d0dce8;margin:22px 0;">

        <h4 style="font-size:14px;font-weight:700;margin-bottom:16px;">Emergency Contacts</h4>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <?php
          $emerg = [
            ['🚒', 'Fire Brigade', '101'],
            ['🚑', 'Ambulance', '102'],
            ['👮', 'Police Control', '100'],
            ['💧', 'Water Supply', '061-521111'],
            ['💡', 'Electricity', '061-520000'],
          ];
          foreach ($emerg as $e): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;">
            <span><?= $e[0] ?> <?= $e[1] ?></span>
            <strong style="color:#0a4d8c;"><?= $e[2] ?></strong>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <div class="form-card">
          <div class="form-card-header">
            <h2>💬 Send Us a Message</h2>
            <p>For inquiries, feedback, or portal support — we'll respond within 24 hours.</p>
          </div>
          <div class="form-body">
            <div class="alert alert-success" id="contact-success" style="display:none;">
              ✅ Your message has been sent! We'll get back to you within 24 hours.
            </div>
            <form id="contact-form">
              <div class="form-grid">
                <div class="form-group">
                  <label>Your Name <span class="req">*</span></label>
                  <input type="text" placeholder="Full name" required>
                </div>
                <div class="form-group">
                  <label>Phone Number</label>
                  <input type="tel" placeholder="98XXXXXXXX">
                </div>
                <div class="form-group full">
                  <label>Email Address <span class="req">*</span></label>
                  <input type="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group full">
                  <label>Subject</label>
                  <select>
                    <option>General Inquiry</option>
                    <option>Technical Support</option>
                    <option>Issue Reporting Help</option>
                    <option>Volunteer Program</option>
                    <option>Token System</option>
                    <option>Feedback / Suggestion</option>
                    <option>Complaint</option>
                  </select>
                </div>
                <div class="form-group full">
                  <label>Message <span class="req">*</span></label>
                  <textarea rows="5" placeholder="Write your message here..." required></textarea>
                </div>
              </div>
            </div>
            <div class="form-footer">
              <span style="font-size:13px;color:#5a6a7e;">We respond within 1–2 working days.</span>
              <button type="button" class="btn btn-primary" onclick="submitContact()">✉️ Send Message</button>
            </div>
            </form>
          </div>
        </div>

        <div style="background:#f0f5ff;border-radius:12px;padding:22px;margin-top:20px;border:1.5px solid #b3cef0;">
          <h4 style="font-size:15px;font-weight:700;margin-bottom:12px;">🔗 Quick Links for JanataConnect Support</h4>
          <ul style="list-style:none;display:flex;flex-direction:column;gap:9px;">
            <li><a href="report_issue.php" style="color:#0a4d8c;text-decoration:none;font-size:14px;">📋 Report a community issue →</a></li>
            <li><a href="volunteer_register.php" style="color:#0a4d8c;text-decoration:none;font-size:14px;">🤝 Register as volunteer →</a></li>
            <li><a href="token_system.php" style="color:#0a4d8c;text-decoration:none;font-size:14px;">🎫 Book a government token →</a></li>
            <li><a href="about.php" style="color:#0a4d8c;text-decoration:none;font-size:14px;">🏛️ Learn about us →</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function submitContact() {
  const form = document.getElementById('contact-form');
  const inputs = form.querySelectorAll('[required]');
  let valid = true;
  inputs.forEach(i => { if (!i.value.trim()) { valid = false; i.style.borderColor = '#c0392b'; } else { i.style.borderColor = ''; } });
  if (valid) {
    document.getElementById('contact-success').style.display = 'flex';
    form.reset();
    window.scrollTo({top: 0, behavior: 'smooth'});
  }
}
</script>

<?php include 'includes/footer.php'; ?>
