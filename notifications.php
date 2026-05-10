<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';

$actor = currentActor();
if ($actor['type'] === 'guest') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['mark']) && $_GET['mark'] === 'all') {
    $m = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE recipient_type=? AND recipient_id=?");
    $m->execute([$actor['type'], $actor['id']]);
    header('Location: notifications.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_type=? AND recipient_id=? ORDER BY notification_id DESC LIMIT 100");
$stmt->execute([$actor['type'], $actor['id']]);
$rows = $stmt->fetchAll();

renderHeader('Notifications');
?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Notifications</h4>
            <a class="btn btn-sm btn-outline-primary" href="notifications.php?mark=all">Mark all as read</a>
        </div>
        <hr>
        <?php if (!$rows): ?>
            <p class="text-muted mb-0">No notifications yet.</p>
        <?php else: ?>
            <?php foreach ($rows as $n): ?>
                <div class="p-3 border rounded mb-2 <?= (int)$n['is_read'] === 0 ? 'bg-light' : '' ?>">
                    <div class="d-flex justify-content-between gap-2">
                        <strong><?= htmlspecialchars($n['title']) ?></strong>
                        <small class="text-muted"><?= htmlspecialchars($n['created_at']) ?></small>
                    </div>
                    <div><?= htmlspecialchars($n['message']) ?></div>
                    <?php
                    $link = trim((string)($n['link'] ?? ''));
                    $adminOnly = str_contains($link, 'admin_dashboard');
                    if ($link !== '' && (!$adminOnly || $actor['type'] === 'admin')): ?>
                        <a href="<?= htmlspecialchars($link) ?>" class="small">Open</a>
                    <?php elseif ($link !== '' && $adminOnly): ?>
                        <span class="small text-muted">This notice is for administrators only.</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php renderFooter(); ?>
