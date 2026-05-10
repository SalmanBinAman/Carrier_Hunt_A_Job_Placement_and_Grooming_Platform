<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
requireAdmin();

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0 || !in_array($type, ['candidate', 'company'], true)) {
    header('Location: admin_dashboard.php');
    exit;
}

if ($type === 'candidate') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        header('Location: admin_dashboard.php');
        exit;
    }
    renderHeader('Candidate details');
    $photo = trim((string)($row['profile_photo'] ?? ''));
    if ($photo !== '') {
        echo '<div class="mb-3"><img src="' . htmlspecialchars($photo) . '" alt="" class="rounded border" style="max-width:180px;max-height:180px;object-fit:cover"></div>';
    }
    echo '<div class="card border-0 shadow-sm"><div class="card-body p-4">';
    echo '<h4>' . htmlspecialchars($row['name']) . '</h4>';
    echo '<p class="text-muted mb-0">User #' . (int)$row['user_id'] . ' · Registered ' . htmlspecialchars($row['created_at'] ?? '') . '</p>';
    echo '<hr><dl class="row mb-0">';
    $fields = array_keys($row);
    foreach ($fields as $k) {
        if ($k === 'password') {
            continue;
        }
        $v = $row[$k];
        $disp = ($v === null || $v === '') ? '—' : nl2br(htmlspecialchars((string)$v));
        echo '<dt class="col-sm-3 text-muted">' . htmlspecialchars(str_replace('_', ' ', $k)) . '</dt>';
        echo '<dd class="col-sm-9">' . $disp . '</dd>';
    }
    echo '</dl></div></div>';
    echo '<a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to admin</a>';
    renderFooter();
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM companies WHERE company_id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: admin_dashboard.php');
    exit;
}
renderHeader('Company details');
$logo = trim((string)($row['company_logo'] ?? ''));
if ($logo !== '') {
    echo '<div class="mb-3"><img src="' . htmlspecialchars($logo) . '" alt="" class="rounded border bg-white p-2" style="max-width:200px;max-height:200px;object-fit:contain"></div>';
}
echo '<div class="card border-0 shadow-sm"><div class="card-body p-4">';
echo '<h4>' . htmlspecialchars($row['company_name']) . '</h4>';
echo '<p class="text-muted mb-0">Company #' . (int)$row['company_id'] . '</p>';
echo '<hr><dl class="row mb-0">';
foreach (array_keys($row) as $k) {
    if ($k === 'password') {
        continue;
    }
    $v = $row[$k];
    $disp = ($v === null || $v === '') ? '—' : nl2br(htmlspecialchars((string)$v));
    echo '<dt class="col-sm-3 text-muted">' . htmlspecialchars(str_replace('_', ' ', $k)) . '</dt>';
    echo '<dd class="col-sm-9">' . $disp . '</dd>';
}
echo '</dl></div></div>';
echo '<a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to admin</a>';
renderFooter();
