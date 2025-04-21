<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Mono - Responsive Admin & Dashboard Template</title>
    <!-- <link rel="stylesheet" href="assets/css/custom.css"> -->
    <style>
        /* General Reset */


        /* Search Bar */
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .search-bar {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            font-size: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .search-bar:focus {
            border-color: #3498db;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        /* Product Card Styling */
        .product-card {
            max-width: 320px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        /* Product Image */
        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        /* Product Details */
        .product-details {
            padding: 16px;
            text-align: center;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .product-qty {
            font-size: 1rem;
            color: #555;
            margin-bottom: 16px;
        }

        /* Add to Cart Button */
        .add-to-cart-btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background-color: #e67e22;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: auto;
        }

        .add-to-cart-btn:hover {
            background-color: #d35400;
            transform: scale(1.05);
        }

        .add-to-cart-btn:active {
            transform: scale(0.95);
        }

        /* Hidden Cards */
        .hidden {
            display: none;
        }
    </style>

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
                                    <!-- Search Bar -->
                                    <div class="search-container">
                                        <input type="text" id="search-bar" class="search-bar"
                                            placeholder="Search products...">
                                    </div>

                                    <!-- Product Grid -->
                                    <?php
                                    include('db_connection.php');
                                    $query = "SELECT * FROM products WHERE status = 'active'";
                                    $result = $conn->query($query);
                                    ?>

<!-- <div class="row row-cols-2 row-cols-lg-5 g-2 g-lg-3">
    <div class="col">
      <div class="p-3 border bg-light">Row column</div>
    </div>   
  </div> -->
                                    <div class="product-grid" id="product-grid">
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <div class="product-card" data-id="<?php echo $row['product_id']; ?>" data-name="<?php echo $row['product_name']; ?>">
                                                <div class="product-image">
                                                    <img src="assets/images/products/p2.jpg" alt="<?php echo $row['product_name']; ?>">
                                                </div>
                                                <div class="product-details">
                                                    <h3 class="product-name"><?php echo $row['product_name']; ?></h3>
                                                    <p class="product-qty">Stock: <?php echo $row['stock']; ?></p>
                                                    <button class="addToCartBtn add-to-cart-btn">Add to Cart</button>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

<!-- <div class="container">
  <div class="row row-cols-2 row-cols-lg-5 g-2 g-lg-3">
    <div class="col">
      <div class="p-3 border bg-light">Row column</div>
    </div>   
  </div>
</div> -->

                </div>

            </div>

            <!-- Footer -->
            <?php include('assets/partials/footer.php') ?>

        </div>
    </div>

    







    <!--  -->

    <?php include('assets/partials/footerfile.php') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Search Functionality
        const searchBar = document.getElementById('search-bar');
        const productGrid = document.getElementById('product-grid');
        const productCards = Array.from(productGrid.getElementsByClassName('product-card'));

        searchBar.addEventListener('input', (event) => {
            const query = event.target.value.toLowerCase();

            productCards.forEach((card) => {
                const productName = card.getAttribute('data-name').toLowerCase();
                if (productName.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });

        // $(document).ready(function() {
        //     $('.addToCartBtn').on('click', function() {
        //         var productId = $(this).closest('.product-card').data('id');
        //        // alert(productId);
        //         $.ajax({
        //             url: 'update_cart.php',
        //             type: 'POST',
        //             data: { product_id: productId },
        //             success: function(response) {
        //                 $('#totalCartItem').text(response.totalCartItem);
                        
        //                     // let x = document.getElementById('totalCartItem');
        //                     // let totalCartItem = 0;
        //                     // x.innerText = totalCartItem;
        //                     // console.log(x.innerText); // This will log the text content of the element

        //             }
        //         });
        //     });
        // });
    </script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
$(document).ready(function() {
    $('.addToCartBtn').on('click', function() {
        var productId = $(this).closest('.product-card').data('id');
        
        $.ajax({
            url: 'update_cart.php',
            type: 'POST',
            data: { product_id: productId },
            success: function(response) {
                console.log("Response from server: ", response);  // Log the raw response
                try {
                    var data = JSON.parse(response);
                    $('#totalCartItem').text(data.totalCartItem);
                } catch (e) {
                    console.error("Error parsing JSON: ", e);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + error);
            }
        });
    });
});

</script>




</body>

</html>