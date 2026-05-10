<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';
requireCompany();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO jobs (company_id,job_title,department,job_category,employment_type,workplace_type,office_location,description,responsibilities,min_experience_years,minimum_education,required_skills,perks_benefits,salary_min,salary_max,salary_currency,salary_visibility,salary,deadline) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        (int)$_SESSION['company_id'],
        trim($_POST['job_title'] ?? ''),
        trim($_POST['department'] ?? ''),
        trim($_POST['job_category'] ?? ''),
        trim($_POST['employment_type'] ?? ''),
        trim($_POST['workplace_type'] ?? ''),
        trim($_POST['office_location'] ?? ''),
        trim($_POST['description'] ?? ''),
        trim($_POST['responsibilities'] ?? ''),
        trim($_POST['min_experience_years'] ?? 0),
        trim($_POST['minimum_education'] ?? ''),
        trim($_POST['required_skills'] ?? ''),
        trim($_POST['perks_benefits'] ?? ''),
        $_POST['salary_min'] !== '' ? (float)$_POST['salary_min'] : null,
        $_POST['salary_max'] !== '' ? (float)$_POST['salary_max'] : null,
        trim($_POST['salary_currency'] ?? 'BDT'),
        trim($_POST['salary_visibility'] ?? 'Public'),
        trim($_POST['salary'] ?? ''),
        trim($_POST['deadline'] ?? '')
    ]);
    pushNotification($pdo, 'admin', 0, 'New job posted', 'A company posted a new job and quiz setup may be pending.', 'admin_dashboard.php');
    $msg = 'Job posted.';
}

renderHeader('Post Job');
?>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <h4>Post New Job</h4>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="job_title" placeholder="Job Title" required></div>
            <div class="col-md-6"><input class="form-control" name="department" placeholder="Department"></div>
            <div class="col-md-6"><input class="form-control" name="job_category" placeholder="Job Category"></div>
            <div class="col-md-6"><input class="form-control" name="required_skills" placeholder="Required Skills"></div>
            <div class="col-md-4"><input class="form-control" name="employment_type" placeholder="Employment Type"></div>
            <div class="col-md-4"><input class="form-control" name="workplace_type" placeholder="Remote/Hybrid/On-site"></div>
            <div class="col-md-4"><input class="form-control" name="office_location" placeholder="Office Location"></div>
            <div class="col-md-4"><input class="form-control" name="minimum_education" placeholder="Minimum Education"></div>
            <div class="col-md-6"><input class="form-control" name="experience_level" placeholder="Experience Level"></div>
            <div class="col-md-3"><input class="form-control" type="number" step="0.01" name="salary_min" placeholder="Salary Min"></div>
            <div class="col-md-3"><input class="form-control" type="number" step="0.01" name="salary_max" placeholder="Salary Max"></div>
            <div class="col-md-3"><input class="form-control" name="salary_currency" value="BDT" placeholder="Currency"></div>
            <div class="col-md-3">
                <select class="form-select" name="salary_visibility"><option value="Public">Salary Public</option><option value="Private">Salary Private</option></select>
            </div>
            <div class="col-md-6"><input class="form-control" type="date" name="deadline"></div>
            <div class="col-12"><textarea class="form-control" rows="3" name="responsibilities" placeholder="Job Responsibilities"></textarea></div>
            <div class="col-12"><textarea class="form-control" rows="2" name="perks_benefits" placeholder="Perks & Benefits"></textarea></div>
            <div class="col-12"><textarea class="form-control" rows="5" name="description" placeholder="Description" required></textarea></div>
        </div>
        <button class="btn btn-success">Post Job</button>
    </form>
</div></div>
<?php renderFooter(); ?>
