<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';
requireCandidate();

$userId = (int)$_SESSION['candidate_id'];
$jobId = (int)($_GET['job_id'] ?? 0);

$profileStmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?');
$profileStmt->execute([$userId]);
$profile = $profileStmt->fetch();
$missingFields = $profile ? candidateQuizRequirementsMissingFields($profile) : [];
if (!$profile || count($missingFields) > 0) {
    renderHeader('Profile Required');
    echo '<div class="alert alert-warning">Complete every field marked <strong>Required for quiz</strong> on your profile before applying.</div>';
    if (count($missingFields) > 0) {
        echo '<div class="alert alert-info">Missing profile items: ' . htmlspecialchars(implode(', ', $missingFields)) . '</div>';
    }
    echo '<a href="candidate_profile.php" class="btn btn-primary">Open profile</a>';
    renderFooter();
    exit;
}

if (!isset($_SESSION['last_passed_job']) || (int)$_SESSION['last_passed_job'] !== $jobId) {
    header("Location: take_quiz.php?job_id={$jobId}");
    exit;
}

$score = (int)($_SESSION['last_quiz_score'] ?? 0);
$check = $pdo->prepare("SELECT application_id FROM applications WHERE user_id=? AND job_id=?");
$check->execute([$userId, $jobId]);
if ($check->fetch()) {
    $msg = 'Already applied to this job.';
} else {
    $stmt = $pdo->prepare("INSERT INTO applications (user_id, job_id, quiz_score, status, applied_at) VALUES (?,?,?,'Applied',NOW())");
    $stmt->execute([$userId, $jobId, $score]);
    $ownerStmt = $pdo->prepare("SELECT company_id FROM jobs WHERE job_id=?");
    $ownerStmt->execute([$jobId]);
    $owner = $ownerStmt->fetch();
    if ($owner) {
        pushNotification($pdo, 'company', (int)$owner['company_id'], 'New application received', 'A candidate applied for Job #' . $jobId, 'company_dashboard.php');
    }

    $attempt = $pdo->prepare("INSERT INTO quiz_attempts (user_id, job_id, score, passed, attempted_at) VALUES (?,?,?,?,NOW())");
    $attempt->execute([$userId, $jobId, $score, 1]);
    $msg = 'Application submitted successfully.';
}

renderHeader('Apply Job');
?>
<div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<a href="candidate_dashboard.php" class="btn btn-primary">Go Dashboard</a>
<?php renderFooter(); ?>
