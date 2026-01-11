<?php
// templates/layout-admin.php
// Admin layout wrapper: includes header, then echoes $page_content, and footer.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering to wrap with body tag
ob_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? e($title) : "Admin Dashboard" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_path('/assets/styles.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/dashboard.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/profile.css') ?>">
  <script defer src="<?= base_path('/assets/script.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="admin-panel">

<?php require __DIR__ . '/admin-header.php'; ?>

<div class="container-fluid" style="padding:20px;">
  <div class="content-wrapper">
    <?php
      echo $page_content ?? '';
    ?>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>

</body>
</html>
