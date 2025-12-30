<?php
// Database setup script
$host = "localhost";
$user = "root";
$pass = "";

// Connect without selecting database
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Connection Error: " . mysqli_connect_error());
}

// Read and execute SQL file
$sql = file_get_contents(__DIR__ . '/../database.sql');

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (mysqli_multi_query($conn, $statement . ';')) {
            // Consume results to clear the buffer
            do {
                if ($result = mysqli_store_result($conn)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($conn));
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        } else {
            echo "✗ Error: " . mysqli_error($conn) . "\n";
            echo "  Statement: " . substr($statement, 0, 50) . "...\n";
        }
    }
}

echo "\n✓ Database setup completed!\n";
mysqli_close($conn);
?>
