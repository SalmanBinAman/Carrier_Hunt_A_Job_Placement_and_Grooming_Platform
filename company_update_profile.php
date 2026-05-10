<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';
requireCompany();

$companyId = (int)$_SESSION['company_id'];
$message = '';

$stmt = $pdo->prepare('SELECT * FROM companies WHERE company_id = ?');
$stmt->execute([$companyId]);
$company = $stmt->fetch();
if (!$company) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['company_name'] ?? '');
    $registrationNo = trim($_POST['business_registration_number'] ?? '');
    $tradeLicenseNumber = trim($_POST['trade_license_number'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $brandName = trim($_POST['brand_name'] ?? '');
    $companyLogo = trim($_POST['company_logo'] ?? '');
    $company_size = trim($_POST['company_size'] ?? '');
    $founded_year = trim($_POST['founded_year'] ?? '');
    $office_house = trim($_POST['office_house'] ?? '');
    $office_road = trim($_POST['office_road'] ?? '');
    $office_area = trim($_POST['office_area'] ?? '');
    $office_city = trim($_POST['office_city'] ?? '');
    $office_address = trim($_POST['office_address'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $need = [$companyName, $registrationNo, $tradeLicenseNumber, $contact_person, $contact_phone, $brandName, $companyLogo, $office_house, $office_road, $office_area, $office_city];
    $ok = true;
    foreach ($need as $v) {
        if ($v === '') {
            $ok = false;
            break;
        }
    }

    if ($ok) {
        $upd = $pdo->prepare('UPDATE companies SET company_name=?,business_registration_number=?,trade_license_number=?,contact_person=?,contact_phone=?,brand_name=?,company_logo=?,company_size=?,founded_year=?,office_house=?,office_road=?,office_area=?,office_city=?,office_address=?,industry=?,website=?,description=?,is_approved=0 WHERE company_id=?');
        $upd->execute([
            $companyName, $registrationNo, $tradeLicenseNumber, $contact_person, $contact_phone, $brandName, $companyLogo,
            $company_size, $founded_year, $office_house, $office_road, $office_area, $office_city, $office_address,
            $industry, $website, $description, $companyId,
        ]);
        pushNotification($pdo, 'admin', 0, 'Company profile update pending', $companyName . ' submitted updated details for re-approval.', 'admin_dashboard.php');
        session_destroy();
        session_start();
        renderHeader('Update submitted');
        echo '<div class="alert alert-warning">Your updates were saved. Your account is pending admin re-approval and has been signed out. After an admin approves your company again, you can log in.</div>';
        echo '<a href="index.php" class="btn btn-primary">Home</a>';
        renderFooter();
        exit;
    }
    $message = 'Fill all required fields before submitting for re-approval.';
}

renderHeader('Update company profile');
$c = $company;
?>
<div class="card shadow-sm"><div class="card-body p-4">
    <h4>Update company information</h4>
    <p class="text-muted">After you save, an admin must approve the changes before you can log in again.</p>
    <?php if ($message): ?><div class="alert alert-danger"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php
    $logo = trim((string)($c['company_logo'] ?? ''));
if ($logo !== '') {
    echo '<div class="mb-3"><img src="' . htmlspecialchars($logo) . '" alt="Logo" class="rounded border bg-white p-2" style="max-height:120px;object-fit:contain"></div>';
}
?>
    <form method="post">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="company_name" placeholder="Company name" value="<?= htmlspecialchars($c['company_name'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" name="business_registration_number" placeholder="Business registration number" value="<?= htmlspecialchars($c['business_registration_number'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" value="<?= htmlspecialchars($c['email'] ?? '') ?>" disabled title="Email cannot be changed here"></div>
            <div class="col-md-6"><input class="form-control" name="trade_license_number" placeholder="Trade licence number" value="<?= htmlspecialchars($c['trade_license_number'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" name="contact_person" value="<?= htmlspecialchars($c['contact_person'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" name="contact_phone" value="<?= htmlspecialchars($c['contact_phone'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" name="brand_name" value="<?= htmlspecialchars($c['brand_name'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" name="company_logo" value="<?= htmlspecialchars($c['company_logo'] ?? '') ?>" required></div>
            <div class="col-md-6"><input class="form-control" name="company_size" value="<?= htmlspecialchars($c['company_size'] ?? '') ?>"></div>
            <div class="col-md-6"><input class="form-control" name="founded_year" value="<?= htmlspecialchars($c['founded_year'] ?? '') ?>"></div>
            <div class="col-md-6"><input class="form-control" name="industry" value="<?= htmlspecialchars($c['industry'] ?? '') ?>"></div>
            <div class="col-md-6"><input class="form-control" name="website" value="<?= htmlspecialchars($c['website'] ?? '') ?>"></div>
            <div class="col-12"><h6 class="mt-2">Office address</h6></div>
            <div class="col-md-3"><input class="form-control" name="office_house" placeholder="House" value="<?= htmlspecialchars($c['office_house'] ?? '') ?>" required></div>
            <div class="col-md-3"><input class="form-control" name="office_road" placeholder="Road" value="<?= htmlspecialchars($c['office_road'] ?? '') ?>" required></div>
            <div class="col-md-3"><input class="form-control" name="office_area" placeholder="Area" value="<?= htmlspecialchars($c['office_area'] ?? '') ?>" required></div>
            <div class="col-md-3"><input class="form-control" name="office_city" placeholder="City" value="<?= htmlspecialchars($c['office_city'] ?? '') ?>" required></div>
            <div class="col-12"><textarea class="form-control" name="office_address" rows="2"><?= htmlspecialchars($c['office_address'] ?? '') ?></textarea></div>
            <div class="col-12"><textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($c['description'] ?? '') ?></textarea></div>
        </div>
        <button class="btn btn-warning mt-3">Submit updates for admin approval</button>
        <a href="company_dashboard.php" class="btn btn-outline-secondary mt-3">Cancel</a>
    </form>
</div></div>
<?php renderFooter(); ?>
