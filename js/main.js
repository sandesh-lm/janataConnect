// ===== NAVBAR MOBILE TOGGLE =====
document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.querySelector('.nav-toggle');
  const links = document.querySelector('.nav-links');
  if (toggle && links) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('open');
    });
  }

  // Active nav link
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(a => {
    const href = a.getAttribute('href').split('/').pop();
    if (href === path) a.classList.add('active');
  });

  // File upload label
  const fileInput = document.getElementById('photo');
  const fileLabel = document.getElementById('file-label');
  if (fileInput && fileLabel) {
    fileInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        fileLabel.textContent = this.files[0].name;
      }
    });
  }

  // Form validation visual
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const btn = form.querySelector('[type="submit"]');
      if (btn && form.checkValidity()) {
        btn.innerHTML = '<span>⏳</span> Submitting...';
        btn.disabled = true;
      }
    });
  });

  // Date min today
  const dateInputs = document.querySelectorAll('input[type="date"]');
  const today = new Date().toISOString().split('T')[0];
  dateInputs.forEach(d => {
    if (!d.value) d.value = today;
    if (d.id === 'date' || d.hasAttribute('data-future')) d.min = today;
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});

// ===== SEARCH TABLE =====
function searchTable(inputId, tableId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    const rows = document.querySelectorAll(`#${tableId} tbody tr`);
    rows.forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
  });
}

// ===== STATUS UPDATE =====
function updateStatus(id, type) {
  const select = document.getElementById(`status-${id}`);
  if (!select) return;
  const formData = new FormData();
  formData.append('id', id);
  formData.append('status', select.value);
  formData.append('type', type);

  fetch('admin/update_status.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const badge = document.getElementById(`badge-${id}`);
        if (badge) {
          badge.textContent = select.value;
          badge.className = 'badge badge-' + (select.value === 'Resolved' ? 'resolved' : 'pending');
        }
        showToast('Status updated!', 'success');
      }
    }).catch(() => showToast('Update failed.', 'error'));
}

// ===== TOAST =====
function showToast(msg, type = 'info') {
  const toast = document.createElement('div');
  toast.style.cssText = `
    position: fixed; bottom: 24px; right: 24px;
    background: ${type === 'success' ? '#1a7a4a' : type === 'error' ? '#c0392b' : '#0a4d8c'};
    color: white; padding: 12px 20px; border-radius: 8px;
    font-size: 14px; font-weight: 500; z-index: 9999;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    animation: slideIn 0.3s ease;
  `;
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3200);
}

// CSS for toast animation
const style = document.createElement('style');
style.textContent = `@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }`;
document.head.appendChild(style);
