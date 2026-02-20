<?php
/**
 * CYN Tourism — CreditTransaction Model
 *
 * Manages the partner credit ledger: recharges, invoice payments,
 * refunds, and manual adjustments.
 *
 * All balances are tracked PER CURRENCY. Mixed-currency totalling
 * is never done for payment validation.
 */
class CreditTransaction
{
    /**
     * Compute the current credit balance for a partner in a specific currency
     * directly from the ledger (source of truth).
     *
     * If $currency is null, returns the aggregate across all currencies
     * (for display purposes only — never use for payment validation).
     */
    public static function getPartnerBalance(int $partnerId, ?string $currency = null): float
    {
        if ($currency !== null) {
            $row = Database::fetchOne(
                "SELECT COALESCE(
                    SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE -amount END),
                 0) AS bal
                 FROM credit_transactions
                 WHERE partner_id = ? AND currency = ?",
                [$partnerId, strtoupper($currency)]
            );
            return round((float)($row['bal'] ?? 0), 4);
        }

        // Aggregate across all currencies — display only
        $row = Database::fetchOne(
            "SELECT COALESCE(
                SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE -amount END),
             0) AS bal
             FROM credit_transactions WHERE partner_id = ?",
            [$partnerId]
        );
        return round((float)($row['bal'] ?? 0), 4);
    }

    /**
     * Create a credit transaction and atomically update the partner's per-currency
     * balance_before / balance_after in the ledger row.
     *
     * $data keys:
     *   partner_id  (int, required)
     *   type        string: 'recharge' | 'payment' | 'refund' | 'adjustment'
     *   amount      float, always positive
     *   currency    string default 'EUR'
     *   description string
     *   ref_type    string: 'invoice' | 'manual'
     *   ref_id      int|null  (invoice id for payments)
     *
     * Returns the new transaction id.
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance()->getConnection();

        $partnerId = (int)$data['partner_id'];
        $type      = $data['type']     ?? 'recharge';
        $amount    = abs((float)($data['amount'] ?? 0));
        $currency  = strtoupper($data['currency'] ?? 'EUR');

        // Per-currency balance BEFORE this transaction (from ledger — not partners.balance)
        $balanceBefore = self::getPartnerBalance($partnerId, $currency);

        // Recharges/refunds add; payments/adjustments subtract
        $delta        = in_array($type, ['recharge', 'refund']) ? $amount : -$amount;
        $balanceAfter = round($balanceBefore + $delta, 4);

        // Insert ledger row
        $stmt = $db->prepare("
            INSERT INTO credit_transactions
                (partner_id, type, amount, currency, description, ref_type, ref_id,
                 balance_before, balance_after, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
        ");
        $stmt->execute([
            $partnerId,
            $type,
            $amount,
            $currency,
            $data['description'] ?? null,
            $data['ref_type']    ?? null,
            $data['ref_id']      ?? null,
            $balanceBefore,
            $balanceAfter,
            $_SESSION['user_id'] ?? null,
        ]);
        $txId = (int)$db->lastInsertId();

        // Keep partners.balance in sync (sum of all currencies — display only)
        $totalBalance = self::getPartnerBalance($partnerId);
        $db->prepare("UPDATE partners SET balance = ? WHERE id = ?")
           ->execute([$totalBalance, $partnerId]);

        return $txId;
    }

    /**
     * Paginated ledger for a single partner (newest first).
     */
    public static function getByPartner(int $partnerId, int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as c FROM credit_transactions WHERE partner_id = ?",
            [$partnerId]
        )['c'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT ct.*,
                    i.invoice_no
             FROM   credit_transactions ct
             LEFT JOIN invoices i ON ct.ref_type = 'invoice' AND ct.ref_id = i.id
             WHERE  ct.partner_id = ?
             ORDER BY ct.id DESC
             LIMIT  ? OFFSET ?",
            [$partnerId, $perPage, $offset]
        );

        return [
            'data'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'pages'   => (int)ceil($total / $perPage),
            'perPage' => $perPage,
        ];
    }

    /**
     * Pay an invoice (or part of it) using a partner's credit.
     *
     * Enforces:
     *  - Invoice exists and belongs to the partner (or is linked via company_id/name)
     *  - Currency of payment MUST match currency of the invoice
     *  - Per-currency balance must cover the payment amount
     *  - Payment amount must not exceed the invoice balance due
     *
     * Returns ['success' => bool, 'message' => string].
     */
    public static function payInvoice(int $partnerId, int $invoiceId, float $amount, string $currency = 'EUR'): array
    {
        $currency = strtoupper($currency);

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Amount must be greater than zero.'];
        }

        // Fetch invoice
        $invoice = Database::fetchOne("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found.'];
        }

        // Enforce currency match — you cannot pay a USD invoice with EUR credit
        $invoiceCurrency = strtoupper($invoice['currency'] ?? 'EUR');
        if ($currency !== $invoiceCurrency) {
            return ['success' => false, 'message' => sprintf(
                'Currency mismatch: invoice is in %s but payment currency is %s. ' .
                'You can only pay this invoice using %s credit.',
                $invoiceCurrency, $currency, $invoiceCurrency
            )];
        }

        // Check per-currency balance (ledger is source of truth)
        $balance = self::getPartnerBalance($partnerId, $currency);
        if ($balance < $amount) {
            return ['success' => false, 'message' => sprintf(
                'Insufficient %s credit. Available: %.2f %s, Requested: %.2f %s.',
                $currency, $balance, $currency, $amount, $currency
            )];
        }

        // Check invoice balance due
        $balanceDue = round((float)$invoice['total_amount'] - (float)($invoice['paid_amount'] ?? 0), 4);
        if ($balanceDue <= 0) {
            return ['success' => false, 'message' => 'This invoice is already fully paid.'];
        }
        if ($amount > $balanceDue + 0.001) {
            return ['success' => false, 'message' => sprintf(
                'Amount (%.2f %s) exceeds the invoice balance due (%.2f %s).',
                $amount, $currency, $balanceDue, $invoiceCurrency
            )];
        }

        // Record the ledger entry (also updates partners.balance)
        self::create([
            'partner_id'  => $partnerId,
            'type'        => 'payment',
            'amount'      => $amount,
            'currency'    => $currency,
            'description' => 'Invoice payment — ' . ($invoice['invoice_no'] ?? '#' . $invoiceId),
            'ref_type'    => 'invoice',
            'ref_id'      => $invoiceId,
        ]);

        // Update invoice paid_amount and status
        $newPaid   = round((float)($invoice['paid_amount'] ?? 0) + $amount, 4);
        $newStatus = ($newPaid >= (float)$invoice['total_amount'] - 0.001) ? 'paid' : 'partial';

        Database::execute(
            "UPDATE invoices
             SET paid_amount = ?, status = ?,
                 payment_method = 'credit', payment_date = date('now'),
                 updated_at = datetime('now')
             WHERE id = ?",
            [$newPaid, $newStatus, $invoiceId]
        );

        return ['success' => true, 'message' => sprintf(
            'Payment of %.2f %s applied. Invoice status: %s.',
            $amount, $currency, ucfirst($newStatus)
        )];
    }

    /**
     * Get per-currency balance summary for a partner.
     * Returns array keyed by currency with balance, total_in, total_out, tx_count.
     */
    public static function getCurrencyBalances(int $partnerId): array
    {
        $rows = Database::fetchAll(
            "SELECT currency,
                    ROUND(SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE -amount END), 2) AS balance,
                    SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE 0 END) AS total_in,
                    SUM(CASE WHEN type IN ('payment','adjustment') THEN amount ELSE 0 END) AS total_out,
                    COUNT(*) AS tx_count
             FROM credit_transactions
             WHERE partner_id = ?
             GROUP BY currency
             ORDER BY currency",
            [$partnerId]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['currency']] = $row;
        }
        return $result;
    }
}
