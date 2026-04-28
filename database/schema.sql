-- ============================================================================
-- SINEAD Integrated Hotel Management System
-- Database Schema Definition
-- 
-- Database Engine: MySQL 8.0+ / InnoDB
-- Character Set:   utf8mb4 (full Unicode support)
-- Collation:       utf8mb4_unicode_ci
-- 
-- This schema follows normalization principles (3NF) and uses:
--   - Foreign key constraints for referential integrity
--   - Indexes on frequently queried columns
--   - ENUM types for controlled vocabularies
--   - Timestamps for audit trails
-- 
-- @version 1.0.0
-- @author  Sinead Development Team
-- ============================================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS sinead_hotel
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sinead_hotel;

-- ─── USERS TABLE ─────────────────────────────────────────────────────────────
-- Stores system users with role-based access control.
-- Passwords are stored as bcrypt hashes (60 characters).
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    full_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(100) DEFAULT NULL,
    role            ENUM('admin', 'receptionist', 'housekeeping') NOT NULL DEFAULT 'receptionist',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    last_login      DATETIME     DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;

-- ─── ROOMS TABLE ─────────────────────────────────────────────────────────────
-- Represents physical hotel rooms with type, status, and pricing.
CREATE TABLE rooms (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    room_number     VARCHAR(10)    NOT NULL UNIQUE,
    type            ENUM('Standard', 'Deluxe', 'Suite') NOT NULL DEFAULT 'Standard',
    floor           INT            NOT NULL DEFAULT 1,
    price_per_night DECIMAL(10, 2) NOT NULL,
    status          ENUM('Available', 'Occupied', 'Maintenance') NOT NULL DEFAULT 'Available',
    description     TEXT           DEFAULT NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_rooms_status (status),
    INDEX idx_rooms_type (type),
    INDEX idx_rooms_floor (floor)
) ENGINE=InnoDB;

-- ─── GUESTS TABLE ────────────────────────────────────────────────────────────
-- Stores guest personal information and contact details.
-- Linked to reservations via one-to-many relationship.
CREATE TABLE guests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(50)  NOT NULL,
    last_name       VARCHAR(50)  NOT NULL,
    email           VARCHAR(100) DEFAULT NULL,
    phone           VARCHAR(20)  NOT NULL,
    id_document     VARCHAR(50)  DEFAULT NULL COMMENT 'National ID or Passport number',
    address         TEXT         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_guests_name (last_name, first_name),
    INDEX idx_guests_phone (phone),
    INDEX idx_guests_email (email)
) ENGINE=InnoDB;

-- ─── RESERVATIONS TABLE ─────────────────────────────────────────────────────
-- Core transactional table linking guests to rooms for specific date ranges.
-- Status transitions: Confirmed -> CheckedIn -> CheckedOut | Cancelled
CREATE TABLE reservations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    guest_id        INT          NOT NULL,
    room_id         INT          NOT NULL,
    created_by      INT          DEFAULT NULL COMMENT 'User who created the reservation',
    check_in_date   DATE         NOT NULL,
    check_out_date  DATE         NOT NULL,
    num_guests      INT          NOT NULL DEFAULT 1,
    status          ENUM('Confirmed', 'CheckedIn', 'CheckedOut', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
    notes           TEXT         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservations_guest    FOREIGN KEY (guest_id)   REFERENCES guests(id)   ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_room     FOREIGN KEY (room_id)    REFERENCES rooms(id)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_creator  FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_dates CHECK (check_out_date > check_in_date),

    INDEX idx_reservations_status (status),
    INDEX idx_reservations_dates (check_in_date, check_out_date),
    INDEX idx_reservations_guest (guest_id),
    INDEX idx_reservations_room (room_id)
) ENGINE=InnoDB;

