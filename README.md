# SINEAD -- Integrated Hotel Management System

## Overview

SINEAD is a web-based hotel management system designed for mid-sized hotels. It replaces manual and fragmented record-keeping with a centralized system covering reservations, room management, guest management, billing, reporting, and housekeeping.

This project follows **Agile Scrum** methodology and demonstrates modern **Software Engineering** best practices including multiple design patterns and advanced database management techniques.

---

## Features

### Functional Features
- **Secure Authentication** -- Role-based login (Admin, Receptionist, Housekeeping) with bcrypt password hashing and Google OAuth support
- **Room Management** -- CRUD operations with grid/list views, real-time status tracking (Available, Occupied, Maintenance), type-specific amenities and occupancy limits
- **Guest Management** -- Guest registration, search, profile viewing with stay history
- **Reservation Handling** -- Walk-in and pre-booked reservations with real-time availability checking, guest count validation against room capacity, and full check-in/check-out workflow
- **Automated Billing** -- Invoice generation on check-out with line items, payment recording, printable invoices
- **Notifications** -- Email and SMS notifications to guests on booking confirmation, check-in, check-out, and cancellation. All attempts are logged to the database
- **Reports & Analytics** -- Revenue trends, occupancy rates, room utilization with interactive Chart.js visualizations
- **Housekeeping** -- Kanban-style task board (Pending/In Progress/Completed) with room-type-based priority levels, automatic room status restoration on task completion, admin task creation modal, and staff assignment/reassignment workflow
- **User Management** -- Admin panel for creating, editing, and deactivating staff accounts
- **Overdue Stay Alerts** -- Admin dashboard automatically flags guests still checked in past their checkout date

### Non-Functional Features
- Secure data handling (CSRF protection, prepared statements, input sanitization)
- Scalable system design (MVC architecture, multiple design patterns)
- Business rules enforced at both the PHP application layer and the database layer (triggers, procedures)
- User-friendly web interface with responsive design
- Activity logging for full audit trails
- Notification logging for all outbound communication attempts

---

## Technology Stack

| Layer           | Technology                                |
|-----------------|-------------------------------------------|
| Frontend        | HTML5, CSS3, JavaScript (ES6)             |
| Styling         | Custom CSS Design System                  |
| Charts          | Chart.js 4.x                              |
| Typography      | Cormorant Garamond + Inter (Google Fonts) |
| Backend         | PHP 8.0+                                  |
| Database        | MySQL 8.0+ (InnoDB)                       |
| Version Control | Git + GitHub                              |

---

## Design Patterns Used

| Pattern                 | Where Used                                                                                       |
|-------------------------|--------------------------------------------------------------------------------------------------|
| **MVC**                 | Project structure — Controllers handle logic, Views handle display, no direct DB access in views |
| **Front Controller**    | `index.php` routes all HTTP requests via a single entry point                                    |
| **Singleton**           | `Database` class — one PDO connection shared across the entire request                           |
| **Factory**             | `RoomFactory` + `Room` abstract class — creates `StandardRoom`, `DeluxeRoom`, or `SuiteRoom` objects with type-specific occupancy limits, amenities, and housekeeping priority |
| **Adapter**             | `NotificationInterface` — `EmailAdapter` (PHP `mail()`) and `SMSAdapter` (DB stub) implement the same interface; `NotificationService` dispatches to all registered adapters |
| **Guard / Middleware**  | `middleware/auth.php` — RBAC enforcement before every controller runs                           |
| **Template Method**     | Shared layout templates (`header.php`, `sidebar.php`, `footer.php`) included by all views        |

---

## Database Design

### Tables

| Table                | Purpose                                                        |
|----------------------|----------------------------------------------------------------|
| `users`              | System users with roles (admin, receptionist, housekeeping)    |
| `rooms`              | Hotel rooms with type, status, and pricing                     |
| `guests`             | Guest personal and contact information                         |
| `reservations`       | Booking records linking guests to rooms with lifecycle status  |
| `invoices`           | Financial records generated at checkout                        |
| `invoice_items`      | Line items within each invoice                                 |
| `housekeeping_tasks` | Cleaning/maintenance tasks assigned to rooms                   |
| `activity_log`       | Audit trail of all significant system actions                  |
| `password_resets`    | Secure token-based password reset records                      |
| `notification_log`   | Log of every email and SMS notification attempt                |

### Stored Routines

| Object                          | Type          | Purpose                                                                                  |
|---------------------------------|---------------|------------------------------------------------------------------------------------------|
| `fn_nights(check_in, check_out)`| Function      | Returns number of nights (minimum 1) — used inside queries across the system             |
| `fn_is_room_available(...)`     | Function      | Returns 1/0 — checks for overlapping confirmed/checked-in bookings for a room            |
| `sp_check_in(res_id, OUT error)`| Procedure     | Validates and transitions a reservation to CheckedIn; updates room to Occupied           |
| `sp_check_out(res_id, OUT ...)`  | Procedure     | Atomic checkout: status update, room to Maintenance, invoice + line item, housekeeping task |
| `sp_cancel_reservation(...)`    | Procedure     | Validates status and cancels a Confirmed reservation                                     |
| `sp_flag_overdue_reservations()`| Procedure + Cursor | Iterates all overdue CheckedIn rows and writes one alert per reservation per day to activity_log |
| `trg_reservation_status_change` | Trigger       | `AFTER UPDATE ON reservations` — auto-syncs room status on every reservation state change |
| `trg_block_room_delete`         | Trigger       | `BEFORE DELETE ON rooms` — raises `SQLSTATE 45000` if active reservations exist          |

