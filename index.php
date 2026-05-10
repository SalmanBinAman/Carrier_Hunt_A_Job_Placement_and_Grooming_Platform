<?php
require_once __DIR__ . '/includes/layout.php';
renderHeader('Carrier Hunt');
?>
<div class="hero-card p-4 p-md-5 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <h1 class="display-6 fw-bold">Carrier Hunt</h1>
            <p class="lead mb-4">A modern job grooming and placement platform where candidates prepare, qualify, and get hired smarter.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="login.php" class="btn btn-light">Get Started</a>
                <a href="jobs.php" class="btn btn-outline-light">Browse Open Jobs</a>
            </div>
        </div>
        <div class="col-lg-5 text-center">
            <img class="img-fluid rounded-4 shadow" alt="Career growth" src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1100&q=80">
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h5>Candidate Zone</h5>
            <p class="text-muted">Create profile, take quizzes, and apply confidently.</p>
            <a href="register.php?role=candidate" class="btn btn-success btn-sm">Register</a>
            <a href="login.php?role=candidate" class="btn btn-primary btn-sm">Login</a>
            <a href="login.php?role=candidate" class="btn btn-outline-dark btn-sm">Dashboard</a>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h5>Company Zone</h5>
            <p class="text-muted">Post jobs and recruit top-ready candidates.</p>
            <a href="register.php?role=company" class="btn btn-success btn-sm">Register</a>
            <a href="login.php?role=company" class="btn btn-primary btn-sm">Login</a>
            <a href="login.php?role=company" class="btn btn-outline-dark btn-sm">Dashboard</a>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h5>Admin Zone</h5>
            <p class="text-muted">Approve companies and monitor the platform.</p>
            <a href="login.php?role=admin" class="btn btn-warning btn-sm">Admin Login</a>
            <a href="login.php?role=admin" class="btn btn-outline-dark btn-sm">Dashboard</a>
        </div></div>
    </div>
</div>
<?php renderFooter(); ?>
