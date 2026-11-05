// TechCorp - Main JavaScript

// ── Navbar scroll shadow ──
window.addEventListener('scroll', () => {
    const nav = document.querySelector('.navbar');
    if (nav) {
        nav.style.boxShadow = window.scrollY > 10
            ? '0 4px 20px rgba(0,0,0,0.3)'
            : '0 2px 12px rgba(0,0,0,0.2)';
    }
});

// ── Register form client-side validation ──
function validateRegister() {
    const password = document.getElementById('reg_password');
    const confirm  = document.getElementById('reg_confirm');

    if (!password || !confirm) return true;

    if (password.value.length < 6) {
        showToast('Password must be at least 6 characters.', 'error');
        password.focus();
        return false;
    }
    if (password.value !== confirm.value) {
        showToast('Passwords do not match!', 'error');
        confirm.focus();
        return false;
    }
    return true;
}

// ── Toast notification ──
function showToast(message, type = 'success') {
    // Remove existing toast
    const existing = document.getElementById('toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'toast';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: ${type === 'error' ? '#ef4444' : '#10b981'};
        color: white;
        padding: 14px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.5s'; }, 2800);
    setTimeout(() => toast.remove(), 3300);
}

// ── Smooth scroll for anchor links ──
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ── Animate stat cards on page load ──
window.addEventListener('load', () => {
    const cards = document.querySelectorAll('.stat-card, .feature-card, .team-card');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + i * 80);
    });
});

// ── Add slide-in keyframe ──
const style = document.createElement('style');
style.textContent = `@keyframes slideIn { from { transform: translateX(60px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
document.head.appendChild(style);
