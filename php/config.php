<?php
// ============================================
//  Al-Hind Solutions - Database Configuration
//  XAMPP Default Settings
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // XAMPP default
define('DB_PASS', '');       // XAMPP default (empty password)
define('DB_NAME', 'alhind_db');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("<div style='font-family:sans-serif;background:#fee2e2;color:#991b1b;padding:20px;margin:20px;border-radius:10px;'>
        <h3>&#10060; Database Connection Failed</h3>
        <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
        <p>Make sure:</p>
        <ul>
            <li>XAMPP is running (Apache + MySQL both green)</li>
            <li>You have imported <strong>db.sql</strong> in phpMyAdmin</li>
            <li>Database name is <strong>alhind_db</strong></li>
        </ul>
    </div>");
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
