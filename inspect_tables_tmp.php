<?php
require 'config.php';
$tables = array('countries','shipping_charges','fuel_charges');
foreach ($tables as $t) {
    $res = $conn->query("SHOW CREATE TABLE `$t`");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "\n--- $t ---\n";
        echo $row['Create Table'] . "\n";
    } else {
        echo "\n--- $t missing? " . $conn->error . "\n";
    }
}