> **Note:** Stored routines use `DELIMITER $$` syntax and must be executed via the MySQL CLI (`mysql`) or phpMyAdmin. They cannot be run through PDO.

---

## Project Structure

```
sinead/
├── index.php                     # Front Controller — routes all requests
├── setup.php                     # Installation checker
├── config/
│   ├── app.php                   # Application constants and configuration
│   └── database.php              # PDO Singleton connection
├── controllers/
│   ├── AuthController.php        # Login, logout, forgot password, Google OAuth
│   ├── DashboardController.php   # Role-specific dashboards + overdue detection
│   ├── RoomController.php        # Room CRUD — uses RoomFactory
│   ├── GuestController.php
│   ├── ReservationController.php # Reservation lifecycle — calls sp_check_in/out/cancel
│   ├── BillingController.php
│   ├── ReportController.php
│   ├── HousekeepingController.php
│   ├── ListingController.php     # Public landing page
│   └── UserController.php
├── models/                       # Factory Pattern — room type hierarchy
│   ├── Room.php                  # Abstract base class (implements ArrayAccess)
│   ├── StandardRoom.php          # Occupancy: 2, Priority: Low
│   ├── DeluxeRoom.php            # Occupancy: 3, Priority: Medium
│   ├── SuiteRoom.php             # Occupancy: 4, Priority: High
│   └── RoomFactory.php           # create(type, data) / fromDbRow(row)
├── services/                     # Adapter Pattern — notification channels
│   ├── NotificationInterface.php # Target interface: send() + getChannel()
│   ├── EmailAdapter.php          # Wraps PHP mail(), logs to notification_log
│   ├── SMSAdapter.php            # Stub: logs to notification_log (ready for Twilio)
│   └── NotificationService.php  # Dispatcher + makeNotifier() helper
├── views/
│   ├── auth/
│   ├── dashboard/
│   ├── rooms/                    # index.php shows amenities via $roomObj
│   ├── guests/
│   ├── reservations/
│   ├── billing/
│   ├── reports/
│   ├── housekeeping/
│   ├── users/
│   ├── layouts/                  # header.php, sidebar.php, footer.php
│   └── errors/
├── helpers/
│   └── functions.php             # Utility functions (sanitize, flash, auth, format)
├── middleware/
│   └── auth.php                  # RBAC middleware
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── database/
│   └── schema.sql                # Tables, stored functions, procedures, triggers, seed data
├── logs/                         # Error logs (gitignored)
├── .gitignore
└── README.md
```

---

## Setup Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher (MySQL Workbench or CLI recommended)
- A web server (PHP's built-in server works for development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Mathew-macharia/sinead.git
   cd sinead
   ```

2. **Create the database and run the schema**

   Open a MySQL CLI session and run the full schema file. This creates all tables, stored functions, procedures, and triggers:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   > Alternatively, open `database/schema.sql` in phpMyAdmin and execute it. Do **not** run it via a PHP script — the `DELIMITER $$` blocks are MySQL CLI syntax and are not supported by PDO.

3. **Configure the database connection**

   `config/database.php` is gitignored (it contains credentials). Copy the example and update:
   ```bash
   cp config/database.php.example config/database.php
   ```
   Then open `config/database.php` and set your MySQL credentials:
   ```php
   private static $host     = '127.0.0.1';
   private static $port     = 3306;
   private static $dbName   = 'sinead_hotel';
   private static $username = 'root';
   private static $password = '';  // update this
   ```

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**

   Open `http://localhost:8000` in your browser and log in with the default credentials:

   | Role          | Username       | Password    |
   |---------------|----------------|-------------|
   | Admin         | `admin`        | `sinead2024`|
   | Receptionist  | `receptionist` | `sinead2024`|
   | Housekeeping  | `housekeeper`  | `sinead2024`|

---

## Security Features

- **CSRF Protection** -- All forms include CSRF tokens validated server-side
- **SQL Injection Prevention** -- All queries use PDO prepared statements; native prepared statements enabled
- **XSS Prevention** -- All user-supplied output is sanitized with `htmlspecialchars()`
- **Password Security** -- bcrypt hashing with cost factor 12
- **Session Security** -- HttpOnly cookies, session regeneration on login, Strict SameSite policy
- **Role-Based Access Control** -- Page-level and action-level role validation via middleware
- **DB-Level Guards** -- `trg_block_room_delete` trigger prevents bypassing PHP-level validation through raw SQL tools

---

## Agile / Scrum

This project was developed using Agile Scrum methodology:

| Sprint   | Focus                                                              | Status |
|----------|--------------------------------------------------------------------|--------|
| Sprint 1 | Authentication, UI framework, Dashboard                            | Done   |
| Sprint 2 | Rooms, Guests, Reservations                                        | Done   |
| Sprint 3 | Billing, Reports, Housekeeping, Polish                             | Done   |
| Sprint 4 | Design Patterns (Factory + Adapter), Advanced DB (procedures, triggers, cursors), Notifications | Done |

Task tracking and collaboration managed via Trello.

---

## License

This project is developed as part of a Software Engineering course final project at Ashesi University.

---

*SINEAD -- Integrated Hotel Management System v2.0.0*
