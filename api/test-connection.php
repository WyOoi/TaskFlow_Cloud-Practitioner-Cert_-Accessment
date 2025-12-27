<?php
// Simple connection test file - minimal code to test database connection

// Direct connection using hardcoded values
$host = '152.42.234.166'; // Your server IP
$user = 'wy';
$password = 'password';
$database = 'mydb1';

echo "<h1>Direct MySQL Connection Test</h1>";

try {
    // Try connecting directly with hardcoded values
    $conn = new PDO("mysql:host=$host", $user, $password);
    echo "<p style='color:green'>✓ Successfully connected to MySQL server at $host!</p>";
    
    // Try selecting the database
    $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    echo "<p style='color:green'>✓ Successfully connected to database '$database'!</p>";
    
    // Run a simple query
    $result = $conn->query("SELECT 'Connection successful!' as message")->fetch();
    echo "<p><strong>Result:</strong> " . $result['message'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>";
    
    // Network test
    echo "<h2>Network Test</h2>";
    $socket = @fsockopen($host, 3306, $errno, $errstr, 5);
    if ($socket) {
        echo "<p style='color:green'>✓ Port 3306 is open on $host</p>";
        fclose($socket);
    } else {
        echo "<p style='color:red'>❌ Cannot connect to port 3306 on $host: $errstr ($errno)</p>";
        echo "<p>This likely means:</p>";
        echo "<ul>";
        echo "<li>MySQL is not running on that server</li>";
        echo "<li>A firewall is blocking the connection</li>";
        echo "<li>MySQL is not configured to accept remote connections</li>";
        echo "</ul>";
    }
}
?>
