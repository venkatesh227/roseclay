<?php
require 'config.php';
$tables = array('countries','shipping_charges','fuel_charges');
foreach ($tables as $t) {
    echo "\n--- $t rows ---\n";
    $res = $conn->query("SELECT * FROM `$t` LIMIT 20");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            print_r($row);
            echo "\n";
        }
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}
