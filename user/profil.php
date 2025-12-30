<?php
// Redirect ke profil_view.php
header('Location: ' . str_replace('profil.php', 'profil_view.php', $_SERVER['REQUEST_URI']));
exit;
?>
