-- Run once when upgrading an older carrier_hunt database to match the new PHP code.
-- Do not run on a database created from the latest schema.sql (columns already exist).
-- If a statement errors with "Duplicate column", skip that line and continue.
USE carrier_hunt;

-- --- users: structured education, permanent address, extra fields, portfolio text ---
ALTER TABLE users MODIFY COLUMN portfolio TEXT NULL;

ALTER TABLE users
  ADD COLUMN perm_house VARCHAR(120) NULL AFTER city,
  ADD COLUMN perm_road VARCHAR(120) NULL AFTER perm_house,
  ADD COLUMN perm_area VARCHAR(120) NULL AFTER perm_road,
  ADD COLUMN perm_city VARCHAR(100) NULL AFTER perm_area;

ALTER TABLE users
  ADD COLUMN ssc_institution VARCHAR(200) NULL AFTER postgraduate_info,
  ADD COLUMN ssc_group VARCHAR(120) NULL AFTER ssc_institution,
  ADD COLUMN ssc_degree VARCHAR(120) NULL AFTER ssc_group,
  ADD COLUMN ssc_year VARCHAR(20) NULL AFTER ssc_degree,
  ADD COLUMN ssc_grade VARCHAR(50) NULL AFTER ssc_year,
  ADD COLUMN hsc_institution VARCHAR(200) NULL AFTER ssc_grade,
  ADD COLUMN hsc_group VARCHAR(120) NULL AFTER hsc_institution,
  ADD COLUMN hsc_degree VARCHAR(120) NULL AFTER hsc_group,
  ADD COLUMN hsc_year VARCHAR(20) NULL AFTER hsc_degree,
  ADD COLUMN hsc_grade VARCHAR(50) NULL AFTER hsc_year,
  ADD COLUMN ug_institution VARCHAR(200) NULL AFTER hsc_grade,
  ADD COLUMN ug_group VARCHAR(120) NULL AFTER ug_institution,
  ADD COLUMN ug_degree VARCHAR(120) NULL AFTER ug_group,
  ADD COLUMN ug_year VARCHAR(20) NULL AFTER ug_degree,
  ADD COLUMN ug_grade VARCHAR(50) NULL AFTER ug_year,
  ADD COLUMN pg_institution VARCHAR(200) NULL AFTER ug_grade,
  ADD COLUMN pg_group VARCHAR(120) NULL AFTER pg_institution,
  ADD COLUMN pg_degree VARCHAR(120) NULL AFTER pg_group,
  ADD COLUMN pg_year VARCHAR(20) NULL AFTER pg_degree,
  ADD COLUMN pg_grade VARCHAR(50) NULL AFTER pg_year;

ALTER TABLE users
  ADD COLUMN languages VARCHAR(255) NULL AFTER pg_grade,
  ADD COLUMN certifications TEXT NULL AFTER languages,
  ADD COLUMN career_objective TEXT NULL AFTER certifications,
  ADD COLUMN expected_salary VARCHAR(120) NULL AFTER career_objective,
  ADD COLUMN preferred_work_type VARCHAR(80) NULL AFTER expected_salary;

-- Copy legacy single-line education fields into structured columns if new columns are empty (optional one-time convenience)
UPDATE users SET ssc_institution = ssc_info WHERE (ssc_institution IS NULL OR ssc_institution = '') AND ssc_info IS NOT NULL AND ssc_info != '';
UPDATE users SET hsc_institution = hsc_info WHERE (hsc_institution IS NULL OR hsc_institution = '') AND hsc_info IS NOT NULL AND hsc_info != '';

-- --- companies: trade licence number, structured office address (safe if columns already exist: skip errors manually) ---
ALTER TABLE companies ADD COLUMN trade_license_number VARCHAR(120) NULL;
ALTER TABLE companies ADD COLUMN office_house VARCHAR(120) NULL;
ALTER TABLE companies ADD COLUMN office_road VARCHAR(120) NULL;
ALTER TABLE companies ADD COLUMN office_area VARCHAR(120) NULL;
ALTER TABLE companies ADD COLUMN office_city VARCHAR(100) NULL;

-- Legacy DBs only (if column trade_license_file exists):
-- UPDATE companies SET trade_license_number = trade_license_file WHERE trade_license_number IS NULL AND trade_license_file IS NOT NULL AND trade_license_file != '';
