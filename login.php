<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
sendNoCacheHeaders();

$defaultRole = $_GET['role'] ?? 'candidate';
$validRoles = ['candidate', 'company', 'admin'];
if (!in_array($defaultRole, $validRoles, true)) {
    $defaultRole = 'candidate';
}

if (!empty($_SESSION['candidate_id'])) {
    header('Location: candidate_dashboard.php');
    exit;
}
if (!empty($_SESSION['company_id'])) {
    header('Location: company_dashboard.php');
    exit;
}
if (!empty($_SESSION['is_admin'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$adminAccounts = [
    'admin@gamil.com' => 'admin123',
    'admin2@gmail.com' => 'admin123',
    'admin3@gmail.com' => 'admin123',
    'admin4@gmail.com' => 'admin123',
];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? $defaultRole;
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($role === 'admin') {
        if (isset($adminAccounts[$email]) && $adminAccounts[$email] === $password) {
            loginAsAdmin($email);
            header('Location: admin_dashboard.php');
            exit;
        }
        $message = 'Invalid admin credentials.';
    } elseif ($role === 'company') {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE email = ?");
        $stmt->execute([$email]);
        $company = $stmt->fetch();
        if ($company && password_verify($password, $company['password'])) {
            if ((int)$company['is_approved'] !== 1) {
                $message = 'Company account pending admin approval.';
            } else {
                loginAsCompany((int)$company['company_id'], (string)$company['company_name']);
                header('Location: company_dashboard.php');
                exit;
            }
        }
        if (!$message) {
            $message = 'Invalid company credentials.';
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            loginAsCandidate((int)$user['user_id'], (string)$user['name']);
            header('Location: candidate_dashboard.php');
            exit;
        }
        $message = 'Invalid candidate credentials.';
    }
}

renderHeader('Login');
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm"><div class="card-body p-4">
            <h4>Login to Carrier Hunt</h4>
            <?php if ($message): ?><div class="alert alert-danger"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <form method="post">
                <select class="form-select mb-2" name="role">
                    <option value="candidate" <?= $defaultRole === 'candidate' ? 'selected' : '' ?>>Candidate</option>
                    <option value="company" <?= $defaultRole === 'company' ? 'selected' : '' ?>>Company</option>
                    <option value="admin" <?= $defaultRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
                <input class="form-control mb-2" type="password" name="password" placeholder="Password" required>
                <button class="btn btn-primary">Login</button>
            </form>
            <p class="small text-muted mt-3 mb-0">Admin has login only. Candidate and Company can register first.</p>
        </div></div>
    </div>
</div>
<?php renderFooter(); ?>
