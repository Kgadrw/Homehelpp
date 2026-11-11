<?php
/**
 * Job seeker register route — kept at /worker-register for deep links.
 * Renders a glass host page and opens the in-page worker register modal.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . '/apply');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php include __DIR__ . '/../includes/seo-meta.php'; ?>
<title>Join as a Job Seeker | Home Help Rwanda</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
</head>
<body>
<?php include __DIR__ . '/../includes/auth-glass-shell.php'; ?>
<div class="login-host">
  <div class="login-host-card">
    <img src="../Image/homehelp-logo.png" alt="Home Help"/>
    <h1>Create job seeker account</h1>
    <p>Join free, then apply for domestic work across Rwanda.</p>
    <button type="button" class="btn" id="openWorkerRegBtn">Create account</button>
  </div>
</div>
<?php include __DIR__ . '/../includes/worker-login-modal.php'; ?>
<?php include __DIR__ . '/../includes/worker-register-modal.php'; ?>
<script>
(function(){
  function openReg(){
    if (window.HomeHelpWorkerRegister) HomeHelpWorkerRegister.open();
  }
  var btn = document.getElementById('openWorkerRegBtn');
  if (btn) btn.addEventListener('click', openReg);
  openReg();
})();
</script>
</body>
</html>