-- ─── INVOICES TABLE ──────────────────────────────────────────────────────────
-- Financial records generated upon guest check-out.
-- Each reservation generates exactly one invoice.
CREATE TABLE invoices (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id  INT            NOT NULL,
    invoice_number  VARCHAR(20)    NOT NULL UNIQUE,
    total_amount    DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    amount_paid     DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status          ENUM('Unpaid', 'Partial', 'Paid') NOT NULL DEFAULT 'Unpaid',
    payment_method  ENUM('Cash', 'Card', 'Bank Transfer') DEFAULT NULL,
    notes           TEXT           DEFAULT NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_invoices_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_invoices_status (status),
    INDEX idx_invoices_number (invoice_number),
    INDEX idx_invoices_reservation (reservation_id)
) ENGINE=InnoDB;

-- ─── INVOICE ITEMS TABLE ────────────────────────────────────────────────────
-- Itemized breakdown of charges on an invoice.
-- Follows the Composite pattern for flexible billing.
CREATE TABLE invoice_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id      INT            NOT NULL,
    description     VARCHAR(255)   NOT NULL,
    quantity        INT            NOT NULL DEFAULT 1,
    unit_price      DECIMAL(10, 2) NOT NULL,
    total           DECIMAL(10, 2) NOT NULL,

    CONSTRAINT fk_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_items_invoice (invoice_id)
) ENGINE=InnoDB;

