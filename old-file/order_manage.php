<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Order Management - Inventory</title>

    <?php include('assets/partials/headerfile.php') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
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
                                    <h2>Order Management</h2>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="orderTable" class="table table-hover table-product" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>User Name</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Example row, replace with dynamic content -->
                                                <tr>
                                                    <td>24547</td>
                                                    <td>John Doe</td>
                                                    <td>20 Jan 2021</td>
                                                    <td>
                                                        <a class="offcanvas-toggler active custom-dropdown-toggler btn btn-sm btn-info" data-offcanvas="order-off" href="javascript:">
                                                            <!-- <i class="mdi mdi-contacts icon"></i> -->
                                                             Action
                                                        </a>
                                                    </td>
                                                </tr>
                                                <!-- End example row -->
                                            </tbody>
                                        </table>
                                    </div>
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


    <style>
            .btn {
                 /* padding: 5px; */
                 padding-top: 1px;
                 padding-bottom: 1px;
                 padding-left: 8px;
                 padding-right: 8px;
            }
    </style>

    <!-- Order Offcanvas -->
    <div class="card card-offcanvas" id="order-off">
        <div class="card-header">
            <h2>Order Details</h2>
            <a href="#" class="btn btn-primary btn-pill px-4">Add New Product</a>
        </div>
        <div class="card-body">
            <div id="orderDetailsContainer">
                <!-- Example product, replace with dynamic content -->
                <div class="media media-sm mb-3">
                    <div class="media-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Product 1</span>
                            <div>
                                <button class="btn btn-xs btn-outline-secondary" onclick="decreaseQuantity(1, 'Product 1')">-</button>
                                <span class="mx-2">2</span>
                                <button class="btn btn-xs btn-outline-secondary" onclick="increaseQuantity(1, 'Product 1')">+</button>
                                <button class="btn btn-xs btn-outline-danger" onclick="removeProduct(1, 'Product 1')"><i class="mdi mdi-trash-can"></i></button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Product 2</span>
                            <div>
                                <button class="btn btn-xs btn-outline-secondary" onclick="decreaseQuantity(1, 'Product 2')">-</button>
                                <span class="mx-2">1</span>
                                <button class="btn btn-xs btn-outline-secondary" onclick="increaseQuantity(1, 'Product 2')">+</button>
                                <button class="btn btn-xs btn-outline-danger" onclick="removeProduct(1, 'Product 2')"><i class="mdi mdi-trash-can"></i></button>
                            </div>
                        </div>
                        <button class="btn btn-success btn-xs" onclick="approveOrder(1)">Approve</button>
                        <button class="btn btn-danger btn-xs" onclick="rejectOrder(1)">Reject</button>
                    </div>
                </div>
                <!-- End example product -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#orderTable').DataTable();
        });

        function approveOrder(orderId) {
            alert(`Order ${orderId} approved`);
            // Implement approval logic here
        }

        function rejectOrder(orderId) {
            alert(`Order ${orderId} rejected`);
            // Implement rejection logic here
        }

        function increaseQuantity(orderId, productName) {
            alert(`Increase quantity of ${productName} in order ${orderId}`);
            // Implement increase quantity logic here
        }

        function decreaseQuantity(orderId, productName) {
            alert(`Decrease quantity of ${productName} in order ${orderId}`);
            // Implement decrease quantity logic here
        }

        function removeProduct(orderId, productName) {
            alert(`Remove ${productName} from order ${orderId}`);
            // Implement remove product logic here
        }
    </script>
</body>

</html>