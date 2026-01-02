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
// After creating the schema, ensure an admin account exists. If none, create one and
// append the seed INSERT into database.sql so the DB file contains the admin seed.
mysqli_select_db($conn, 'projek_pwd');

$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1");
if (!$admin_check || mysqli_num_rows($admin_check) === 0) {
    $admin_email = 'admin@local';
    $admin_pass_plain = 'Admin@123';
    $admin_fullname = 'Admin';
    $admin_role = 'admin';

    $hashed = password_hash($admin_pass_plain, PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $admin_fullname, $admin_email, $hashed, $admin_role);
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Admin account created: $admin_email / $admin_pass_plain\n";

            // Append seed to database.sql for future reference
            $seed_sql = "\n-- Seed admin account (created by setup_db.php)\n";
            $seed_sql .= "INSERT INTO users (fullname, email, password, role) VALUES ('" . addslashes($admin_fullname) . "', '" . addslashes($admin_email) . "', '" . addslashes($hashed) . "', '" . addslashes($admin_role) . "');\n";
            @file_put_contents(__DIR__ . '/../database.sql', $seed_sql, FILE_APPEND | LOCK_EX);
            echo "✓ Seed admin appended to database.sql\n";
        } else {
            echo "✗ Failed to insert admin: " . mysqli_error($conn) . "\n";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "✗ Failed to prepare insert admin: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "i Admin account already exists, skipping seed creation.\n";
}

mysqli_close($conn);
?>
