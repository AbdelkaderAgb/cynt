<?php
/**
 * CurrencyController — Manages active currencies and exchange rate history.
 *
 * Exchange rates use the formula:
 *   effective_rate = market_rate × (1 + markup_percent / 100)
 *
 * The effective_rate is stored on every rate record so that historical
 * conversions always use the rate that was active at the time of the
 * transaction, not the current rate.
 */
class CurrencyController extends Controller
{
    // -------------------------------------------------------------------------
    // CURRENCIES — list & manage
    // -------------------------------------------------------------------------

    public function index(): void
    {
        Auth::requireAdmin();

        $currencies  = Database::fetchAll("SELECT * FROM currencies ORDER BY is_base DESC, code ASC");
        $rateCount   = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM exchange_rates WHERE valid_to IS NULL OR valid_to >= date('now')"
        );

        $this->view('settings/currencies', [
            'currencies'  => $currencies,
            'rateCount'   => $rateCount,
            'pageTitle'   => 'Currency Settings',
            'activePage'  => 'settings',
            'activeTab'   => 'currencies',
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $this->requireCsrf();

        $code   = strtoupper(trim($_POST['code'] ?? ''));
        $name   = trim($_POST['name'] ?? '');
        $symbol = trim($_POST['symbol'] ?? '');
        $isBase = isset($_POST['is_base']) ? 1 : 0;

        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            header('Location: ' . url('settings/currencies') . '?error=invalid_code');
            exit;
        }
        if ($name === '') {
            header('Location: ' . url('settings/currencies') . '?error=name_required');
            exit;
        }

        // Only one base currency allowed
        if ($isBase) {
            Database::execute("UPDATE currencies SET is_base = 0");
        }

        $existing = Database::fetchOne("SELECT id FROM currencies WHERE code = ?", [$code]);
        if ($existing) {
            Database::execute(
                "UPDATE currencies SET name = ?, symbol = ?, is_base = ?, is_active = 1, updated_at = datetime('now') WHERE code = ?",
                [$name, $symbol, $isBase, $code]
            );
        } else {
            Database::execute(
                "INSERT INTO currencies (code, name, symbol, is_base, is_active) VALUES (?, ?, ?, ?, 1)",
                [$code, $name, $symbol, $isBase]
            );
        }

        Logger::info('Currency saved', ['code' => $code, 'user' => Auth::user()['id'] ?? 0]);

        header('Location: ' . url('settings/currencies') . '?saved=1');
        exit;
    }

    public function delete(): void
    {
        Auth::requireAdmin();

        $code = strtoupper(trim($_GET['code'] ?? ''));
        if ($code === '') {
            header('Location: ' . url('settings/currencies') . '?error=missing');
            exit;
        }

        // Cannot delete the base currency
        $currency = Database::fetchOne("SELECT is_base FROM currencies WHERE code = ?", [$code]);
        if (!$currency) {
            header('Location: ' . url('settings/currencies') . '?error=not_found');
            exit;
        }
        if ($currency['is_base']) {
            header('Location: ' . url('settings/currencies') . '?error=cannot_delete_base');
            exit;
        }

        // Check usage in exchange_rates
        $inUse = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM exchange_rates WHERE from_currency = ? OR to_currency = ?",
            [$code, $code]
        );
        if ($inUse > 0) {
            Database::execute("UPDATE currencies SET is_active = 0, updated_at = datetime('now') WHERE code = ?", [$code]);
            Logger::info('Currency deactivated (in use)', ['code' => $code]);
        } else {
            Database::execute("DELETE FROM currencies WHERE code = ?", [$code]);
            Logger::info('Currency deleted', ['code' => $code]);
        }

