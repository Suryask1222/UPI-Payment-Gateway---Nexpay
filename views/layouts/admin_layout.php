<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title ?? 'Admin Panel') ?> - <?= h(SITE_NAME) ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <script>

        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const BASE_URL = '<?= BASE_URL ?>';
    </script>

    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-wrapper">
                <div class="brand">
                    <i class="fas fa-wallet" style="color: var(--primary);"></i>
                    <span>Nex Pay</span>
                </div>

                <div class="menu-label">Main Menu</div>
                <nav style="flex: 1;">
                    <ul class="nav-menu">
                        <li>
                            <a href="<?= BASE_URL ?>/admin_dashboard.php"
                                class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                                <i class="fas fa-chart-pie"></i>
                                <span>Overview</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/admin_transactions.php"
                                class="nav-link <?= ($activePage ?? '') === 'transactions' ? 'active' : '' ?>">
                                <i class="fas fa-shield-halved"></i>
                                <span>Verifications</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/admin_payment_links.php"
                                class="nav-link <?= ($activePage ?? '') === 'payment-links' ? 'active' : '' ?>">
                                <i class="fas fa-link"></i>
                                <span>Payment Links</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/admin_upi.php"
                                class="nav-link <?= ($activePage ?? '') === 'upi' ? 'active' : '' ?>">
                                <i class="fas fa-rotate"></i>
                                <span>UPI Shuffler</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="menu-label" style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 1.25rem;">Preferences</div>
                <div class="preference-row"
                    style="padding: 0.5rem 0.75rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <span style="font-size: 0.85rem; color: var(--text-sidebar); font-weight: 500;">Dark Mode</span>
                    <label class="theme-switch">
                        <input type="checkbox" id="theme-toggle-input">
                        <span class="switch-slider"></span>
                    </label>
                </div>

                <div class="sidebar-footer">
                    <a href="<?= BASE_URL ?>/index.php?route=/admin/logout" class="nav-link"
                        style="color: var(--danger); padding: 0.75rem 0.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="main-content">
            <!-- Header bar -->
            <header class="topbar">
                <div class="page-title"><?= h($title ?? 'Control Center') ?></div>
                <div class="topbar-actions">
                    <span
                        style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); display: inline-flex; align-items: center; gap: 8px; background-color: var(--bg-surface-hover); padding: 6px 14px; border-radius: 50px; border: 1px solid var(--border-color);">
                        <i class="far fa-user-circle" style="color: var(--primary);"></i>
                        <?= h($_SESSION['admin_user'] ?? 'Administrator') ?>
                    </span>
                </div>
            </header>

            <!-- Page Body Render -->
            <main class="content-body">
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Script assets -->
    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
</body>

</html>