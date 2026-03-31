<?php
require_once 'config.php';

// Create admins table
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'admins' created successfully.<br>";
}
else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if default admin exists
$check_sql = "SELECT id FROM admins WHERE email = 'admin@happyhomes.com'";
$result = $conn->query($check_sql);

if ($result && $result->num_rows == 0) {
    // Insert default admin
    $hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
    $insert_sql = "INSERT INTO admins (name, email, password) VALUES ('Master Admin', 'admin@happyhomes.com', '$hashed_password')";
    if ($conn->query($insert_sql) === TRUE) {
        echo "Default admin account created successfully.<br>";
        echo "Email: admin@happyhomes.com<br>";
        echo "Password: admin123<br>";
    }
    else {
        echo "Error inserting admin: " . $conn->error;
    }
}
else {
    echo "Default admin already exists.<br>";
}

$conn->close();
?>
