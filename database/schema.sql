SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS admin_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS administrators (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES admin_roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email_verified_at DATETIME NULL,
    login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS states (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS local_governments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    INDEX idx_lga_state (state_id),
    CONSTRAINT fk_lga_state FOREIGN KEY (state_id) REFERENCES states(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS banks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS occupations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    first_name VARCHAR(80) NULL,
    middle_name VARCHAR(80) NULL,
    last_name VARCHAR(80) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male','female','other','prefer_not_to_say') NULL,
    phone VARCHAR(20) NULL,
    alt_phone VARCHAR(20) NULL,
    nationality VARCHAR(80) NULL DEFAULT 'Nigerian',
    state_id INT UNSIGNED NULL,
    lga_id INT UNSIGNED NULL,
    address VARCHAR(255) NULL,
    id_type VARCHAR(50) NULL,
    id_number VARCHAR(80) NULL,
    photo_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_profile_state FOREIGN KEY (state_id) REFERENCES states(id),
    CONSTRAINT fk_profile_lga FOREIGN KEY (lga_id) REFERENCES local_governments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    reference VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('draft','submitted','under_review','additional_info','approved','rejected') NOT NULL DEFAULT 'draft',
    step TINYINT UNSIGNED NOT NULL DEFAULT 1,
    employment_status VARCHAR(50) NULL,
    occupation VARCHAR(120) NULL,
    employment_category VARCHAR(80) NULL,
    employer_name VARCHAR(160) NULL,
    monthly_income_range VARCHAR(50) NULL,
    employment_state_id INT UNSIGNED NULL,
    employment_lga_id INT UNSIGNED NULL,
    work_address VARCHAR(255) NULL,
    financial_circumstances TEXT NULL,
    bank_id INT UNSIGNED NULL,
    account_number VARCHAR(20) NULL,
    account_name VARCHAR(160) NULL,
    account_verified TINYINT(1) NOT NULL DEFAULT 0,
    declaration_accepted TINYINT(1) NOT NULL DEFAULT 0,
    privacy_accepted TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at DATETIME NULL,
    reviewed_at DATETIME NULL,
    reviewed_by INT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    additional_info_request TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_app_status (status),
    INDEX idx_app_ref (reference),
    CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_app_bank FOREIGN KEY (bank_id) REFERENCES banks(id),
    CONSTRAINT fk_app_emp_state FOREIGN KEY (employment_state_id) REFERENCES states(id),
    CONSTRAINT fk_app_emp_lga FOREIGN KEY (employment_lga_id) REFERENCES local_governments(id),
    CONSTRAINT fk_app_reviewer FOREIGN KEY (reviewed_by) REFERENCES administrators(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS application_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(190) NOT NULL,
    mime_type VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_doc_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS application_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_note_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_note_admin FOREIGN KEY (admin_id) REFERENCES administrators(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grant_funding (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(160) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    allocated_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    available_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grant_approvals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    funding_id INT UNSIGNED NULL,
    amount DECIMAL(15,2) NOT NULL,
    approval_reference VARCHAR(40) NOT NULL UNIQUE,
    eligibility_confirmed TINYINT(1) NOT NULL DEFAULT 0,
    funding_confirmed TINYINT(1) NOT NULL DEFAULT 0,
    wallet_credited TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_ga_app FOREIGN KEY (application_id) REFERENCES applications(id),
    CONSTRAINT fk_ga_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_ga_admin FOREIGN KEY (admin_id) REFERENCES administrators(id),
    CONSTRAINT fk_ga_fund FOREIGN KEY (funding_id) REFERENCES grant_funding(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    available_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    ledger_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reference VARCHAR(40) NOT NULL UNIQUE,
    direction ENUM('credit','debit') NOT NULL,
    category ENUM('grant_credit','card_processing_fee','card_delivery_fee','wallet_adjustment','refund') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    related_id INT UNSIGNED NULL,
    status ENUM('pending','success','failed','reversed') NOT NULL DEFAULT 'success',
    created_at DATETIME NOT NULL,
    INDEX idx_wtx_user (user_id, created_at),
    CONSTRAINT fk_wtx_wallet FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    CONSTRAINT fk_wtx_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS collection_centres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    state_id INT UNSIGNED NOT NULL,
    lga_id INT UNSIGNED NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_cc_state FOREIGN KEY (state_id) REFERENCES states(id),
    CONSTRAINT fk_cc_lga FOREIGN KEY (lga_id) REFERENCES local_governments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS card_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reference VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(160) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    state_id INT UNSIGNED NOT NULL,
    lga_id INT UNSIGNED NOT NULL,
    delivery_address VARCHAR(255) NOT NULL,
    collection_centre_id INT UNSIGNED NULL,
    processing_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
    delivery_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('not_requested','awaiting_payment','payment_confirmed','processing','produced','dispatched','ready_for_collection','delivered','cancelled') NOT NULL DEFAULT 'awaiting_payment',
    production_reference VARCHAR(80) NULL,
    dispatch_info VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_cr_user (user_id),
    INDEX idx_cr_status (status),
    CONSTRAINT fk_cr_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_cr_state FOREIGN KEY (state_id) REFERENCES states(id),
    CONSTRAINT fk_cr_lga FOREIGN KEY (lga_id) REFERENCES local_governments(id),
    CONSTRAINT fk_cr_centre FOREIGN KEY (collection_centre_id) REFERENCES collection_centres(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS card_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    card_request_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    reference VARCHAR(80) NOT NULL UNIQUE,
    amount DECIMAL(15,2) NOT NULL,
    status ENUM('pending','success','failed','abandoned') NOT NULL DEFAULT 'pending',
    channel VARCHAR(40) NULL,
    gateway_response VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    verified_at DATETIME NULL,
    raw_payload JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_cp_request FOREIGN KEY (card_request_id) REFERENCES card_requests(id),
    CONSTRAINT fk_cp_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    card_request_id INT UNSIGNED NOT NULL UNIQUE,
    display_name VARCHAR(160) NOT NULL,
    masked_pan VARCHAR(30) NULL,
    status ENUM('pending','issued','active','blocked') NOT NULL DEFAULT 'pending',
    issued_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_card_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_card_request FOREIGN KEY (card_request_id) REFERENCES card_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT NOT NULL,
    type VARCHAR(40) NOT NULL DEFAULT 'info',
    link VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_notif_user (user_id, is_read),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_evt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id INT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_aal_admin (admin_id, created_at),
    CONSTRAINT fk_aal_admin FOREIGN KEY (admin_id) REFERENCES administrators(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(80) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    is_secret TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
