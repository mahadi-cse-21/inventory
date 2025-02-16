<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Manual Bulk Inventory Entry - Inventory</title>

    <?php include('assets/partials/headerfile.php') ?>
</head>

<body class="navbar-fixed sidebar-fixed" id="body">
    <div id="toaster"></div>

    <div class="wrapper">
        <?php include('assets/partials/sidebar.php') ?>

        <div class="page-wrapper">
            <?php include('assets/partials/navbar.php') ?>

            <div class="content-wrapper">
                <div class="content">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-default">
                                <div class="card-header">
                                    <h2>Manual Bulk Inventory Entry</h2>
                                </div>
                                <div class="card-body">
                                    <form action="process_manual_bulk_entry.php" method="post">
                                        <div id="inventoryEntries">
                                            <div class="form-row mb-3">
                                                <div class="col">
                                                    <input type="text" class="form-control" name="productName[]" placeholder="Product Name" required>
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control" name="productId[]" placeholder="Product ID" required>
                                                </div>
                                                <div class="col">
                                                    <input type="number" class="form-control" name="quantity[]" placeholder="Quantity" required>
                                                </div>
                                                <div class="col">
                                                    <input type="number" class="form-control" name="inStock[]" placeholder="In Stock" required>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-secondary" onclick="addEntry()">Add Another Entry</button>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('assets/partials/footer.php') ?>
        </div>
    </div>

    <?php include('assets/partials/footerfile.php') ?>

    <script>
        function addEntry() {
            const entryHtml = `
                <div class="form-row mb-3">
                    <div class="col">
                        <input type="text" class="form-control" name="productName[]" placeholder="Product Name" required>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" name="productId[]" placeholder="Product ID" required>
                    </div>
                    <div class="col">
                        <input type="number" class="form-control" name="quantity[]" placeholder="Quantity" required>
                    </div>
                    <div class="col">
                        <input type="number" class="form-control" name="inStock[]" placeholder="In Stock" required>
                    </div>
                </div>
            `;
            document.getElementById('inventoryEntries').insertAdjacentHTML('beforeend', entryHtml);
        }
    </script>
</body>

</html>
