<?php
/**
 * Generate Hotel Import XLSX Template
 * Creates a valid XLSX file with headers and sample rows
 * Uses native PHP ZipArchive — no external library needed
 */

$outputFile = __DIR__ . '/hotel_import_template.xlsx';

// Create ZIP (XLSX is just a ZIP file)
$zip = new ZipArchive();
if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('Cannot create XLSX file');
}

// Shared strings (all text values)
$strings = [
    'Hotel Name', 'Address', 'City', 'Country', 'Stars',
    'Room Type', 'Capacity', 'Single', 'Double', 'Triple',
    'Quad', 'Child', 'Currency', 'Board', 'Season',
    // Sample row 1 — Hilton Istanbul Bosphorus
    'Hilton Istanbul Bosphorus', 'Cumhuriyet Cad. Harbiye', 'Istanbul', 'Turkey',
    'Standard Room', 'Deluxe Room', 'Suite',
    'USD', 'BB', 'summer',
    'HB', 'winter',
    // Sample row 2 — DoubleTree by Hilton Antalya
    'DoubleTree by Hilton Antalya', 'Lara Yolu', 'Antalya',
    'Superior Room', 'Family Room',
    'EUR', 'AI',
    // Sample row 3 — Cappadocia Cave Resort
    'Cappadocia Cave Resort', 'Goreme Merkez', 'Nevsehir',
    'Cave Room', 'Cave Suite',
    'FB',
    // Sample row 4 — Bodrum Palace Hotel
    'Bodrum Palace Hotel', 'Torba Mah.', 'Bodrum',
    'Sea View Room', 'Premium Suite',
    'all',
];

// Build shared strings XML
$ssCount = count($strings);
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $ssCount . '" uniqueCount="' . $ssCount . '">';
foreach ($strings as $s) {
    $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1) . '</t></si>';
}
$ssXml .= '</sst>';

// Helper: get string index
$strIdx = array_flip($strings);

// Build sheet data — header + 10 sample rows
function si($idx) { return '<c t="s"><v>' . $idx . '</v></c>'; }
function ni($val) { return '<c><v>' . $val . '</v></c>'; }

// Row builder
$sheetRows = '';

// Row 1: Headers (indices 0-14)
$sheetRows .= '<row r="1">';
for ($i = 0; $i < 15; $i++) {
    $sheetRows .= si($i);
}
$sheetRows .= '</row>';

// Sample data rows
$sampleData = [
    // Hilton Istanbul: 3 room types
    [$strIdx['Hilton Istanbul Bosphorus'], $strIdx['Cumhuriyet Cad. Harbiye'], $strIdx['Istanbul'], $strIdx['Turkey'], 5,
     $strIdx['Standard Room'], 2, 120, 180, 240, 300, 60, $strIdx['USD'], $strIdx['BB'], $strIdx['summer']],
    [$strIdx['Hilton Istanbul Bosphorus'], $strIdx['Cumhuriyet Cad. Harbiye'], $strIdx['Istanbul'], $strIdx['Turkey'], 5,
     $strIdx['Deluxe Room'], 3, 180, 260, 340, 420, 80, $strIdx['USD'], $strIdx['HB'], $strIdx['summer']],
    [$strIdx['Hilton Istanbul Bosphorus'], $strIdx['Cumhuriyet Cad. Harbiye'], $strIdx['Istanbul'], $strIdx['Turkey'], 5,
     $strIdx['Suite'], 4, 320, 450, 580, 700, 120, $strIdx['USD'], $strIdx['BB'], $strIdx['winter']],
    // DoubleTree Antalya: 2 room types
    [$strIdx['DoubleTree by Hilton Antalya'], $strIdx['Lara Yolu'], $strIdx['Antalya'], $strIdx['Turkey'], 5,
     $strIdx['Superior Room'], 2, 95, 140, 185, 230, 45, $strIdx['EUR'], $strIdx['AI'], $strIdx['summer']],
    [$strIdx['DoubleTree by Hilton Antalya'], $strIdx['Lara Yolu'], $strIdx['Antalya'], $strIdx['Turkey'], 5,
     $strIdx['Family Room'], 4, 150, 220, 290, 360, 70, $strIdx['EUR'], $strIdx['AI'], $strIdx['summer']],
    // Cappadocia Cave Resort: 2 room types
    [$strIdx['Cappadocia Cave Resort'], $strIdx['Goreme Merkez'], $strIdx['Nevsehir'], $strIdx['Turkey'], 4,
     $strIdx['Cave Room'], 2, 85, 130, 175, 0, 40, $strIdx['USD'], $strIdx['FB'], $strIdx['summer']],
    [$strIdx['Cappadocia Cave Resort'], $strIdx['Goreme Merkez'], $strIdx['Nevsehir'], $strIdx['Turkey'], 4,
     $strIdx['Cave Suite'], 3, 160, 240, 320, 400, 75, $strIdx['USD'], $strIdx['HB'], $strIdx['winter']],
    // Bodrum Palace: 2 room types
    [$strIdx['Bodrum Palace Hotel'], $strIdx['Torba Mah.'], $strIdx['Bodrum'], $strIdx['Turkey'], 5,
     $strIdx['Sea View Room'], 2, 200, 300, 400, 0, 90, $strIdx['EUR'], $strIdx['AI'], $strIdx['summer']],
    [$strIdx['Bodrum Palace Hotel'], $strIdx['Torba Mah.'], $strIdx['Bodrum'], $strIdx['Turkey'], 5,
     $strIdx['Premium Suite'], 4, 380, 520, 660, 800, 150, $strIdx['EUR'], $strIdx['AI'], $strIdx['all']],
];

$rowNum = 2;
foreach ($sampleData as $row) {
    $sheetRows .= '<row r="' . $rowNum . '">';
    foreach ($row as $idx => $val) {
        // Columns 0-3 (name, address, city, country), 5 (room type), 12-14 (currency, board, season) are strings
        if (in_array($idx, [0,1,2,3,5,12,13,14])) {
            $sheetRows .= si($val);
        } else {
            $sheetRows .= ni($val);
        }
    }
    $sheetRows .= '</row>';
    $rowNum++;
}

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    . '<sheetData>' . $sheetRows . '</sheetData>'
    . '</worksheet>';

// Content types
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
    . '</Types>';

// Workbook
$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    . '<sheets><sheet name="Hotels" sheetId="1" r:id="rId1"/></sheets>'
    . '</workbook>';

// Relationships
$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    . '</Relationships>';

$workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
    . '</Relationships>';

// Add files to ZIP
$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('xl/workbook.xml', $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$zip->addFromString('xl/sharedStrings.xml', $ssXml);
$zip->close();

echo "✅ Template created: " . realpath($outputFile) . "\n";
echo "Contains 4 sample hotels with 9 room entries.\n";
