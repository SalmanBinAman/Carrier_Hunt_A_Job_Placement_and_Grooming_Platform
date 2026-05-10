<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/notifications.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['company_name'] ?? '');
    $registrationNo = trim($_POST['business_registration_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
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

    $need = [$companyName, $registrationNo, $email, $password, $tradeLicenseNumber, $contact_person, $contact_phone, $brandName, $companyLogo, $office_house, $office_road, $office_area, $office_city];
    $ok = true;
    foreach ($need as $v) {
        if ($v === '') {
            $ok = false;
            break;
        }
    }

    if ($ok) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO companies (company_name,business_registration_number,email,password,trade_license_number,contact_person,contact_phone,brand_name,company_logo,company_size,founded_year,office_house,office_road,office_area,office_city,office_address,industry,website,description,is_approved) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)');
        try {
            $stmt->execute([
                $companyName, $registrationNo, $email, $hash, $tradeLicenseNumber, $contact_person, $contact_phone,
                $brandName, $companyLogo, $company_size, $founded_year, $office_house, $office_road, $office_area, $office_city,
                $office_address, $industry, $website, $description,
            ]);
            pushNotification($pdo, 'admin', 0, 'New company registration', $companyName . ' is waiting for approval.', 'admin_dashboard.php');
            $message = 'Company registration submitted. Wait for admin approval before login.';
        } catch (PDOException $e) {
            $message = 'Registration failed (email may already exist).';
        }
    } else {
        $message = 'Fill all required fields (including office address lines and trade licence number).';
    }
}

renderHeader('Company Registration');
?>
<div class="card shadow-sm"><div class="card-body p-4">
    <h4>Company Registration</h4>
    <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="company_name" placeholder="Company name" required></div>
            <div class="col-md-6"><input class="form-control" name="business_registration_number" placeholder="Business registration number" required></div>
            <div class="col-md-6"><input class="form-control" type="email" name="email" placeholder="Email (login)" required></div>
            <div class="col-md-6"><input class="form-control" type="password" name="password" placeholder="Password" required></div>
            <div class="col-md-6"><input class="form-control" name="trade_license_number" placeholder="Trade licence number" required></div>
            <div class="col-md-6"><input class="form-control" name="contact_person" placeholder="Contact person" required></div>
            <div class="col-md-6"><input class="form-control" name="contact_phone" placeholder="Contact phone" required></div>
            <div class="col-md-6"><input class="form-control" name="brand_name" placeholder="Brand / trading name" required></div>
            <div class="col-md-6"><input class="form-control" name="company_logo" placeholder="Company logo URL" required></div>
            <div class="col-md-6"><input class="form-control" name="company_size" placeholder="Company size (e.g. 50–200)"></div>
            <div class="col-md-6"><input class="form-control" name="founded_year" placeholder="Founded year"></div>
            <div class="col-md-6"><input class="form-control" name="industry" placeholder="Industry"></div>
            <div class="col-md-6"><input class="form-control" name="website" placeholder="Website"></div>
            <div class="col-12"><h6 class="mt-2">Office address</h6></div>
            <div class="col-md-3"><input class="form-control" name="office_house" placeholder="House" required></div>
            <div class="col-md-3"><input class="form-control" name="office_road" placeholder="Road" required></div>
            <div class="col-md-3"><input class="form-control" name="office_area" placeholder="Area" required></div>
            <div class="col-md-3"><input class="form-control" name="office_city" placeholder="City" required></div>
            <div class="col-12"><textarea class="form-control" name="office_address" placeholder="Additional address / landmark (optional)" rows="2"></textarea></div>
            <div class="col-12"><textarea class="form-control" name="description" placeholder="Company description (optional)" rows="3"></textarea></div>
        </div>
        <button class="btn btn-success mt-3">Register</button>
    </form>
</div></div>
<?php renderFooter(); ?>
