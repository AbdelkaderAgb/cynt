<?php
/**
 * CYN Tourism — Invoice Model
 */
class Invoice
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(invoice_no LIKE ? OR company_name LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s;
        }
        if (!empty($filters['status'])) {
            $where[]  = "status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = "invoice_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = "invoice_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['type'])) {
            $where[]  = "type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['currency'])) {
            $where[]  = "currency = ?";
            $params[] = $filters['currency'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as c FROM invoices $whereClause", $params
        )['c'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT * FROM invoices $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'data'  => $rows,
            'total' => $total,
            'page'  => $page,
            'pages' => (int)ceil($total / $perPage),
        ];
    }

    public static function getById(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM invoices WHERE id = ?", [$id]) ?: null;
    }

    public static function create(array $data): int
    {
        $no = 'INV-' . date('Ym') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['invoice_no']  = $no;
        $data['created_at']  = date('Y-m-d H:i:s');
        $data['created_by']  = $_SESSION['user_id'] ?? null;
        return Database::getInstance()->insert('invoices', $data);
    }

    public static function update(int $id, array $data): void
    {
        $sets   = [];
        $params = [];
        foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
        $params[] = $id;
        Database::execute("UPDATE invoices SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM invoices WHERE id = ?", [$id]);
    }

    public static function markPaid(int $id, string $method = 'cash'): void
    {
        Database::execute(
            "UPDATE invoices SET status = 'paid', payment_method = ?, payment_date = date('now'), paid_amount = total_amount WHERE id = ?",
            [$method, $id]
        );
    }

    public static function getSummary(): array
    {
        $row = Database::fetchOne(
            "SELECT
                COUNT(*)                                                                     AS total,
                SUM(CASE WHEN status IN ('sent','draft')     THEN 1 ELSE 0 END)             AS pending,
                SUM(CASE WHEN status = 'paid'                THEN 1 ELSE 0 END)             AS paid,
                SUM(CASE WHEN status = 'partial'             THEN 1 ELSE 0 END)             AS partial,
                SUM(CASE WHEN status = 'overdue'             THEN 1 ELSE 0 END)             AS overdue,
                COALESCE(SUM(total_amount), 0)                                              AS total_amount,
                COALESCE(SUM(paid_amount),  0)                                              AS paid_amount,
                COALESCE(SUM(total_amount) - SUM(paid_amount), 0)                          AS outstanding_amount,
                COALESCE(SUM(CASE WHEN status = 'overdue'
                               THEN total_amount - COALESCE(paid_amount,0) ELSE 0 END), 0) AS overdue_amount
             FROM invoices"
        ) ?: [];

        // Collection rate (avoid division by zero)
        $row['collection_rate'] = ($row['total_amount'] ?? 0) > 0
            ? round(($row['paid_amount'] / $row['total_amount']) * 100, 1)
            : 0;

        // Per-currency breakdown (amounts only — counts already in $row)
        $row['by_currency'] = Database::fetchAll(
            "SELECT currency,
                    COUNT(*)                            AS count,
                    COALESCE(SUM(total_amount), 0)      AS total_amount,
                    COALESCE(SUM(paid_amount),  0)      AS paid_amount,
                    COALESCE(SUM(total_amount) - SUM(paid_amount), 0) AS outstanding
             FROM invoices
             GROUP BY currency
             ORDER BY SUM(total_amount) DESC"
        ) ?: [];

        return $row;
    }
}