-- ─── HOUSEKEEPING TASKS TABLE ───────────────────────────────────────────────
-- Task management for housekeeping staff.
-- Status transitions: Pending -> InProgress -> Completed
CREATE TABLE housekeeping_tasks (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    room_id         INT          NOT NULL,
    assigned_to     INT          DEFAULT NULL COMMENT 'Housekeeping staff user ID',
    task_type       ENUM('Cleaning', 'Maintenance', 'Restocking') NOT NULL DEFAULT 'Cleaning',
    status          ENUM('Pending', 'InProgress', 'Completed') NOT NULL DEFAULT 'Pending',
    priority        ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    notes           TEXT         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at    DATETIME     DEFAULT NULL,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tasks_room      FOREIGN KEY (room_id)     REFERENCES rooms(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tasks_assignee  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX idx_tasks_status (status),
    INDEX idx_tasks_room (room_id),
    INDEX idx_tasks_assigned (assigned_to),
    INDEX idx_tasks_priority (priority)
) ENGINE=InnoDB;

-- ─── ACTIVITY LOG TABLE ─────────────────────────────────────────────────────
-- Audit trail for all significant system operations.
-- Implements the Observer pattern for system monitoring.
CREATE TABLE activity_log (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT          DEFAULT NULL,
    action          VARCHAR(100) NOT NULL,
    details         TEXT         DEFAULT NULL,
    ip_address      VARCHAR(45)  DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX idx_log_user (user_id),
    INDEX idx_log_action (action),
    INDEX idx_log_date (created_at)
) ENGINE=InnoDB;

-- ─── PASSWORD RESETS TABLE ──────────────────────────────────────────────────
-- Stores password reset tokens with expiration.
-- Tokens are hashed for security.
CREATE TABLE password_resets (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT          NOT NULL,
    token_hash      VARCHAR(255) NOT NULL,
    expires_at      DATETIME     NOT NULL,
    used            TINYINT(1)   NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_resets_token (token_hash),
    INDEX idx_resets_expiry (expires_at)
) ENGINE=InnoDB;


-- ═══════════════════════════════════════════════════════════════════════════
-- STORED FUNCTIONS
-- Run this file via MySQL CLI or phpMyAdmin — DELIMITER is a client command
-- and is not supported by PDO.
-- ═══════════════════════════════════════════════════════════════════════════

DELIMITER $$

-- ─── fn_nights ───────────────────────────────────────────────────────────────
-- Returns the number of nights between two dates (minimum 1).
-- Used inside queries wherever a night count is needed — avoids duplicating
-- the DATEDIFF + GREATEST logic across procedures, reports, and PHP.
CREATE FUNCTION fn_nights(p_check_in DATE, p_check_out DATE)
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN GREATEST(1, DATEDIFF(p_check_out, p_check_in));
END$$

-- ─── fn_is_room_available ────────────────────────────────────────────────────
-- Returns 1 if no confirmed or checked-in booking overlaps the requested dates,
-- 0 otherwise. Used by the reservation form and sp_check_in to validate dates.
CREATE FUNCTION fn_is_room_available(
    p_room_id   INT,
    p_check_in  DATE,
    p_check_out DATE
)
RETURNS TINYINT(1)
DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_conflicts INT DEFAULT 0;

    SELECT COUNT(*) INTO v_conflicts
    FROM reservations
    WHERE room_id      = p_room_id
      AND status       IN ('Confirmed', 'CheckedIn')
      AND check_in_date  < p_check_out
      AND check_out_date > p_check_in;

    RETURN (v_conflicts = 0);
END$$

DELIMITER ;


-- ═══════════════════════════════════════════════════════════════════════════
-- STORED PROCEDURES
-- Each procedure uses DECLARE variables to hold intermediate query results
-- and OUT parameters to return status back to PHP.
-- ═══════════════════════════════════════════════════════════════════════════

DELIMITER $$

-- ─── sp_check_in ─────────────────────────────────────────────────────────────
-- Transitions a Confirmed reservation to CheckedIn and marks the room Occupied.
-- OUT p_error is NULL on success, an error string on failure.
-- PHP checks @err after CALL to decide whether to show success or error flash.
CREATE PROCEDURE sp_check_in(
    IN  p_res_id INT,
    OUT p_error  VARCHAR(255)
)
BEGIN
    DECLARE v_status  VARCHAR(20);
    DECLARE v_room_id INT;

    -- Variables capture the reservation's current state before making any changes
    SELECT status, room_id
    INTO   v_status, v_room_id
    FROM   reservations
    WHERE  id = p_res_id;

    IF v_status IS NULL THEN
        SET p_error = 'Reservation not found.';
    ELSEIF v_status != 'Confirmed' THEN
        SET p_error = CONCAT('Cannot check in: reservation status is ', v_status, '.');
    ELSE
        UPDATE reservations SET status = 'CheckedIn' WHERE id = p_res_id;
        UPDATE rooms        SET status = 'Occupied'  WHERE id = v_room_id;
        SET p_error = NULL;
    END IF;
END$$

-- ─── sp_check_out ────────────────────────────────────────────────────────────
-- Handles the full checkout flow in a single atomic transaction:
--   1. Validates the reservation is CheckedIn
--   2. Sets reservation → CheckedOut, room → Maintenance
--   3. Generates an invoice and line item using fn_nights()
--   4. Creates a housekeeping task with priority based on room type
-- OUT parameters return the invoice number and total so PHP can use them
-- for notifications and flash messages without a second query.
CREATE PROCEDURE sp_check_out(
    IN  p_res_id  INT,
    OUT p_invoice VARCHAR(50),
    OUT p_total   DECIMAL(10,2),
    OUT p_error   VARCHAR(255)
)
BEGIN
    DECLARE v_room_id     INT;
    DECLARE v_room_number VARCHAR(10);
    DECLARE v_room_type   VARCHAR(20);
    DECLARE v_price       DECIMAL(10,2);
    DECLARE v_nights      INT;
    DECLARE v_invoice_id  INT;
    DECLARE v_priority    VARCHAR(10);

    -- Load everything needed for invoice + housekeeping in one query
    SELECT r.room_id, rm.room_number, rm.type, rm.price_per_night,
           fn_nights(r.check_in_date, r.check_out_date)
    INTO   v_room_id, v_room_number, v_room_type, v_price, v_nights
    FROM   reservations r
    JOIN   rooms rm ON r.room_id = rm.id
    WHERE  r.id = p_res_id AND r.status = 'CheckedIn';

    IF v_room_id IS NULL THEN
        SET p_error = 'Reservation not found or guest is not currently checked in.';
    ELSE
        SET p_total   = v_nights * v_price;
        SET p_invoice = CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(p_res_id, 4, '0'));

        -- Housekeeping priority mirrors the PHP Factory Pattern logic
        SET v_priority = CASE v_room_type
            WHEN 'Suite'  THEN 'High'
            WHEN 'Deluxe' THEN 'Medium'
            ELSE               'Low'
        END;

        START TRANSACTION;

            UPDATE reservations SET status = 'CheckedOut' WHERE id = p_res_id;
            UPDATE rooms        SET status = 'Maintenance' WHERE id = v_room_id;

            INSERT INTO invoices (reservation_id, invoice_number, total_amount, status)
            VALUES (p_res_id, p_invoice, p_total, 'Unpaid');

            SET v_invoice_id = LAST_INSERT_ID();

            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total)
            VALUES (v_invoice_id,
                    CONCAT('Room ', v_room_number, ' - Accommodation'),
                    v_nights, v_price, p_total);

            INSERT INTO housekeeping_tasks (room_id, task_type, status, priority, notes)
            VALUES (v_room_id, 'Cleaning', 'Pending', v_priority, 'Post-checkout cleaning required');

        COMMIT;
        SET p_error = NULL;
    END IF;
