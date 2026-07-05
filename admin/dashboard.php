<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/storage.php';

// Handle quick status updates via AJAX or form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id     = $_POST['appointment_id'];
    $status = $_POST['status'];
    update_appointment_status($id, $status);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    header('Location: dashboard.php');
    exit;
}

$today = date('Y-m-d');

// ── Stat card figures ──
$todays_sessions   = count_appointments($today);
$pending_bookings  = count_appointments(null, 'Pending');
$vip_this_month    = count_appointments(null, null, null, true, 'VIP');
$total_sessions    = count_appointments();

// ── Charts ──
$all_appointments        = get_appointments();
$service_category_counts = count_by_service_category();
$total_for_pct           = array_sum($service_category_counts) ?: 1;
$therapist_counts        = count_by_therapist_today();

// ── Recent bookings (last 10) ──
$recent = array_slice(array_reverse($all_appointments), 0, 10);

function get_status_badge_class($status) {
    switch (strtolower($status)) {
        case 'pending':     return 'badge-pending';
        case 'confirmed':   return 'badge-confirmed';
        case 'in progress': return 'badge-inprogress';
        case 'completed':   return 'badge-completed';
        case 'cancelled':   return 'badge-cancelled';
        default:            return 'badge-pending';
    }
}

function get_membership_badge($ms) {
    switch ($ms) {
        case 'VIP':       return '<span class="badge badge-vip">⭐ VIP</span>';
        case 'Regular':   return '<span class="badge badge-regular">Regular</span>';
        case 'Corporate': return '<span class="badge badge-corporate">Corporate</span>';
        default:          return '<span class="badge badge-walkin">Walk-in</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6D3B47">
    <title>Dashboard | GlowSpa CRM</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<!-- Mobile Top Bar -->
<div class="mobile-topbar">
    <a href="dashboard.php" class="mobile-topbar-logo">
        <span class="logo-symbol">✿</span>
        <span>GlowSpa</span>
    </a>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation" aria-expanded="false">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </button>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="sidebar-logo">
            <span class="logo-symbol">✿</span>
            <span>GlowSpa</span>
        </a>
        <div class="sidebar-tagline">Spa &amp; Wellness CRM</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">
                <span class="nav-icon">◈</span> Dashboard
            </a>
            <a href="appointments.php">
                <span class="nav-icon">◇</span> Bookings
            </a>
            <a href="../index.php" target="_blank">
                <span class="nav-icon">✦</span> Booking Page
            </a>
            <a href="logout.php">
                <span class="nav-icon">↩</span> Logout
            </a>
        </nav>
        <div class="sidebar-footer">
            &copy; <?php echo date('Y'); ?> GlowSpa CRM
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h2>Dashboard</h2>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 0.82rem; color: var(--text-muted);">
                    <?php echo date('l, F j, Y'); ?>
                </span>
                <div class="user-badge">Admin</div>
            </div>
        </header>

        <!-- ── Stat Cards ── -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">🌸</span>
                <h3><?php echo number_format($todays_sessions); ?></h3>
                <p>Today's Sessions</p>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⏳</span>
                <h3><?php echo number_format($pending_bookings); ?></h3>
                <p>Pending Bookings</p>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⭐</span>
                <h3><?php echo number_format($vip_this_month); ?></h3>
                <p>VIP Clients This Month</p>
            </div>
            <div class="stat-card">
                <span class="stat-icon">✿</span>
                <h3><?php echo number_format($total_sessions); ?></h3>
                <p>Total Sessions</p>
            </div>
        </div>

        <!-- ── Charts Row ── -->
        <div class="chart-row-flex" style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">

            <!-- Service Category Chart -->
            <div class="chart-container" style="flex: 1; min-width: 300px;">
                <h3>Sessions by Service Category</h3>
                <?php if (empty($service_category_counts)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No booking data yet. Sessions will appear here after the first booking.</p>
                <?php else: ?>
                    <?php foreach ($service_category_counts as $cat => $count): ?>
                        <?php $pct = round(($count / $total_for_pct) * 100); ?>
                        <div class="chart-bar-wrap">
                            <div class="chart-label">
                                <span><?php echo htmlspecialchars($cat); ?></span>
                                <span><?php echo $count; ?> (<?php echo $pct; ?>%)</span>
                            </div>
                            <div class="chart-bar-bg">
                                <div class="chart-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Therapist Schedule Today -->
            <div class="chart-container" style="flex: 1; min-width: 300px;">
                <h3>Therapist Schedule — Today</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Therapist</th>
                                <th>Sessions Today</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($therapist_counts as $therapist => $count): ?>
                                <?php
                                    $is_busy      = $count >= 4;
                                    $status_label = $is_busy ? 'Busy' : ($count > 0 ? 'Active' : 'Available');
                                    $status_class = $is_busy ? 'badge-cancelled' : ($count > 0 ? 'badge-confirmed' : 'badge-completed');
                                ?>
                                <tr>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($therapist); ?></td>
                                    <td>
                                        <?php echo $count; ?>
                                        <?php if ($count > 0): ?>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);">session<?php echo $count !== 1 ? 's' : ''; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ── Recent Bookings ── -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="recent-bookings-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="margin: 0; font-size: 1.2rem;">Recent Bookings</h3>
                <a href="appointments.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-container">
                <table style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="min-width: 180px;">Client</th>
                            <th style="min-width: 120px;">Service</th>
                            <th style="min-width: 120px;">Therapist</th>
                            <th style="min-width: 100px;">Membership</th>
                            <th style="min-width: 110px;">Date</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 3rem; color: var(--text-muted);">
                                    No bookings yet. <a href="../index.php">Make the first booking</a>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent as $b): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;">#<?php echo $b['id']; ?></td>
                                    <td style="font-weight: 600;">
                                        <?php echo htmlspecialchars($b['client_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($b['service_category']); ?></td>
                                    <td style="color: var(--text-muted); font-size: 0.88rem;"><?php echo htmlspecialchars($b['therapist']); ?></td>
                                    <td><?php echo get_membership_badge($b['membership_status']); ?></td>
                                    <td style="font-size: 0.88rem;"><?php echo date('M j, Y', strtotime($b['appointment_date'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo get_status_badge_class($b['status']); ?>">
                                            <?php echo htmlspecialchars($b['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $b['id']; ?>">
                                            <select name="status" class="form-control"
                                                    style="padding: 0.3rem 0.6rem; font-size: 0.82rem; width: auto; border-radius: 8px;"
                                                    onchange="updateStatusAJAX(this)">
                                                <option value="">Update…</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Confirmed">Confirmed</option>
                                                <option value="In Progress">In Progress</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
/* ── Hamburger / Sidebar Toggle ── */
(function() {
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar || !overlay) return;

    function openSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggle.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function() {
        sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // Close sidebar when a nav link is tapped on mobile
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
})();

/* ── AJAX Status Update ── */
function updateStatusAJAX(selectElement) {
    const form     = selectElement.closest('form');
    const formData = new FormData(form);

    selectElement.disabled = true;
    selectElement.style.opacity = '0.5';

    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        selectElement.disabled = false;
        selectElement.style.opacity = '1';

        if (data.success) {
            const tr    = selectElement.closest('tr');
            const badge = tr ? tr.querySelector('.badge') : null;
            if (badge) {
                const val = selectElement.value.toLowerCase();
                badge.textContent = selectElement.value;
                badge.className   = 'badge';
                if (val === 'pending')     badge.classList.add('badge-pending');
                if (val === 'confirmed')   badge.classList.add('badge-confirmed');
                if (val === 'in progress') badge.classList.add('badge-inprogress');
                if (val === 'completed')   badge.classList.add('badge-completed');
                if (val === 'cancelled')   badge.classList.add('badge-cancelled');
            }
            selectElement.value = '';
        }
    })
    .catch(() => {
        selectElement.disabled = false;
        selectElement.style.opacity = '1';
    });
}
</script>

</body>
</html>
