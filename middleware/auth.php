<?php
/**
 * Authentication Middleware
 * 
 * Include this file at the top of any protected page to enforce
 * authentication and role-based access control (RBAC).
 * 
 * Implements the Guard pattern: redirects unauthenticated users
 * to the login page and unauthorized users to the dashboard
 * with an appropriate error message.
 * 
 * @package    Sinead
 * @subpackage Middleware
 * @author     Sinead Development Team
 * @version    1.0.0
 */

/**
 * Require the user to be authenticated.
 * Redirects to login page if not authenticated.
 * 
 * @return void
 */
function requireAuth(): void
{
    if (!isAuthenticated()) {
        setFlash('error', 'Please log in to access this page.');
        redirect('login');
    }
}

/**
 * Require the user to have one of the specified roles.
 * 
 * Must be called after requireAuth() to ensure
 * session data is available.
 * 
 * @param string|array $roles Allowed role(s) for the current page
 * @return void
 */
function requireRole($roles): void
{
    requireAuth();

    if (!hasRole($roles)) {
        setFlash('error', 'You do not have permission to access this page.');
        redirect('dashboard');
    }
}

/**
 * Restrict access to admin users only.
 * Convenience wrapper around requireRole().
 * 
 * @return void
 */
function requireAdmin(): void
{
    requireRole(ROLE_ADMIN);
}

/**
 * Allow access to admin and receptionist roles only.
 * Used for front-desk operations like reservations and billing.
 * 
 * @return void
 */
function requireFrontDesk(): void
{
    requireRole([ROLE_ADMIN, ROLE_RECEPTIONIST]);
}