END$$

-- ─── sp_cancel_reservation ───────────────────────────────────────────────────
-- Cancels a Confirmed reservation. Refuses if the guest is CheckedIn or the
-- reservation is already in a terminal state (CheckedOut, Cancelled).
CREATE PROCEDURE sp_cancel_reservation(
    IN  p_res_id INT,
    OUT p_error  VARCHAR(255)
)
BEGIN
    DECLARE v_status VARCHAR(20);

    SELECT status INTO v_status FROM reservations WHERE id = p_res_id;

    IF v_status IS NULL THEN
        SET p_error = 'Reservation not found.';
    ELSEIF v_status = 'CheckedIn' THEN
        SET p_error = 'This guest is currently checked in. Complete checkout first.';
    ELSEIF v_status != 'Confirmed' THEN
        SET p_error = CONCAT('Only confirmed reservations can be cancelled. Current status: ', v_status, '.');
    ELSE
        UPDATE reservations SET status = 'Cancelled' WHERE id = p_res_id;
        SET p_error = NULL;
    END IF;
END$$

-- ─── sp_flag_overdue_reservations ────────────────────────────────────────────
-- Cursor-based procedure: iterates every reservation that is still CheckedIn
-- but past its checkout date, and writes one alert per reservation per day
-- into activity_log. Called by the admin dashboard on each page load.
CREATE PROCEDURE sp_flag_overdue_reservations()
BEGIN
    DECLARE v_done     TINYINT(1) DEFAULT 0;
    DECLARE v_res_id   INT;
    DECLARE v_guest    VARCHAR(120);
    DECLARE v_room_num VARCHAR(10);

    -- Cursor selects all overdue rows; the handler exits the loop cleanly
    DECLARE cur_overdue CURSOR FOR
        SELECT r.id,
               CONCAT(g.first_name, ' ', g.last_name),
               rm.room_number
        FROM   reservations r
        JOIN   guests  g  ON r.guest_id = g.id
        JOIN   rooms   rm ON r.room_id  = rm.id
        WHERE  r.status = 'CheckedIn'
          AND  r.check_out_date < CURDATE();

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;

    OPEN cur_overdue;

    read_loop: LOOP
        FETCH cur_overdue INTO v_res_id, v_guest, v_room_num;
        IF v_done THEN LEAVE read_loop; END IF;

        -- Only log once per reservation per calendar day to avoid duplicate noise
        INSERT INTO activity_log (user_id, action, details, created_at)
        SELECT NULL,
               'Overdue Stay Flagged',
               CONCAT('Reservation #', v_res_id, ' — ', v_guest,
                      ' in Room ', v_room_num, ' is overdue.'),
               NOW()
        WHERE NOT EXISTS (
            SELECT 1 FROM activity_log
            WHERE  action = 'Overdue Stay Flagged'
              AND  details LIKE CONCAT('%Reservation #', v_res_id, '%')
              AND  DATE(created_at) = CURDATE()
        );
    END LOOP;

    CLOSE cur_overdue;
END$$

DELIMITER ;


-- ═══════════════════════════════════════════════════════════════════════════
-- TRIGGERS
-- ═══════════════════════════════════════════════════════════════════════════

DELIMITER $$

