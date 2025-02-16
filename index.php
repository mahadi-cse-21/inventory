<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Mono - Responsive Admin & Dashboard Template</title>

    <?php include('assets/partials/headerfile.php') ?>
</head>


<body class="navbar-fixed sidebar-fixed" id="body">
    <!-- <script>
        NProgress.configure({ showSpinner: false });
        NProgress.start();
    </script> -->


    <div id="toaster"></div>


    <!-- ====================================
    ——— WRAPPER
    ===================================== -->
    <div class="wrapper">


        <!-- ====================================
          ——— LEFT SIDEBAR WITH OUT FOOTER
        ===================================== -->
        <?php include('assets/partials/sidebar.php') ?>


        <!-- ====================================
      ——— PAGE WRAPPER
      ===================================== -->
        <div class="page-wrapper">

            <!-- Header -->
            <?php include('assets/partials/navbar.php') ?>

            <!-- ====================================
        ——— CONTENT WRAPPER
        ===================================== -->
            <div class="content-wrapper">
                <div class="content">

                    <div class="row">
                        <div class="col-12">
                            <div class="card card-default">
                                <div class="card-header">
                                    <h2>Products Inventory</h2>
                                   
                                </div>
                                <div class="card-body">
                                    <table id="productsTable1" class="table table-hover table-product" style="width:100%">
                                        <thead> 
                                            <tr>
                                                <th></th>
                                                <th>Product Name</th>
                                                <th>ID</th>
                                                <th>Qty</th>

                                                <th>In Stock</th>

                                            </tr>
                                        </thead>
                                        <tbody class="product-list">

                                        <?php for($i=0; $i<10; $i++){ ?>

                                            <tr>
                                                <td class="py-0">
                                                    <img src="assets/images/products/products-xs-01.jpg"  alt="Product Image">
                                                </td> 

                                                <td>Ledger Nano X</td>
                                                <td>24547</td>
                                                <td>61</td>
                                                <td>46</td>                                                                                            
                                            </tr>

                                        <?php } ?>

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>



                </div>

            </div>

            <!-- Footer -->
            <?php include('assets/partials/footer.php') ?>

        </div>
    </div>







    <!--  -->

    <?php include('assets/partials/footerfile.php') ?>

</body>

</html>