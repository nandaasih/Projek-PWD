<?php
// templates/layout-user.php
// User layout wrapper: outputs full HTML (no shared header include), sidebar (user), content, and footer.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/helpers.php';

// Ensure sidebar renders user menu
$sidebar_type = $sidebar_type ?? 'user';

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? e($title) : "Dashboard" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_path('/assets/styles.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/dashboard.css') ?>">
  <link rel="stylesheet" href="<?= base_path('/assets/profile.css') ?>">
  <script defer src="<?= base_path('/assets/script.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="user-dashboard">

<div class="main-layout" style="max-width:1400px;margin:20px auto;padding:20px;">
  <div class="content-wrapper" style="flex:1;">
    <?php
      echo $page_content ?? '';
    ?>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>

</body>
</html>
