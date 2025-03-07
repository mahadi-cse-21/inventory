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

    <?php
 include 'dbconnect.php';
 
?>
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
                                                <th>SI No.</th>
                                                <th>Product Name</th>
                                                <th>ID</th>
                                                <th>Qty</th>

                                                <th>In Stock</th>

                                            </tr>
                                        </thead>
                                        <tbody class="product-list">

                                        <?php
                                         $sql = "SELECT * FROM items";
                                         $result = mysqli_query($conn,$sql);
                                         if(mysqli_num_rows($result)>0) {
                                            $count =0;
                                            while($row = mysqli_fetch_assoc($result)) {
                                                $count++;
                                            
                                         
                                          ?>

                                            <tr>
                                                <td class="py-0">
                                                    <?php  echo $count?>
                                                </td> 

                                                <td><?php  echo $row["item_name"]?></td>
                                                <td><?php  echo $row["item_id"]?></td>
                                                <td><?php  echo $row["stock_quantity"]?></td>
                                                <td><?php  echo $row["item_code"]?></td>                                                                                            
                                            </tr>

                                        <?php } }?>

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