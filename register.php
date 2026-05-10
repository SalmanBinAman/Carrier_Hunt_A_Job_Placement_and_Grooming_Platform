<?php
$role = $_GET['role'] ?? '';
if ($role === 'candidate') {
    header('Location: register_candidate.php');
    exit;
}
if ($role === 'company') {
    header('Location: company_register.php');
    exit;
}

require_once __DIR__ . '/includes/layout.php';
renderHeader('Register');
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4 class="mb-2">Create Account</h4>
        <p class="text-muted">Choose your role to continue registration.</p>
        <a class="btn btn-success me-2" href="register.php?role=candidate">Candidate Registration</a>
        <a class="btn btn-primary" href="register.php?role=company">Company Registration</a>
        <p class="small text-muted mt-3 mb-0">Admin accounts are managed by system owner and cannot self-register.</p>
    </div>
</div>
<?php renderFooter(); ?>
