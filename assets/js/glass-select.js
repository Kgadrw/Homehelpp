(function () {
  var CHEVRON =
    '<svg class="glass-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';

  function closeAll(except) {
    document.querySelectorAll('.glass-select.open').forEach(function (el) {
      if (except && el === except) return;
      el.classList.remove('open');
      var t = el.querySelector('.glass-select-trigger');
      if (t) t.setAttribute('aria-expanded', 'false');
    });
  }

  function selectedLabel(select) {
    var opt = select.options[select.selectedIndex];
    return opt ? String(opt.textContent || '').trim() : '';
  }

  function isPlaceholderOption(opt) {
    if (!opt) return true;
    var v = opt.value;
    return v === '' || opt.disabled;
  }

  function syncTrigger(root, select) {
    var label = root.querySelector('[data-glass-label]');
    if (!label) return;
    var text = selectedLabel(select);
    label.textContent = text || 'Select…';
    var opt = select.options[select.selectedIndex];
    label.classList.toggle('is-placeholder', isPlaceholderOption(opt));
    label.classList.toggle('placeholder', isPlaceholderOption(opt));
    root.querySelectorAll('.glass-select-option').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-value') === select.value);
    });
  }

  function buildMenu(root, select) {
    var menu = document.createElement('div');
    menu.className = 'glass-select-menu';
    menu.setAttribute('role', 'listbox');
    Array.prototype.forEach.call(select.options, function (opt) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'glass-select-option';
      if (isPlaceholderOption(opt)) btn.classList.add('is-placeholder');
      if (opt.selected) btn.classList.add('is-active');
      btn.setAttribute('data-value', opt.value);
      btn.setAttribute('role', 'option');
      btn.textContent = String(opt.textContent || '').trim();
      if (opt.disabled && opt.value !== '') btn.disabled = true;
      btn.addEventListener('click', function () {
        select.value = opt.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        syncTrigger(root, select);
        closeAll();
      });
      menu.appendChild(btn);
    });
    return menu;
  }

  function enhanceSelect(select) {
    if (!select || select.dataset.glassReady === '1') return;
    select.dataset.glassReady = '1';
    select.classList.add('glass-select-native');

    var root = document.createElement('div');
    root.className = 'glass-select';
    if (select.classList.contains('filter-select') || select.classList.contains('mini-select')) {
      root.classList.add('glass-select--pill');
    }
    if (select.classList.contains('mini-select-sm') || select.dataset.glass === 'sm') {
      root.classList.add('glass-select--sm');
    }

    var parent = select.parentNode;
    parent.insertBefore(root, select);
    root.appendChild(select);

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'glass-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    if (select.id) trigger.setAttribute('aria-labelledby', select.id);
    if (select.disabled) trigger.disabled = true;

    var label = document.createElement('span');
    label.className = 'glass-select-label';
    label.setAttribute('data-glass-label', '');
    trigger.appendChild(label);
    trigger.insertAdjacentHTML('beforeend', CHEVRON);

    root.appendChild(trigger);
    root.appendChild(buildMenu(root, select));
    syncTrigger(root, select);

    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      if (select.disabled) return;
      var willOpen = !root.classList.contains('open');
      closeAll(root);
      root.classList.toggle('open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    select.addEventListener('change', function () {
      syncTrigger(root, select);
    });
  }

  function bindManual(root) {
    if (!root || root.dataset.glassReady === '1') return;
    root.dataset.glassReady = '1';
    var trigger = root.querySelector('.glass-select-trigger');
    var hidden = root.querySelector('input[type="hidden"]');
    var label = root.querySelector('[data-glass-label]');
    var options = [].slice.call(root.querySelectorAll('.glass-select-option'));
    if (!trigger || !hidden || !label) return;

    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      var willOpen = !root.classList.contains('open');
      closeAll(root);
      root.classList.toggle('open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    options.forEach(function (opt) {
      opt.addEventListener('click', function () {
        var val = opt.getAttribute('data-value') || '';
        hidden.value = val;
        label.textContent = opt.textContent.trim();
        label.classList.toggle('placeholder', val === '');
        label.classList.toggle('is-placeholder', val === '');
        options.forEach(function (o) {
          o.classList.toggle('is-active', o === opt);
        });
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
        closeAll();
      });
    });
  }

  function init(scope) {
    var root = scope || document;
    root.querySelectorAll('select[data-glass], select.filter-select, select.mini-select').forEach(enhanceSelect);
    root.querySelectorAll('[data-glass-select]').forEach(bindManual);
  }

  window.HHGlassSelect = { init: init, enhance: enhanceSelect, closeAll: closeAll };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      init();
    });
  } else {
    init();
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.glass-select')) closeAll();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAll();
  });
})();
