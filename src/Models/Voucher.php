<?php
/**
 * CYN Tourism — Voucher Model
 * CRUD + search/filter for transfer, tour, and hotel vouchers.
 */
class Voucher
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(voucher_no LIKE ? OR company_name LIKE ? OR hotel_name LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if (!empty($filters['status'])) {
            $where[]  = "status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = "pickup_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = "pickup_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['company'])) {
            $where[]  = "company_name = ?";
            $params[] = $filters['company'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as c FROM vouchers $whereClause", $params
        )['c'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT * FROM vouchers $whereClause ORDER BY pickup_date DESC, pickup_time DESC LIMIT $perPage OFFSET $offset",
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
        return Database::fetchOne("SELECT * FROM vouchers WHERE id = ?", [$id]) ?: null;
    }

    public static function create(array $data): int
    {
        $no = 'VC-' . date('Ym') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $data['voucher_no'] = $no;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $_SESSION['user_id'] ?? null;
        return Database::getInstance()->insert('vouchers', $data);
    }

    public static function update(int $id, array $data): void
    {
        $data['updated_by'] = $_SESSION['user_id'] ?? null;
        $sets   = [];
        $params = [];
        foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
        $params[] = $id;
        Database::execute("UPDATE vouchers SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM vouchers WHERE id = ?", [$id]);
    }

    public static function getUpcoming(int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT * FROM vouchers WHERE pickup_date >= CURDATE() ORDER BY pickup_date ASC, pickup_time ASC LIMIT ?",
            [$limit]
        );
    }

    public static function getCompanies(): array
    {
        return Database::fetchAll("SELECT DISTINCT company_name FROM vouchers ORDER BY company_name");
    }
}
