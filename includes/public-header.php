<?php
/**
 * Shared public site header — same markup/behavior as the homepage.
 * Optional: $nav_active = home|find-worker|career|services|how-it-works|stories|contact|faq
 */
$nav_active = $nav_active ?? '';
$base = rtrim(BASE_URL, '/');
$logo = $base . '/Image/icon/homehelp_logo_transparent_dark.svg';

$is = function (string $key) use ($nav_active): string {
  return $nav_active === $key ? ' active' : '';
};

if (empty($GLOBALS['__hh_public_glass_shell'])) {
  $GLOBALS['__hh_public_glass_shell'] = true;
  echo '<link rel="stylesheet" href="' . htmlspecialchars($base) . '/assets/css/gradient-ascii.css"/>';
  echo '<link rel="stylesheet" href="' . htmlspecialchars($base) . '/assets/css/site-nav.css"/>';
  echo '<link rel="stylesheet" href="' . htmlspecialchars($base) . '/assets/css/glass-select.css"/>';
  echo '<div class="page-ascii-bg" id="heroAscii" aria-hidden="true"></div>';
  echo '<div class="page-ascii-veil" aria-hidden="true"></div>';
  echo '<script src="' . htmlspecialchars($base) . '/assets/js/gradient-ascii.js" defer></script>';
  echo '<script defer>
(function(){
  function mount(){
    var el=document.getElementById("heroAscii");
    if(!el||!window.GradientASCII||el.dataset.mounted) return;
    el.dataset.mounted="1";
    GradientASCII.mount(el,{
      colors:["#1e3fd8","#000000","#a5d8ff","#38bdf8"],
      shape:"horizon",density:0,glyphOpacity:0,glow:0.2,depthOfField:0,
      flicker:0.2,softness:0.5,scale:1,flow:0.5,speed:1,gradientOpacity:0.9,grain:0.08
    });
  }
  if(document.readyState==="loading") document.addEventListener("DOMContentLoaded",mount);
  else mount();
  window.addEventListener("load",mount);
})();
</script>';
} else {
  echo '<link rel="stylesheet" href="' . htmlspecialchars($base) . '/assets/css/site-nav.css"/>';
  echo '<link rel="stylesheet" href="' . htmlspecialchars($base) . '/assets/css/glass-select.css"/>';
}
?>
<nav class="navbar" id="navbar">
  <div class="container">
    <div class="navbar-inner">
      <a href="<?= $base ?>/" class="nav-logo">
        <img src="<?= $logo ?>" alt="Home Help Logo"/>
        <span class="nav-logo-text">HomeHelp</span>
      </a>
      <ul class="nav-links">
        <li><a href="<?= $base ?>/find-worker" class="<?= trim($is('find-worker')) ?>">Find Workers</a></li>
        <li><a href="<?= $base ?>/career" class="<?= trim($is('career')) ?>">Career</a></li>
        <li><a href="<?= $base ?>/#home-services" class="<?= trim($is('services')) ?>">Services</a></li>
        <li><a href="<?= $base ?>/#how-it-works" class="<?= trim($is('how-it-works')) ?>">How It Works</a></li>
        <li><a href="<?= $base ?>/#testimonials" class="<?= trim($is('stories')) ?>">Stories</a></li>
        <li><a href="<?= $base ?>/#contact" class="<?= trim($is('contact')) ?>">Contact</a></li>
      </ul>
      <div class="nav-cta">
        <a href="<?= $base ?>/worker-login" class="nav-login" data-open-worker-login>Login</a>
        <a href="<?= $base ?>/worker-register" class="nav-register" data-open-worker-register>Register</a>
        <a href="<?= $base ?>/login" class="btn-find-worker" data-open-login>Hire Now</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Menu" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <a href="<?= $base ?>/find-worker" class="mob-link<?= $is('find-worker') ?>">Find Workers</a>
  <a href="<?= $base ?>/career" class="mob-link<?= $is('career') ?>">Career</a>
  <a href="<?= $base ?>/#home-services" class="mob-link<?= $is('services') ?>">Services</a>
  <a href="<?= $base ?>/#how-it-works" class="mob-link<?= $is('how-it-works') ?>">How It Works</a>
  <a href="<?= $base ?>/#testimonials" class="mob-link<?= $is('stories') ?>">Stories</a>
  <a href="<?= $base ?>/#contact" class="mob-link<?= $is('contact') ?>">Contact</a>
  <a href="<?= $base ?>/worker-login" class="mob-link" data-open-worker-login>Login</a>
  <a href="<?= $base ?>/worker-register" class="mob-link" data-open-worker-register>Register</a>
  <div class="mobile-menu-footer">
    <a href="<?= $base ?>/login" class="btn-find-worker" data-open-login>Hire Now</a>
  </div>
</div>
<script src="<?= $base ?>/assets/js/site-nav.js" defer></script>
