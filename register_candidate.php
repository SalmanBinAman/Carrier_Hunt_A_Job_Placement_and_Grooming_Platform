<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $professionalHeadline = trim($_POST['professional_headline'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $countryCode = trim($_POST['country_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name && $professionalHeadline && $email && $password && $countryCode && $phone) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name,professional_headline,email,password,country_code,phone,created_at) VALUES (?,?,?,?,?,?,NOW())");
        try {
            $stmt->execute([$name, $professionalHeadline, $email, $hash, $countryCode, $phone]);
            pushNotification($pdo, 'admin', 0, 'New candidate registration', $name . ' has registered on the platform.', 'admin_dashboard.php');
            $message = 'Registration successful. Complete your full profile after login before applying.';
        } catch (PDOException $e) {
            $message = 'Registration failed (email may already exist).';
        }
    } else {
        $message = 'Name, headline, email, country code, phone and password are required.';
    }
}

renderHeader('Candidate Registration');
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4>Candidate Registration</h4>
        <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form method="post">
            <div class="row g-2">
                <div class="col-md-6"><input class="form-control" name="name" placeholder="Name" required></div>
                <div class="col-md-6"><input class="form-control" name="professional_headline" placeholder="Professional Headline" required></div>
                <div class="col-md-6"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
                <div class="col-md-6"><input class="form-control" type="password" name="password" placeholder="Password" required></div>
                <div class="col-md-3"><input class="form-control" name="country_code" placeholder="+880" required></div>
                <div class="col-md-3"><input class="form-control" name="phone" placeholder="Phone" required></div>
            </div>
            <button class="btn btn-success mt-3">Register</button>
            <a href="login.php?role=candidate" class="btn btn-link">Go to login</a>
        </form>
    </div>
</div>
<?php renderFooter(); ?>
