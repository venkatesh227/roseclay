<?php

session_start();
require_once '../config.php';
requireAdmin();

$count = count($_POST['id']);

for ($i = 0; $i < $count; $i++) {

    $id = mysqli_real_escape_string($conn, $_POST['id'][$i]);

    $rate_type = mysqli_real_escape_string($conn, $_POST['rate_type'][$i]);
    $weight = mysqli_real_escape_string($conn, $_POST['weight'][$i]);

    $zone1 = mysqli_real_escape_string($conn, $_POST['zone1'][$i]);
    $zone2 = mysqli_real_escape_string($conn, $_POST['zone2'][$i]);
    $zone3 = mysqli_real_escape_string($conn, $_POST['zone3'][$i]);
    $zone4 = mysqli_real_escape_string($conn, $_POST['zone4'][$i]);
    $zone5 = mysqli_real_escape_string($conn, $_POST['zone5'][$i]);
    $zone6 = mysqli_real_escape_string($conn, $_POST['zone6'][$i]);
    $zone7 = mysqli_real_escape_string($conn, $_POST['zone7'][$i]);
    $zone8 = mysqli_real_escape_string($conn, $_POST['zone8'][$i]);
    $zone9 = mysqli_real_escape_string($conn, $_POST['zone9'][$i]);
    $zone10 = mysqli_real_escape_string($conn, $_POST['zone10'][$i]);

    $handling_charges = mysqli_real_escape_string($conn, $_POST['handling_charges'][$i]);

    $sql = "UPDATE shipping_charges SET
                rate_type='$rate_type',
                weight='$weight',
                zone1='$zone1',
                zone2='$zone2',
                zone3='$zone3',
                zone4='$zone4',
                zone5='$zone5',
                zone6='$zone6',
                zone7='$zone7',
                zone8='$zone8',
                zone9='$zone9',
                zone10='$zone10',
                handling_charges='$handling_charges'
            WHERE id='$id'";

    mysqli_query($conn, $sql);
}

header("Location: shipping-charge.php?updated=1");
exit;
