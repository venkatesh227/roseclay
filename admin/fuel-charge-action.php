<?php
session_start();
require_once '../config.php';
requireAdmin();

$fuel_charge = $_POST['fuel_charge'];
$gst = $_POST['gst'];

$check = mysqli_query($conn, "SELECT id FROM fuel_charges LIMIT 1");

if (mysqli_num_rows($check) > 0) {

    $row = mysqli_fetch_assoc($check);

    mysqli_query($conn, "
        UPDATE fuel_charges
        SET
        fuel_charge='$fuel_charge',
        gst='$gst'
        WHERE id='" . $row['id'] . "'
    ");
} else {

    mysqli_query($conn, "
        INSERT INTO fuel_charges(fuel_charge,gst)
        VALUES('$fuel_charge','$gst')
    ");
}

header("Location: fuel-charge.php");
exit;
