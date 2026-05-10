# Carrier_Hunt_A_Job_Placement_and_Grooming_Platform
# Carrier Hunt

## About Carrier Hunt
Carrier Hunt is a role-based recruitment and grooming platform that built for candidates, companies, and administrators.Main focus of this project  is to connect job seekers with employers through a quiz-driven application process as sorting all quality cv that is not ideal or good for any specific position can be difficult and time consummiing so this system ensure that the specific candidate has the quality  for the position,  who failed to qualify for  the position , will be suggested some content to  groom them so they can fullsill their lackings .

## What this project does
- Lets companies register, create job postings, and manage candidate applications.
- Lets candidates browse jobs, complete profile details, take job-specific quizzes, and apply only after meeting eligibility conditions.
- Lets admins review and approve companies, monitor platform activity, and support onboarding in early project phases.
 
## Carrier Hunt is divided in two phase 

## Phase 1 
Implemented:
- Candidate registration and login - Company registration and login Job posting by company Job browsing by candidate
- Quiz creation per job
- Quiz attempt and scoring
- Pass/fail gate before apply
- Candidate profile as auto CV/resume 
- Grooming course listing and progress marking
- Basic candidate application tracking
- Admin dashboard with company approval queue
- Landing page redesign with role-based auth entry and footer

## Stack
- Frontend: HTML, Bootstrap, JS
- Backend: PHP (PDO)
- Database: MySQL

## Setup
1. Create database `carrier_hunt` in phpMyAdmin.
2. Import `database/schema.sql` (re-import if you used previous schema). 
3. Update DB credentials in `config/database.php` if needed.
4. Place this folder inside `htdocs`.
5. Open Link: `http://localhost/Web%20Project/`

## Test Accounts
- Create users/companies via registration pages.
- Admin login :  
  - `admin@gamil.com` / `admin123`  
  - `admin2@gmail.com` / `admin123`  
  - `admin3@gmail.com` / `admin123`  
  - `admin4@gmail.com` / `admin123`


