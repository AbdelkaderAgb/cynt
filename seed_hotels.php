<?php
/**
 * Seed Sample Hotel Data
 * Adds 4 sample hotels with room types and pricing into the database
 * Run: php seed_hotels.php
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Check if hotels table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS hotels (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        address TEXT DEFAULT '',
        city TEXT DEFAULT '',
        country TEXT DEFAULT 'Turkey',
        stars INTEGER DEFAULT 3,
        phone TEXT DEFAULT '',
        email TEXT DEFAULT '',
        website TEXT DEFAULT '',
        description TEXT DEFAULT '',
        status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS hotel_rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hotel_id INTEGER NOT NULL,
        room_type TEXT NOT NULL,
        capacity INTEGER DEFAULT 2,
        price_single REAL DEFAULT 0,
        price_double REAL DEFAULT 0,
        price_triple REAL DEFAULT 0,
        price_quad REAL DEFAULT 0,
        price_child REAL DEFAULT 0,
        currency TEXT DEFAULT 'USD',
        board_type TEXT DEFAULT 'BB',
        season TEXT DEFAULT 'all',
        FOREIGN KEY (hotel_id) REFERENCES hotels(id)
    )");
} catch (Exception $e) {
    // Tables likely exist
}

// Sample hotels
$hotels = [
    [
        'name' => 'Hilton Istanbul Bosphorus',
        'address' => 'Cumhuriyet Cad. Harbiye',
        'city' => 'Istanbul',
        'country' => 'Turkey',
        'stars' => 5,
        'phone' => '+90 212 315 6000',
        'email' => 'info@hilton-istanbul.com',
        'description' => 'Iconic 5-star hotel overlooking the Bosphorus with luxury amenities and conference facilities.',
        'rooms' => [
            ['room_type' => 'Standard Room', 'capacity' => 2, 'price_single' => 120, 'price_double' => 180, 'price_triple' => 240, 'price_quad' => 300, 'price_child' => 60, 'currency' => 'USD', 'board_type' => 'BB', 'season' => 'summer'],
            ['room_type' => 'Deluxe Room', 'capacity' => 3, 'price_single' => 180, 'price_double' => 260, 'price_triple' => 340, 'price_quad' => 420, 'price_child' => 80, 'currency' => 'USD', 'board_type' => 'HB', 'season' => 'summer'],
            ['room_type' => 'Executive Suite', 'capacity' => 4, 'price_single' => 320, 'price_double' => 450, 'price_triple' => 580, 'price_quad' => 700, 'price_child' => 120, 'currency' => 'USD', 'board_type' => 'BB', 'season' => 'winter'],
        ],
    ],
    [
        'name' => 'DoubleTree by Hilton Antalya',
        'address' => 'Lara Yolu Caddesi',
        'city' => 'Antalya',
        'country' => 'Turkey',
        'stars' => 5,
        'phone' => '+90 242 310 1500',
        'email' => 'info@doubletree-antalya.com',
        'description' => 'Beachfront resort with all-inclusive packages, water sports, and kids club.',
        'rooms' => [
            ['room_type' => 'Superior Room', 'capacity' => 2, 'price_single' => 95, 'price_double' => 140, 'price_triple' => 185, 'price_quad' => 230, 'price_child' => 45, 'currency' => 'EUR', 'board_type' => 'AI', 'season' => 'summer'],
            ['room_type' => 'Family Room', 'capacity' => 4, 'price_single' => 150, 'price_double' => 220, 'price_triple' => 290, 'price_quad' => 360, 'price_child' => 70, 'currency' => 'EUR', 'board_type' => 'AI', 'season' => 'summer'],
        ],
    ],
    [
        'name' => 'Cappadocia Cave Resort & Spa',
        'address' => 'Goreme Merkez Mah.',
        'city' => 'Nevsehir',
        'country' => 'Turkey',
        'stars' => 4,
        'phone' => '+90 384 271 2800',
        'email' => 'stay@cappadocia-cave.com',
        'description' => 'Unique cave hotel carved into fairy chimneys with spa, rooftop terrace, and hot air balloon views.',
        'rooms' => [
            ['room_type' => 'Cave Room', 'capacity' => 2, 'price_single' => 85, 'price_double' => 130, 'price_triple' => 175, 'price_quad' => 0, 'price_child' => 40, 'currency' => 'USD', 'board_type' => 'FB', 'season' => 'summer'],
            ['room_type' => 'Cave Suite', 'capacity' => 3, 'price_single' => 160, 'price_double' => 240, 'price_triple' => 320, 'price_quad' => 400, 'price_child' => 75, 'currency' => 'USD', 'board_type' => 'HB', 'season' => 'winter'],
        ],
    ],
    [
        'name' => 'Bodrum Palace Hotel',
        'address' => 'Torba Mah. Ataturk Cad.',
        'city' => 'Bodrum',
        'country' => 'Turkey',
        'stars' => 5,
        'phone' => '+90 252 367 1500',
        'email' => 'reservations@bodrumpalace.com',
        'description' => 'Luxury beach resort with infinity pool, private beach, and fine dining overlooking the Aegean Sea.',
        'rooms' => [
            ['room_type' => 'Sea View Room', 'capacity' => 2, 'price_single' => 200, 'price_double' => 300, 'price_triple' => 400, 'price_quad' => 0, 'price_child' => 90, 'currency' => 'EUR', 'board_type' => 'AI', 'season' => 'summer'],
            ['room_type' => 'Premium Suite', 'capacity' => 4, 'price_single' => 380, 'price_double' => 520, 'price_triple' => 660, 'price_quad' => 800, 'price_child' => 150, 'currency' => 'EUR', 'board_type' => 'AI', 'season' => 'all'],
        ],
    ],
];

$hotelCount = 0;
$roomCount = 0;

foreach ($hotels as $hotelData) {
    $rooms = $hotelData['rooms'];
    unset($hotelData['rooms']);

    // Check if hotel already exists
    $existing = $db->prepare("SELECT id FROM hotels WHERE name = ?");
    $existing->execute([$hotelData['name']]);
    $row = $existing->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "⏭️  Hotel '{$hotelData['name']}' already exists, skipping.\n";
        continue;
    }

    // Insert hotel
    $cols = implode(', ', array_keys($hotelData));
    $placeholders = implode(', ', array_fill(0, count($hotelData), '?'));
    $stmt = $db->prepare("INSERT INTO hotels ({$cols}) VALUES ({$placeholders})");
    $stmt->execute(array_values($hotelData));
    $hotelId = $db->lastInsertId();
    $hotelCount++;

    echo "✅ Added hotel: {$hotelData['name']} (⭐{$hotelData['stars']})\n";

    // Insert rooms
    foreach ($rooms as $room) {
        $room['hotel_id'] = $hotelId;
        $cols = implode(', ', array_keys($room));
        $placeholders = implode(', ', array_fill(0, count($room), '?'));
        $stmt = $db->prepare("INSERT INTO hotel_rooms ({$cols}) VALUES ({$placeholders})");
        $stmt->execute(array_values($room));
        $roomCount++;
        echo "   🛏️  {$room['room_type']} — {$room['currency']} {$room['price_single']}/{$room['price_double']}/{$room['price_triple']} ({$room['board_type']}, {$room['season']})\n";
    }
}

echo "\n🎉 Done! Added {$hotelCount} hotels with {$roomCount} room types.\n";
echo "📁 XLSX Template available at: public/templates/hotel_import_template.xlsx\n";
