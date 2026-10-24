<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function check_db($host, $user, $pass, $dbname) {
    echo "<h2>Checking Database: $dbname</h2>";
    try {
        $conn = new mysqli($host, $user, $pass, $dbname);
        
        if ($conn->connect_error) {
            echo "<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>";
            return;
        }
        echo "<p style='color:green'>Connection successful!</p>";
        
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            echo "<p>Found " . count($tables) . " tables:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                // Check if table is readable
                $check = $conn->query("SELECT count(*) as c FROM `$table`");
                if ($check) {
                    $row = $check->fetch_assoc();
                    echo "<li>$table - OK ({$row['c']} rows)</li>";
                } else {
                    echo "<li style='color:red'>$table - ERROR: " . $conn->error . "</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red'>Error showing tables: " . $conn->error . "</p>";
        }
        $conn->close();
    } catch (Exception $e) {
        echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
    }
}

include 'config.php'; // Load config for roseclay_db

echo "<h1>Database Debugger</h1>";

// Check roseclay_db
check_db(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check cms (hardcoded credentials assuming they are same/standard)
check_db('localhost', 'root', '', 'cms');

?>
