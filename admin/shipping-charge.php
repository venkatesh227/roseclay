<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = "Shipping Charge";
include 'includes/header.php';

$result = mysqli_query($conn, "SELECT * FROM shipping_charges
ORDER BY rate_type, weight");
?>

<form method="post" action="update-shipping.php">

    <table class="table table-bordered table-striped">

        <thead>

            <tr>
                <th>Type</th>
                <th>KG</th>
                <th>Z1</th>
                <th>Z2</th>
                <th>Z3</th>
                <th>Z4</th>
                <th>Z5</th>
                <th>Z6</th>
                <th>Z7</th>
                <th>Z8</th>
                <th>Z9</th>
                <th>Z10</th>
                <th>Handling Charges</th>
            </tr>

        </thead>

        <tbody>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <tr>

                    <input type="hidden"
                        name="id[]"
                        value="<?= $row['id'] ?>">

                    <td>

                        <input type="text"
                            class="form-control"
                            name="rate_type[]"
                            value="<?= $row['rate_type'] ?>">

                    </td>

                    <td>

                        <input
                            type="text"
                            class="form-control"
                            name="weight[]"
                            value="<?= $row['weight'] ?>">

                    </td>

                    <?php for ($i = 1; $i <= 10; $i++) { ?>

                        <td>

                            <input
                                type="text"
                                class="form-control"
                                name="zone<?= $i ?>[]"
                                value="<?= $row["zone$i"] ?>">

                        </td>

                    <?php } ?>
                    <td>
                        <input
                            type="text"
                            class="form-control"
                            name="handling_charges[]"
                            value="<?= $row['handling_charges'] ?>">
                    </td>

                </tr>

            <?php } ?>

        </tbody>

    </table>

    <button class="btn btn-success">
        Save Changes
    </button>

</form>

<?php include 'includes/footer.php'; ?>