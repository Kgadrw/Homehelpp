<?php
/**
 * Hiring (client) register route — /register
 * Host page that opens the create hiring account modal.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . clean_redirect($_SESSION['user_role'] ?? 'client'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php include __DIR__ . '/../includes/seo-meta.php'; ?>
<title>Create Hiring Account | Home Help Rwanda</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
</head>
<body>
<?php include __DIR__ . '/../includes/auth-glass-shell.php'; ?>
<div class="reg-host">
  <div class="reg-host-card">
    <img src="../Image/homehelp-logo.png" alt="Home Help"/>
    <h1>Create hiring account</h1>
    <p>Opening sign-up…</p>
  </div>
</div>
<?php include __DIR__ . '/../includes/client-login-modal.php'; ?>
<?php include __DIR__ . '/../includes/register-modal.php'; ?>
<script>
(function(){
  function boot(){ if (window.HomeHelpRegister) HomeHelpRegister.open(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
</script>
</body>
</html>
