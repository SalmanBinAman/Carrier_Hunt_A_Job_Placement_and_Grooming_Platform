<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';
requireCompany();

$companyId = (int)$_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['new_status'])) {
    $applicationId = (int)$_POST['application_id'];
    $newStatus = trim($_POST['new_status']);
    $allowed = ['Applied', 'Shortlisted', 'Rejected', 'Interview Scheduled'];
    if (in_array($newStatus, $allowed, true)) {
        $statusText = $newStatus;
        if ($newStatus === 'Interview Scheduled' && !empty($_POST['interview_at'])) {
            $statusText .= ' - ' . trim($_POST['interview_at']);
        }
        $update = $pdo->prepare("UPDATE applications a JOIN jobs j ON j.job_id=a.job_id SET a.status=? WHERE a.application_id=? AND j.company_id=?");
        $update->execute([$statusText, $applicationId, $companyId]);

        $owner = $pdo->prepare("SELECT user_id FROM applications WHERE application_id=?");
        $owner->execute([$applicationId]);
        $row = $owner->fetch();
        if ($row) {
            pushNotification($pdo, 'candidate', (int)$row['user_id'], 'Application update', 'Your application status is now: ' . $statusText, 'candidate_dashboard.php');
        }
    }
}

$jobsStmt = $pdo->prepare("SELECT * FROM jobs WHERE company_id=? ORDER BY job_id DESC");
$jobsStmt->execute([$companyId]);
$jobs = $jobsStmt->fetchAll();

renderHeader('Company Dashboard');
?>
<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4><?= htmlspecialchars($_SESSION['company_name']) ?> Dashboard</h4>
    <div>
        <a href="company_update_profile.php" class="btn btn-outline-secondary btn-sm">Update company info</a>
        <a href="post_job.php" class="btn btn-success btn-sm">Post Job</a>
        <a href="create_quiz.php" class="btn btn-primary btn-sm">Create Quiz</a>
    </div>
</div>
</div></div>

<?php foreach ($jobs as $job): ?>
<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
    <h5><?= htmlspecialchars($job['job_title']) ?></h5>
    <p class="text-secondary"><?= htmlspecialchars($job['description']) ?></p>
    <p><strong>Skills:</strong> <?= htmlspecialchars($job['required_skills']) ?></p>
    <a class="btn btn-sm btn-outline-primary" href="create_quiz.php?job_id=<?= (int)$job['job_id'] ?>">Manage Quiz</a>
    <hr>
    <h6>Applicants</h6>
    <?php
    $aStmt = $pdo->prepare("SELECT a.*, u.name, u.email, u.professional_headline, u.hard_skills, u.soft_skills, u.summary FROM applications a JOIN users u ON u.user_id=a.user_id WHERE a.job_id=? ORDER BY a.application_id DESC");
    $aStmt->execute([$job['job_id']]);
    $apps = $aStmt->fetchAll();
    ?>
    <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
        <thead><tr><th>Candidate</th><th>CV Snapshot</th><th>Score</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($apps as $app): ?>
            <tr>
                <td><?= htmlspecialchars($app['name']) ?><br><small><?= htmlspecialchars($app['email']) ?></small></td>
                <td><small><?= htmlspecialchars($app['professional_headline']) ?><br><?= htmlspecialchars($app['hard_skills'] . ' | ' . $app['soft_skills']) ?></small></td>
                <td><?= (int)$app['quiz_score'] ?></td>
                <td><?= htmlspecialchars($app['status']) ?></td>
                <td>
                    <form method="post" class="d-flex gap-1 flex-wrap">
                        <input type="hidden" name="application_id" value="<?= (int)$app['application_id'] ?>">
                        <select class="form-select form-select-sm" name="new_status">
                            <option value="Shortlisted">Shortlisted</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Interview Scheduled">Interview Scheduled</option>
                        </select>
                        <input class="form-control form-control-sm" name="interview_at" placeholder="Interview time/details">
                        <button class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p class="text-muted mb-0">Phase 1 note: interview scheduling notifications are not fully implemented yet.</p>
</div></div>
<?php endforeach; ?>
<?php renderFooter(); ?>
