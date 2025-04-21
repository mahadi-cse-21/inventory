<!-- Footer -->
<!-- </div> -->
<?php if (!isset($hideSidebar) || !$hideSidebar): ?>
<footer class="app-footer" style="text-align: center;">
    <div>© <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</div>
    <div>Version <?php echo APP_VERSION; ?></div>
</footer>
<?php endif; ?>
</div> <!-- End of Content Container -->
</div> <!-- End of Main Container -->
</div> <!-- End of Container -->

<!-- External JavaScript -->
<script src="<?php echo ASSETS_URL; ?>/js/inventory-script.js"></script>
<?php if (isset($extraJS)): ?>
    <?php foreach ($extraJS as $js): ?>
        <script src="<?php echo ASSETS_URL . '/js/' . $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineJS)): ?>
    <script>
        <?php echo $inlineJS; ?>
    </script>
<?php endif; ?>
</body>

</html>