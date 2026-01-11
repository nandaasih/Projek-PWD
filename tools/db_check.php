<?php
// tools/db_check.php
// Simple diagnostic script to test MySQL connection attempts.

$hostEnv = getenv('DB_HOST') ?: '127.0.0.1';
$portEnv = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db = getenv('DB_NAME') ?: 'projek_pwd';

$tests = [];
// prefer configured host first, then its alternate, then defaults
$tests[] = ['host'=>$hostEnv, 'port'=>$portEnv];
$tests[] = ['host'=>($hostEnv === '127.0.0.1' ? 'localhost' : '127.0.0.1'), 'port'=>$portEnv];
$tests[] = ['host'=>'127.0.0.1', 'port'=>3306];
$tests[] = ['host'=>'localhost', 'port'=>3306];

// unique tests
$seen = [];
$uniq = [];
foreach ($tests as $t) {
    $k = $t['host'] . ':' . $t['port'];
    if (!isset($seen[$k])) { $seen[$k] = true; $uniq[] = $t; }
}

echo "DB Diagnostic\n";
echo "= Settings used if not overridden by env vars:\n";
echo " user={$user} db={$db}\n\n";

foreach ($uniq as $t) {
    $h = $t['host']; $p = (int)$t['port'];
    echo "Trying {$h}:{$p} ... ";
    $conn = @mysqli_connect($h, $user, $pass, $db, $p);
    if ($conn) {
        echo "OK\n";
        mysqli_close($conn);
    } else {
        $err = mysqli_connect_error();
        echo "FAILED - " . $err . "\n";
    }
}

echo "\nHelpful checks:\n";
echo " - Ensure MySQL/MariaDB is running (open XAMPP Control Panel -> Start MySQL).\n";
echo " - Check which port MySQL listens on (my.ini / my.cnf -> port).\n";
echo " - Test from Windows PowerShell: Test-NetConnection -ComputerName 127.0.0.1 -Port 3306\n";
echo " - Check firewall allowing 3306 or try connecting via localhost if using named pipe.\n";

echo "\nRun this script in browser: http://localhost/FINAL_P/tools/db_check.php or CLI: php tools/db_check.php\n";

// mark TODO done

?>