SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
CREATE TABLE IF NOT EXISTS users (
    user_id        INT            NOT NULL AUTO_INCREMENT,
    username       VARCHAR(100)   NOT NULL UNIQUE,
    password_hash  VARCHAR(255)   NOT NULL,
    full_name      VARCHAR(255)   NOT NULL,
    email          VARCHAR(255)   NOT NULL UNIQUE,
    role           ENUM('FrontDesk','Housekeeper','HKSupervisor',
                        'Accountant','SalesManager','RevenueManager','Manager')
                                  NOT NULL,
    is_active      TINYINT(1)     NOT NULL DEFAULT 1,
    last_login     TIMESTAMP      NULL,
    created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO users (user_id, username, password_hash, full_name, email, role, is_active)
VALUES (1, 'admin', 'hash_here', 'System Administrator', 'admin@hotel.com', 'Manager', 1)
ON DUPLICATE KEY UPDATE username = username;
CREATE TABLE IF NOT EXISTS rooms (
    room_id      INT           NOT NULL AUTO_INCREMENT,
    room_number  VARCHAR(10)   NOT NULL UNIQUE,
    room_type    ENUM('Single','Double','Suite') NOT NULL,
    floor_number INT           NOT NULL,
    capacity     INT           NOT NULL DEFAULT 2,
    base_rate    DECIMAL(10,2) NOT NULL,
    status       ENUM('Clean','Occupied','Dirty','InCleaning',
                      'Inspecting','Ready','OutOfOrder')
                               NOT NULL DEFAULT 'Clean',
    features     JSON          NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (room_id),
    INDEX idx_room_status (status),
    INDEX idx_room_type   (room_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hotel_settings (
    setting_id    INT            NOT NULL AUTO_INCREMENT,
    setting_key   VARCHAR(100)   NOT NULL UNIQUE,
    setting_value TEXT           NOT NULL,
    updated_by    INT            NOT NULL,
    updated_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO hotel_settings (setting_key, setting_value, updated_by) VALUES
('vip_ltv_threshold',   '3000',  1),
('no_show_window_hours','2',     1),
('no_show_rate',        '1',     1),
('exchange_margin_pct', '2',     1),
('checkin_time',        '14:00', 1),
('checkout_time',       '12:00', 1),
('base_currency',       'EGP',   1),
('pre_arrival_hours',   '48',    1)
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    updated_by    = VALUES(updated_by);

CREATE TABLE IF NOT EXISTS audit_log (
    log_id      INT           NOT NULL AUTO_INCREMENT,
    actor_id    INT           NOT NULL,
    action      VARCHAR(255)  NOT NULL,
    entity_type VARCHAR(100)  NOT NULL,
    entity_id   INT           NOT NULL,
    old_value   TEXT          NULL,
    new_value   TEXT          NULL,
    ip_address  VARCHAR(45)   NOT NULL,
    logged_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    FOREIGN KEY (actor_id) REFERENCES users(user_id),
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_actor  (actor_id),
    INDEX idx_audit_time   (logged_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS guests (
    guest_id        INT            NOT NULL AUTO_INCREMENT,
    first_name      VARCHAR(100)   NOT NULL,
    last_name       VARCHAR(100)   NOT NULL,
    email           VARCHAR(255)   NOT NULL,
    phone           VARCHAR(30)    NULL,
    nationality     VARCHAR(100)   NULL,
    date_of_birth   DATE           NULL,
    id_type         VARCHAR(50)    NULL,
    id_number       VARCHAR(100)   NULL,
    loyalty_tier    ENUM('Bronze','Silver','Gold','Platinum')
                                   NOT NULL DEFAULT 'Bronze',
    ltv             DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    total_nights    INT            NOT NULL DEFAULT 0,
    avg_daily_rate  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,  
    visit_count     INT            NOT NULL DEFAULT 0,
    vip_flag        TINYINT(1)     NOT NULL DEFAULT 0,
    is_anonymised   TINYINT(1)     NOT NULL DEFAULT 0,
    anonymised_at   TIMESTAMP      NULL,
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (guest_id),
    INDEX idx_guest_email (email),
    INDEX idx_guest_tier  (loyalty_tier),
    INDEX idx_guest_ltv   (ltv)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS inventory_items (
    item_id           INT           NOT NULL AUTO_INCREMENT,
    item_name         VARCHAR(255)  NOT NULL,
    category          VARCHAR(100)  NOT NULL,
    current_stock     INT           NOT NULL DEFAULT 0,
    reorder_threshold INT           NOT NULL DEFAULT 10,
    unit              VARCHAR(50)   NOT NULL DEFAULT 'piece',
    updated_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS pos_terminals (
    terminal_id   INT           NOT NULL AUTO_INCREMENT,
    terminal_name VARCHAR(100)  NOT NULL,
    location      VARCHAR(100)  NOT NULL,
    api_token_hash VARCHAR(255) NOT NULL,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    registered_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (terminal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS corporate_accounts (
    corporate_account_id INT            NOT NULL AUTO_INCREMENT,
    company_name         VARCHAR(255)   NOT NULL,
    contact_name         VARCHAR(255)   NOT NULL,
    contact_email        VARCHAR(255)   NOT NULL,
    contracted_rate      DECIMAL(10,2)  NULL,
    credit_limit         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    account_manager_id   INT            NOT NULL,
    created_at           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (corporate_account_id),
    FOREIGN KEY (account_manager_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS room_type_config (
    config_id         INT          NOT NULL AUTO_INCREMENT,
    room_type         VARCHAR(50)  NOT NULL UNIQUE,
    cleaning_time_min INT          NOT NULL DEFAULT 30,
    updated_by        INT          NOT NULL,
    updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (config_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO room_type_config (room_type, cleaning_time_min, updated_by) VALUES
('Single', 20, 1),
('Double', 30, 1),
('Suite',  50, 1)
ON DUPLICATE KEY UPDATE
    cleaning_time_min = VALUES(cleaning_time_min),
    updated_by        = VALUES(updated_by);
    CREATE TABLE IF NOT EXISTS special_dates (
    date_id      INT           NOT NULL AUTO_INCREMENT,
    guest_id     INT           NOT NULL,
    date_type    ENUM('Birthday','Anniversary','Other')
                               NOT NULL DEFAULT 'Birthday',
    label        VARCHAR(100)  NULL,          
    month        TINYINT       NOT NULL,      
    day          TINYINT       NOT NULL,      
    recorded_by  INT           NOT NULL,
    recorded_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (date_id),
    FOREIGN KEY (guest_id)    REFERENCES guests(guest_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id),
    INDEX idx_sd_guest (guest_id),
    INDEX idx_sd_month (month, day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS master_bookings (
    master_booking_id    INT           NOT NULL AUTO_INCREMENT,
    corporate_account_id INT           NULL,
    group_reference      VARCHAR(50)   NOT NULL UNIQUE,
    billing_mode         ENUM('Consolidated','Split') NOT NULL DEFAULT 'Consolidated',
    billing_contact      VARCHAR(255)  NOT NULL,
    created_by           INT           NOT NULL,
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (master_booking_id),
    FOREIGN KEY (corporate_account_id) REFERENCES corporate_accounts(corporate_account_id),
    FOREIGN KEY (created_by)           REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS guest_preferences (
    preference_id INT           NOT NULL AUTO_INCREMENT,
    guest_id      INT           NOT NULL,
    category      VARCHAR(100)  NOT NULL,
    value         VARCHAR(255)  NOT NULL,
    severity      ENUM('Preference','Allergy','Requirement')
                                NOT NULL DEFAULT 'Preference',
    recorded_by   INT           NOT NULL,
    recorded_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (preference_id),
    FOREIGN KEY (guest_id)    REFERENCES guests(guest_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id),
    INDEX idx_pref_guest    (guest_id),
    INDEX idx_pref_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reservations (
    reservation_id    INT           NOT NULL AUTO_INCREMENT,
    guest_id          INT           NOT NULL,
    room_id           INT           NOT NULL,
    master_booking_id INT           NULL,
    state             ENUM('Inquiry','Confirmed','CheckedIn',
                           'CheckedOut','FolioClosed','NoShow','Cancelled')
                                    NOT NULL DEFAULT 'Inquiry',
    arrival_date      DATE          NOT NULL,
    departure_date    DATE          NOT NULL,
    arrival_time      TIME          NULL,
    adults            INT           NOT NULL DEFAULT 1,
    children          INT           NOT NULL DEFAULT 0,
    special_requests  TEXT          NULL,
    credit_limit      DECIMAL(12,2) NOT NULL DEFAULT 500.00,  
    vip_flag          TINYINT(1)    NOT NULL DEFAULT 0,
    created_by        INT           NOT NULL,
    created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (reservation_id),
    FOREIGN KEY (guest_id)          REFERENCES guests(guest_id),
    FOREIGN KEY (room_id)           REFERENCES rooms(room_id),
    FOREIGN KEY (master_booking_id) REFERENCES master_bookings(master_booking_id),
    FOREIGN KEY (created_by)        REFERENCES users(user_id),
    INDEX idx_res_guest (guest_id),
    INDEX idx_res_room  (room_id),
    INDEX idx_res_dates (arrival_date, departure_date),
    INDEX idx_res_state (state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS stay_history (
    stay_id          INT            NOT NULL AUTO_INCREMENT,
    guest_id         INT            NOT NULL,
    reservation_id   INT            NOT NULL,
    total_spend      DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    nights           INT            NOT NULL DEFAULT 0,
    avg_daily_rate   DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    check_in_date    DATE           NOT NULL,
    check_out_date   DATE           NOT NULL,
    is_amended       TINYINT(1)     NOT NULL DEFAULT 0,
    amendment_reason TEXT           NULL,
    created_at       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (stay_id),
    FOREIGN KEY (guest_id)       REFERENCES guests(guest_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    INDEX idx_stay_guest (guest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS non_grata (
    entry_id      INT           NOT NULL AUTO_INCREMENT,
    guest_id      INT           NOT NULL,
    reason_code   VARCHAR(100)  NOT NULL,
    incident_date DATE          NOT NULL,
    added_by      INT           NOT NULL,
    status        ENUM('Active','Lifted') NOT NULL DEFAULT 'Active',
    lifted_reason TEXT          NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (entry_id),
    FOREIGN KEY (guest_id)  REFERENCES guests(guest_id),
    FOREIGN KEY (added_by)  REFERENCES users(user_id),
    INDEX idx_nongrata_guest (guest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS room_status_log (
    log_id         INT          NOT NULL AUTO_INCREMENT,
    room_id        INT          NOT NULL,
    previous_state VARCHAR(50)  NOT NULL,
    new_state      VARCHAR(50)  NOT NULL,
    changed_by     INT          NOT NULL,   
    changed_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    FOREIGN KEY (room_id)    REFERENCES rooms(room_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id),
    INDEX idx_rsl_room (room_id, changed_at)    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS ltv_audit_log (
    log_id        INT            NOT NULL AUTO_INCREMENT,
    guest_id      INT            NOT NULL,
    stay_id       INT            NOT NULL,
    previous_ltv  DECIMAL(12,2)  NOT NULL,
    new_ltv       DECIMAL(12,2)  NOT NULL,
    previous_tier VARCHAR(20)    NOT NULL,
    new_tier      VARCHAR(20)    NOT NULL,
    calculated_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id),
    FOREIGN KEY (stay_id)  REFERENCES stay_history(stay_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS reservation_state_log (
    log_id         INT          NOT NULL AUTO_INCREMENT,
    reservation_id INT          NOT NULL,
    previous_state VARCHAR(50)  NOT NULL,
    new_state      VARCHAR(50)  NOT NULL,
    triggered_by   INT          NOT NULL,
    transition_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (triggered_by)   REFERENCES users(user_id),
    INDEX idx_reslog_res (reservation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS preference_task_rules (
    rule_id   INT           NOT NULL AUTO_INCREMENT,
    category  VARCHAR(100)  NOT NULL,
    value     VARCHAR(255)  NOT NULL,
    severity  VARCHAR(50)   NULL,
    task_type VARCHAR(100)  NOT NULL,
    task_desc VARCHAR(500)  NOT NULL,
    priority  ENUM('LOW','NORMAL','HIGH') NOT NULL DEFAULT 'NORMAL',
    is_active TINYINT(1)    NOT NULL DEFAULT 1,
    created_by INT          NOT NULL,
    PRIMARY KEY (rule_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS maintenance_schedule (
    schedule_id  INT           NOT NULL AUTO_INCREMENT,
    task_type    ENUM('DeepClean','HVAC','PestControl','Other')
                               NOT NULL,
    label        VARCHAR(255)  NOT NULL,        
    interval_days INT          NOT NULL,        
    room_scope   ENUM('All','ByType','ByFloor','Specific')
                               NOT NULL DEFAULT 'All',
    room_type    VARCHAR(50)   NULL,            
    floor_number INT           NULL,            
    room_id      INT           NULL,            
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_by   INT           NOT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (schedule_id),
    FOREIGN KEY (room_id)    REFERENCES rooms(room_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS consumption_rules (
    rule_id   INT          NOT NULL AUTO_INCREMENT,
    room_type VARCHAR(50)  NOT NULL,
    item_id   INT          NOT NULL,
    quantity  INT          NOT NULL DEFAULT 1,
    updated_by INT         NOT NULL,
    PRIMARY KEY (rule_id),
    UNIQUE KEY uq_consumption (room_type, item_id),
    FOREIGN KEY (item_id)    REFERENCES inventory_items(item_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lost_found (
    item_id          INT           NOT NULL AUTO_INCREMENT,
    room_id          INT           NOT NULL,
    found_by         INT           NOT NULL,
    linked_guest_id  INT           NULL,
    description      TEXT          NOT NULL,
    photo_path       VARCHAR(500)  NULL,
    found_date       DATE          NOT NULL,
    status           ENUM('Unclaimed','Returned','Disposed')
                                   NOT NULL DEFAULT 'Unclaimed',
    claimant_name    VARCHAR(255)  NULL,
    claimant_contact VARCHAR(255)  NULL,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (item_id),
    FOREIGN KEY (room_id)        REFERENCES rooms(room_id),
    FOREIGN KEY (found_by)       REFERENCES users(user_id),
    FOREIGN KEY (linked_guest_id) REFERENCES guests(guest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS folios (
    folio_id            INT            NOT NULL AUTO_INCREMENT,
    reservation_id      INT            NOT NULL,
    guest_id            INT            NOT NULL,
    total_amount        DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    total_paid          DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    outstanding_balance DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    status              ENUM('Open','Closed','Split') NOT NULL DEFAULT 'Open',
    parent_folio_id     INT            NULL,
    closed_at           DATETIME       NULL,
    created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (folio_id),
    FOREIGN KEY (reservation_id)  REFERENCES reservations(reservation_id),
    FOREIGN KEY (guest_id)        REFERENCES guests(guest_id),
    FOREIGN KEY (parent_folio_id) REFERENCES folios(folio_id),
    INDEX idx_folio_res   (reservation_id),
    INDEX idx_folio_guest (guest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS perk_alerts (
    alert_id       INT           NOT NULL AUTO_INCREMENT,
    reservation_id INT           NOT NULL,
    guest_id       INT           NOT NULL,
    date_id        INT           NOT NULL,          
    suggested_perks JSON         NULL,              
    status         ENUM('Pending','Approved','Modified','Dismissed')
                                 NOT NULL DEFAULT 'Pending',
    handled_by     INT           NULL,
    handled_at     TIMESTAMP     NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (alert_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (guest_id)       REFERENCES guests(guest_id),
    FOREIGN KEY (date_id)        REFERENCES special_dates(date_id),
    FOREIGN KEY (handled_by)     REFERENCES users(user_id),
    INDEX idx_pa_reservation (reservation_id),
    INDEX idx_pa_status      (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS hk_tasks (
    task_id        INT           NOT NULL AUTO_INCREMENT,
    room_id        INT           NOT NULL,
    reservation_id INT           NULL,
    assigned_to    INT           NULL,
    inspected_by   INT           NULL,         
    task_type      ENUM('Cleaning','SpecialSetup','Inspection',
                        'Maintenance','DeepClean','TurnDown')
                                 NOT NULL DEFAULT 'Cleaning',
    status         ENUM('Pending','InProgress','Completed','Cancelled')
                                 NOT NULL DEFAULT 'Pending',
    priority       ENUM('LOW','NORMAL','HIGH','URGENT')
                                 NOT NULL DEFAULT 'NORMAL',
    deadline       DATETIME      NULL,
    completed_at   DATETIME      NULL,
    quality_score  DECIMAL(4,2)  NULL,
    notes          TEXT          NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (task_id),
    FOREIGN KEY (room_id)        REFERENCES rooms(room_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (assigned_to)    REFERENCES users(user_id),
    FOREIGN KEY (inspected_by)   REFERENCES users(user_id),
    INDEX idx_hk_room     (room_id),
    INDEX idx_hk_assigned (assigned_to),
    INDEX idx_hk_status   (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS pre_arrival_responses (
    response_id        INT           NOT NULL AUTO_INCREMENT,
    reservation_id     INT           NOT NULL,
    guest_id           INT           NOT NULL,
    sent_at            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at       TIMESTAMP     NULL,
    dietary_notes      TEXT          NULL,
    transport_request  TEXT          NULL,
    special_occasion   VARCHAR(255)  NULL,
    early_checkin_pref TINYINT(1)    NOT NULL DEFAULT 0,
    extra_notes        TEXT          NULL,
    status             ENUM('Sent','Responded','NoResponse','Expired')
                                     NOT NULL DEFAULT 'Sent',
    PRIMARY KEY (response_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (guest_id)       REFERENCES guests(guest_id),
    INDEX idx_par_reservation (reservation_id),
    INDEX idx_par_status      (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS digital_keys (
    key_id         INT           NOT NULL AUTO_INCREMENT,
    reservation_id INT           NOT NULL,
    room_id        INT           NOT NULL,
    guest_id       INT           NOT NULL,
    encrypted_key  VARCHAR(500)  NOT NULL,
    valid_from     DATETIME      NOT NULL,
    valid_until    DATETIME      NOT NULL,
    is_active      TINYINT(1)    NOT NULL DEFAULT 1,
    generated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (key_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (room_id)        REFERENCES rooms(room_id),
    FOREIGN KEY (guest_id)       REFERENCES guests(guest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS folio_items (
    line_id                 INT            NOT NULL AUTO_INCREMENT,
    folio_id                INT            NOT NULL,
    description             VARCHAR(500)   NOT NULL,
    quantity                INT            NOT NULL DEFAULT 1,
    unit_rate               DECIMAL(10,2)  NOT NULL,
    total                   DECIMAL(12,2)  NOT NULL,
    item_type               ENUM('RoomRate','Tax','TourismLevy','POSCharge',
                                 'Minibar','Manual','Cancellation','NoShowPenalty')
                                           NOT NULL,
    source_id               VARCHAR(100)   NULL,
    external_transaction_id VARCHAR(255)   NULL,
    is_voided               TINYINT(1)     NOT NULL DEFAULT 0,
    void_reason             TEXT           NULL,
    posted_by               INT            NOT NULL,
    posted_at               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (line_id),
    UNIQUE KEY uq_ext_txn (external_transaction_id),
    FOREIGN KEY (folio_id)  REFERENCES folios(folio_id),
    FOREIGN KEY (posted_by) REFERENCES users(user_id),
    INDEX idx_fi_folio     (folio_id, posted_at),    -- SRS-specified index
    INDEX idx_fi_type      (item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS payments (
    payment_id               INT            NOT NULL AUTO_INCREMENT,
    folio_id                 INT            NOT NULL,
    amount_foreign           DECIMAL(12,2)  NOT NULL,
    currency                 VARCHAR(10)    NOT NULL DEFAULT 'EGP',
    exchange_rate_snapshot   DECIMAL(10,6)  NOT NULL DEFAULT 1.000000,
    rate_date                DATE           NOT NULL,
    base_currency_equivalent DECIMAL(12,2)  NOT NULL,
    payment_method           VARCHAR(100)   NOT NULL,
    transaction_ref          VARCHAR(255)   NULL,
    processed_by             INT            NOT NULL,
    processed_at             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (payment_id),
    FOREIGN KEY (folio_id)     REFERENCES folios(folio_id),
    FOREIGN KEY (processed_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS work_orders (
    work_order_id INT           NOT NULL AUTO_INCREMENT,
    room_id       INT           NOT NULL,
    reported_by   INT           NOT NULL,
    description   TEXT          NOT NULL,
    severity      ENUM('Minor','Major','Critical') NOT NULL,
    status        ENUM('Open','InProgress','Resolved') NOT NULL DEFAULT 'Open',
    priority      ENUM('LOW','NORMAL','URGENT') NOT NULL DEFAULT 'NORMAL',
    sla_deadline  DATETIME      NULL,         
    photo_path    VARCHAR(500)  NULL,
    assigned_to   INT           NULL,
    resolved_at   DATETIME      NULL,
    sla_breached  TINYINT(1)    NOT NULL DEFAULT 0,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (work_order_id),
    FOREIGN KEY (room_id)     REFERENCES rooms(room_id),
    FOREIGN KEY (reported_by) REFERENCES users(user_id),
    FOREIGN KEY (assigned_to) REFERENCES users(user_id),
    INDEX idx_wo_room     (room_id),
    INDEX idx_wo_severity (severity),
    INDEX idx_wo_status   (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS inventory_deductions (
    deduction_id      INT          NOT NULL AUTO_INCREMENT,
    task_id           INT          NOT NULL,
    item_id           INT          NOT NULL,
    quantity_deducted INT          NOT NULL,
    is_extra          TINYINT(1)   NOT NULL DEFAULT 0,
    deducted_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (deduction_id),
    FOREIGN KEY (task_id) REFERENCES hk_tasks(task_id),
    FOREIGN KEY (item_id) REFERENCES inventory_items(item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS exchange_rates (
    rate_id    INT            NOT NULL AUTO_INCREMENT,
    currency   VARCHAR(10)    NOT NULL,
    rate_date  DATE           NOT NULL,
    rate       DECIMAL(10,6)  NOT NULL,
    margin_pct DECIMAL(5,2)   NOT NULL DEFAULT 2.00,
    updated_by INT            NOT NULL,
    PRIMARY KEY (rate_id),
    UNIQUE KEY uq_rate_date (currency, rate_date),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS cancellation_policies (
    policy_id         INT            NOT NULL AUTO_INCREMENT,
    service_type      VARCHAR(100)   NOT NULL UNIQUE,
    free_window_hours INT            NOT NULL DEFAULT 24,
    fee_rate          DECIMAL(5,2)   NOT NULL DEFAULT 0.50,
    fixed_fee         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    updated_by        INT            NOT NULL,
    updated_at        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (policy_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS split_bill_log (
    log_id            INT          NOT NULL AUTO_INCREMENT,
    original_folio_id INT          NOT NULL,
    sub_folio_a_id    INT          NOT NULL,
    sub_folio_b_id    INT          NOT NULL,
    split_method      ENUM('Equal','LineItem','Percentage') NOT NULL,
    performed_by      INT          NOT NULL,
    performed_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    FOREIGN KEY (original_folio_id) REFERENCES folios(folio_id),
    FOREIGN KEY (sub_folio_a_id)    REFERENCES folios(folio_id),
    FOREIGN KEY (sub_folio_b_id)    REFERENCES folios(folio_id),
    FOREIGN KEY (performed_by)      REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT           NOT NULL AUTO_INCREMENT,
    recipient_id    INT           NOT NULL,
    title           VARCHAR(255)  NOT NULL,
    message         TEXT          NOT NULL,
    type            ENUM('VIPArrival','RoomReady','MaintenanceAlert',
                         'CheckoutReminder','NoShowAlert','General')
                                  NOT NULL DEFAULT 'General',
    is_read         TINYINT(1)    NOT NULL DEFAULT 0,
    related_type    VARCHAR(50)   NULL,  -- 'reservation','room','guest'
    related_id      INT           NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id),
    FOREIGN KEY (recipient_id) REFERENCES users(user_id),
    INDEX idx_notif_recipient (recipient_id),
    INDEX idx_notif_read      (is_read),
    INDEX idx_notif_type      (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;
