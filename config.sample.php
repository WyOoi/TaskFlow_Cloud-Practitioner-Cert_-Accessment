<?php
// Database configuration - SAMPLE FILE
// Copy this file to config.php and update with your database credentials

// For local development, set your database credentials directly
// $user = "your_database_username";
// $password = "your_database_password";
// $database = "your_database_name";
// $table = "todo_list";

// For Vercel deployment, use environment variables
$user = getenv('DB_USER') ?: 'your_database_username';
$password = getenv('DB_PASSWORD') ?: 'your_database_password';
$database = getenv('DB_NAME') ?: 'your_database_name';
$table = getenv('DB_TABLE') ?: 'todo_list';

// For Vercel deployment, you may need to update the host
$host = getenv('DB_HOST') ?: 'localhost';
?>