-- ─── trg_reservation_status_change ───────────────────────────────────────────
-- Automatically syncs room status whenever a reservation status changes.
-- This enforces the business rule at the DB layer — it fires even if the
-- change comes from a raw SQL tool or a future API, not just the PHP app.
CREATE TRIGGER trg_reservation_status_change
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    IF NEW.status = 'CheckedIn'  AND OLD.status != 'CheckedIn' THEN
        UPDATE rooms SET status = 'Occupied'    WHERE id = NEW.room_id;

    ELSEIF NEW.status = 'CheckedOut' AND OLD.status != 'CheckedOut' THEN
        UPDATE rooms SET status = 'Maintenance' WHERE id = NEW.room_id;

    ELSEIF NEW.status = 'Cancelled'  AND OLD.status  = 'Confirmed' THEN
        UPDATE rooms SET status = 'Available'   WHERE id = NEW.room_id;
    END IF;
END$$

-- ─── trg_block_room_delete ───────────────────────────────────────────────────
-- Prevents deletion of a room that has active (Confirmed or CheckedIn)
-- reservations. Raises SQLSTATE 45000 which PDO surfaces as a PDOException —
-- the message text is shown directly to the user in the flash message.
CREATE TRIGGER trg_block_room_delete
BEFORE DELETE ON rooms
FOR EACH ROW
BEGIN
    DECLARE v_active INT DEFAULT 0;

    SELECT COUNT(*) INTO v_active
    FROM   reservations
    WHERE  room_id = OLD.id
      AND  status IN ('Confirmed', 'CheckedIn');

    IF v_active > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot delete a room with active reservations.';
    END IF;
END$$

DELIMITER ;


-- ═══════════════════════════════════════════════════════════════════════════
-- SEED DATA
-- Default data for initial system setup and testing.
-- ═══════════════════════════════════════════════════════════════════════════

-- ─── Default Users ──────────────────────────────────────────────────────────
-- Default password for all seed users: "sinead2024"
-- bcrypt hash generated with cost factor 12
INSERT INTO users (username, password_hash, full_name, email, role) VALUES
    ('admin',        '$2y$12$aRWz7CTScCjntkBsVOBu6.SWLSGeeQfn1/ysPhMazLeZ6AaZiyD5e', 'System Administrator', 'admin@sinead.hotel', 'admin'),
    ('receptionist', '$2y$12$aRWz7CTScCjntkBsVOBu6.SWLSGeeQfn1/ysPhMazLeZ6AaZiyD5e', 'Jane Receptionist',    'jane@sinead.hotel',  'receptionist'),
    ('housekeeper',  '$2y$12$aRWz7CTScCjntkBsVOBu6.SWLSGeeQfn1/ysPhMazLeZ6AaZiyD5e', 'Mary Housekeeper',     'mary@sinead.hotel',  'housekeeping');

-- ─── Rooms ──────────────────────────────────────────────────────────────────
-- 10 Standard, 5 Deluxe, 3 Suite = 18 rooms total across 3 floors

-- Standard Rooms (Floor 1)
INSERT INTO rooms (room_number, type, floor, price_per_night, status, description) VALUES
    ('101', 'Standard', 1, 3500.00, 'Available',   'Cozy room with a queen bed, work desk, and city view.'),
    ('102', 'Standard', 1, 3500.00, 'Available',   'Standard room with twin beds and garden view.'),
    ('103', 'Standard', 1, 3500.00, 'Occupied',    'Queen bed room with en-suite bathroom.'),
    ('104', 'Standard', 1, 3500.00, 'Available',   'Comfortable room with modern amenities.'),
    ('105', 'Standard', 1, 3500.00, 'Maintenance', 'Under renovation - new carpet installation.');

-- Standard Rooms (Floor 2)
INSERT INTO rooms (room_number, type, floor, price_per_night, status, description) VALUES
    ('201', 'Standard', 2, 3500.00, 'Available', 'Standard room with queen bed and balcony.'),
    ('202', 'Standard', 2, 3500.00, 'Available', 'Twin bed room with pool view.'),
    ('203', 'Standard', 2, 3500.00, 'Available', 'Queen bed room with mini refrigerator.'),
    ('204', 'Standard', 2, 3500.00, 'Occupied',  'Standard room with work desk and WiFi.'),
    ('205', 'Standard', 2, 3500.00, 'Available', 'Corner room with extra natural lighting.');

