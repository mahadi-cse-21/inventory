<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Bulk Inventory Entry - Inventory</title>

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
                                    <h2>Bulk Inventory Entry</h2>
                                </div>
                                <div class="card-body">
                                    <form action="process_bulk_entry.php" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="inventoryFile">Upload CSV File</label>
                                            <input type="file" class="form-control" id="inventoryFile" name="inventoryFile" accept=".csv" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Upload</button>
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
</body>

</html>
