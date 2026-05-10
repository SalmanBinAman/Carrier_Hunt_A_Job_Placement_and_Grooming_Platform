<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
requireCandidate();

$userId = (int)$_SESSION['candidate_id'];
$message = '';

function reqBadge(bool $quizRequired): string
{
    return $quizRequired ? ' <span class="badge text-bg-danger">Required for quiz</span>' : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'professional_headline' => trim($_POST['professional_headline'] ?? ''),
        'country_code' => trim($_POST['country_code'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'profile_photo' => trim($_POST['profile_photo'] ?? ''),
        'dob' => trim($_POST['dob'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'current_location' => trim($_POST['current_location'] ?? ''),
        'house' => trim($_POST['house'] ?? ''),
        'road' => trim($_POST['road'] ?? ''),
        'area' => trim($_POST['area'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'perm_house' => trim($_POST['perm_house'] ?? ''),
        'perm_road' => trim($_POST['perm_road'] ?? ''),
        'perm_area' => trim($_POST['perm_area'] ?? ''),
        'perm_city' => trim($_POST['perm_city'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'graduation_year' => trim($_POST['graduation_year'] ?? ''),
        'grade_cgpa' => trim($_POST['grade_cgpa'] ?? ''),
        'hard_skills' => trim($_POST['hard_skills'] ?? ''),
        'soft_skills' => trim($_POST['soft_skills'] ?? ''),
        'experience_level' => trim($_POST['experience_level'] ?? ''),
        'skills' => trim($_POST['skills'] ?? ''),
        'linkedin' => trim($_POST['linkedin'] ?? ''),
        'github' => trim($_POST['github'] ?? ''),
        'portfolio' => trim($_POST['portfolio'] ?? ''),
        'summary' => trim($_POST['summary'] ?? ''),
        'languages' => trim($_POST['languages'] ?? ''),
        'certifications' => trim($_POST['certifications'] ?? ''),
        'career_objective' => trim($_POST['career_objective'] ?? ''),
        'expected_salary' => trim($_POST['expected_salary'] ?? ''),
        'preferred_work_type' => trim($_POST['preferred_work_type'] ?? ''),
    ];
    $edu = ['ssc', 'hsc', 'ug', 'pg'];
    foreach ($edu as $p) {
        $data[$p . '_institution'] = trim($_POST[$p . '_institution'] ?? '');
        $data[$p . '_group'] = trim($_POST[$p . '_group'] ?? '');
        $data[$p . '_degree'] = trim($_POST[$p . '_degree'] ?? '');
        $data[$p . '_year'] = trim($_POST[$p . '_year'] ?? '');
        $data[$p . '_grade'] = trim($_POST[$p . '_grade'] ?? '');
    }

    $isComplete = candidateQuizRequirementsMet($data) ? 1 : 0;

    $stmt = $pdo->prepare('UPDATE users SET professional_headline=?,country_code=?,phone=?,profile_photo=?,dob=?,gender=?,current_location=?,house=?,road=?,area=?,city=?,perm_house=?,perm_road=?,perm_area=?,perm_city=?,address=?,ssc_institution=?,ssc_group=?,ssc_degree=?,ssc_year=?,ssc_grade=?,hsc_institution=?,hsc_group=?,hsc_degree=?,hsc_year=?,hsc_grade=?,ug_institution=?,ug_group=?,ug_degree=?,ug_year=?,ug_grade=?,pg_institution=?,pg_group=?,pg_degree=?,pg_year=?,pg_grade=?,graduation_year=?,grade_cgpa=?,hard_skills=?,soft_skills=?,experience_level=?,skills=?,linkedin=?,github=?,portfolio=?,summary=?,languages=?,certifications=?,career_objective=?,expected_salary=?,preferred_work_type=?,profile_completed=? WHERE user_id=?');
    $stmt->execute([
        $data['professional_headline'], $data['country_code'], $data['phone'], $data['profile_photo'], $data['dob'] ?: null, $data['gender'],
        $data['current_location'], $data['house'], $data['road'], $data['area'], $data['city'],
        $data['perm_house'], $data['perm_road'], $data['perm_area'], $data['perm_city'], $data['address'],
        $data['ssc_institution'], $data['ssc_group'], $data['ssc_degree'], $data['ssc_year'], $data['ssc_grade'],
        $data['hsc_institution'], $data['hsc_group'], $data['hsc_degree'], $data['hsc_year'], $data['hsc_grade'],
        $data['ug_institution'], $data['ug_group'], $data['ug_degree'], $data['ug_year'], $data['ug_grade'],
        $data['pg_institution'], $data['pg_group'], $data['pg_degree'], $data['pg_year'], $data['pg_grade'],
        $data['graduation_year'], $data['grade_cgpa'], $data['hard_skills'], $data['soft_skills'],
        $data['experience_level'], $data['skills'], $data['linkedin'], $data['github'], $data['portfolio'], $data['summary'],
        $data['languages'], $data['certifications'], $data['career_objective'], $data['expected_salary'], $data['preferred_work_type'],
        $isComplete, $userId,
    ]);
    $message = $isComplete
        ? 'Profile saved. You meet the minimum requirements to take job quizzes.'
        : 'Profile saved. Fill every field marked “Required for quiz” before taking a quiz.';
}

$userStmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

renderHeader('Candidate Profile');
$u = $user;
$photo = trim((string)($u['profile_photo'] ?? ''));
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h4>My profile</h4>
        <p class="text-muted">Save any time. Only fields marked <span class="badge text-bg-danger">Required for quiz</span> must be complete before quizzes and applications.</p>
        <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($photo !== ''): ?>
            <div class="mb-3"><img src="<?= htmlspecialchars($photo) ?>" alt="Profile" class="rounded border" style="max-width:160px;max-height:160px;object-fit:cover"></div>
        <?php endif; ?>
        <form method="post">
            <div class="row g-2">
                <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" value="<?= htmlspecialchars($u['name']) ?>" disabled></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="<?= htmlspecialchars($u['email']) ?>" disabled></div>
                <div class="col-md-6"><label class="form-label">Professional headline<?= reqBadge(true) ?></label><input class="form-control" name="professional_headline" value="<?= htmlspecialchars($u['professional_headline'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Country code<?= reqBadge(true) ?></label><input class="form-control" name="country_code" value="<?= htmlspecialchars($u['country_code'] ?? '') ?>" placeholder="+880" required></div>
                <div class="col-md-3"><label class="form-label">Mobile<?= reqBadge(true) ?></label><input class="form-control" name="phone" value="<?= htmlspecialchars($u['phone'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Profile photo URL</label><input class="form-control" name="profile_photo" value="<?= htmlspecialchars($u['profile_photo'] ?? '') ?>" placeholder="https://…"></div>
                <div class="col-md-3"><label class="form-label">Date of birth</label><input class="form-control" type="date" name="dob" value="<?= htmlspecialchars($u['dob'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Gender</label><input class="form-control" name="gender" value="<?= htmlspecialchars($u['gender'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Current location</label><input class="form-control" name="current_location" value="<?= htmlspecialchars($u['current_location'] ?? '') ?>"></div>

                <div class="col-12 mt-2"><h6>Current address</h6></div>
                <div class="col-md-3"><label class="form-label">House<?= reqBadge(true) ?></label><input class="form-control" name="house" value="<?= htmlspecialchars($u['house'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Road<?= reqBadge(true) ?></label><input class="form-control" name="road" value="<?= htmlspecialchars($u['road'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Area<?= reqBadge(true) ?></label><input class="form-control" name="area" value="<?= htmlspecialchars($u['area'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">City<?= reqBadge(true) ?></label><input class="form-control" name="city" value="<?= htmlspecialchars($u['city'] ?? '') ?>" required></div>

                <div class="col-12 mt-2"><h6>Permanent address</h6></div>
                <div class="col-md-3"><label class="form-label">House<?= reqBadge(true) ?></label><input class="form-control" name="perm_house" value="<?= htmlspecialchars($u['perm_house'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Road<?= reqBadge(true) ?></label><input class="form-control" name="perm_road" value="<?= htmlspecialchars($u['perm_road'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Area<?= reqBadge(true) ?></label><input class="form-control" name="perm_area" value="<?= htmlspecialchars($u['perm_area'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">City<?= reqBadge(true) ?></label><input class="form-control" name="perm_city" value="<?= htmlspecialchars($u['perm_city'] ?? '') ?>" required></div>

                <div class="col-12"><label class="form-label">Extra address notes</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($u['address'] ?? '') ?></textarea></div>

                <?php
                $levels = [
        ['ssc', 'SSC'],
        ['hsc', 'HSC'],
        ['ug', 'Undergraduate'],
        ['pg', 'Post graduate'],
    ];
foreach ($levels as [$pfx, $label]) {
    $req = in_array($pfx, ['ssc', 'hsc'], true);
    $requiredAttr = $req ? ' required' : '';
    echo '<div class="col-12 mt-3"><h6>' . htmlspecialchars($label) . '</h6></div>';
    echo '<div class="col-md-6"><label class="form-label">Institution' . ($req ? reqBadge(true) : '') . '</label><input class="form-control" name="' . $pfx . '_institution" value="' . htmlspecialchars($u[$pfx . '_institution'] ?? '') . '"' . $requiredAttr . '></div>';
    echo '<div class="col-md-6"><label class="form-label">Group / board</label><input class="form-control" name="' . $pfx . '_group" value="' . htmlspecialchars($u[$pfx . '_group'] ?? '') . '"></div>';
    if (!in_array($pfx, ['ssc', 'hsc'], true)) {
        echo '<div class="col-md-4"><label class="form-label">Degree / program</label><input class="form-control" name="' . $pfx . '_degree" value="' . htmlspecialchars($u[$pfx . '_degree'] ?? '') . '"></div>';
    }
    echo '<div class="col-md-4"><label class="form-label">Passed year' . ($req ? reqBadge(true) : '') . '</label><input class="form-control" name="' . $pfx . '_year" value="' . htmlspecialchars($u[$pfx . '_year'] ?? '') . '"' . $requiredAttr . '></div>';
    echo '<div class="col-md-4"><label class="form-label">Grade / GPA</label><input class="form-control" name="' . $pfx . '_grade" value="' . htmlspecialchars($u[$pfx . '_grade'] ?? '') . '"></div>';
}
?>

                <div class="col-md-6"><label class="form-label">Overall graduation year</label><input class="form-control" name="graduation_year" value="<?= htmlspecialchars($u['graduation_year'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Overall grade / CGPA</label><input class="form-control" name="grade_cgpa" value="<?= htmlspecialchars($u['grade_cgpa'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Hard skills<?= reqBadge(true) ?></label><input class="form-control" name="hard_skills" value="<?= htmlspecialchars($u['hard_skills'] ?? '') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Soft skills</label><input class="form-control" name="soft_skills" value="<?= htmlspecialchars($u['soft_skills'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Experience level</label><input class="form-control" name="experience_level" value="<?= htmlspecialchars($u['experience_level'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Other skills</label><input class="form-control" name="skills" value="<?= htmlspecialchars($u['skills'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Languages</label><input class="form-control" name="languages" value="<?= htmlspecialchars($u['languages'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">LinkedIn</label><input class="form-control" name="linkedin" value="<?= htmlspecialchars($u['linkedin'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">GitHub</label><input class="form-control" name="github" value="<?= htmlspecialchars($u['github'] ?? '') ?>"></div>
                <div class="col-12"><label class="form-label">Portfolio (projects, links, notes)</label><textarea class="form-control" name="portfolio" rows="8" placeholder="Describe projects vertically — links, bullets, or narrative."><?= htmlspecialchars($u['portfolio'] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label">Professional summary<?= reqBadge(true) ?></label><textarea class="form-control" name="summary" rows="4" required><?= htmlspecialchars($u['summary'] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label">Certifications</label><textarea class="form-control" name="certifications" rows="2"><?= htmlspecialchars($u['certifications'] ?? '') ?></textarea></div>
                <div class="col-12"><label class="form-label">Career objective</label><textarea class="form-control" name="career_objective" rows="2"><?= htmlspecialchars($u['career_objective'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label class="form-label">Expected salary range</label><input class="form-control" name="expected_salary" value="<?= htmlspecialchars($u['expected_salary'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Preferred work type</label><input class="form-control" name="preferred_work_type" value="<?= htmlspecialchars($u['preferred_work_type'] ?? '') ?>" placeholder="Remote / Hybrid / On-site"></div>
            </div>
            <button class="btn btn-primary mt-3">Save profile</button>
        </form>
    </div>
</div>
<?php renderFooter(); ?>