-- Deluxe Rooms (Floor 2-3)
INSERT INTO rooms (room_number, type, floor, price_per_night, status, description) VALUES
    ('206', 'Deluxe', 2, 6500.00, 'Available', 'Spacious deluxe room with king bed and lounge area.'),
    ('207', 'Deluxe', 2, 6500.00, 'Occupied',  'Deluxe room with panoramic city view and jacuzzi.'),
    ('301', 'Deluxe', 3, 6500.00, 'Available', 'Premium deluxe with separate living area.'),
    ('302', 'Deluxe', 3, 6500.00, 'Available', 'Deluxe corner room with dual aspect windows.'),
    ('303', 'Deluxe', 3, 6500.00, 'Available', 'Deluxe room with private terrace.');

-- Suites (Floor 3)
INSERT INTO rooms (room_number, type, floor, price_per_night, status, description) VALUES
    ('304', 'Suite', 3, 12000.00, 'Available',  'Executive suite with full living room and dining area.'),
    ('305', 'Suite', 3, 12000.00, 'Occupied',   'Presidential suite with panoramic views and butler service.'),
    ('306', 'Suite', 3, 15000.00, 'Available',  'Royal suite - our finest accommodation with private lounge.');

-- ─── Sample Guests ──────────────────────────────────────────────────────────
INSERT INTO guests (first_name, last_name, email, phone, id_document, address) VALUES
    ('John',    'Kamau',   'john.kamau@email.com',   '+254712345678', 'ID-29384756', 'Nairobi, Kenya'),
    ('Sarah',   'Wanjiku', 'sarah.w@email.com',      '+254723456789', 'ID-38475612', 'Mombasa, Kenya'),
    ('Michael', 'Odhiambo','m.odhiambo@email.com',   '+254734567890', 'PP-KE847362', 'Kisumu, Kenya'),
    ('Grace',   'Muthoni', 'grace.m@email.com',      '+254745678901', 'ID-47561283', 'Nakuru, Kenya'),
    ('David',   'Kiprop',  'david.kiprop@email.com',  '+254756789012', 'ID-56128347', 'Eldoret, Kenya');

-- ─── Sample Reservations ────────────────────────────────────────────────────
INSERT INTO reservations (guest_id, room_id, created_by, check_in_date, check_out_date, num_guests, status, notes) VALUES
    (1, 3,  1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 2, 'CheckedIn',  'Regular guest - prefers ground floor'),
    (2, 10, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 1, 'CheckedIn',  'Business traveler'),
    (3, 12, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 2, 'CheckedIn',  'Anniversary celebration'),
    (4, 18, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 2, 'CheckedIn',  'VIP guest - complimentary fruit basket'),
    (5, 1,  1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 1, 'Confirmed', 'Early check-in requested');

-- ─── NOTIFICATION LOG TABLE ─────────────────────────────────────────────────
-- Stores every outbound notification attempt made by the Adapter Pattern
-- service layer. Email sends are logged with 'sent'/'failed'; SMS entries
-- use 'logged' until a real provider is wired in.
CREATE TABLE notification_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    channel     ENUM('email', 'sms')              NOT NULL,
    recipient   VARCHAR(100)                       NOT NULL,
    subject     VARCHAR(255)                       NOT NULL,
    message     TEXT                               NOT NULL,
    status      ENUM('sent', 'failed', 'logged')   NOT NULL DEFAULT 'logged',
    created_at  DATETIME                           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_notif_channel (channel),
    INDEX idx_notif_status  (status),
    INDEX idx_notif_created (created_at)
) ENGINE=InnoDB;


-- ─── Sample Housekeeping Tasks ──────────────────────────────────────────────
INSERT INTO housekeeping_tasks (room_id, assigned_to, task_type, status, priority, notes) VALUES
    (1,  3, 'Cleaning',     'Pending',     'Medium', 'Standard daily cleaning'),
    (2,  3, 'Restocking',   'InProgress',  'Low',    'Restock minibar and towels'),
    (5,  3, 'Maintenance',  'Pending',     'High',   'Carpet replacement in progress'),
    (6,  3, 'Cleaning',     'Completed',   'Medium', 'Deep cleaning completed');
