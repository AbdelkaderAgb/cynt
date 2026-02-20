<?php
/**
 * CYN Tourism — Fleet Model (Drivers, Vehicles, Tour Guides)
 */
class Fleet
{
    // ── Drivers ──────────────────────────────────────────
    public static function getDrivers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return self::getEntities('drivers', $filters, $page, $perPage,
            "(first_name || ' ' || last_name) LIKE ? OR phone LIKE ?", 2);
    }

    public static function getDriver(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM drivers WHERE id = ?", [$id]) ?: null;
    }

    public static function saveDriver(array $data, int $id = 0): int
    {
        return self::saveEntity('drivers', $data, $id);
    }

    public static function deleteDriver(int $id): void
    {
        Database::execute("DELETE FROM drivers WHERE id = ?", [$id]);
    }

    // ── Vehicles ─────────────────────────────────────────
    public static function getVehicles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return self::getEntities('vehicles', $filters, $page, $perPage,
            "plate_number LIKE ? OR make LIKE ? OR model LIKE ?", 3);
    }

    public static function getVehicle(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM vehicles WHERE id = ?", [$id]) ?: null;
    }

    public static function saveVehicle(array $data, int $id = 0): int
    {
        return self::saveEntity('vehicles', $data, $id);
    }

    public static function deleteVehicle(int $id): void
    {
        Database::execute("DELETE FROM vehicles WHERE id = ?", [$id]);
    }

    // ── Tour Guides ──────────────────────────────────────
    public static function getGuides(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return self::getEntities('tour_guides', $filters, $page, $perPage,
            "(first_name || ' ' || last_name) LIKE ? OR languages LIKE ?", 2);
    }

    public static function getGuide(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM tour_guides WHERE id = ?", [$id]) ?: null;
    }

    public static function saveGuide(array $data, int $id = 0): int
    {
        return self::saveEntity('tour_guides', $data, $id);
    }

    public static function deleteGuide(int $id): void
    {
        Database::execute("DELETE FROM tour_guides WHERE id = ?", [$id]);
    }

    // ── Active lists (for dropdowns) ─────────────────────
    public static function getActiveDrivers(): array
    {
        return Database::fetchAll("SELECT id, first_name, last_name, (first_name || ' ' || last_name) as name, phone FROM drivers WHERE status='active' ORDER BY first_name");
    }

    public static function getActiveVehicles(): array
    {
        return Database::fetchAll("SELECT id, plate_number, make, model, (make || ' ' || model) as name, capacity FROM vehicles WHERE status IN ('active','available') ORDER BY plate_number");
    }

    public static function getActiveGuides(): array
    {
        return Database::fetchAll("SELECT id, first_name, last_name, (first_name || ' ' || last_name) as name, languages FROM tour_guides WHERE status='active' ORDER BY first_name");
    }

    // ── Private helpers ──────────────────────────────────
    private static function getEntities(string $table, array $filters, int $page, int $perPage, string $searchExpr, int $searchParamCount): array
    {
        $where = []; $params = [];

        if (!empty($filters['search'])) {
            $where[] = "($searchExpr)";
            $s = '%' . $filters['search'] . '%';
            for ($i = 0; $i < $searchParamCount; $i++) $params[] = $s;
        }
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        $wc = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $total = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM $table $wc", $params)['c'] ?? 0);
        $rows  = Database::fetchAll("SELECT * FROM $table $wc ORDER BY id DESC LIMIT $perPage OFFSET $offset", $params);

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    private static function saveEntity(string $table, array $data, int $id): int
    {
        if ($id > 0) {
            $sets = []; $params = [];
            foreach ($data as $k => $v) { $sets[] = "$k = ?"; $params[] = $v; }
            $params[] = $id;
            Database::execute("UPDATE $table SET " . implode(', ', $sets) . " WHERE id = ?", $params);
            return $id;
        }
        return Database::getInstance()->insert($table, $data);
    }
}
