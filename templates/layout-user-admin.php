<?php
// templates/layout-user-admin.php
// User layout wrapper dengan admin style: header, content, dan footer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/helpers.php';

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? e($title) : "Dashboard User" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_path('/assets/styles.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/dashboard.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/profile.css') ?>">
  <script defer src="<?= base_path('/assets/script.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="user-dashboard" style="display: flex; flex-direction: column; min-height: 100vh;">

<?php require __DIR__ . '/user-header.php'; ?>

<div class="container-fluid" style="padding:20px; flex: 1;">
  <div class="content-wrapper">
    <?php
      echo $page_content ?? '';
    ?>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>

</body>
</html>

