<?php
/**
 * CYN Tourism — Hotel Profile Controller
 * Manages hotel profiles with room types and pricing
 * Supports XLSX import for bulk data entry
 */
class HotelProfileController extends Controller
{
    /**
     * List all hotels with search/filter
     */
    public function index(): void
    {
        Auth::requireAuth();
        $search = trim($_GET['search'] ?? '');
        $stars = $_GET['stars'] ?? '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(h.name LIKE ? OR h.city LIKE ? OR h.country LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if ($stars) {
            $where[] = "h.stars = ?";
            $params[] = (int)$stars;
        }

        $whereStr = implode(' AND ', $where);

        $hotels = Database::fetchAll(
            "SELECT h.*,
                    (SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = h.id) as room_count,
                    (SELECT MIN(price_single) FROM hotel_rooms WHERE hotel_id = h.id AND price_single > 0) as min_price,
                    (SELECT MAX(price_single) FROM hotel_rooms WHERE hotel_id = h.id) as max_price
             FROM hotels h WHERE {$whereStr} ORDER BY h.name ASC",
            $params
        );

        $this->view('hotel_profiles/index', [
            'hotels'     => $hotels,
            'search'     => $search,
            'stars'      => $stars,
            'pageTitle'  => 'Hotel Profiles',
            'activePage' => 'hotel-profiles',
        ]);
    }

    /**
     * Create form
     */
    public function create(): void
    {
        Auth::requireAuth();
        $this->view('hotel_profiles/form', [
            'hotel'      => [],
            'rooms'      => [],
            'isEdit'     => false,
            'pageTitle'  => 'Add Hotel',
            'activePage' => 'hotel-profiles',
        ]);
    }

    /**
     * Edit form
     */
    public function edit(): void
    {
        Auth::requireAuth();
        $id = intval($_GET['id'] ?? 0);
        $hotel = Database::fetchOne("SELECT * FROM hotels WHERE id = ?", [$id]);
        if (!$hotel) { $this->redirect('hotels/profiles'); return; }

        $rooms = Database::fetchAll("SELECT * FROM hotel_rooms WHERE hotel_id = ? ORDER BY room_type ASC", [$id]);

        $this->view('hotel_profiles/form', [
            'hotel'      => $hotel,
            'rooms'      => $rooms,
            'isEdit'     => true,
            'pageTitle'  => 'Edit Hotel: ' . $hotel['name'],
            'activePage' => 'hotel-profiles',
        ]);
    }

    /**
     * Store hotel + rooms
     */
    public function store(): void
    {
        Auth::requireAuth();
        $id = intval($_POST['id'] ?? 0);

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'address'     => trim($_POST['address'] ?? ''),
            'city'        => trim($_POST['city'] ?? ''),
            'country'     => trim($_POST['country'] ?? 'Turkey'),
            'stars'       => intval($_POST['stars'] ?? 3),
            'phone'       => trim($_POST['phone'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'website'     => trim($_POST['website'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'active',
        ];

        if ($id) {
            Database::update('hotels', $data, 'id = ?', [$id]);
        } else {
            $id = Database::insert('hotels', $data);
        }

        // Delete old rooms and re-insert
        Database::execute("DELETE FROM hotel_rooms WHERE hotel_id = ?", [$id]);

        $roomTypes = $_POST['room_type'] ?? [];
        $capacities = $_POST['room_capacity'] ?? [];
        $priceSingle = $_POST['price_single'] ?? [];
        $priceDouble = $_POST['price_double'] ?? [];
        $priceTriple = $_POST['price_triple'] ?? [];
        $priceQuad = $_POST['price_quad'] ?? [];
        $priceChild = $_POST['price_child'] ?? [];
        $currencies = $_POST['room_currency'] ?? [];
        $boards = $_POST['board_type'] ?? [];
        $seasons = $_POST['season'] ?? [];

        for ($i = 0; $i < count($roomTypes); $i++) {
            if (empty(trim($roomTypes[$i]))) continue;
            Database::insert('hotel_rooms', [
                'hotel_id'     => $id,
                'room_type'    => trim($roomTypes[$i]),
                'capacity'     => intval($capacities[$i] ?? 2),
                'price_single' => floatval($priceSingle[$i] ?? 0),
                'price_double' => floatval($priceDouble[$i] ?? 0),
                'price_triple' => floatval($priceTriple[$i] ?? 0),
                'price_quad'   => floatval($priceQuad[$i] ?? 0),
                'price_child'  => floatval($priceChild[$i] ?? 0),
                'currency'     => $currencies[$i] ?? 'USD',
                'board_type'   => $boards[$i] ?? 'BB',
                'season'       => $seasons[$i] ?? 'all',
            ]);
        }

        header('Location: ' . url('hotels/profiles') . '?saved=1');
        exit;
    }

    /**
     * Delete hotel + rooms
     */
    public function delete(): void
    {
        Auth::requireAuth();
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id) {
            Database::execute("DELETE FROM hotel_rooms WHERE hotel_id = ?", [$id]);
            Database::execute("DELETE FROM hotels WHERE id = ?", [$id]);
        }
        header('Location: ' . url('hotels/profiles') . '?deleted=1');
        exit;
    }

    /**
     * Import hotels and rooms from XLSX file
     * Uses PHP ZipArchive + XML parsing — no external library needed
     */
    public function importXlsx(): void
    {
        Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['xlsx_file']['tmp_name'])) {
            header('Location: ' . url('hotels/profiles') . '?error=no_file');
            exit;
        }

        $file = $_FILES['xlsx_file']['tmp_name'];
        $imported = 0;

        try {
            $rows = $this->parseXlsx($file);

            if (empty($rows)) {
                header('Location: ' . url('hotels/profiles') . '?error=empty_file');
                exit;
            }

            // Expected columns: Hotel Name, Address, City, Country, Stars, Room Type, Capacity, Single, Double, Triple, Quad, Child, Currency, Board, Season
            $headers = array_map('strtolower', array_map('trim', $rows[0]));

            $hotelCache = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (count($row) < 6) continue;

                $hotelName = trim($row[0] ?? '');
                if (empty($hotelName)) continue;

                // Create or find hotel
                if (!isset($hotelCache[$hotelName])) {
                    $existing = Database::fetchOne("SELECT id FROM hotels WHERE name = ?", [$hotelName]);
                    if ($existing) {
                        $hotelCache[$hotelName] = $existing['id'];
                    } else {
                        $hotelCache[$hotelName] = Database::insert('hotels', [
                            'name'    => $hotelName,
                            'address' => trim($row[1] ?? ''),
                            'city'    => trim($row[2] ?? ''),
                            'country' => trim($row[3] ?? 'Turkey'),
                            'stars'   => intval($row[4] ?? 3),
                            'status'  => 'active',
                        ]);
                    }
                }

                // Insert room
                $roomType = trim($row[5] ?? '');
                if (!empty($roomType)) {
                    Database::insert('hotel_rooms', [
                        'hotel_id'     => $hotelCache[$hotelName],
                        'room_type'    => $roomType,
                        'capacity'     => intval($row[6] ?? 2),
                        'price_single' => floatval($row[7] ?? 0),
                        'price_double' => floatval($row[8] ?? 0),
                        'price_triple' => floatval($row[9] ?? 0),
                        'price_quad'   => floatval($row[10] ?? 0),
                        'price_child'  => floatval($row[11] ?? 0),
                        'currency'     => trim($row[12] ?? 'USD'),
                        'board_type'   => trim($row[13] ?? 'BB'),
                        'season'       => trim($row[14] ?? 'all'),
                    ]);
                    $imported++;
                }
            }
        } catch (\Exception $e) {
            header('Location: ' . url('hotels/profiles') . '?error=' . urlencode($e->getMessage()));
            exit;
        }

        header('Location: ' . url('hotels/profiles') . '?imported=' . $imported);
        exit;
    }

    /**
     * Parse XLSX file using ZipArchive + SimpleXML (no external library)
     */
    private function parseXlsx(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Cannot open XLSX file');
        }

        // Read shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml);
            foreach ($ss->si as $si) {
                $sharedStrings[] = (string)$si->t;
            }
        }

        // Read first sheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception('No sheet found in XLSX');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $value = (string)$cell->v;
                $type = (string)$cell['t'];
                if ($type === 's' && isset($sharedStrings[(int)$value])) {
                    $value = $sharedStrings[(int)$value];
                }
                $rowData[] = $value;
            }
            $rows[] = $rowData;
        }

        $zip->close();
        return $rows;
    }
}
