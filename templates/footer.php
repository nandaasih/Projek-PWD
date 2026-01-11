<?php
// Detect admin pages to close additional wrappers opened in header.php
$current_path_f = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$is_admin_page_f = (bool) preg_match('#/admin/#', $current_path_f);
?>

</main>

<?php if ($is_admin_page_f): ?>
  </div>
  </div>
<?php endif; ?>

<footer class="app-footer">
  <div class="footer-content">
    <p>&copy; <?= date('Y') ?> Reservasi Ruangan. All rights reserved.</p>
  </div>
</footer>

</body>
</html>
