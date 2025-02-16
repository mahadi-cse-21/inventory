<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Products</title>
    <?php include('assets/partials/headerfile.php') ?>
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
</head>
<body> 
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
                    <div class="page-header">
                        <h2>Borrowed Products</h2>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <table id="borrowedProductsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>Borrower</th>
                                        <th>Borrow Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dummy data for frontend design -->
                                    <tr>
                                        <td>1</td>
                                        <td>Product A</td>
                                        <td>John Doe</td>
                                        <td>2023-10-01</td>
                                        <td>
                                            <button class="btn btn-success" data-toggle="modal" data-target="#returnModal" data-id="1">Return</button>
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#emailModal" data-id="1">Email</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Product B</td>
                                        <td>Jane Smith</td>
                                        <td>2023-10-02</td>
                                        <td>
                                            <button class="btn btn-success" data-toggle="modal" data-target="#returnModal" data-id="2">Return</button>
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#emailModal" data-id="2">Email</button>
                                        </td>
                                    </tr>
                                    <!-- ...more dummy data... -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('assets/partials/footer.php'); ?>
        </div>
    </div>

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Return Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="returnForm">
                        <input type="hidden" id="returnProductId" name="product_id">
                        <p>Are you sure you want to return this product?</p>
                        <button type="submit" class="btn btn-success">Yes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Send Email</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="emailForm">
                        <input type="hidden" id="emailProductId" name="product_id">
                        <div class="form-group">
                            <label for="emailSubject">Subject</label>
                            <input type="text" class="form-control" id="emailSubject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="emailMessage">Message</label>
                            <textarea class="form-control" id="emailMessage" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#borrowedProductsTable').DataTable({
                "searching": true,
                "ordering": true
            });
        });

        $('#returnModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var productId = button.data('id');
            var modal = $(this);
            modal.find('#returnProductId').val(productId);
        });

        $('#emailModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var productId = button.data('id');
            var modal = $(this);
            modal.find('#emailProductId').val(productId);
        });

        $('#returnForm').on('submit', function (e) {
            e.preventDefault();
            var productId = $('#returnProductId').val();
            // Add AJAX call to handle product return
        });

        $('#emailForm').on('submit', function (e) {
            e.preventDefault();
            var productId = $('#emailProductId').val();
            var subject = $('#emailSubject').val();
            var message = $('#emailMessage').val();
            // Add AJAX call to handle sending email
        });
    </script>

    <?php include('assets/partials/footerfile.php') ?>
</body>
</html>
