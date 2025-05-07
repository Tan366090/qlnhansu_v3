<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qlnhansu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Tables in database:\n";
echo "------------------\n";
$tables = $conn->query("SHOW TABLES");
while ($table = $tables->fetch_array()) {
    echo $table[0] . "\n";
    
    // Show table structure
    echo "\nStructure of " . $table[0] . ":\n";
    $structure = $conn->query("DESCRIBE " . $table[0]);
    while ($field = $structure->fetch_assoc()) {
        echo "  " . $field['Field'] . " - " . $field['Type'] . "\n";
    }
    echo "\n";
}

$conn->close();
?> 