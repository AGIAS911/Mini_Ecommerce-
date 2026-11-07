<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

set_time_limit(300);

$isCli = (php_sapi_name() === 'cli');

function printLog(string $msg, bool $isCli): void {
    echo $isCli ? "[SEED] " . $msg . PHP_EOL : "<p style='margin:4px 0; font-family:monospace;'><strong>[SEED]</strong> " . htmlspecialchars($msg) . "</p>";
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

function fetchOrGenerateImage(string $filename, string $remoteUrl, string $fallbackTitle): string {
    $targetPath = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $filename;

    $downloaded = false;
    $ch = curl_init($remoteUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ]);
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($imageData)) {
        if (file_put_contents($targetPath, $imageData) !== false) {
            $downloaded = true;
        }
    }

    if (!$downloaded) {
        if (extension_loaded('gd')) {
            $width = 600;
            $height = 450;
            $im = imagecreatetruecolor($width, $height);
            $bg = imagecolorallocate($im, 240, 243, 246);
            $border = imagecolorallocate($im, 210, 215, 222);
            $text = imagecolorallocate($im, 33, 37, 41);

            imagefilledrectangle($im, 0, 0, $width, $height, $bg);
            imagerectangle($im, 0, 0, $width - 1, $height - 1, $border);

            $font = 5;
            $x = (int)(($width - imagefontwidth($font) * strlen($fallbackTitle)) / 2);
            $y = (int)(($height - imagefontheight($font)) / 2);
            imagestring($im, $font, $x, $y, $fallbackTitle, $text);

            imagejpeg($im, $targetPath, 90);
            imagedestroy($im);
        } else {
            $dummyPixel = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
            file_put_contents($targetPath, $dummyPixel);
        }
    }

    return 'uploads/products/' . $filename;
}

try {
    $db = Database::getInstance();

    printLog("Resetting database records...", $isCli);
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("TRUNCATE TABLE order_items");
    $db->exec("TRUNCATE TABLE orders");
    $db->exec("TRUNCATE TABLE product_images");
    $db->exec("TRUNCATE TABLE products");
    $db->exec("TRUNCATE TABLE categories");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    printLog("Inserting product categories...", $isCli);
    $categories = [
        'PC Peripherals' => 'Tactile mechanical keyboards, high-precision mice, and desktop equipment.',
        'Displays'       => 'High refresh rate and color-accurate gaming and productivity monitors.',
        'Audio Gear'     => 'Studio monitoring headphones, wireless earbuds, and condenser microphones.',
        'Gaming & VR'    => 'Virtual reality headsets, gamepads, and extended gaming desk pads.'
    ];

    $catMap = [];
    $catStmt = $db->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
    foreach ($categories as $name => $desc) {
        $catStmt->execute([':name' => $name, ':description' => $desc]);
        $catMap[$name] = (int)$db->lastInsertId();
    }

    $productsDataset = [
        [
            'category'    => 'PC Peripherals',
            'name'        => 'Redragon Mechanical RGB Keyboard',
            'description' => 'Tactile switches with per-key dynamic RGB backlighting, full anti-ghosting, and doubleshot keycaps.',
            'price'       => 54.99,
            'stock'       => 20,
            'image_file'  => 'prod_keyboard.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'PC Peripherals',
            'name'        => 'Logitech High-Precision Gaming Mouse',
            'description' => 'HERO optical sensor with adjustable weights, programmable macro buttons, and up to 25,600 DPI sensitivity.',
            'price'       => 42.50,
            'stock'       => 35,
            'image_file'  => 'prod_mouse.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'Displays',
            'name'        => 'AOC 24-Inch 165Hz IPS Gaming Monitor',
            'description' => '1080p FHD panel featuring 1ms response time, FreeSync Premium support, and height-adjustable stand.',
            'price'       => 169.00,
            'stock'       => 9,
            'image_file'  => 'prod_monitor.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'Audio Gear',
            'name'        => 'Studio Over-Ear Wireless Headphones',
            'description' => 'Deep acoustic tuning with hybrid active noise cancellation and up to 40 hours of continuous playback.',
            'price'       => 89.00,
            'stock'       => 18,
            'image_file'  => 'prod_headphones.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'Gaming & VR',
            'name'        => 'PlayStation VR Virtual Reality Headset',
            'description' => 'Immersive 3D audio, 120Hz OLED display panel, and precision motion tracking sensors for PC/console VR.',
            'price'       => 229.00,
            'stock'       => 7,
            'image_file'  => 'prod_vr.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1622979135225-d2ba269bc1df?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'Gaming & VR',
            'name'        => 'Pro Wireless Game Controller',
            'description' => 'Textured grip handles, responsive impulse triggers, and cross-platform Bluetooth connectivity for PC and mobile.',
            'price'       => 59.99,
            'stock'       => 25,
            'image_file'  => 'prod_gamepad.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1600080972464-8e5f35f63d08?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'PC Peripherals',
            'name'        => 'Aluminum Ergonomic Laptop Stand',
            'description' => 'Premium CNC finished aluminum riser with heat ventilation cutouts and non-slip silicone protective pads.',
            'price'       => 28.00,
            'stock'       => 40,
            'image_file'  => 'prod_stand.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'Gaming & VR',
            'name'        => 'Extended XL Gaming Mouse Pad (900x400)',
            'description' => 'Water-resistant micro-woven cloth surface with stitched anti-fray borders and dense rubber backing.',
            'price'       => 19.50,
            'stock'       => 30,
            'image_file'  => 'prod_deskpad.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1616440347437-b1c73416efc2?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'Audio Gear',
            'name'        => 'USB Cardioid Condenser Microphone',
            'description' => 'Plug-and-play podcast and streaming microphone with on-body gain control and zero-latency audio monitoring.',
            'price'       => 75.00,
            'stock'       => 14,
            'image_file'  => 'prod_mic.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=700&q=80'
        ],
        [
            'category'    => 'PC Peripherals',
            'name'        => '1TB Extreme Portable NVMe SSD',
            'description' => 'Rugged shock-resistant solid state drive offering up to 1050MB/s read speeds via high-speed USB-C.',
            'price'       => 119.99,
            'stock'       => 12,
            'image_file'  => 'prod_ssd.jpg',
            'image_url'   => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=700&q=80'
        ]
    ];

    $prodStmt = $db->prepare("
        INSERT INTO products (category_id, name, description, price, stock) 
        VALUES (:category_id, :name, :description, :price, :stock)
    ");

    $imgStmt = $db->prepare("
        INSERT INTO product_images (product_id, image_path, is_primary) 
        VALUES (:product_id, :image_path, 1)
    ");

    foreach ($productsDataset as $item) {
        printLog("Processing: {$item['name']}...", $isCli);
        
        $savedPath = fetchOrGenerateImage($item['image_file'], $item['image_url'], $item['name']);

        $prodStmt->execute([
            ':category_id' => $catMap[$item['category']],
            ':name'        => $item['name'],
            ':description' => $item['description'],
            ':price'       => $item['price'],
            ':stock'       => $item['stock']
        ]);
        
        $productId = (int)$db->lastInsertId();

        $imgStmt->execute([
            ':product_id' => $productId,
            ':image_path' => $savedPath
        ]);
    }

    printLog("Seeding finished successfully! 10 products with imagery have been loaded.", $isCli);
    printLog("Check your store at: " . BASE_URL, $isCli);

} catch (Throwable $e) {
    printLog("Execution Error: " . $e->getMessage(), $isCli);
}