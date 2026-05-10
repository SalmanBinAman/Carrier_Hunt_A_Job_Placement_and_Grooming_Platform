<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/layout.php';

$skill = trim($_GET['skill'] ?? '');
$department = trim($_GET['department'] ?? '');
$employmentType = trim($_GET['employment_type'] ?? '');
$workplaceType = trim($_GET['workplace_type'] ?? '');
$salaryMin = $_GET['salary_min'] ?? '';
$salaryMax = $_GET['salary_max'] ?? '';
$sql = 'SELECT j.*, c.company_name FROM jobs j JOIN companies c ON c.company_id=j.company_id WHERE c.is_approved=1';
$params = [];

if ($skill !== '') {
    $sql .= ' AND (j.required_skills LIKE ? OR j.job_category LIKE ? OR j.job_title LIKE ?)';
    $like = '%' . $skill . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($department !== '') {
    $sql .= ' AND j.department LIKE ?';
    $params[] = '%' . $department . '%';
}
if ($employmentType !== '') {
    $sql .= ' AND j.employment_type LIKE ?';
    $params[] = '%' . $employmentType . '%';
}
if ($workplaceType !== '') {
    $sql .= ' AND j.workplace_type LIKE ?';
    $params[] = '%' . $workplaceType . '%';
}
if ($salaryMin !== '' && is_numeric($salaryMin)) {
    $v = (float)$salaryMin;
    $sql .= ' AND (j.salary_max >= ? OR j.salary_min >= ? OR (j.salary_min IS NULL AND j.salary_max IS NULL))';
    $params[] = $v;
    $params[] = $v;
}
if ($salaryMax !== '' && is_numeric($salaryMax)) {
    $v = (float)$salaryMax;
    $sql .= ' AND (j.salary_min <= ? OR j.salary_max <= ? OR (j.salary_min IS NULL AND j.salary_max IS NULL))';
    $params[] = $v;
    $params[] = $v;
}

$sql .= ' ORDER BY j.job_id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

renderHeader('Jobs');
?>
<div class="card shadow-sm border-0 mb-3">
<div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h4 class="mb-0">Explore jobs</h4>
        <span class="badge text-bg-primary"><?= count($jobs) ?> openings</span>
    </div>
    <form class="row g-2" method="get">
        <div class="col-md-4"><input class="form-control" name="skill" placeholder="Keyword (skills, title, category)" value="<?= htmlspecialchars($skill) ?>"></div>
        <div class="col-md-4"><input class="form-control" name="department" placeholder="Department" value="<?= htmlspecialchars($department) ?>"></div>
        <div class="col-md-4">
            <select class="form-select" name="employment_type">
                <option value="">Job type (any)</option>
                <?php foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= $employmentType === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="workplace_type">
                <option value="">Workplace (any)</option>
                <?php foreach (['Remote', 'Hybrid', 'On-site', 'Home office', 'Office'] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>" <?= strcasecmp($workplaceType, $opt) === 0 ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><input class="form-control" type="number" step="0.01" name="salary_min" placeholder="Min salary (approx)" value="<?= htmlspecialchars($salaryMin) ?>"></div>
        <div class="col-md-4"><input class="form-control" type="number" step="0.01" name="salary_max" placeholder="Max salary (approx)" value="<?= htmlspecialchars($salaryMax) ?>"></div>
        <div class="col-12"><button class="btn btn-dark">Apply filters</button> <a href="jobs.php" class="btn btn-outline-secondary">Reset</a></div>
    </form>
</div></div>

<?php if (!$jobs): ?>
<div class="alert alert-info shadow-sm">No jobs match these filters.</div>
<?php endif; ?>

<?php foreach ($jobs as $job): ?>
<div class="card shadow-sm border-0 mb-3"><div class="card-body p-4">
    <div class="d-flex justify-content-between flex-wrap gap-2">
        <h5><?= htmlspecialchars($job['job_title']) ?></h5>
        <small class="text-muted">Deadline: <?= htmlspecialchars($job['deadline'] ?: 'N/A') ?></small>
    </div>
    <p class="mb-1"><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
    <p class="mb-1"><strong>Department:</strong> <?= htmlspecialchars($job['department'] ?: '—') ?></p>
    <p class="mb-1"><strong>Required skills:</strong> <?= htmlspecialchars($job['required_skills'] ?: 'Not specified') ?></p>
    <p class="mb-1"><strong>Category / type / workplace:</strong> <?= htmlspecialchars(($job['job_category'] ?: 'General') . ' | ' . ($job['employment_type'] ?: 'N/A') . ' | ' . ($job['workplace_type'] ?: 'N/A')) ?></p>
    <p class="mb-1"><strong>Salary:</strong> <?= htmlspecialchars($job['salary_visibility'] === 'Private' ? 'Private' : ($job['salary'] ?: 'Negotiable')) ?></p>
    <p class="mb-3 text-secondary"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
    <a href="take_quiz.php?job_id=<?= (int)$job['job_id'] ?>" class="btn btn-primary btn-sm px-3">Start quiz &amp; apply</a>
</div></div>
<?php endforeach; ?>
<?php renderFooter(); ?>
