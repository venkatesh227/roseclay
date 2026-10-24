<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Fuel Charge';
include 'includes/header.php';

$result = mysqli_query($conn, "SELECT * FROM fuel_charges LIMIT 1");
$data = mysqli_fetch_assoc($result);
?>

<div class="card">
    <div class="card-header">
        <h3>Fuel Charge Settings</h3>
    </div>

    <div class="card-body">

        <form action="fuel-charge-action.php" method="POST">

            <div class="form-group">
                <label>Fuel Charge (%)</label>
                <input type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    name="fuel_charge"
                    class="form-control"
                    value="<?= $data['fuel_charge'] ?? '' ?>"
                    placeholder="Enter Fuel Charge %"
                    required>
            </div>

            <div class="form-group mt-3">
                <label>GST (%)</label>
                <input type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    name="gst"
                    class="form-control"
                    value="<?= $data['gst'] ?? '' ?>"
                    placeholder="Enter GST %"
                    required>
            </div>

            <br>

            <button type="submit" class="btn btn-primary">
                Save
            </button>

        </form>

    </div>
</div>

<?php include 'includes/footer.php'; ?>