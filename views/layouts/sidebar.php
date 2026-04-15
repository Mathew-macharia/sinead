<!-- 
/**
 * Layout: Sidebar Navigation
 * 
 * Implements the application's primary navigation using a vertical sidebar.
 * Navigation items are conditionally rendered based on user roles (RBAC).
 * 
 * Role Visibility:
 *   - Admin:        All sections
 *   - Receptionist: Dashboard, Rooms, Guests, Reservations, Billing
 *   - Housekeeping: Dashboard, Housekeeping
 * 
 * @package    Sinead
 * @subpackage Views/Layouts
 */
-->

<?php $currentPage = $_GET['page'] ?? 'dashboard'; ?>

<!-- Inline script to prevent FOUC (flash of unstyled content) on sidebar load -->
<script>
    if (window.innerWidth > 768 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>

<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div>
            <h1>SINEAD</h1>
            <span class="brand-sub">Hotel Management</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Main Section -->
        <div class="nav-section">
            <div class="nav-section-title">Main</div>

            <a href="<?php echo url('dashboard'); ?>" 
               class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Operations Section -->
        <?php if (hasRole([ROLE_ADMIN, ROLE_RECEPTIONIST])): ?>
        <div class="nav-section">
            <div class="nav-section-title">Operations</div>

            <a href="<?php echo url('rooms'); ?>" 
               class="nav-item <?php echo $currentPage === 'rooms' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Rooms</span>
            </a>

            <a href="<?php echo url('guests'); ?>" 
               class="nav-item <?php echo $currentPage === 'guests' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Guests</span>
            </a>

            <a href="<?php echo url('reservations'); ?>" 
               class="nav-item <?php echo $currentPage === 'reservations' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Reservations</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Finance Section: Admin only -->
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <div class="nav-section">
            <div class="nav-section-title">Finance</div>

            <a href="<?php echo url('billing'); ?>" 
               class="nav-item <?php echo $currentPage === 'billing' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <span>Billing</span>
            </a>

            <a href="<?php echo url('reports'); ?>" 
               class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span>Reports</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Housekeeping Section -->
        <?php if (hasRole([ROLE_ADMIN, ROLE_HOUSEKEEPING])): ?>
        <div class="nav-section">
            <div class="nav-section-title">Maintenance</div>

            <a href="<?php echo url('housekeeping'); ?>" 
               class="nav-item <?php echo $currentPage === 'housekeeping' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
                <span>Housekeeping</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Admin Section -->
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <div class="nav-section">
            <div class="nav-section-title">Administration</div>

            <a href="<?php echo url('users'); ?>" 
               class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span>User Management</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer: User Info -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?php echo strtoupper(substr(currentUser('full_name') ?? 'U', 0, 1)); ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo sanitize(currentUser('full_name') ?? ''); ?></div>
                <div class="sidebar-user-role"><?php echo sanitize(currentUser('role') ?? ''); ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay for mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
