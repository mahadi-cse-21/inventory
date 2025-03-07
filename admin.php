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
        <table>
            <thead></thead>
            <tbody>
                <tr>
                    
                </tr>
            </tbody>
        </table>


            <!-- Footer -->
            <?php include('assets/partials/footer.php') ?>

        </div>
    </div>







    <!--  -->

    <?php include('assets/partials/footerfile.php') ?>

</body>

</html>