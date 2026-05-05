/**
 * SPMB Al-Munawwir IIBS — Main Application JavaScript
 */

'use strict';

// ===========================================
// CSRF Helper
// ===========================================
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// ===========================================
// Fetch Helper (AJAX with CSRF)
// ===========================================
async function postJson(url, data = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(data),
    });
    return response.json();
}

async function postForm(url, formData) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: formData,
    });
    return response.json();
}

async function deleteRequest(url) {
    const response = await fetch(url, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
    });
    return response.json();
}

// ===========================================
// Flash Message Auto-dismiss
// ===========================================
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('[data-auto-dismiss]');
    alerts.forEach(alert => {
        const delay = parseInt(alert.dataset.autoDismiss) || 5000;
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, delay);
    });
});

// ===========================================
// Mobile Sidebar Toggle
// ===========================================
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle  = document.getElementById('sidebar-toggle');
    const sidebarClose   = document.getElementById('sidebar-close');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebar        = document.getElementById('sidebar');

    function openSidebar() {
        sidebar?.classList.remove('-translate-x-full');
        sidebarOverlay?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.add('-translate-x-full');
        sidebarOverlay?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    sidebarToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);
});

// ===========================================
// Form Auto-save (Multi-step Form)
// ===========================================
class AutoSave {
    constructor(formSelector, saveUrl, step, interval = 30000) {
        this.form     = document.querySelector(formSelector);
        this.saveUrl  = saveUrl;
        this.step     = step;
        this.interval = interval;
        this.timer    = null;
        this.saved    = false;

        if (this.form) {
            this.form.addEventListener('change', () => this.schedule());
            this.form.addEventListener('input', () => this.schedule());
            this.startInterval();
        }
    }

    schedule() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.save(), 3000); // 3s after last change
    }

    startInterval() {
        setInterval(() => this.save(), this.interval);
    }

    async save() {
        if (!this.form) return;

        const formData = new FormData(this.form);
        formData.set('step', this.step);

        try {
            const data = await postForm(this.saveUrl, formData);
            if (data.success) {
                this.showSavedIndicator();
            }
        } catch (e) {
            // Silent fail for auto-save
        }
    }

    showSavedIndicator() {
        const indicator = document.getElementById('autosave-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            setTimeout(() => indicator.classList.add('hidden'), 3000);
        }
    }
}

// ===========================================
// Notification Bell
// ===========================================
class NotificationPoller {
    constructor(countUrl, listUrl) {
        this.countUrl = countUrl;
        this.listUrl  = listUrl;
        this.badge    = document.getElementById('notif-badge');
        this.dropdown = document.getElementById('notif-dropdown');

        this.poll();
        setInterval(() => this.poll(), 60000); // Poll every 60s
    }

    async poll() {
        try {
            const data = await fetch(this.countUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json());

            if (this.badge) {
                if (data.count > 0) {
                    this.badge.textContent = data.count > 99 ? '99+' : data.count;
                    this.badge.classList.remove('hidden');
                } else {
                    this.badge.classList.add('hidden');
                }
            }
        } catch (e) {
            // Ignore polling errors
        }
    }
}

// ===========================================
// File Upload Preview
// ===========================================
function setupFilePreview(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            alert('Ukuran file melebihi 2MB. Pilih file yang lebih kecil.');
            this.value = '';
            return;
        }

        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan PDF, JPG, atau PNG.');
            this.value = '';
            return;
        }

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" class="max-h-32 rounded-lg object-contain">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-file-pdf text-red-500 text-xl"></i>
                    <span>${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>
                </div>`;
        }
    });
}

// ===========================================
// Confirm Delete
// ===========================================
function confirmDelete(message = 'Yakin ingin menghapus data ini?') {
    return confirm(message);
}

// ===========================================
// Format Currency Input
// ===========================================
function formatCurrencyInput(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('input', function () {
        const raw = this.value.replace(/[^0-9]/g, '');
        const num = parseInt(raw, 10);
        if (!isNaN(num)) {
            this.value = num.toLocaleString('id-ID');
        }
    });
}

// ===========================================
// Tooltip (simple)
// ===========================================
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function () {
            const tip = document.createElement('div');
            tip.className = 'fixed z-50 px-2 py-1 bg-gray-900 text-white text-xs rounded pointer-events-none';
            tip.textContent = this.dataset.tooltip;
            tip.id = 'tooltip-popup';
            document.body.appendChild(tip);

            const rect = this.getBoundingClientRect();
            tip.style.top  = (rect.top - tip.offsetHeight - 8) + 'px';
            tip.style.left = (rect.left + rect.width / 2 - tip.offsetWidth / 2) + 'px';
        });

        el.addEventListener('mouseleave', function () {
            document.getElementById('tooltip-popup')?.remove();
        });
    });
});

// ===========================================
// Initialize on DOM Ready
// ===========================================
document.addEventListener('DOMContentLoaded', function () {
    // Format currency fields
    document.querySelectorAll('[data-currency]').forEach(el => {
        formatCurrencyInput(el.id);
    });

    // Auto-resize textareas
    document.querySelectorAll('textarea[data-autoresize]').forEach(ta => {
        const resize = () => {
            ta.style.height = 'auto';
            ta.style.height = ta.scrollHeight + 'px';
        };
        ta.addEventListener('input', resize);
        resize();
    });
});
