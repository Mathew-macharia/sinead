<!--
/**
 * Layout: Top Header Bar
 * 
 * Renders the top navigation bar with page title,
 * date/time display, and user action buttons.
 * 
 * @package    Sinead
 * @subpackage Views/Layouts
 */
-->

<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        <?php if (!empty($pageSubtitle)): ?>
            <span class="page-subtitle"><?php echo sanitize($pageSubtitle); ?></span>
        <?php endif; ?>
    </div>

    <div class="header-right">
        <div class="header-datetime" id="headerDateTime">
            <div id="currentDate"><?php echo date('l, d M Y'); ?></div>
            <div id="currentTime" style="color: var(--accent-gold); font-weight: 500;"><?php echo date('H:i'); ?></div>
        </div>

        <a href="<?php echo url('logout'); ?>" class="header-action" data-tooltip="Sign Out">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
        </a>
    </div>
</header>
