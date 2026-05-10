<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notifications.php';

function renderHeader(string $title = 'Carrier Hunt'): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = activeUserRole();
    global $pdo;
    $actor = currentActor();
    $notifCount = isset($pdo) ? unreadNotificationCount($pdo, $actor['type'], $actor['id']) : 0;
    $avatarUrl = '';
    if (isset($pdo)) {
        try {
            if ($role === 'candidate' && !empty($_SESSION['candidate_id'])) {
                $av = $pdo->prepare('SELECT profile_photo FROM users WHERE user_id=? LIMIT 1');
                $av->execute([(int)$_SESSION['candidate_id']]);
                $avatarUrl = trim((string)($av->fetch()['profile_photo'] ?? ''));
            } elseif ($role === 'company' && !empty($_SESSION['company_id'])) {
                $av = $pdo->prepare('SELECT company_logo FROM companies WHERE company_id=? LIMIT 1');
                $av->execute([(int)$_SESSION['company_id']]);
                $avatarUrl = trim((string)($av->fetch()['company_logo'] ?? ''));
            }
        } catch (Throwable $e) {
            $avatarUrl = '';
        }
    }

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<style>
    body { background:#f4f7fb; color:#1f2937; min-height:100vh; display:flex; flex-direction:column; }
    .main-nav { background: linear-gradient(90deg, #0f172a, #1d4ed8); }
    .brand-logo { width:40px; height:40px; border-radius:12px; background:#ffffff22; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; }
    .brand-logo span { font-weight:700; color:#fff; }
    .app-shell { max-width:1240px; }
    .page-content { flex:1 0 auto; width:100%; }
    .hero-card { border:0; border-radius:22px; overflow:hidden; background: linear-gradient(130deg, #1d4ed8, #06b6d4); color:#fff; }
    .site-footer { margin-top:60px; background:#0f172a; color:#cbd5e1; flex-shrink:0; }
    .site-footer a { color:#93c5fd; text-decoration:none; }
    </style>';
    echo '</head><body>';
    echo '<nav class="navbar navbar-expand-lg navbar-dark main-nav"><div class="container-fluid app-shell px-3 px-lg-4">';
    echo '<a class="navbar-brand fw-semibold d-flex align-items-center" href="index.php"><span class="brand-logo"><span>CH</span></span>Carrier Hunt</a>';
    echo '<div class="d-flex gap-2 flex-wrap justify-content-end">';
    if ($role === 'candidate' || $role === 'guest') {
        echo '<a class="btn btn-sm btn-outline-light" href="jobs.php">Explore Jobs</a>';
    }
    if ($role !== 'guest') {
        echo '<a class="btn btn-sm btn-warning position-relative" href="notifications.php">Notifications';
        if ($notifCount > 0) {
            echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . (int)$notifCount . '</span>';
        }
        echo '</a>';
    }

    if ($role === 'candidate' && $avatarUrl !== '') {
        echo '<img src="' . htmlspecialchars($avatarUrl) . '" alt="" class="rounded-circle border border-light align-self-center" width="32" height="32" style="object-fit:cover" referrerpolicy="no-referrer">';
    }

    if ($role === 'guest') {
        echo '<a class="btn btn-sm btn-light" href="login.php">Login</a>';
        echo '<a class="btn btn-sm btn-warning" href="register.php">Register</a>';
    } elseif ($role === 'candidate') {
        echo '<a class="btn btn-sm btn-light" href="candidate_dashboard.php">Dashboard</a>';
        echo '<a class="btn btn-sm btn-outline-info" href="logout.php">Logout</a>';
    } elseif ($role === 'company') {
        echo '<a class="btn btn-sm btn-light" href="company_dashboard.php">Dashboard</a>';
        echo '<a class="btn btn-sm btn-outline-info" href="logout.php">Logout</a>';
    } else {
        echo '<a class="btn btn-sm btn-light" href="admin_dashboard.php">Admin Panel</a>';
        echo '<a class="btn btn-sm btn-outline-info" href="logout.php">Logout</a>';
    }
    echo '</div></div></nav>';
    echo '<main class="page-content"><div class="container-fluid app-shell px-3 px-lg-4 pt-4">';
}

function renderFooter(): void
{
    echo '</div></main>';
    echo '<footer class="site-footer py-4 mt-5"><div class="container-fluid app-shell px-3 px-lg-4">';
    echo '<div class="row g-3 align-items-center"><div class="col-md-6">';
    echo '<h6 class="mb-1">Carrier Hunt</h6><small>Quiz-first hiring and grooming platform for quality placements.</small>';
    echo '</div><div class="col-md-6 text-md-end">';
    echo '<small>Email: support@carrierhunt.local | Built for Web Lab Project</small>';
    echo '</div></div></div></footer>';
    echo '</body></html>';
}
?>
