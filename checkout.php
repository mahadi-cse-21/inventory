<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Checkout - Mono</title>

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
                                    <h2>Checkout</h2>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="checkoutTable" class="table table-hover table-product" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Product Name</th>
                                                    <th>Qty</th>
                                                    <!-- <th>Price</th>
                                                    <th>Total</th> -->
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Example row, replace with dynamic content -->
                                                <tr>
                                                    <td>24547</td>
                                                    <td>Ledger Nano X</td>
                                                    <td>
                                                        <button class="btn btn-sm  decrease-qty">-</button>
                                                        <span class="quantity">2</span>
                                                        <button class="btn btn-sm  increase-qty">+</button>
                                                    </td>
                                                    <!-- <td>$119</td>
                                                    <td>$238</td> -->
                                                    <td>
                                                        <button class="btn btn-sm btn-danger remove-item">
                                                            <i class="mdi mdi-trash-can"></i> 
                                                        </button>
                                                    </td>
                                                </tr>
                                                <!-- End example row -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- <div class="text-right mt-3">
                                        <button class="btn btn-primary">Proceed to Payment</button>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-default">
                                <div class="card-header">
                                    <h2>Other Information</h2>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <label for="inputName">Pupose for borrow </label>
                                                <input type="text" class="form-control" id="inputName" placeholder="Pupose for borrow">
                                            </div>
                                           
                                        </div>                                        
                                        <button type="submit" class="btn btn-success">Complete Purchase</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Payment Information -->

                </div>
            </div>

            <?php include('assets/partials/footer.php') ?>
        </div>
    </div>

    <?php include('assets/partials/footerfile.php') ?>

    <script>
        $(document).ready(function() {
            // Increase quantity
            $('.increase-qty').on('click', function() {
                var $qty = $(this).siblings('.quantity');
                var currentQty = parseInt($qty.text());
                $qty.text(currentQty + 1);
                updateTotal($(this).closest('tr'));
            });

            // Decrease quantity
            $('.decrease-qty').on('click', function() {
                var $qty = $(this).siblings('.quantity');
                var currentQty = parseInt($qty.text());
                if (currentQty > 1) {
                    $qty.text(currentQty - 1);
                    updateTotal($(this).closest('tr'));
                }
            });

            // Remove item
            $('.remove-item').on('click', function() {
                $(this).closest('tr').remove();
            });

            // Update total price
            // function updateTotal($row) {
            //     var price = parseFloat($row.find('td:nth-child(4)').text().replace('$', ''));
            //     var qty = parseInt($row.find('.quantity').text());
            //     var total = price * qty;
            //     $row.find('td:nth-child(5)').text('$' + total.toFixed(2));
            // }
        });
    </script>
</body>

</html>
