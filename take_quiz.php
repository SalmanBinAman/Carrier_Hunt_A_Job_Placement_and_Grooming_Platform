<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';
requireCandidate();

$userId = (int)$_SESSION['candidate_id'];
$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);

$profileStmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?');
$profileStmt->execute([$userId]);
$profileRow = $profileStmt->fetch();
$missingFields = $profileRow ? candidateQuizRequirementsMissingFields($profileRow) : [];
if (!$profileRow || count($missingFields) > 0) {
    renderHeader('Profile Required');
    echo '<div class="alert alert-warning">Complete every field marked <strong>Required for quiz</strong> on your profile before taking a job quiz.</div>';
    if (count($missingFields) > 0) {
        echo '<div class="alert alert-info">Missing profile items: ' . htmlspecialchars(implode(', ', $missingFields)) . '</div>';
    }
    echo '<a href="candidate_profile.php" class="btn btn-primary">Open profile</a>';
    renderFooter();
    exit;
}

$quizStmt = $pdo->prepare("SELECT * FROM quiz WHERE job_id = ?");
$quizStmt->execute([$jobId]);
$quiz = $quizStmt->fetch();

if (!$quiz) {
    renderHeader('Quiz Not Found');
    echo '<div class="alert alert-danger">No quiz set for this job yet.</div>';
    renderFooter();
    exit;
}

$qStmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY RAND()');
$qStmt->execute([$quiz['quiz_id']]);
$questions = $qStmt->fetchAll();
if (count($questions) === 0) {
    renderHeader('Quiz not ready');
    echo '<div class="alert alert-warning">This job quiz has no questions yet. Please try again later or contact the employer.</div>';
    echo '<a href="jobs.php" class="btn btn-primary">Back to jobs</a>';
    renderFooter();
    exit;
}
$ruleStmt = $pdo->prepare("SELECT category_name, min_correct FROM quiz_category_rules WHERE quiz_id=?");
$ruleStmt->execute([$quiz['quiz_id']]);
$categoryRules = $ruleStmt->fetchAll();

$attemptCheck = $pdo->prepare("SELECT attempt_id FROM quiz_attempts WHERE user_id=? AND job_id=? LIMIT 1");
$attemptCheck->execute([$userId, $jobId]);
if ($attemptCheck->fetch()) {
    renderHeader('Quiz Locked');
    echo '<div class="alert alert-warning">You already attempted this quiz once. Please continue to grooming page.</div>';
    echo '<a href="grooming.php?job_id=' . $jobId . '" class="btn btn-warning">Go to Grooming</a>';
    renderFooter();
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionsPost = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ?');
    $questionsPost->execute([$quiz['quiz_id']]);
    $baseQuestions = $questionsPost->fetchAll();
    if (count($baseQuestions) === 0) {
        renderHeader('Quiz not ready');
        echo '<div class="alert alert-danger">No questions are configured for this quiz.</div>';
        echo '<a href="jobs.php" class="btn btn-primary">Back to jobs</a>';
        renderFooter();
        exit;
    }

    $score = 0;
    $categoryCorrect = [];
    foreach ($baseQuestions as $q) {
        $answer = $_POST['answer_' . $q['question_id']] ?? '';
        if ($answer === $q['correct_answer']) {
            $score += 1;
            $cat = $q['category_name'] ?: 'General';
            $categoryCorrect[$cat] = ($categoryCorrect[$cat] ?? 0) + 1;
        }
    }
    $scoreMarks = ($score * 20) / max(count($baseQuestions), 1);
    $categoryPass = true;
    foreach ($categoryRules as $rule) {
        $got = $categoryCorrect[$rule['category_name']] ?? 0;
        if ($got < (int)$rule['min_correct']) {
            $categoryPass = false;
            break;
        }
    }

    if ($scoreMarks >= (float)$quiz['pass_marks'] && $categoryPass) {
        $_SESSION['last_passed_job'] = $jobId;
        $_SESSION['last_quiz_score'] = (int)$scoreMarks;
        $jobOwner = $pdo->prepare("SELECT company_id FROM jobs WHERE job_id=?");
        $jobOwner->execute([$jobId]);
        $jobOwnerRow = $jobOwner->fetch();
        if ($jobOwnerRow) {
            pushNotification($pdo, 'company', (int)$jobOwnerRow['company_id'], 'Quiz completed (passed)', 'A candidate passed quiz for Job #' . $jobId, 'company_dashboard.php');
        }
        header("Location: apply_job.php?job_id={$jobId}");
        exit;
    } else {
        $message = "You scored {$scoreMarks}/20. You did not pass category/overall criteria.";
        $progressCheck = $pdo->prepare("INSERT INTO quiz_attempts (user_id, job_id, score, passed, attempted_at) VALUES (?,?,?,?,NOW())");
        $progressCheck->execute([$userId, $jobId, $scoreMarks, 0]);
        header("Location: grooming.php?job_id={$jobId}");
        exit;
    }
}

renderHeader('Job Quiz');
?>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">Job Qualification Quiz</h4>
        <span class="badge text-bg-primary">Job ID #<?= $jobId ?></span>
    </div>
    <p class="text-muted mt-2">Total Marks: <?= (int)$quiz['total_marks'] ?> | Pass Marks: <?= (int)$quiz['pass_marks'] ?> | Duration: <?= (int)$quiz['duration'] ?> min</p>
    <?php if ($message): ?><div class="alert alert-warning"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="job_id" value="<?= $jobId ?>">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="mb-3 p-3 border rounded bg-light">
                <p><strong>Q<?= $idx + 1 ?>:</strong> <?= htmlspecialchars($q['question_text']) ?></p>
                <span class="badge text-bg-secondary mb-2">Category: <?= htmlspecialchars($q['category_name']) ?></span>
                <?php foreach (['a','b','c','d'] as $opt): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer_<?= (int)$q['question_id'] ?>" value="<?= $opt ?>" required>
                        <label class="form-check-label"><?= htmlspecialchars($q['option_' . $opt]) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button class="btn btn-primary">Submit Quiz</button>
        <a href="grooming.php?job_id=<?= $jobId ?>" class="btn btn-warning">Go to Grooming</a>
    </form>
</div></div>
<?php renderFooter(); ?>
