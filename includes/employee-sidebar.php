<aside class="sidebar" data-sidebar>
    <a class="brand" href="<?= e(base_url('employee/dashboard.php')) ?>"><span class="brand-mark">N</span><span>northstar<small>people operations</small></span></a>
    <div class="sidebar-label">My workspace</div>
    <nav class="sidebar-nav">
        <a href="<?= e(base_url('employee/dashboard.php')) ?>">&#9632; <span>Overview</span></a>
        <a href="<?= e(base_url('employee/apply-leave.php')) ?>">&#43; <span>Apply for leave</span></a>
        <a href="<?= e(base_url('employee/leave-history.php')) ?>">&#9776; <span>Leave history</span></a>
        <a href="<?= e(base_url('employee/notifications.php')) ?>">&#9673; <span>Notifications</span></a>
    </nav>
    <div class="sidebar-label">Account</div>
    <nav class="sidebar-nav"><a href="<?= e(base_url('employee/profile.php')) ?>">&#9786; <span>My profile</span></a></nav>
    <div class="sidebar-footer"><div class="sidebar-note"><strong>Need help?</strong><span>Talk to People & Culture</span></div><a href="<?= e(base_url('logout.php')) ?>" class="logout-link">&#8594; <span>Sign out</span></a></div>
</aside>
