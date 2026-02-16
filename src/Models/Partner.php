<?php
/**
 * CYN Tourism — Partner Model
 */
class Partner
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(company_name LIKE ? OR contact_person LIKE ? OR email LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if (!empty($filters['status'])) {
            $where[]  = "status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[]  = "partner_type = ?";
            $params[] = $filters['type'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) as c FROM partners $whereClause", $params
        )['c'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT * FROM partners $whereClause ORDER BY company_name ASC LIMIT $perPage OFFSET $offset",
            $params
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function getById(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM partners WHERE id = ?", [$id]) ?: null;
    }

    public static function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $_SESSION['user_id'] ?? null;
        return Database::getInstance()->insert('partners', $data);
    }

    public static function update(int $id, array $data): void
    {
        $sets = []; $params = [];
        foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
        $params[] = $id;
        Database::execute("UPDATE partners SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM partners WHERE id = ?", [$id]);
    }

    public static function getActive(): array
    {
        return Database::fetchAll("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name");
    }
}
