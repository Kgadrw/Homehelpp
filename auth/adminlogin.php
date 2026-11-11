<?php
require_once '../config/db.php';
require_once '../config/config.php';

$error = '';

// /admin-login (or ?next=/admin*) is admin-only — agents cannot use it to reach /agent
$reqPath   = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$nextRaw   = $_GET['next'] ?? $_POST['next'] ?? '';
$nextPath  = (is_string($nextRaw) && $nextRaw !== '') ? (parse_url($nextRaw, PHP_URL_PATH) ?: '') : '';
$adminOnly = str_contains($reqPath, 'admin-login')
    || (is_string($nextPath) && str_starts_with($nextPath, '/admin'));

if (isLoggedIn()) {
    $role = $_SESSION['user_role'] ?? 'admin';
    if ($adminOnly && $role === 'admin') {
        redirect(BASE_URL . '/admin');
    }
    if (!$adminOnly && in_array($role, ['admin', 'agent'], true)) {
        redirect(BASE_URL . clean_redirect($role));
    }
    // Wrong role for this portal — clear and show the form
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();
    $email = sanitize($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (!$email || !$pass) {
        $error = 'Please fill in all fields.';
    } elseif (isLoginBlocked($email, $ip)) {
        $error = 'Too many failed attempts. Please wait 15 minutes.';
    } else {
        if ($adminOnly) {
            $stmt = $conn->prepare("
                SELECT * FROM users
                WHERE email = ? AND role = 'admin' AND status = 'active'
                LIMIT 1
            ");
        } else {
            $stmt = $conn->prepare("
                SELECT * FROM users
                WHERE email = ? AND role IN ('admin','agent') AND status = 'active'
                LIMIT 1
            ");
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            clearLoginAttempts($email);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $next     = $_GET['next'] ?? $_POST['next'] ?? '';
            $nextPath = (is_string($next) && $next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//'))
                ? (parse_url($next, PHP_URL_PATH) ?: '')
                : '';
            if ($user['role'] === 'admin') {
                if (is_string($nextPath) && str_starts_with($nextPath, '/admin')) {
                    redirect(BASE_URL . $next);
                }
                redirect(BASE_URL . '/admin');
            }
            if ($user['role'] === 'agent') {
                if (is_string($nextPath) && str_starts_with($nextPath, '/agent')) {
                    redirect(BASE_URL . $next);
                }
                redirect(BASE_URL . '/agent');
            }
            post_login_redirect($user['role']);
        } else {
            logLoginAttempt($email, $ip);
            $error = $adminOnly
                ? 'Invalid admin email or password. Use the administrator account (admin@homehelp.rw), not a team/agent account.'
                : 'Invalid email or password. This portal is for admin and agents only.';
        }
    }
}

$page_title = $adminOnly ? 'Admin Login | Home Help Rwanda' : 'Team Login | Home Help Rwanda';
$heading    = $adminOnly ? 'Admin login' : 'Team login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php include __DIR__ . '/../includes/seo-meta.php'; ?>
<title><?= e($page_title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/auth-glass-shell.php'; ?>
<div class="auth-wrap">
  <div class="auth-right">
    <div class="auth-card">
      <h1><?= e($heading) ?></h1>

      <?php if ($error): ?>
        <div class="alert" role="alert">
          <i data-lucide="circle-alert"></i>
          <span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>"/>
        <?php if (!empty($_GET['next'])): ?><input type="hidden" name="next" value="<?= e($_GET['next']) ?>"/><?php endif; ?>

        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-wrap">
            <span class="input-icon" aria-hidden="true"><i data-lucide="mail"></i></span>
            <input type="email" id="email" name="email" placeholder="Email"
              value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required/>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <span class="input-icon" aria-hidden="true"><i data-lucide="lock"></i></span>
            <input type="password" id="password" name="password"
              placeholder="Password" autocomplete="current-password" required/>
            <button type="button" class="eye-toggle" id="eyeBtn" aria-label="Show password">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>

        <div class="form-meta">
          <label class="checkbox-label">
            <input type="checkbox" name="remember" value="1"/> Remember me
          </label>
          <a href="<?= BASE_URL ?>/reset-password" class="forgot">Forgot password?</a>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <span>Sign in</span>
          <i data-lucide="arrow-right"></i>
        </button>
      </form>

      <div class="auth-card-foot">
        <a href="<?= BASE_URL ?>" class="back-home-mobile">
          <i data-lucide="arrow-left"></i> Back to home
        </a>
      </div>
    </div>
  </div>
</div>
<script>
lucide.createIcons();
(function(){
  var pw = document.getElementById('password');
  var btn = document.getElementById('eyeBtn');
  btn.addEventListener('click', function(){
    var show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    btn.innerHTML = show ? '<i data-lucide="eye-off"></i>' : '<i data-lucide="eye"></i>';
    lucide.createIcons();
  });
  document.getElementById('loginForm').addEventListener('submit', function(e){
    var em = document.getElementById('email'), ok = true;
    [em, pw].forEach(function(el){ el.classList.remove('error-field'); });
    if (!em.value.trim() || !/\S+@\S+\.\S+/.test(em.value)) { em.classList.add('error-field'); ok = false; }
    if (!pw.value) { pw.classList.add('error-field'); ok = false; }
    if (!ok) { e.preventDefault(); return; }
    var submit = document.getElementById('submitBtn');
    submit.classList.add('loading');
    submit.innerHTML = '<span>Signing in...</span>';
  });
})();
</script>
</body>
</html>
