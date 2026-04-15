# SINEAD -- Integrated Hotel Management System

## Overview

SINEAD is a web-based hotel management system designed for mid-sized hotels. It replaces manual and fragmented record-keeping with a centralized system covering reservations, room management, guest management, billing, reporting, and housekeeping.

This project follows **Agile Scrum** methodology and demonstrates modern **Software Engineering** best practices.

## Features

### Functional Features
- **Secure Authentication** -- Role-based login (Admin, Receptionist, Housekeeping) with bcrypt password hashing
- **Room Management** -- CRUD operations with grid/list views, real-time status tracking (Available, Occupied, Maintenance)
- **Guest Management** -- Guest registration, search, profile viewing with stay history
- **Reservation Handling** -- Walk-in and pre-booked reservations with real-time availability checking, check-in/check-out workflow
- **Automated Billing** -- Invoice generation on check-out with line items, payment recording, printable invoices
- **Reports & Analytics** -- Revenue trends, occupancy rates, room utilization with interactive Chart.js visualizations
- **Housekeeping** -- Kanban-style task board (Pending/In Progress/Completed) with priority levels
- **User Management** -- Admin panel for creating, editing, and deactivating staff accounts

### Non-Functional Features
- Secure data handling (CSRF protection, prepared statements, input sanitization)
- Scalable system design (MVC architecture, Singleton database connection)
- User-friendly web interface with responsive design
- Activity logging for audit trails

## Technology Stack

| Layer          | Technology                           |
|----------------|--------------------------------------|
| Frontend       | HTML5, CSS3, JavaScript (ES6)        |
| Styling        | Custom CSS Design System             |
| Charts         | Chart.js 4.x                         |
| Typography     | Cormorant Garamond + Inter (Google Fonts) |
| Backend        | PHP 8.0+                             |
| Database       | MySQL 8.0+ (InnoDB)                  |
| Version Control| Git + GitHub                         |

## Design Patterns Used

| Pattern           | Where Used                          |
|-------------------|-------------------------------------|
| **MVC**           | Project structure (Controllers/Views) |
| **Front Controller** | `index.php` routes all requests   |
| **Singleton**     | `Database` class (one connection)    |
| **Guard / Middleware** | `middleware/auth.php` for RBAC  |
| **Template Method** | Common layout templates (header/sidebar/footer) |

## Project Structure

```
sinead/
├── index.php                 # Front Controller (entry point)
├── config/
│   ├── app.php               # Application configuration
│   └── database.php          # Database connection (Singleton)
├── controllers/
│   ├── AuthController.php    # Login, logout, forgot password
│   ├── DashboardController.php
│   ├── RoomController.php
│   ├── GuestController.php
│   ├── ReservationController.php
│   ├── BillingController.php
│   ├── ReportController.php
│   ├── HousekeepingController.php
│   └── UserController.php
├── views/
│   ├── auth/                 # Login, forgot password views
│   ├── dashboard/            # Dashboard view
│   ├── rooms/                # Room listing, form views
│   ├── guests/               # Guest listing, form, detail views
│   ├── reservations/         # Reservation listing, create, detail views
│   ├── billing/              # Invoice listing, invoice detail views
│   ├── reports/              # Reports with charts
│   ├── housekeeping/         # Kanban task board
│   ├── users/                # User management views
│   ├── layouts/              # Reusable layout templates
│   └── errors/               # Error pages (404)
├── helpers/
│   └── functions.php         # Utility functions
├── middleware/
│   └── auth.php              # Authentication middleware
├── assets/
│   ├── css/
│   │   ├── main.css          # Design system
│   │   └── login.css         # Login page styles
│   ├── js/
│   │   └── main.js           # Core JavaScript
│   └── images/               # Hotel images
├── database/
│   └── schema.sql            # Database schema + seed data
├── logs/                     # Error logs (gitignored)
├── .gitignore
└── README.md
```

## Setup Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher (MySQL Workbench recommended)
- A web server (PHP's built-in server works for development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/sinead.git
   cd sinead
   ```

2. **Create the database**
   - Open MySQL Workbench
   - Run the `database/schema.sql` file to create the database, tables, and seed data

3. **Configure the database connection**
   - Open `config/database.php`
   - Update the credentials if needed:
     ```php
     private static $host = 'localhost';
     private static $dbName = 'sinead_hotel';
     private static $username = 'root';
     private static $password = '';  // Update this
     ```

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**
   - Open `http://localhost:8000` in your browser
   - Login with default credentials:
     - **Admin**: `admin` / `sinead2024`
     - **Receptionist**: `receptionist` / `sinead2024`
     - **Housekeeping**: `housekeeper` / `sinead2024`

## Security Features

- **CSRF Protection**: All forms include CSRF tokens validated server-side
- **SQL Injection Prevention**: All queries use PDO prepared statements
- **XSS Prevention**: All user-supplied output is sanitized with `htmlspecialchars()`
- **Password Security**: bcrypt hashing with cost factor 12
- **Session Security**: HttpOnly cookies, session regeneration on login, Strict SameSite policy
- **Role-Based Access Control**: Page-level and action-level role validation

## Agile / Scrum

This project was developed using Agile Scrum methodology across 3 sprints:

| Sprint   | Focus                                    | Status |
|----------|------------------------------------------|--------|
| Sprint 1 | Authentication, UI framework, Dashboard  | Done   |
| Sprint 2 | Rooms, Guests, Reservations              | Done   |
| Sprint 3 | Billing, Reports, Housekeeping, Polish   | Done   |

Task tracking and collaboration managed via Trello.

## License

This project is developed as part of a Software Engineering course final project.

---

*SINEAD -- Integrated Hotel Management System v1.0.0*
