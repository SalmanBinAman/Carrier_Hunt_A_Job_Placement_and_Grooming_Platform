<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_candidate_id'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->execute([(int)$_POST['delete_candidate_id']]);
    }
    if (isset($_POST['delete_company_id'])) {
        $stmt = $pdo->prepare("DELETE FROM companies WHERE company_id=?");
        $stmt->execute([(int)$_POST['delete_company_id']]);
    }
    if (isset($_POST['delete_job_id'])) {
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE job_id=?");
        $stmt->execute([(int)$_POST['delete_job_id']]);
    }

    $companyId = (int)($_POST['company_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($companyId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $value = $action === 'approve' ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE companies SET is_approved=? WHERE company_id=?");
        $stmt->execute([$value, $companyId]);
        pushNotification(
            $pdo,
            'company',
            $companyId,
            $action === 'approve' ? 'Company verified' : 'Company verification rejected',
            $action === 'approve' ? 'Admin approved your company account.' : 'Admin rejected your company account.',
            'company_dashboard.php'
        );
    }
}

$totalUsers = (int)$pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
$totalCompanies = (int)$pdo->query("SELECT COUNT(*) c FROM companies")->fetch()['c'];
$totalJobs = (int)$pdo->query("SELECT COUNT(*) c FROM jobs")->fetch()['c'];
$totalApplications = (int)$pdo->query("SELECT COUNT(*) c FROM applications")->fetch()['c'];
$courseCompletions = (int)$pdo->query("SELECT COUNT(*) c FROM course_progress WHERE completion_status='Completed'")->fetch()['c'];
$pendingCompanies = $pdo->query("SELECT * FROM companies WHERE is_approved=0 ORDER BY company_id DESC")->fetchAll();
$allCandidates = $pdo->query("SELECT user_id,name,email,phone,professional_headline,current_location,hard_skills,soft_skills,profile_completed,created_at FROM users ORDER BY user_id DESC")->fetchAll();
$allCompanies = $pdo->query('SELECT company_id,company_name,business_registration_number,email,trade_license_number,industry,contact_person,contact_phone,office_city,is_approved FROM companies ORDER BY company_id DESC')->fetchAll();
$allJobs = $pdo->query("SELECT j.job_id,j.job_title,j.job_category,j.employment_type,j.workplace_type,j.required_skills,j.deadline,c.company_name FROM jobs j JOIN companies c ON c.company_id=j.company_id ORDER BY j.job_id DESC")->fetchAll();

renderHeader('Admin Dashboard');
?>
<h4>Admin Dashboard</h4>
<div class="row g-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Users</h6><h3><?= $totalUsers ?></h3></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Companies</h6><h3><?= $totalCompanies ?></h3></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Jobs</h6><h3><?= $totalJobs ?></h3></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Applications</h6><h3><?= $totalApplications ?></h3></div></div></div>
</div>
<div class="card mt-3"><div class="card-body">
    <h6>Course Completions</h6>
    <h4><?= $courseCompletions ?></h4>
    <p class="text-muted mb-0">Logged in admin: <?= htmlspecialchars($_SESSION['admin_email'] ?? 'Unknown') ?></p>
</div></div>

<div class="card mt-3"><div class="card-body">
    <h5>Pending Company Approval</h5>
    <?php if (!$pendingCompanies): ?>
        <p class="text-muted mb-0">No pending companies.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead><tr><th>Company</th><th>Email</th><th>Industry</th><th>Contact</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($pendingCompanies as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['company_name']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['industry']) ?></td>
                    <td><?= htmlspecialchars($c['contact_person'] . ' / ' . $c['contact_phone']) ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="company_id" value="<?= (int)$c['company_id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="company_id" value="<?= (int)$c['company_id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="btn btn-outline-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div></div>

<div class="card mt-3"><div class="card-body">
    <h5>All Candidates (Full View)</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Name</th><th>Email/Phone</th><th>Headline</th><th>Location</th><th>Skills</th><th>Profile</th><th>Details</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($allCandidates as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?><br><small><?= htmlspecialchars($u['phone']) ?></small></td>
                    <td><?= htmlspecialchars($u['professional_headline']) ?></td>
                    <td><?= htmlspecialchars($u['current_location']) ?></td>
                    <td><small><?= htmlspecialchars(($u['hard_skills'] ?? '') . ' | ' . ($u['soft_skills'] ?? '')) ?></small></td>
                    <td><span class="badge <?= (int)$u['profile_completed'] === 1 ? 'text-bg-success' : 'text-bg-warning' ?>"><?= (int)$u['profile_completed'] === 1 ? 'Quiz-ready' : 'Incomplete' ?></span></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="admin_view.php?type=candidate&amp;id=<?= (int)$u['user_id'] ?>">Details</a></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Remove this candidate?')">
                            <input type="hidden" name="delete_candidate_id" value="<?= (int)$u['user_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div></div>

<div class="card mt-3"><div class="card-body">
    <h5>All Companies (Full View)</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Company</th><th>Reg / licence</th><th>Contact</th><th>City</th><th>Industry</th><th>Status</th><th>Details</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($allCompanies as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['company_name']) ?><br><small><?= htmlspecialchars($c['email']) ?></small></td>
                    <td><small>Reg: <?= htmlspecialchars($c['business_registration_number'] ?? '') ?><br>Licence: <?= htmlspecialchars($c['trade_license_number'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($c['contact_person'] . ' / ' . $c['contact_phone']) ?></td>
                    <td><?= htmlspecialchars($c['office_city'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['industry']) ?></td>
                    <td><span class="badge <?= (int)$c['is_approved'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int)$c['is_approved'] === 1 ? 'Approved' : 'Pending' ?></span></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="admin_view.php?type=company&amp;id=<?= (int)$c['company_id'] ?>">Details</a></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Remove this company?')">
                            <input type="hidden" name="delete_company_id" value="<?= (int)$c['company_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div></div>

<div class="card mt-3"><div class="card-body">
    <h5>All Job Posts</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Title</th><th>Company</th><th>Category</th><th>Type</th><th>Skills</th><th>Deadline</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($allJobs as $j): ?>
                <tr>
                    <td><?= htmlspecialchars($j['job_title']) ?></td>
                    <td><?= htmlspecialchars($j['company_name']) ?></td>
                    <td><?= htmlspecialchars($j['job_category']) ?></td>
                    <td><?= htmlspecialchars($j['employment_type'] . ' / ' . $j['workplace_type']) ?></td>
                    <td><small><?= htmlspecialchars($j['required_skills']) ?></small></td>
                    <td><?= htmlspecialchars($j['deadline']) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Remove this job post?')">
                            <input type="hidden" name="delete_job_id" value="<?= (int)$j['job_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div></div>
<?php renderFooter(); ?>
