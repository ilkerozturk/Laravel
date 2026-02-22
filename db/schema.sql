CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'sales', 'operator') NOT NULL DEFAULT 'operator',
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE companies (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  place_id VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(190) NOT NULL,
  phone VARCHAR(64) NULL,
  email VARCHAR(190) NULL,
  address VARCHAR(255) NULL,
  city VARCHAR(120) NULL,
  district VARCHAR(120) NULL,
  website VARCHAR(255) NULL,
  google_category VARCHAR(190) NULL,
  activity_area VARCHAR(255) NULL,
  activity_confidence DECIMAL(4,3) NULL,
  demo_prompt LONGTEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE leads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id BIGINT UNSIGNED NOT NULL UNIQUE,
  owner_user_id BIGINT UNSIGNED NULL,
  status ENUM('new','demo_ready','email_sent','call_due','won','lost','postponed') NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_leads_company FOREIGN KEY (company_id) REFERENCES companies(id),
  CONSTRAINT fk_leads_owner FOREIGN KEY (owner_user_id) REFERENCES users(id)
);

CREATE TABLE demo_sites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  prompt_text LONGTEXT NOT NULL,
  deploy_url VARCHAR(255) NULL,
  status ENUM('pending','generated','deployed','failed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_demo_sites_lead FOREIGN KEY (lead_id) REFERENCES leads(id)
);

CREATE TABLE outreach_emails (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  to_email VARCHAR(190) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  body_html LONGTEXT NOT NULL,
  status ENUM('draft','sent','failed') NOT NULL DEFAULT 'draft',
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_outreach_emails_lead FOREIGN KEY (lead_id) REFERENCES leads(id)
);

CREATE TABLE follow_ups (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT UNSIGNED NOT NULL,
  due_at TIMESTAMP NOT NULL,
  status ENUM('open','done','canceled') NOT NULL DEFAULT 'open',
  call_note TEXT NULL,
  completed_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_follow_ups_lead FOREIGN KEY (lead_id) REFERENCES leads(id)
);

CREATE TABLE place_import_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(120) NOT NULL,
  district VARCHAR(120) NOT NULL,
  keyword VARCHAR(190) NULL,
  max_pages TINYINT UNSIGNED NOT NULL DEFAULT 1,
  pages_processed TINYINT UNSIGNED NOT NULL DEFAULT 0,
  fetched_result_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_count INT UNSIGNED NOT NULL DEFAULT 0,
  updated_count INT UNSIGNED NOT NULL DEFAULT 0,
  skipped_count INT UNSIGNED NOT NULL DEFAULT 0,
  new_lead_count INT UNSIGNED NOT NULL DEFAULT 0,
  executed_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE app_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(120) NOT NULL UNIQUE,
  `value` TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE demo_projects (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  status ENUM('pending','generated','failed') NOT NULL DEFAULT 'pending',
  prompt_text LONGTEXT NULL,
  folder_path VARCHAR(255) NULL,
  zip_path VARCHAR(255) NULL,
  download_token VARCHAR(80) NOT NULL UNIQUE,
  progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
  error_message TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_demo_projects_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
