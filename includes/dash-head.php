<?php
/**
 * Shared dashboard <head> assets.
 * Expects: $page_title (string)
 */
$page_title = $page_title ?? 'Dashboard';
$assets_base = rtrim(BASE_URL, '/') . '/assets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($page_title) ?> | Home Help</title>
<?php
$seo_robots = 'noindex, nofollow';
include __DIR__ . '/seo-meta.php';
?>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="<?= $assets_base ?>/css/gradient-ascii.css"/>
<link rel="stylesheet" href="<?= $assets_base ?>/css/dash.css"/>
<link rel="stylesheet" href="<?= $assets_base ?>/css/glass-select.css"/>
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<script src="<?= $assets_base ?>/js/gradient-ascii.js" defer></script>
<script src="<?= $assets_base ?>/js/glass-select.js" defer></script>
<script defer>
(function(){
  function mount(){
    var el=document.getElementById("heroAscii");
    if(!el||!window.GradientASCII||el.dataset.mounted) return;
    el.dataset.mounted="1";
    GradientASCII.mount(el,{
      colors:["#1e3fd8","#000000","#a5d8ff","#38bdf8"],
      shape:"horizon",density:0,glyphOpacity:0,glow:0.2,depthOfField:0,
      flicker:0.15,softness:0.5,scale:1,flow:0.45,speed:0.85,gradientOpacity:0.45,grain:0.05
    });
  }
  if(document.readyState==="loading") document.addEventListener("DOMContentLoaded",mount);
  else mount();
  window.addEventListener("load",mount);
})();
</script>
