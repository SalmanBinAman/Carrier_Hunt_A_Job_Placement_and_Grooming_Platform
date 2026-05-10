CREATE DATABASE IF NOT EXISTS carrier_hunt;
USE carrier_hunt;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    professional_headline VARCHAR(160),
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    country_code VARCHAR(10),
    phone VARCHAR(30),
    profile_photo VARCHAR(255),
    dob DATE NULL,
    gender VARCHAR(20),
    current_location VARCHAR(255),
    house VARCHAR(120),
    road VARCHAR(120),
    area VARCHAR(120),
    city VARCHAR(100),
    perm_house VARCHAR(120),
    perm_road VARCHAR(120),
    perm_area VARCHAR(120),
    perm_city VARCHAR(100),
    address TEXT,
    ssc_info VARCHAR(255),
    hsc_info VARCHAR(255),
    undergraduate_info VARCHAR(255),
    postgraduate_info VARCHAR(255),
    ssc_institution VARCHAR(200),
    ssc_group VARCHAR(120),
    ssc_degree VARCHAR(120),
    ssc_year VARCHAR(20),
    ssc_grade VARCHAR(50),
    hsc_institution VARCHAR(200),
    hsc_group VARCHAR(120),
    hsc_degree VARCHAR(120),
    hsc_year VARCHAR(20),
    hsc_grade VARCHAR(50),
    ug_institution VARCHAR(200),
    ug_group VARCHAR(120),
    ug_degree VARCHAR(120),
    ug_year VARCHAR(20),
    ug_grade VARCHAR(50),
    pg_institution VARCHAR(200),
    pg_group VARCHAR(120),
    pg_degree VARCHAR(120),
    pg_year VARCHAR(20),
    pg_grade VARCHAR(50),
    graduation_year VARCHAR(30),
    grade_cgpa VARCHAR(20),
    hard_skills TEXT,
    soft_skills TEXT,
    education VARCHAR(150),
    university VARCHAR(150),
    cgpa VARCHAR(20),
    experience_level VARCHAR(100),
    skills TEXT,
    linkedin VARCHAR(200),
    github VARCHAR(200),
    portfolio TEXT,
    summary TEXT,
    languages VARCHAR(255),
    certifications TEXT,
    career_objective TEXT,
    expected_salary VARCHAR(120),
    preferred_work_type VARCHAR(80),
    profile_completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(180) NOT NULL,
    business_registration_number VARCHAR(120),
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    trade_license_number VARCHAR(120),
    contact_person VARCHAR(120),
    contact_phone VARCHAR(30),
    brand_name VARCHAR(150),
    company_logo VARCHAR(255),
    company_size VARCHAR(50),
    founded_year VARCHAR(10),
    office_house VARCHAR(120),
    office_road VARCHAR(120),
    office_area VARCHAR(120),
    office_city VARCHAR(100),
    office_address TEXT,
    industry VARCHAR(100),
    website VARCHAR(150),
    description TEXT,
    is_approved TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    job_title VARCHAR(150) NOT NULL,
    department VARCHAR(120),
    job_category VARCHAR(120),
    employment_type VARCHAR(50),
    workplace_type VARCHAR(50),
    office_location VARCHAR(255),
    description TEXT NOT NULL,
    responsibilities TEXT,
    min_experience_years INT DEFAULT 0,
    minimum_education VARCHAR(120),
    required_skills TEXT,
    perks_benefits TEXT,
    experience_level VARCHAR(80),
    salary_min DECIMAL(12,2) NULL,
    salary_max DECIMAL(12,2) NULL,
    salary_currency VARCHAR(10) DEFAULT 'BDT',
    salary_visibility ENUM('Public','Private') DEFAULT 'Public',
    salary VARCHAR(100),
    deadline DATE,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_jobs_company FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    quiz_score INT DEFAULT 0,
    status VARCHAR(40) DEFAULT 'Applied',
    applied_at DATETIME NOT NULL,
    CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_app_job FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_job (user_id, job_id)
);

CREATE TABLE IF NOT EXISTS quiz (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    total_marks INT DEFAULT 20,
    pass_marks INT DEFAULT 12,
    duration INT DEFAULT 20,
    CONSTRAINT fk_quiz_job FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    category_name VARCHAR(80) NOT NULL DEFAULT 'General',
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer ENUM('a','b','c','d') NOT NULL,
    CONSTRAINT fk_question_quiz FOREIGN KEY (quiz_id) REFERENCES quiz(quiz_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_category_rules (
    rule_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    category_name VARCHAR(80) NOT NULL,
    min_correct INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_quiz_rule_quiz FOREIGN KEY (quiz_id) REFERENCES quiz(quiz_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    score INT NOT NULL,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL,
    CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_job FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_type ENUM('admin','company','candidate') NOT NULL,
    recipient_id INT NOT NULL DEFAULT 0,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS course (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_title VARCHAR(150) NOT NULL,
    description TEXT,
    related_skill VARCHAR(120)
);

CREATE TABLE IF NOT EXISTS course_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    completion_status ENUM('In Progress','Completed') DEFAULT 'In Progress',
    CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_course FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_course (user_id, course_id)
);

INSERT INTO course (course_title, description, related_skill) VALUES
('Programming Fundamentals', 'Core logic, syntax and problem solving basics.', 'Programming'),
('Database Essentials', 'SQL basics, joins and normalization fundamentals.', 'Database'),
('Algorithms Practice', 'Complexity and common data structure practice.', 'Algorithms')
ON DUPLICATE KEY UPDATE course_title = course_title;
