<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
requireCandidate();

$userId = (int)$_SESSION['candidate_id'];
$userRowStmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?');
$userRowStmt->execute([$userId]);
$userRow = $userRowStmt->fetch();
$profileDone = $userRow && candidateQuizRequirementsMet($userRow);

$stmt = $pdo->prepare("SELECT a.*, j.job_title FROM applications a JOIN jobs j ON j.job_id=a.job_id WHERE a.user_id=? ORDER BY a.application_id DESC");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

renderHeader('Candidate Dashboard');
?>
<div class="card border-0 shadow-sm mb-3">
<div class="card-body p-4">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Welcome, <?= htmlspecialchars($_SESSION['candidate_name']) ?></h4>
    <div>
        <a href="candidate_profile.php" class="btn btn-dark btn-sm">My Profile/CV</a>
        <a href="jobs.php" class="btn btn-primary btn-sm">Browse Jobs</a>
        <a href="grooming.php" class="btn btn-warning btn-sm">Grooming</a>
    </div>
</div>
</div>
</div>

<?php if (!$profileDone): ?>
<div class="alert alert-warning">
    Complete every field marked <strong>Required for quiz</strong> on <a href="candidate_profile.php">My profile</a> before taking quizzes or applying.
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h5>Your Applications</h5>
        <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Job</th><th>Quiz Score</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($applications as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['job_title']) ?></td>
                    <td><?= (int)$a['quiz_score'] ?></td>
                    <td><?= htmlspecialchars($a['status']) ?></td>
                    <td><?= htmlspecialchars($a['applied_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
