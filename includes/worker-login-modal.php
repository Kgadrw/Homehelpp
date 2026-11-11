<?php
/**
 * In-page Job Seeker login modal.
 * Open with: data-open-worker-login, or window.HomeHelpWorkerLogin.open()
 */
if (!defined('BASE_URL')) {
    return;
}
if (!empty($GLOBALS['__hh_worker_login_modal_included'])) {
    return;
}
$GLOBALS['__hh_worker_login_modal_included'] = true;

$hh_wl_csrf   = function_exists('generateCSRFToken') ? generateCSRFToken() : '';
$hh_wl_logo   = rtrim(BASE_URL, '/') . '/Image/homehelp-logo.png';
$hh_wl_api    = rtrim(BASE_URL, '/') . '/worker-login-api';
$hh_wl_reg    = rtrim(BASE_URL, '/') . '/worker-register';
$hh_wl_reset  = rtrim(BASE_URL, '/') . '/reset-password';
$hh_wl_google = rtrim(BASE_URL, '/') . '/google-login?role=provider';
$hh_wl_next   = (isset($_GET['next']) && is_string($_GET['next'])) ? $_GET['next'] : '';
?>
<style>
.hh-wl-root{--hh-navy:#fff;--hh-navy-mid:#A5D8FF;--hh-blue:#A5D8FF;--hh-line:rgba(255,255,255,.14);--hh-bg:rgba(255,255,255,.08);--hh-text:#fff;--hh-muted:rgba(255,255,255,.65);--hh-white:rgba(255,255,255,.1);--hh-danger:#FCA5A5;--hh-font:'Plus Jakarta Sans',system-ui,sans-serif}
.hh-wl-root[hidden]{display:none!important}
.hh-wl-root{position:fixed;inset:0;z-index:10002;display:flex;align-items:center;justify-content:center;padding:20px 14px;font-family:var(--hh-font);color-scheme:dark}
.hh-wl-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(18px) saturate(140%);-webkit-backdrop-filter:blur(18px) saturate(140%);border:0;cursor:pointer}
.hh-wl-dialog{
  position:relative;width:100%;max-width:400px;max-height:min(92vh,680px);overflow:auto;
  background:linear-gradient(160deg,rgba(255,255,255,.18),rgba(255,255,255,.07));
  backdrop-filter:blur(36px) saturate(170%);-webkit-backdrop-filter:blur(36px) saturate(170%);
  border:none;border-radius:28px;box-shadow:inset 0 1px 0 rgba(255,255,255,.22),0 24px 80px rgba(0,0,0,.35);
  padding:32px 28px 26px;animation:hhWlIn .26s cubic-bezier(.22,1,.36,1) both;color:#fff;
}
@keyframes hhWlIn{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:none}}
.hh-wl-close{
  position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:999px;
  border:none;background:rgba(255,255,255,.12);color:rgba(255,255,255,.75);cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
}
.hh-wl-close:hover{color:#fff;background:rgba(255,255,255,.2)}
.hh-wl-close svg{width:16px;height:16px;stroke-width:2.25}
.hh-wl-brand{display:none}
.hh-wl-dialog h2{margin:0 0 8px;text-align:left;font-size:1.45rem;font-weight:800;letter-spacing:-.03em;color:#fff;line-height:1.15}
.hh-wl-sub{margin:0 0 20px;text-align:left;font-size:.9rem;color:rgba(255,255,255,.62);line-height:1.5;font-weight:500}
.hh-wl-sub a{color:var(--hh-blue);font-weight:600;text-decoration:none}
.hh-wl-sub a:hover{text-decoration:underline}
.hh-wl-alert{
  display:none;align-items:flex-start;gap:8px;padding:10px 12px;margin-bottom:14px;border-radius:12px;
  background:rgba(254,242,242,.18);border:none;color:#FECACA;font-size:.8125rem;line-height:1.4;
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
}
.hh-wl-alert.is-on{display:flex}
.hh-wl-google{
  width:100%;display:flex;align-items:center;justify-content:center;gap:10px;
  padding:11px 14px;margin-bottom:14px;border-radius:999px;border:none;
  background:rgba(255,255,255,.12);color:#fff;font:inherit;font-size:.875rem;font-weight:600;
  text-decoration:none;cursor:pointer;transition:background .15s;
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:none;
}
.hh-wl-google:hover{background:rgba(255,255,255,.2)}
.hh-wl-google svg{width:18px;height:18px}
.hh-wl-div{display:flex;align-items:center;gap:12px;margin:0 0 14px;color:var(--hh-muted);font-size:.75rem;font-weight:500}
.hh-wl-div::before,.hh-wl-div::after{content:'';flex:1;height:1px;background:var(--hh-line)}
.hh-wl-field{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
.hh-wl-field label{font-size:.78rem;font-weight:650;color:#fff;letter-spacing:-.01em}
.hh-wl-field .wrap{position:relative}
.hh-wl-field input{
  width:100%;padding:13px 15px;border:none;border-radius:16px;color-scheme:dark;
  font:inherit;font-size:.92rem;font-weight:500;color:#fff;outline:none;
  background:linear-gradient(135deg,rgba(255,255,255,.16),rgba(255,255,255,.07));
  backdrop-filter:blur(14px) saturate(150%);-webkit-backdrop-filter:blur(14px) saturate(150%);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.18);transition:box-shadow .15s,background .15s
}
.hh-wl-field input:focus{background:rgba(255,255,255,.2);box-shadow:inset 0 1px 0 rgba(255,255,255,.24),0 0 0 3px rgba(165,216,255,.22)}
.hh-wl-field input.is-bad{box-shadow:0 0 0 2px rgba(252,165,165,.5)}
.hh-wl-field input::placeholder{color:rgba(255,255,255,.38)}
.hh-wl-eye{
  position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;
  color:rgba(255,255,255,.55);cursor:pointer;padding:6px;display:flex;border-radius:6px
}
.hh-wl-eye:hover{color:#fff}
.hh-wl-eye svg{width:16px;height:16px;stroke-width:2}
.hh-wl-meta{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:2px 0 18px;font-size:.78rem}
.hh-wl-meta label{display:inline-flex;align-items:center;gap:9px;color:#fff;cursor:pointer;font-weight:550}
.hh-wl-meta input[type=checkbox]{
  appearance:none;-webkit-appearance:none;width:18px;height:18px;margin:0;flex-shrink:0;cursor:pointer;
  border-radius:6px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.12);position:relative;
}
.hh-wl-meta input[type=checkbox]:checked{background:rgba(165,216,255,.35);border-color:rgba(165,216,255,.65)}
.hh-wl-meta input[type=checkbox]:checked::after{
  content:'';position:absolute;left:5px;top:1px;width:5px;height:9px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg);
}
.hh-wl-meta a{color:#A5D8FF;font-weight:650;text-decoration:none}
.hh-wl-meta a:hover{text-decoration:underline}
.hh-wl-submit{
  width:100%;padding:14px 16px;border:0;border-radius:999px;
  background:rgba(255,255,255,.18);color:#fff;
  font:inherit;font-size:.95rem;font-weight:750;cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-shadow:inset 0 1px 0 rgba(255,255,255,.2);transition:background .15s
}
.hh-wl-submit:hover{background:rgba(255,255,255,.26)}
.hh-wl-submit:disabled{opacity:.65;pointer-events:none}
.hh-wl-submit svg{width:16px;height:16px;stroke-width:2.25}
@media(max-width:480px){
  .hh-wl-root{padding:0;align-items:stretch}
  .hh-wl-dialog{max-width:none;max-height:none;min-height:100%;border-radius:0;padding:24px 18px 28px}
}
</style>

<div class="hh-wl-root" id="hhWorkerLoginModal" hidden>
  <button type="button" class="hh-wl-backdrop" data-hh-wl-close aria-label="Close"></button>
  <div class="hh-wl-dialog" role="dialog" aria-modal="true" aria-labelledby="hhWlTitle" tabindex="-1">
    <button type="button" class="hh-wl-close" data-hh-wl-close aria-label="Close">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
    <a href="<?= htmlspecialchars(BASE_URL) ?>/" class="hh-wl-brand" aria-label="Home Help">
      <img src="<?= htmlspecialchars($hh_wl_logo) ?>" alt="Home Help"/>
    </a>
    <h2 id="hhWlTitle">Job seeker sign in</h2>
    <p class="hh-wl-sub">Open your worker dashboard. New here? <a href="<?= htmlspecialchars($hh_wl_reg) ?>" data-open-worker-register data-hh-wl-close>Create a free account</a></p>

    <div class="hh-wl-alert" id="hhWlAlert" role="alert"><span id="hhWlAlertText"></span></div>

    <a class="hh-wl-google" href="<?= htmlspecialchars($hh_wl_google) ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
      </svg>
      Continue with Google
    </a>
    <div class="hh-wl-div">or with email</div>

    <form id="hhWlForm" method="post" action="<?= htmlspecialchars($hh_wl_api) ?>" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($hh_wl_csrf) ?>"/>
      <?php if ($hh_wl_next !== ''): ?><input type="hidden" name="next" value="<?= htmlspecialchars($hh_wl_next) ?>"/><?php endif; ?>
      <div class="hh-wl-field">
        <label for="hhWlEmail">Email</label>
        <input type="email" id="hhWlEmail" name="email" placeholder="you@example.com" autocomplete="email" required/>
      </div>
      <div class="hh-wl-field">
        <label for="hhWlPass">Password</label>
        <div class="wrap">
          <input type="password" id="hhWlPass" name="password" placeholder="Your password" autocomplete="current-password" required style="padding-right:40px"/>
          <button type="button" class="hh-wl-eye" id="hhWlEye" aria-label="Show password">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <div class="hh-wl-meta">
        <label><input type="checkbox" name="remember" value="1"/> Remember me</label>
        <a href="<?= htmlspecialchars($hh_wl_reset) ?>">Forgot password?</a>
      </div>
      <button type="submit" class="hh-wl-submit" id="hhWlSubmit">
        <span>Sign in</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </button>
    </form>
  </div>
</div>

<script>
(function(){
  if (window.HomeHelpWorkerLogin) return;
  var root = document.getElementById('hhWorkerLoginModal');
  if (!root) return;
  var form = document.getElementById('hhWlForm');
  var alertBox = document.getElementById('hhWlAlert');
  var alertText = document.getElementById('hhWlAlertText');
  var submitBtn = document.getElementById('hhWlSubmit');
  var api = <?= json_encode($hh_wl_api) ?>;
  var lastFocus = null;

  function showError(msg){
    alertText.textContent = msg || 'Something went wrong.';
    alertBox.classList.add('is-on');
  }
  function clearError(){
    alertBox.classList.remove('is-on');
    alertText.textContent = '';
    form.querySelectorAll('input.is-bad').forEach(function(el){ el.classList.remove('is-bad'); });
  }
  function open(){
    if (window.HomeHelpWorkerRegister) HomeHelpWorkerRegister.close();
    if (window.HomeHelpLogin) HomeHelpLogin.close();
    if (window.HomeHelpRegister) HomeHelpRegister.close();
    if (window.HomeHelpBook) HomeHelpBook.close();
    lastFocus = document.activeElement;
    root.hidden = false;
    document.body.style.overflow = 'hidden';
    clearError();
    var first = document.getElementById('hhWlEmail');
    if (first) setTimeout(function(){ first.focus(); }, 40);
  }
  function close(){
    root.hidden = true;
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  window.HomeHelpWorkerLogin = { open: open, close: close };

  root.querySelectorAll('[data-hh-wl-close]').forEach(function(el){
    el.addEventListener('click', close);
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && !root.hidden) close();
  });

  var eye = document.getElementById('hhWlEye');
  var pw = document.getElementById('hhWlPass');
  if (eye && pw) {
    eye.addEventListener('click', function(){
      var show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  }

  document.addEventListener('click', function(e){
    var a = e.target.closest('a[data-open-worker-login], a[href="/worker-login"], a[href$="/worker-login"]');
    if (!a) return;
    var href = (a.getAttribute('href') || '').split('?')[0].split('#')[0];
    if (!a.hasAttribute('data-open-worker-login') && !/\/worker-login\/?$/.test(href)) return;
    e.preventDefault();
    open();
    var mob = document.getElementById('mobileMenu');
    var ham = document.getElementById('hamburger');
    if (mob) mob.classList.remove('open');
    if (ham) ham.classList.remove('open');
  });

  try {
    var q = new URLSearchParams(window.location.search);
    if (q.get('worker-login') === '1' || window.location.hash === '#worker-login') open();
  } catch (err) {}

  form.addEventListener('submit', function(e){
    e.preventDefault();
    clearError();
    var email = document.getElementById('hhWlEmail');
    var pass = document.getElementById('hhWlPass');
    var bad = false;
    if (!email.value.trim() || !/\S+@\S+\.\S+/.test(email.value)) { email.classList.add('is-bad'); bad = true; }
    if (!pass.value) { pass.classList.add('is-bad'); bad = true; }
    if (bad) { showError('Please check your email and password.'); return; }

    var fd = new FormData(form);
    submitBtn.disabled = true;
    submitBtn.querySelector('span').textContent = 'Signing in…';

    fetch(api, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function(r){ return r.json().then(function(j){ return { j: j }; }); })
      .then(function(res){
        if (res.j && res.j.ok) {
          window.location.href = res.j.redirect || '/provider-dashboard';
          return;
        }
        showError((res.j && res.j.error) || 'Could not sign in.');
        submitBtn.disabled = false;
        submitBtn.querySelector('span').textContent = 'Sign in';
      })
      .catch(function(){
        showError('Network error. Please try again.');
        submitBtn.disabled = false;
        submitBtn.querySelector('span').textContent = 'Sign in';
      });
  });
})();
</script>
