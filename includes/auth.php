<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function clearAllRoleSession(): void
{
    unset(
        $_SESSION['candidate_id'],
        $_SESSION['candidate_name'],
        $_SESSION['company_id'],
        $_SESSION['company_name'],
        $_SESSION['is_admin'],
        $_SESSION['admin_email'],
        $_SESSION['last_passed_job'],
        $_SESSION['last_quiz_score']
    );
}

function loginAsCandidate(int $userId, string $name): void
{
    session_regenerate_id(true);
    clearAllRoleSession();
    $_SESSION['candidate_id'] = $userId;
    $_SESSION['candidate_name'] = $name;
}

function loginAsCompany(int $companyId, string $companyName): void
{
    session_regenerate_id(true);
    clearAllRoleSession();
    $_SESSION['company_id'] = $companyId;
    $_SESSION['company_name'] = $companyName;
}

function loginAsAdmin(string $email): void
{
    session_regenerate_id(true);
    clearAllRoleSession();
    $_SESSION['is_admin'] = true;
    $_SESSION['admin_email'] = $email;
}

function sendNoCacheHeaders(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

function requireCandidate(): void
{
    sendNoCacheHeaders();
    if (!empty($_SESSION['company_id']) || !empty($_SESSION['is_admin'])) {
        header('Location: login.php?role=candidate');
        exit;
    }
    if (!isset($_SESSION['candidate_id'])) {
        header('Location: login.php?role=candidate');
        exit;
    }
}

function requireCompany(): void
{
    sendNoCacheHeaders();
    if (!empty($_SESSION['candidate_id']) || !empty($_SESSION['is_admin'])) {
        header('Location: login.php?role=company');
        exit;
    }
    if (!isset($_SESSION['company_id'])) {
        header('Location: login.php?role=company');
        exit;
    }
}

function requireAdmin(): void
{
    sendNoCacheHeaders();
    if (!empty($_SESSION['candidate_id']) || !empty($_SESSION['company_id'])) {
        header('Location: login.php?role=admin');
        exit;
    }
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: login.php?role=admin');
        exit;
    }
}

function activeUserRole(): string
{
    $hasCand = !empty($_SESSION['candidate_id']);
    $hasComp = !empty($_SESSION['company_id']);
    $hasAdm = !empty($_SESSION['is_admin']);
    $n = (int)$hasCand + (int)$hasComp + (int)$hasAdm;
    if ($n > 1) {
        clearAllRoleSession();
        return 'guest';
    }
    if ($hasAdm) {
        return 'admin';
    }
    if ($hasComp) {
        return 'company';
    }
    if ($hasCand) {
        return 'candidate';
    }
    return 'guest';
}

/** Fields required before taking a job quiz (subset of profile). */
function candidateQuizRequiredFields(): array
{
    return [
        'professional_headline' => 'Professional headline',
        'country_code' => 'Country code',
        'phone' => 'Mobile',
        'house' => 'Current house',
        'road' => 'Current road',
        'area' => 'Current area',
        'city' => 'Current city',
        'perm_house' => 'Permanent house',
        'perm_road' => 'Permanent road',
        'perm_area' => 'Permanent area',
        'perm_city' => 'Permanent city',
        'ssc_institution' => 'SSC institution',
        'ssc_year' => 'SSC passed year',
        'hsc_institution' => 'HSC institution',
        'hsc_year' => 'HSC passed year',
        'hard_skills' => 'Hard skills',
        'summary' => 'Professional summary',
    ];
}

function candidateQuizRequirementsMet(array $user): bool
{
    foreach (candidateQuizRequiredFields() as $k => $label) {
        if (trim((string)($user[$k] ?? '')) === '') {
            return false;
        }
    }
    return true;
}

function candidateQuizRequirementsMissingFields(array $user): array
{
    $missing = [];
    foreach (candidateQuizRequiredFields() as $k => $label) {
        if (trim((string)($user[$k] ?? '')) === '') {
            $missing[] = $label;
        }
    }
    return $missing;
}
?>
