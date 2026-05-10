<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
requireCompany();

$companyId = (int)$_SESSION['company_id'];
$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);
$message = '';

if (isset($_POST['create_quiz'])) {
    $total = (int)($_POST['total_marks'] ?? 20);
    $pass = (int)($_POST['pass_marks'] ?? 12);
    $duration = (int)($_POST['duration'] ?? 20);
    $stmt = $pdo->prepare("INSERT INTO quiz (job_id,total_marks,pass_marks,duration) VALUES (?,?,?,?)");
    $stmt->execute([$jobId, $total, $pass, $duration]);
    $message = 'Quiz created.';
}

if (isset($_POST['add_category_rule'])) {
    $quizIdStmt = $pdo->prepare("SELECT quiz_id FROM quiz WHERE job_id=? ORDER BY quiz_id DESC LIMIT 1");
    $quizIdStmt->execute([$jobId]);
    $quizRow = $quizIdStmt->fetch();
    if ($quizRow) {
        $stmt = $pdo->prepare("INSERT INTO quiz_category_rules (quiz_id,category_name,min_correct) VALUES (?,?,?)");
        $stmt->execute([$quizRow['quiz_id'], trim($_POST['category_name'] ?? ''), (int)($_POST['min_correct'] ?? 0)]);
        $message = 'Category rule added.';
    } else {
        $message = 'Create quiz first for this job.';
    }
}

if (isset($_POST['add_question'])) {
    $quizIdStmt = $pdo->prepare("SELECT quiz_id FROM quiz WHERE job_id=? ORDER BY quiz_id DESC LIMIT 1");
    $quizIdStmt->execute([$jobId]);
    $quizRow = $quizIdStmt->fetch();
    if ($quizRow) {
        $stmt = $pdo->prepare("INSERT INTO quiz_questions (quiz_id,category_name,question_text,option_a,option_b,option_c,option_d,correct_answer) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $quizRow['quiz_id'],
            trim($_POST['question_category'] ?? 'General'),
            trim($_POST['question_text'] ?? ''),
            trim($_POST['option_a'] ?? ''),
            trim($_POST['option_b'] ?? ''),
            trim($_POST['option_c'] ?? ''),
            trim($_POST['option_d'] ?? ''),
            trim($_POST['correct_answer'] ?? 'a')
        ]);
        $message = 'Question added.';
    } else {
        $message = 'Create quiz first for this job.';
    }
}

$jobsStmt = $pdo->prepare("SELECT * FROM jobs WHERE company_id=? ORDER BY job_id DESC");
$jobsStmt->execute([$companyId]);
$jobs = $jobsStmt->fetchAll();

$quizRowForJob = null;
$ruleCategoryNames = [];
if ($jobId > 0) {
    $quizIdStmt = $pdo->prepare('SELECT quiz_id FROM quiz WHERE job_id=? ORDER BY quiz_id DESC LIMIT 1');
    $quizIdStmt->execute([$jobId]);
    $quizRowForJob = $quizIdStmt->fetch();
    if ($quizRowForJob) {
        $rc = $pdo->prepare('SELECT DISTINCT category_name FROM quiz_category_rules WHERE quiz_id=? ORDER BY category_name');
        $rc->execute([$quizRowForJob['quiz_id']]);
        foreach ($rc->fetchAll() as $r) {
            $ruleCategoryNames[] = $r['category_name'];
        }
    }
}

