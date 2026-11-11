<?php
/**
 * Shared liquid-glass auth background (GradientASCII + veil).
 * Include once in <head> or early <body> on auth host/form pages.
 */
if (!empty($GLOBALS['__hh_auth_glass_shell'])) {
  return;
}
$GLOBALS['__hh_auth_glass_shell'] = true;
$base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
?>
<link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/gradient-ascii.css"/>
<link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/auth-glass.css"/>
<link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/css/glass-select.css"/>
<div class="page-ascii-bg" id="heroAscii" aria-hidden="true"></div>
<div class="page-ascii-veil" aria-hidden="true"></div>
<script src="<?= htmlspecialchars($base) ?>/assets/js/gradient-ascii.js" defer></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/glass-select.js" defer></script>
<script defer>
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
</script>
