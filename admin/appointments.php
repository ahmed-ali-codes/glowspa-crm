<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/storage.php';

// ── Quick status update from list view ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    update_appointment_status($_POST['appointment_id'], $_POST['status']);
    header('Location: appointments.php');
    exit;
}

// ── Action buttons from detail view ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_button'])) {
    $id     = $_POST['appointment_id'];
    $action = $_POST['action_button'];

    if ($action === 'delete') {
        delete_appointment($id);
        header('Location: appointments.php');
        exit;
    } else {
        update_appointment_status($id, $action);
        header('Location: appointments.php?id=' . $id);
        exit;
    }
}

// ── Badge helpers ──
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

function membership_badge_html($ms) {
    switch ($ms) {
        case 'VIP':       return '<span class="badge badge-vip">⭐ VIP</span>';
        case 'Regular':   return '<span class="badge badge-regular">Regular</span>';
        case 'Corporate': return '<span class="badge badge-corporate">Corporate</span>';
        default:          return '<span class="badge badge-walkin">Walk-in</span>';
    }
}

// ══════════════════════════════════════════════════════════════
// DETAIL VIEW — single booking
// ══════════════════════════════════════════════════════════════
$view_id = $_GET['id'] ?? null;

if ($view_id) {
    $b = get_appointment($view_id);
    if (!$b) {
        die('<p style="padding: 2rem; font-family: sans-serif;">Booking not found.</p>');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #<?php echo $b['id']; ?> | GlowSpa CRM</title>
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

    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="sidebar-logo">
            <span class="logo-symbol">✿</span>
            <span>GlowSpa</span>
        </a>
        <div class="sidebar-tagline">Spa &amp; Wellness CRM</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><span class="nav-icon">◈</span> Dashboard</a>
            <a href="appointments.php" class="active"><span class="nav-icon">◇</span> Bookings</a>
            <a href="../index.php" target="_blank"><span class="nav-icon">✦</span> Booking Page</a>
            <a href="logout.php"><span class="nav-icon">↩</span> Logout</a>
        </nav>
        <div class="sidebar-footer">&copy; <?php echo date('Y'); ?> GlowSpa CRM</div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <div class="flex align-center gap-3">
                <a href="appointments.php" class="btn btn-sm btn-secondary">&larr; Back</a>
                <h2>Booking #<?php echo $b['id']; ?></h2>
                <span class="badge <?php echo get_status_badge_class($b['status']); ?>">
                    <?php echo htmlspecialchars($b['status']); ?>
                </span>
                <?php echo membership_badge_html($b['membership_status']); ?>
            </div>
            <div class="user-badge">Admin</div>
        </header>

        <div class="card">
            <div class="detail-grid">
                <!-- Client Info -->
                <div>
                    <div class="section-heading">Client Information</div>
                    <table class="detail-table">
                        <tr>
                            <th>Name</th>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($b['client_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($b['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($b['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Membership</th>
                            <td><?php echo membership_badge_html($b['membership_status']); ?></td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td style="color: var(--text-muted); font-size: 0.88rem;"><?php echo htmlspecialchars($b['created_at']); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Session Details -->
                <div>
                    <div class="section-heading">Session Details</div>
                    <table class="detail-table">
                        <tr>
                            <th>Service</th>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($b['service_category']); ?></td>
                        </tr>
                        <tr>
                            <th>Therapist</th>
                            <td style="color: var(--rose-gold); font-weight: 600;"><?php echo htmlspecialchars($b['therapist']); ?></td>
                        </tr>
                        <tr>
                            <th>Duration</th>
                            <td><?php echo htmlspecialchars($b['duration']); ?></td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td style="font-weight: 600;"><?php echo date('F j, Y', strtotime($b['appointment_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Time</th>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($b['appointment_time']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Special Requests -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <div class="section-heading">Special Requests / Notes</div>
                <div class="notes-box">
                    <?php
                    if (!empty($b['special_requests'])) {
                        echo nl2br(htmlspecialchars($b['special_requests']));
                    } else {
                        echo '<em style="color: var(--text-muted);">No special requests provided.</em>';
                    }
                    ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-btn-group" style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="appointment_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" name="action_button" value="Confirmed" class="btn btn-primary" style="background: #2E86C1;">
                        ✓ Confirm
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="appointment_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" name="action_button" value="In Progress" class="btn btn-primary" style="background: #1ABC9C;">
                        ↻ In Progress
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="appointment_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" name="action_button" value="Completed" class="btn btn-success">
                        ✔ Complete
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="appointment_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" name="action_button" value="Cancelled" class="btn btn-warning">
                        ✕ Cancel
                    </button>
                </form>
                <form method="POST" style="display: inline; margin-left: auto;"
                      onsubmit="return confirm('Delete this booking permanently?');">
                    <input type="hidden" name="appointment_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" name="action_button" value="delete" class="btn btn-danger">
                        🗑 Delete
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
(function() {
    var toggle  = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
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
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        link.addEventListener('click', function() { if (window.innerWidth <= 768) closeSidebar(); });
    });
})();
</script>

</body>
</html>
<?php
    exit;
}

// ══════════════════════════════════════════════════════════════
// LIST VIEW — paginated bookings with filters
// ══════════════════════════════════════════════════════════════
$filter_date     = $_GET['date']             ?? null;
$filter_service  = $_GET['service_category'] ?? null;
$filter_therapist= $_GET['therapist']        ?? null;
$filter_membership = $_GET['membership']     ?? null;
$filter_status   = $_GET['status']           ?? null;

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;

$all_bookings  = get_appointments($filter_date, $filter_service, $filter_therapist, $filter_membership, $filter_status);
$total         = count($all_bookings);
$total_pages   = ceil($total / $per_page);
$offset        = ($page - 1) * $per_page;
$bookings_page = array_slice($all_bookings, $offset, $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | GlowSpa CRM</title>
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
            <a href="dashboard.php"><span class="nav-icon">◈</span> Dashboard</a>
            <a href="appointments.php" class="active"><span class="nav-icon">◇</span> Bookings</a>
            <a href="../index.php" target="_blank"><span class="nav-icon">✦</span> Booking Page</a>
            <a href="logout.php"><span class="nav-icon">↩</span> Logout</a>
        </nav>
        <div class="sidebar-footer">&copy; <?php echo date('Y'); ?> GlowSpa CRM</div>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <h2>Manage Bookings</h2>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 0.82rem; color: var(--text-muted);">
                    <?php echo number_format($total); ?> booking<?php echo $total !== 1 ? 's' : ''; ?> found
                </span>
                <div class="user-badge">Admin</div>
            </div>
        </header>

        <!-- ── Filters ── -->
        <form class="filter-form" method="GET" id="filter-form">
            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control"
                       value="<?php echo htmlspecialchars($filter_date ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Service Category</label>
                <select name="service_category" class="form-control">
                    <option value="">All Services</option>
                    <?php
                    $cats = ['Facial','Massage','Nail Care','Waxing','Body Wrap','Hair Treatment','Couple Spa','Other'];
                    foreach ($cats as $c) {
                        $sel = $filter_service === $c ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($c) . "\" $sel>" . htmlspecialchars($c) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Therapist</label>
                <select name="therapist" class="form-control">
                    <option value="">All Therapists</option>
                    <?php
                    global $therapists;
                    foreach ($therapists as $t) {
                        $sel = $filter_therapist === $t ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($t) . "\" $sel>" . htmlspecialchars($t) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Membership</label>
                <select name="membership" class="form-control">
                    <option value="">All Members</option>
                    <?php
                    $memberships = ['Walk-in','Regular','VIP','Corporate'];
                    foreach ($memberships as $m) {
                        $sel = $filter_membership === $m ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($m) . "\" $sel>" . htmlspecialchars($m) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php
                    $statuses = ['Pending','Confirmed','In Progress','Completed','Cancelled'];
                    foreach ($statuses as $s) {
                        $sel = $filter_status === $s ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($s) . "\" $sel>" . htmlspecialchars($s) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group" style="flex: 0 0 auto; display: flex; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="height: 48px;">Filter</button>
                <a href="appointments.php" class="btn btn-secondary" style="height: 48px;">Reset</a>
            </div>
        </form>

        <!-- ── Table ── -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="table-container">
                <table style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="min-width: 180px;">Client</th>
                            <th style="min-width: 120px;">Service</th>
                            <th style="min-width: 120px;">Therapist</th>
                            <th>Duration</th>
                            <th>Membership</th>
                            <th style="min-width: 110px;">Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings_page)): ?>
                            <tr>
                                <td colspan="10" class="text-center" style="padding: 3.5rem; color: var(--text-muted);">
                                    No bookings match your filters.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings_page as $b): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;">#<?php echo $b['id']; ?></td>
                                    <td style="font-weight: 600;">
                                        <?php echo htmlspecialchars($b['client_name']); ?>
                                        <br>
                                        <small style="color: var(--text-muted); font-weight: 400;">
                                            <?php echo htmlspecialchars($b['phone']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($b['service_category']); ?></td>
                                    <td style="color: var(--text-muted); font-size: 0.88rem;"><?php echo htmlspecialchars($b['therapist']); ?></td>
                                    <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($b['duration']); ?></td>
                                    <td><?php echo membership_badge_html($b['membership_status']); ?></td>
                                    <td style="font-size: 0.88rem; white-space: nowrap;">
                                        <?php echo date('M j, Y', strtotime($b['appointment_date'])); ?>
                                    </td>
                                    <td style="font-size: 0.88rem; white-space: nowrap;">
                                        <?php echo htmlspecialchars($b['appointment_time']); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo get_status_badge_class($b['status']); ?>">
                                            <?php echo htmlspecialchars($b['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="appointments.php?id=<?php echo $b['id']; ?>"
                                           class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Pagination ── -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                        $query        = $_GET;
                        $query['page'] = $i;
                        $qs           = http_build_query($query);
                        $is_active    = $i === $page;
                    ?>
                    <a href="?<?php echo $qs; ?>"
                       class="<?php echo $is_active ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<script>
(function() {
    var toggle  = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
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
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        link.addEventListener('click', function() { if (window.innerWidth <= 768) closeSidebar(); });
    });
})();
</script>

</body>
</html>
