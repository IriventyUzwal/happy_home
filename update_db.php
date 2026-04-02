<?php
require_once 'config.php';

// Add new columns to rooms table
$queries = [
    "ALTER TABLE rooms ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT 'default_room.png'",
    "ALTER TABLE rooms ADD COLUMN IF NOT EXISTS description TEXT",
    "ALTER TABLE rooms ADD COLUMN IF NOT EXISTS is_available TINYINT DEFAULT 1"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Query successful: $q\n";
    } else {
        echo "Query failed: " . $conn->error . "\n";
    }
}

// Update existing rooms with default images if they match our current set
$updates = [
    "Deluxe Room" => "deluxe.png",
    "Standard Room" => "standard.png",
    "Executive Suite" => "suite.png",
    "Family Room" => "family.png"
];

foreach ($updates as $type => $img) {
    $conn->query("UPDATE rooms SET image = '$img', description = 'Experience luxury and comfort in our $type.' WHERE room_type = '$type' AND (image IS NULL OR image = 'default_room.png')");
}

echo "Database schema update complete.";
?>
