<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/simplebar/simplebar.min.js"></script>
<script src="https://unpkg.com/hotkeys-js/dist/hotkeys.min.js"></script>



<script src="assets/plugins/apexcharts/apexcharts.js"></script>



<script src="assets/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>



<script src="assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
<script src="assets/plugins/jvectormap/jquery-jvectormap-world-mill.js"></script>
<script src="assets/plugins/jvectormap/jquery-jvectormap-us-aea.js"></script>



<script src="assets/plugins/daterangepicker/moment.min.js"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script>
    jQuery(document).ready(function () {
        jQuery('input[name="dateRange"]').daterangepicker({
            autoUpdateInput: false,
            singleDatePicker: true,
            locale: {
                cancelLabel: 'Clear'
            }
        });
        jQuery('input[name="dateRange"]').on('apply.daterangepicker', function (ev, picker) {
            jQuery(this).val(picker.startDate.format('MM/DD/YYYY'));
        });
        jQuery('input[name="dateRange"]').on('cancel.daterangepicker', function (ev, picker) {
            jQuery(this).val('');
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#productsTable1').DataTable({
            "dom": '<"top"f>rt<"bottom"lp><"clear">'
        });
    });
</script>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>



<script src="assets/plugins/toaster/toastr.min.js"></script>



<script src="assets/js/mono.js"></script>
<script src="assets/js/chart.js"></script>
<script src="assets/js/map.js"></script>
<script src="assets/js/custom.js"></script>