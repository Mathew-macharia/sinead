/**
 * SINEAD Hotel Management System
 * Core JavaScript Module
 * 
 * Handles:
 *   - Sidebar toggle and responsive behavior
 *   - Live clock in the header
 *   - Modal management (open/close)
 *   - Toast notification system
 *   - Table search and filtering
 *   - Dropdown menus
 *   - Delete confirmation dialogs
 *   - AJAX helper for async operations
 * 
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─── Sidebar Toggle ────────────────────────────────────────────────────
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                // Mobile behavior: slide in off-canvas
                sidebar.classList.toggle('open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('active');
                }
            } else {
                // Desktop behavior: collapse width
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });
    }

    // ─── Live Clock ────────────────────────────────────────────────────────
    const timeElement = document.getElementById('currentTime');
    const dateElement = document.getElementById('currentDate');

    function updateClock() {
        const now = new Date();
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('en-GB', {
                weekday: 'long',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    }

    if (timeElement) {
        updateClock();
        setInterval(updateClock, 30000); // Update every 30 seconds
    }

    // ─── Modal Management ──────────────────────────────────────────────────
    // Open modal: add 'active' class to .modal-overlay
    // Close modal: click overlay background or close button

    document.querySelectorAll('[data-modal-target]').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = this.getAttribute('data-modal-target');
            var modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    document.querySelectorAll('.modal-close, [data-modal-close]').forEach(function (closeBtn) {
        closeBtn.addEventListener('click', function () {
            var overlay = this.closest('.modal-overlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // ─── Dropdown Menus ────────────────────────────────────────────────────
    document.querySelectorAll('.dropdown-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var dropdown = this.closest('.dropdown');
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown.open').forEach(function (d) {
                if (d !== dropdown) d.classList.remove('open');
            });

            dropdown.classList.toggle('open');
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown.open').forEach(function (d) {
            d.classList.remove('open');
        });
    });

    // ─── Table Search ──────────────────────────────────────────────────────
    var searchInputs = document.querySelectorAll('[data-table-search]');

    searchInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            var tableId = this.getAttribute('data-table-search');
            var table = document.getElementById(tableId);
            if (!table) return;

            var query = this.value.toLowerCase().trim();
            var rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // ─── Status Filter ─────────────────────────────────────────────────────
    var statusFilters = document.querySelectorAll('[data-filter-status]');

    statusFilters.forEach(function (select) {
        select.addEventListener('change', function () {
            var tableId = this.getAttribute('data-filter-status');
            var table = document.getElementById(tableId);
            if (!table) return;

            var status = this.value.toLowerCase();
            var rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {
                if (!status) {
                    row.style.display = '';
                    return;
                }
                var badge = row.querySelector('.badge');
                var rowStatus = badge ? badge.textContent.toLowerCase().trim() : '';
                row.style.display = rowStatus.includes(status) ? '' : 'none';
            });
        });
    });

    // ─── Delete Confirmation ───────────────────────────────────────────────
    document.querySelectorAll('[data-confirm-delete]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ─── Auto-dismiss Alerts ───────────────────────────────────────────────
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 300ms ease, transform 300ms ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(function () {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // ─── View Toggle (Grid/List) ───────────────────────────────────────────
    document.querySelectorAll('.view-toggle button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var parent = this.closest('.view-toggle');
            parent.querySelectorAll('button').forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            var viewType = this.getAttribute('data-view');
            var gridView = document.getElementById('gridView');
            var listView = document.getElementById('listView');

            if (gridView && listView) {
                if (viewType === 'grid') {
                    gridView.style.display = '';
                    listView.style.display = 'none';
                } else {
                    gridView.style.display = 'none';
                    listView.style.display = '';
                }
            }
        });
    });

    // ─── Form Validation Visual Feedback ───────────────────────────────────
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var isValid = true;
            var requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger)';
                    field.addEventListener('input', function handler() {
                        field.style.borderColor = '';
                        field.removeEventListener('input', handler);
                    });
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

});

// ─── Utility: Close Modal by ID ────────────────────────────────────────────
function closeModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ─── Utility: Open Modal by ID ─────────────────────────────────────────────
function openModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// ─── Utility: Format Currency ──────────────────────────────────────────────
function formatCurrency(amount) {
    return 'KES ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
