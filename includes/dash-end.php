    </div><!-- .content -->
  </div><!-- .main -->
</div><!-- .dash-wrap -->

<div class="modal-overlay" id="dashLogoutModal" role="dialog" aria-modal="true" aria-labelledby="dashLogoutTitle">
  <div class="modal dash-logout-modal">
    <h3 id="dashLogoutTitle">Sign out?</h3>
    <p>You will be signed out of your Home Help account on this device.</p>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline" id="dashLogoutCancel">Cancel</button>
      <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/logout') ?>" class="btn btn-danger" id="dashLogoutConfirm">Sign Out</a>
    </div>
  </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
  document.body.style.overflow = document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.body.style.overflow = '';
}
window.addEventListener('resize', function(){ if (window.innerWidth > 768) closeSidebar(); });

(function(){
  var modal = document.getElementById('dashLogoutModal');
  var openBtn = document.getElementById('dashLogoutBtn');
  var cancelBtn = document.getElementById('dashLogoutCancel');
  if (!modal || !openBtn) return;
  function openLogout(e){
    if (e) e.preventDefault();
    modal.classList.add('open');
  }
  function closeLogout(){
    modal.classList.remove('open');
  }
  openBtn.addEventListener('click', openLogout);
  if (cancelBtn) cancelBtn.addEventListener('click', closeLogout);
  modal.addEventListener('click', function(e){
    if (e.target === modal) closeLogout();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && modal.classList.contains('open')) closeLogout();
  });
})();

if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
