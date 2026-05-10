<?php
function currentActor(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['is_admin'])) {
        return ['type' => 'admin', 'id' => 0];
    }
    if (!empty($_SESSION['company_id'])) {
        return ['type' => 'company', 'id' => (int)$_SESSION['company_id']];
    }
    if (!empty($_SESSION['candidate_id'])) {
        return ['type' => 'candidate', 'id' => (int)$_SESSION['candidate_id']];
    }
    return ['type' => 'guest', 'id' => 0];
}

function pushNotification(PDO $pdo, string $recipientType, int $recipientId, string $title, string $message, string $link = ''): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (recipient_type, recipient_id, title, message, link, is_read, created_at) VALUES (?,?,?,?,?,0,NOW())"
    );
    $stmt->execute([$recipientType, $recipientId, trim($title), trim($message), trim($link)]);
}

function unreadNotificationCount(PDO $pdo, string $role, int $id): int
{
    if ($role === 'guest') {
        return 0;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM notifications WHERE recipient_type=? AND recipient_id=? AND is_read=0");
    $stmt->execute([$role, $id]);
    return (int)($stmt->fetch()['c'] ?? 0);
}
?>