renderHeader('Create Quiz');
?>
<h4 class="mb-3">Create Quiz and Questions</h4>
<?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<form class="card border-0 shadow-sm p-3 mb-3">
    <label class="form-label fw-semibold">Select Job</label>
    <select class="form-select" name="job_id" onchange="this.form.submit()">
        <option value="0">Select Job</option>
        <?php foreach ($jobs as $job): ?>
            <option value="<?= (int)$job['job_id'] ?>" <?= $jobId === (int)$job['job_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($job['job_title']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($jobId > 0): ?>
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
            <h5>Quiz Setup</h5>
            <form method="post">
                <input type="hidden" name="job_id" value="<?= $jobId ?>">
                <input class="form-control mb-2" name="total_marks" value="20" placeholder="Total Marks">
                <input class="form-control mb-2" name="pass_marks" value="12" placeholder="Pass Marks">
                <input class="form-control mb-2" name="duration" value="20" placeholder="Duration (min)">
                <button class="btn btn-primary" name="create_quiz">Create/Reset Quiz</button>
            </form>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
            <h5>Category Rule</h5>
            <form method="post" class="mb-3">
                <input type="hidden" name="job_id" value="<?= $jobId ?>">
                <input class="form-control mb-2" name="category_name" placeholder="Category (e.g., DSA)" required>
                <input class="form-control mb-2" name="min_correct" placeholder="Min Correct in Category" required>
                <button class="btn btn-outline-primary btn-sm" name="add_category_rule">Add Rule</button>
            </form>
            <h5>Add Question</h5>
            <?php if ($quizRowForJob && count($ruleCategoryNames) > 0): ?>
                <p class="small text-muted">Categories are taken from your quiz rules. Pick one below or open the chooser.</p>
                <button type="button" class="btn btn-outline-secondary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#pickCategoryModal">Choose category…</button>
            <?php endif; ?>
            <form method="post" id="addQuestionForm">
                <input type="hidden" name="job_id" value="<?= $jobId ?>">
                <?php if ($quizRowForJob && count($ruleCategoryNames) > 0): ?>
                    <label class="form-label">Question category</label>
                    <select class="form-select mb-2" name="question_category" id="questionCategorySelect" required>
                        <option value="">Select category</option>
                        <?php foreach ($ruleCategoryNames as $cn): ?>
                            <option value="<?= htmlspecialchars($cn) ?>"><?= htmlspecialchars($cn) ?></option>
                        <?php endforeach; ?>
                        <option value="General">General</option>
                    </select>
                <?php else: ?>
                    <input class="form-control mb-2" name="question_category" placeholder="Question category (e.g. General)" required>
                <?php endif; ?>
                <textarea class="form-control mb-2" name="question_text" placeholder="Question" required></textarea>
                <input class="form-control mb-2" name="option_a" placeholder="Option A" required>
                <input class="form-control mb-2" name="option_b" placeholder="Option B" required>
                <input class="form-control mb-2" name="option_c" placeholder="Option C" required>
                <input class="form-control mb-2" name="option_d" placeholder="Option D" required>
                <select class="form-select mb-2" name="correct_answer">
                    <option value="a">A</option><option value="b">B</option><option value="c">C</option><option value="d">D</option>
                </select>
                <button class="btn btn-success" name="add_question">Add Question</button>
            </form>
            <?php if ($quizRowForJob && count($ruleCategoryNames) > 0): ?>
            <div class="modal fade" id="pickCategoryModal" tabindex="-1" aria-labelledby="pickCategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pickCategoryModalLabel">Place question under category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted mb-2">These match your category rules.</p>
                            <div class="list-group">
                                <?php foreach ($ruleCategoryNames as $cn): ?>
                                    <button type="button" class="list-group-item list-group-item-action js-pick-cat" data-cat="<?= htmlspecialchars($cn) ?>"><?= htmlspecialchars($cn) ?></button>
                                <?php endforeach; ?>
                                <button type="button" class="list-group-item list-group-item-action js-pick-cat" data-cat="General">General</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
            document.querySelectorAll('.js-pick-cat').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var v = this.getAttribute('data-cat');
                    var sel = document.getElementById('questionCategorySelect');
                    if (sel) { sel.value = v; }
                    var m = document.getElementById('pickCategoryModal');
                    if (m && window.bootstrap) { bootstrap.Modal.getOrCreateInstance(m).hide(); }
                });
            });
            </script>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<?php endif; ?>
<?php renderFooter(); ?>
