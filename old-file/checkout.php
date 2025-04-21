<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include('db_connection.php');

$userId = $_SESSION['user_id'];
function userid($userId):int{
    global $conn;
    $sql = "SELECT id FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_assoc()['id'];
}

$u = userid($userId);
$query = "SELECT ci.product_id, p.product_name, ci.quantity 
          FROM cart_items ci
          JOIN products p ON ci.product_id = p.product_id
          JOIN carts c ON ci.cart_id = c.cart_id
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $u);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Checkout - Mono</title>

    <?php include('assets/partials/headerfile.php') ?>
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .checkout-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .checkout-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .checkout-table th, .checkout-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .checkout-table th {
            background-color: #f1f1f1;
        }
        .checkout-table td {
            vertical-align: middle;
        }
        .checkout-table .quantity-controls {
            display: flex;
            align-items: center;
        }
        .checkout-table .quantity-controls button {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            margin: 0 5px;
        }
        .checkout-table .quantity-controls button:hover {
            background-color: #2980b9;
        }
        .checkout-table .remove-item {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .checkout-table .remove-item:hover {
            background-color: #c0392b;
        }
        .checkout-form {
            margin-top: 20px;
        }
        .checkout-form .form-group {
            margin-bottom: 15px;
        }
        .checkout-form .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .checkout-form .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .checkout-form .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .checkout-form .form-group button:hover {
            background-color: #27ae60;
        }
    </style>
</head>

<body class="navbar-fixed sidebar-fixed" id="body">
    <div id="toaster"></div>

    <div class="wrapper">
        <?php include('assets/partials/sidebar.php') ?>

        <div class="page-wrapper">
            <?php include('assets/partials/navbar.php') ?>

            <div class="content-wrapper">
                <div class="content">
                    <div class="checkout-container">
                        <div class="checkout-header">
                            <h2>Checkout</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="checkout-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product Name</th>
                                        <th>Qty</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item) { ?>
                                    <tr>
                                        <td><?php echo $item['product_id']; ?></td>
                                        <td><?php echo $item['product_name']; ?></td>
                                        <td>
                                            <div class="quantity-controls">
                                                <button class="decrease-qty">-</button>
                                                <span class="quantity"><?php echo $item['quantity']; ?></span>
                                                <button class="increase-qty">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="remove-item">
                                                <i class="mdi mdi-trash-can"></i> Remove
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="checkout-form">
                            <form action="process_checkout.php" method="POST">
                                <div class="form-group">
                                    <label for="inputPurpose">Purpose for borrow</label>
                                    <input type="text" class="form-control" id="inputPurpose" name="purpose" placeholder="Purpose for borrow" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Complete Purchase</button>
                                </div>
                            </form>
                        </div>
                    </div>
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
            function updateTotal($row) {
                var price = parseFloat($row.find('td:nth-child(4)').text().replace('$', ''));
                var qty = parseInt($row.find('.quantity').text());
                var total = price * qty;
                $row.find('td:nth-child(5)').text('$' + total.toFixed(2));
            }
        });
    </script>
</body>

</html>
