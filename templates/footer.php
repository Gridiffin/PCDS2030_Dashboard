<footer>
    <div class="copyright">&copy; 2025 PCDS2030. All rights reserved.</div>
</footer>

<?php if (isset($notification) && $notification): ?>
<!-- Notification element -->
<div id="notification" class="notification"></div>
<?php endif; ?>

<?php if (isset($scripts) && is_array($scripts)): ?>
    <?php foreach($scripts as $script): ?>
        <?php
        // Determine if script should be loaded as a module
        $isModule = strpos($script, '/core/') !== false || 
                   strpos($script, 'api-client.js') !== false || 
                   strpos($script, 'notifications.js') !== false ||
                   strpos($script, 'login.js') !== false || // Explicitly make login.js a module
                   pathinfo($script, PATHINFO_EXTENSION) === 'mjs';
        ?>
        <script src="<?php echo $script; ?>" <?php echo $isModule ? 'type="module"' : ''; ?>></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($includeMobileJs) && $includeMobileJs): ?>
<!-- Include mobile.js for responsive behavior -->
<script src="js/mobile.js"></script>
<?php endif; ?>
</body>
</html>
