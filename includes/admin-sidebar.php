<aside class="sidebar" data-sidebar>
    <a class="brand" href="<?= e(base_url('admin/dashboard.php')) ?>"><span class="brand-mark">N</span><span>northstar<small>people operations</small></span></a>
    <div class="sidebar-label">Administration</div>
    <nav class="sidebar-nav">
        <a href="<?= e(base_url('admin/dashboard.php')) ?>">&#9632; <span>Overview</span></a>
        <a href="<?= e(base_url('admin/leaves.php')) ?>">&#10003; <span>Leave requests</span></a>
        <a href="<?= e(base_url('admin/employees.php')) ?>">&#9786; <span>Employees</span></a>
        <a href="<?= e(base_url('admin/leave-types.php')) ?>">&#9672; <span>Leave types</span></a>
        <a href="<?= e(base_url('admin/reports.php')) ?>">&#9644; <span>Reports</span></a>
    </nav>
    <div class="sidebar-label">Account</div>
    <nav class="sidebar-nav">
        <a href="<?= e(base_url('admin/notifications.php')) ?>">&#9673; <span>Notifications</span></a>
        <a href="<?= e(base_url('admin/profile.php')) ?>">&#9881; <span>Settings</span></a>
    </nav>
    <div class="sidebar-footer"><div class="sidebar-note"><strong>System status</strong><span class="status-dot">All services operational</span></div><a href="<?= e(base_url('logout.php')) ?>" class="logout-link">&#8594; <span>Sign out</span></a></div>
</aside>
