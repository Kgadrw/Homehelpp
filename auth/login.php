<?php
/**
 * Hiring (client) login route — /login
 * Host page for Google OAuth redirects; opens the hiring login modal.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . clean_redirect($_SESSION['user_role'] ?? 'client'));
}

$flash = function_exists('flashGet') ? flashGet() : null;
$open_register = (($_GET['register'] ?? '') === '1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php include __DIR__ . '/../includes/seo-meta.php'; ?>
<title>Hire Login | Home Help Rwanda</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
</head>
<body>
<?php include __DIR__ . '/../includes/auth-glass-shell.php'; ?>
<div class="login-host">
  <div class="login-host-card">
    <img src="../Image/homehelp-logo.png" alt="Home Help"/>
    <h1>Hire with Home Help</h1>
    <p>Opening your hiring <?= $open_register ? 'sign-up' : 'sign-in' ?>…</p>
    <?php if (!empty($flash['message'])): ?>
      <div class="login-host-flash <?= e($flash['type'] ?? 'error') ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <p class="login-host-note">Looking for work? <a href="<?= BASE_URL ?>/worker-login">Job Seeker Login</a></p>
  </div>
</div>
<?php include __DIR__ . '/../includes/client-login-modal.php'; ?>
<?php include __DIR__ . '/../includes/register-modal.php'; ?>
<script>
(function(){
  var openRegisterFirst = <?= $open_register ? 'true' : 'false' ?>;
  function boot(){
    if (openRegisterFirst) {
      if (window.HomeHelpRegister) HomeHelpRegister.open();
    } else if (window.HomeHelpLogin) {
      HomeHelpLogin.open();
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
</script>
</body>
</html>
