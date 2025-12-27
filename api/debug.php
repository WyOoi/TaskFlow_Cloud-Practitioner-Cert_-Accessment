<?php
// Debug file to check environment variables and connection

// Output header
echo "<h1>Vercel Debug Information</h1>";

// Check if we're running on Vercel
echo "<h2>Environment Detection</h2>";
echo "<p>Running on Vercel: " . (getenv('VERCEL') ? 'Yes' : 'No') . "</p>";
echo "<p>Vercel Region: " . (getenv('NOW_REGION') ?: 'Not detected') . "</p>";

// Check environment variables
echo "<h2>Database Environment Variables</h2>";
echo "<ul>";
echo "<li>DB_HOST: " . (getenv('DB_HOST') ?: 'Not set') . "</li>";
echo "<li>DB_USER: " . (getenv('DB_USER') ?: 'Not set') . "</li>";
echo "<li>DB_PASSWORD: " . (getenv('DB_PASSWORD') ? 'Set (hidden)' : 'Not set') . "</li>";
echo "<li>DB_NAME: " . (getenv('DB_NAME') ?: 'Not set') . "</li>";
echo "<li>DB_TABLE: " . (getenv('DB_TABLE') ?: 'Not set') . "</li>";
echo "</ul>";

// Try different ways to get environment variables
echo "<h2>Alternative Environment Variable Methods</h2>";
echo "<ul>";
echo "<li>DB_HOST using _ENV: " . (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'Not set') . "</li>";
echo "<li>DB_HOST using _SERVER: " . (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : 'Not set') . "</li>";
echo "</ul>";

// Try to connect to the database
echo "<h2>Database Connection Test</h2>";

// Get values for connection
$host = getenv('DB_HOST') ?: '152.42.234.166'; // Use provided IP as fallback
$user = getenv('DB_USER') ?: 'wy';
$password = getenv('DB_PASSWORD') ?: 'password';
$database = getenv('DB_NAME') ?: 'mydb1';

echo "<p>Attempting to connect with:</p>";
echo "<ul>";
echo "<li>Host: $host</li>";
echo "<li>User: $user</li>";
echo "<li>Database: $database</li>";
echo "</ul>";

try {
    // Set connection options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
        PDO::ATTR_PERSISTENT => false
    ];
    
    // Try to connect
    echo "<p>Connecting to MySQL server...</p>";
    $conn = new PDO("mysql:host=$host", $user, $password, $options);
    echo "<p style='color:green'>✓ Successfully connected to MySQL server!</p>";
    
    // Try to select database
    echo "<p>Selecting database '$database'...</p>";
    $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password, $options);
    echo "<p style='color:green'>✓ Successfully connected to database!</p>";
    
    // Try a simple query
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tables in database:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>";
    
    // Additional diagnostics
    echo "<h3>Network Diagnostics</h3>";
    
    // Check if we can resolve the hostname
    if (function_exists('gethostbyname')) {
        $ip = gethostbyname($host);
        echo "<p>DNS resolution for '$host': " . ($ip !== $host ? $ip : 'Failed to resolve') . "</p>";
    }
    
    // Check if we can reach the host
    if (function_exists('fsockopen')) {
        echo "<p>Testing direct connection to $host:3306...</p>";
        $socket = @fsockopen($host, 3306, $errno, $errstr, 5);
        if ($socket) {
            echo "<p style='color:green'>✓ Successfully opened socket to $host:3306</p>";
            fclose($socket);
        } else {
            echo "<p style='color:red'>❌ Failed to connect to $host:3306: $errstr ($errno)</p>";
            echo "<p>This may indicate a firewall issue or that MySQL is not accepting remote connections.</p>";
        }
    }
}

// Output phpinfo for additional debugging
echo "<h2>PHP Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Loaded Extensions:</p>";
echo "<ul>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo "<li>$ext</li>";
}
echo "</ul>";
?>
