<?php
/**
 * Billing Controller
 * 
 * Manages invoices and payment processing.
 * Provides invoice listing, detail view, payment recording, and printable invoices.
 * 
 * Access: Admin and Receptionist roles
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @version    1.0.0
 */

requireAuth(); // individual actions enforce their own role check

$db     = Database::getInstance();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'view':    handleViewInvoice($db); break;
    case 'pay':     handlePayment($db);     break;
    default:        handleListInvoices($db); break;
}

function handleListInvoices(PDO $db): void
{
    requireAdmin(); // Revenue summary is management-only

    $pageTitle = 'Billing & Invoices';
    $pageSubtitle = 'Manage payments and invoices';
    $statusFilter = $_GET['status'] ?? '';

    $where = '';
    $params = [];
    if ($statusFilter) {
        $where = 'WHERE i.status = :status';
        $params[':status'] = $statusFilter;
    }

    $stmt = $db->prepare("
        SELECT i.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name,
               rm.room_number, r.check_in_date, r.check_out_date
        FROM invoices i
        JOIN reservations r ON i.reservation_id = r.id
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        {$where}
        ORDER BY i.created_at DESC
    ");
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();

    // Summary figures
    $summary = $db->query("
        SELECT 
            COALESCE(SUM(total_amount), 0) as total_billed,
            COALESCE(SUM(amount_paid), 0) as total_collected,
            COALESCE(SUM(total_amount - amount_paid), 0) as outstanding
        FROM invoices WHERE status != 'Paid'
    ")->fetch();

    $totalPaid = $db->query("SELECT COALESCE(SUM(amount_paid), 0) as t FROM invoices")->fetch()['t'];

    require_once VIEWS_PATH . '/billing/index.php';
}

function handleViewInvoice(PDO $db): void
{
    requireFrontDesk(); // Receptionist needs to view a guest's invoice at checkout

    $invoiceId = (int)($_GET['id'] ?? 0);
    $pageTitle = 'Invoice';

    $stmt = $db->prepare("
        SELECT i.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name,
               g.email as guest_email, g.phone as guest_phone, g.address as guest_address,
               rm.room_number, rm.type as room_type,
               r.check_in_date, r.check_out_date, r.num_guests,
               fn_nights(r.check_in_date, r.check_out_date) as nights
        FROM invoices i
        JOIN reservations r ON i.reservation_id = r.id
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        WHERE i.id = :id
    ");
    $stmt->execute([':id' => $invoiceId]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        setFlash('error', 'Invoice not found.');
        redirect('billing');
        return;
    }

    // Get line items
    $items = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
    $items->execute([':id' => $invoiceId]);
    $lineItems = $items->fetchAll();

    require_once VIEWS_PATH . '/billing/invoice.php';
}

function handlePayment(PDO $db): void
{
    requireFrontDesk(); // Receptionist collects payment at checkout
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('billing'); return; }
    verifyCsrf();

    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['payment_method'] ?? '';

    if ($amount <= 0 || !in_array($method, PAYMENT_METHODS, true)) {
        setFlash('error', 'Invalid payment details.');
        redirect('billing&action=view&id=' . $invoiceId);
        return;
    }

    try {
        $invoice = $db->prepare("SELECT * FROM invoices WHERE id = :id");
        $invoice->execute([':id' => $invoiceId]);
        $inv = $invoice->fetch();

        if (!$inv) {
            setFlash('error', 'Invoice not found.');
            redirect('billing');
            return;
        }

        $newPaid = $inv['amount_paid'] + $amount;
        $newStatus = $newPaid >= $inv['total_amount'] ? 'Paid' : 'Partial';

        $db->prepare("
            UPDATE invoices SET amount_paid = :paid, status = :status, payment_method = :method
            WHERE id = :id
        ")->execute([
            ':paid'   => min($newPaid, $inv['total_amount']),
            ':status' => $newStatus,
            ':method' => $method,
            ':id'     => $invoiceId
        ]);

        logActivity('Payment Recorded', "Payment of " . formatCurrency($amount) . " recorded for invoice {$inv['invoice_number']}.");
        setFlash('success', 'Payment of ' . formatCurrency($amount) . ' recorded successfully.');
    } catch (PDOException $e) {
        error_log('Payment error: ' . $e->getMessage());
        setFlash('error', 'Failed to record payment.');
    }

    redirect('billing&action=view&id=' . $invoiceId);
}