        header('Location: ' . url('settings/currencies') . '?deleted=1');
        exit;
    }

    // -------------------------------------------------------------------------
    // EXCHANGE RATES — list & manage
    // -------------------------------------------------------------------------

    public function rates(): void
    {
        Auth::requireAdmin();

        $currencies = Database::fetchAll("SELECT code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY is_base DESC, code ASC");
        $rates      = Database::fetchAll(
            "SELECT * FROM exchange_rates ORDER BY from_currency, to_currency, valid_from DESC"
        );

        // Build a current-rate matrix keyed by "FROM_TO"
        $matrix = [];
        foreach ($rates as $r) {
            $key = $r['from_currency'] . '_' . $r['to_currency'];
            if (!isset($matrix[$key])) {
                $matrix[$key] = $r; // first (most recent) wins
            }
        }

        $this->view('settings/exchange_rates', [
            'currencies'  => $currencies,
            'rates'       => $rates,
            'matrix'      => $matrix,
            'pageTitle'   => 'Exchange Rates',
            'activePage'  => 'settings',
            'activeTab'   => 'rates',
        ]);
    }

    public function rateStore(): void
    {
        Auth::requireAdmin();
        $this->requireCsrf();

        $from          = strtoupper(trim($_POST['from_currency'] ?? ''));
        $to            = strtoupper(trim($_POST['to_currency'] ?? ''));
        $marketRate    = (float) ($_POST['market_rate'] ?? 0);
        $markupPercent = (float) ($_POST['markup_percent'] ?? 0);
        $validFrom     = trim($_POST['valid_from'] ?? date('Y-m-d'));
        $validTo       = trim($_POST['valid_to'] ?? '') ?: null;
        $notes         = trim($_POST['notes'] ?? '');
        $userId        = (int) (Auth::user()['id'] ?? 0);

        if ($from === '' || $to === '' || $from === $to) {
            header('Location: ' . url('settings/currencies/rates') . '?error=invalid_pair');
            exit;
        }
        if ($marketRate <= 0) {
            header('Location: ' . url('settings/currencies/rates') . '?error=invalid_rate');
            exit;
        }

        // Compute effective rate = market × (1 + markup/100)
        $effectiveRate = round($marketRate * (1 + $markupPercent / 100), 6);

        // Expire the previous open-ended rate for this pair
        Database::execute(
            "UPDATE exchange_rates SET valid_to = ? WHERE from_currency = ? AND to_currency = ? AND valid_to IS NULL",
            [date('Y-m-d', strtotime($validFrom . ' -1 day')), $from, $to]
        );

        Database::execute(
            "INSERT INTO exchange_rates (from_currency, to_currency, market_rate, markup_percent, effective_rate, valid_from, valid_to, notes, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$from, $to, $marketRate, $markupPercent, $effectiveRate, $validFrom, $validTo, $notes, $userId]
        );

        Logger::info('Exchange rate saved', [
            'pair' => "$from/$to", 'market' => $marketRate,
            'markup' => $markupPercent, 'effective' => $effectiveRate,
            'user' => $userId,
        ]);

        header('Location: ' . url('settings/currencies/rates') . '?saved=1');
        exit;
    }

    public function rateDelete(): void
    {
        Auth::requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . url('settings/currencies/rates') . '?error=missing');
            exit;
        }

        Database::execute("DELETE FROM exchange_rates WHERE id = ?", [$id]);
        Logger::info('Exchange rate deleted', ['id' => $id]);

        header('Location: ' . url('settings/currencies/rates') . '?deleted=1');
        exit;
    }

    // -------------------------------------------------------------------------
    // API — Live conversion calculator
    // -------------------------------------------------------------------------

    public function convertApi(): void
    {
        Auth::requireAdmin();

        $from   = strtoupper(trim($_GET['from'] ?? ''));
        $to     = strtoupper(trim($_GET['to'] ?? ''));
        $amount = (float) ($_GET['amount'] ?? 1);

        if ($from === $to) {
            $this->json(['rate' => 1.0, 'result' => $amount, 'from' => $from, 'to' => $to]);
            return;
        }

        $rate = $this->getEffectiveRate($from, $to);
        if ($rate === null) {
            $this->json(['error' => "No exchange rate found for $from → $to", 'rate' => null, 'result' => null], 404);
            return;
        }

        $this->json([
            'from'          => $from,
            'to'            => $to,
            'amount'        => $amount,
            'rate'          => $rate['effective_rate'],
            'market_rate'   => $rate['market_rate'],
            'markup_percent'=> $rate['markup_percent'],
            'result'        => round($amount * $rate['effective_rate'], 2),
            'valid_from'    => $rate['valid_from'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Public static helper — used by PricingCalculator and other controllers
    // -------------------------------------------------------------------------

    /**
     * Convert an amount from one currency to another using the current effective rate.
     *
     * @param float  $amount
     * @param string $from   ISO 4217 code
     * @param string $to     ISO 4217 code
     * @param string $date   Y-m-d — use the rate valid on this date (defaults to today)
     * @return float|null    null if no rate path exists
     */
    public static function convert(float $amount, string $from, string $to, string $date = ''): ?float
    {
        if ($from === $to) return $amount;
        if ($date === '') $date = date('Y-m-d');

        $rate = self::getRateForDate($from, $to, $date);
        if ($rate !== null) {
            return round($amount * $rate, 4);
        }

        // Try cross-rate through USD (base currency fallback)
        $rateToBase  = self::getRateForDate($from, 'USD', $date);
        $rateFromBase = self::getRateForDate('USD', $to, $date);
        if ($rateToBase !== null && $rateFromBase !== null) {
            return round($amount * $rateToBase * $rateFromBase, 4);
        }

        return null;
    }

    /**
     * Get the effective rate for a currency pair on a specific date.
     * Returns null if no rate is defined for that pair and date.
     */
    public static function getRateForDate(string $from, string $to, string $date): ?float
    {
        $row = Database::fetchOne(
            "SELECT effective_rate FROM exchange_rates
             WHERE from_currency = ? AND to_currency = ?
               AND valid_from <= ?
               AND (valid_to IS NULL OR valid_to >= ?)
             ORDER BY valid_from DESC
             LIMIT 1",
            [$from, $to, $date, $date]
        );
        return $row ? (float) $row['effective_rate'] : null;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getEffectiveRate(string $from, string $to): ?array
    {
        $today = date('Y-m-d');
        $row = Database::fetchOne(
            "SELECT effective_rate, market_rate, markup_percent, valid_from
             FROM exchange_rates
             WHERE from_currency = ? AND to_currency = ?
               AND valid_from <= ?
               AND (valid_to IS NULL OR valid_to >= ?)
             ORDER BY valid_from DESC
             LIMIT 1",
            [$from, $to, $today, $today]
        );
        return $row ?: null;
    }
}
