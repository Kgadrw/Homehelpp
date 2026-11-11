<?php
/**
 * Job seeker login route — kept at /worker-login for Google OAuth and deep links.
 * Renders a glass host page and opens the in-page worker login modal.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . clean_redirect($_SESSION['user_role'] ?? 'provider'));
}

$flash = function_exists('flashGet') ? flashGet() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php include __DIR__ . '/../includes/seo-meta.php'; ?>
<title>Job Seeker Login | Home Help Rwanda</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
</head>
<body>
<?php include __DIR__ . '/../includes/auth-glass-shell.php'; ?>
<div class="login-host">
  <div class="login-host-card">
    <img src="../Image/homehelp-logo.png" alt="Home Help"/>
    <h1>Job seeker sign in</h1>
    <p>Continue to your worker dashboard, or create a free job seeker account.</p>
    <?php if (!empty($flash['message'])): ?>
      <div class="login-host-flash <?= e($flash['type'] ?? 'error') ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <button type="button" class="btn" id="openWorkerLoginBtn">Sign in</button>
  </div>
</div>
<?php include __DIR__ . '/../includes/worker-login-modal.php'; ?>
<?php include __DIR__ . '/../includes/worker-register-modal.php'; ?>
<script>
(function(){
  function openLogin(){
    if (window.HomeHelpWorkerLogin) HomeHelpWorkerLogin.open();
  }
  var btn = document.getElementById('openWorkerLoginBtn');
  if (btn) btn.addEventListener('click', openLogin);
  openLogin();
})();
</script>
</body>
</html>
