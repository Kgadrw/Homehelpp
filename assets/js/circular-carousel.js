(function (global) {
  'use strict';

  var TO_RAD = Math.PI / 180;
  var SPRING = 118;
  var SETTLE_SPEED = 9;
  var DRAG_THRESHOLD = 5;

  function clamp(v, min, max) {
    return Math.min(max, Math.max(min, v));
  }
  function wrap(degrees) {
    return ((((degrees + 180) % 360) + 360) % 360) - 180;
  }
  function easeOutQuint(t) {
    return 1 - Math.pow(1 - t, 5);
  }
  function prefersReducedMotion() {
    return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  }

  function CircularCarousel(root, options) {
    if (!root) return;
    options = options || {};
    var items = options.items && options.items.length ? options.items : [];
    if (!items.length) return;

    var reduced = prefersReducedMotion();
    var cardW = Math.max(40, options.cardWidth || 252);
    var aspect = clamp(options.aspectRatio || 1, 0.2, 5);
    var cardH = cardW / aspect;
    var gap = options.gap != null ? options.gap : 55;
    var curve = clamp(options.curve != null ? options.curve : 1, 0, 1);
    var tilt = options.tilt != null ? options.tilt : -5;
    var perspective = options.perspective != null ? options.perspective : 2500;
    var speed = options.speed != null ? options.speed : 14;
    var direction = options.direction === 'right' ? 1 : -1;
    var momentum = clamp(options.momentum != null ? options.momentum : 0.6, 0, 1);
    var snap = options.snap !== false;
    var pauseOnHover = options.pauseOnHover !== false;
    var focusOnClick = options.focusOnClick !== false;
    var draggable = options.draggable !== false;
    var autoplay = reduced ? 'off' : (options.autoplay || 'drift');
    var intro = reduced ? 'none' : (options.intro || 'rise');
    var parallax = reduced ? 0 : clamp(options.parallax != null ? options.parallax : 0.3, 0, 1);
    var stretch = reduced ? 0 : clamp(options.stretch != null ? options.stretch : 0.5, 0, 1);
    var depthFade = clamp(options.depthFade != null ? options.depthFade : 0.55, 0, 1);
    var fadeColor = options.fadeColor || '#000000';
    var cornerRadius = options.cornerRadius != null ? options.cornerRadius : 12;
    var count = items.length;
    var step = 360 / count;

    var n = Math.max(count, 3);
    var pitch = (cardW + gap);
    var chord = pitch / (2 * Math.sin(Math.PI / n));
    var arc = (n * pitch) / (2 * Math.PI);
    var radius = Math.max(chord + (arc - chord) * curve, cardW * 0.6);

    root.classList.add('circular-carousel');
    root.style.setProperty('--cc-fade', fadeColor);
    root.style.setProperty('--cc-radius', cornerRadius + 'px');
    if (draggable) root.setAttribute('data-draggable', '');
    root.setAttribute('role', 'region');
    root.setAttribute('aria-roledescription', 'carousel');
    root.setAttribute('aria-label', 'Image carousel');
    root.tabIndex = 0;

    root.innerHTML =
      '<div class="circular-carousel__view">' +
        '<div class="circular-carousel__stage">' +
          '<div class="circular-carousel__camera">' +
            '<div class="circular-carousel__ring"></div>' +
          '</div>' +
        '</div>' +
      '</div>' +
      '<div class="circular-carousel__live" aria-live="polite" aria-atomic="true"></div>';

    var stage = root.querySelector('.circular-carousel__stage');
    var camera = root.querySelector('.circular-carousel__camera');
    var ring = root.querySelector('.circular-carousel__ring');
    var live = root.querySelector('.circular-carousel__live');
    var cards = [];

    items.forEach(function (item, index) {
      var card = document.createElement('div');
      card.className = 'circular-carousel__card';
      card.setAttribute('data-cc-index', String(index));
      card.setAttribute('role', 'group');
      card.setAttribute('aria-roledescription', 'slide');
      card.setAttribute(
        'aria-label',
        (item.title || item.alt || 'Image ' + (index + 1)) + ', ' + (index + 1) + ' of ' + count
      );

      var frame = document.createElement('div');
      frame.className = 'circular-carousel__frame';
      frame.style.width = cardW + 'px';
      frame.style.height = cardH + 'px';

      var img = document.createElement('img');
      img.className = 'circular-carousel__photo';
      img.src = item.src;
      img.alt = item.alt || '';
      img.draggable = false;
      img.decoding = 'async';

      var shade = document.createElement('div');
      shade.className = 'circular-carousel__shade';

      frame.appendChild(img);
      frame.appendChild(shade);
      card.appendChild(frame);
      ring.appendChild(card);
      cards.push(card);
    });

    var state = {
      angle: 0,
      velocity: 0,
      target: null,
      dir: direction,
      press: null,
      drag: false,
      hover: false,
      pointer: { inside: false, x: 0, y: 0 },
      yaw: 0,
      pitch: 0,
      intro: null,
      introDone: false,
      holdUntil: 0,
      suppressClick: false,
      fit: 1,
      shift: 0,
      drop: cardH * 1.2,
      last: 0,
      active: 0
    };

    var raf = 0;
    var visible = true;
    var ready = false;

    function nearest(angle) {
      return Math.round(angle / step) * step;
    }

    function measure() {
      var rect = root.getBoundingClientRect();
      if (!rect.width || !rect.height) return;
      var width = rect.width * 0.94;
      var height = rect.height * 0.92;
      var span = Math.max(radius * 1.55 + cardW, 1);
      var fit = Math.min(1, width / span, height / (cardH * 1.35 + Math.abs(tilt) * 4));
      state.fit = fit;
      // Bias the ring upward so cards sit closer to hero copy
      state.shift = -Math.sin(tilt * TO_RAD) * radius * 0.15 - (rect.height * 0.12) / Math.max(fit, 0.2);
      state.drop = (rect.height / Math.max(fit, 0.2)) * 0.45 + cardH;
      stage.style.perspective = perspective + 'px';
      stage.style.transform = 'translate3d(0, ' + state.shift + 'px, 0) scale(' + fit + ')';
    }

    function setReady() {
      ready = true;
      root.setAttribute('data-ready', '');
      if (intro === 'none') state.introDone = true;
      else state.intro = { type: intro, start: performance.now() };
      wake();
    }

    function preload() {
      var sources = items.slice(0, 16).map(function (i) { return i.src; });
      var done = 0;
      var total = sources.length || 1;
      function check() {
        done += 1;
        if (done >= total) setReady();
      }
      if (!sources.length) {
        setReady();
        return;
      }
      sources.forEach(function (src) {
        var image = new Image();
        image.onload = check;
        image.onerror = check;
        image.src = src;
      });
      setTimeout(function () {
        if (!ready) setReady();
      }, 2400);
    }

    function introCard(elapsed, landing) {
      if (!state.intro) return { radius: 1, lift: 0 };
      if (state.intro.type === 'rise') {
        var reach = Math.abs(wrap(landing + state.angle));
        var delay = (reach / 180) * 480;
        var p = easeOutQuint(clamp((elapsed - delay) / 900, 0, 1));
        return { radius: 1, lift: (1 - p) * state.drop };
      }
      return { radius: 1, lift: 0 };
    }

    function advance(dt, now) {
      if (!state.introDone && ready) {
        if (state.intro && now - state.intro.start >= 1400) {
          state.intro = null;
          state.introDone = true;
        }
      }

      var paused = (pauseOnHover && state.hover) || state.drag || now < state.holdUntil;
      var cruise = autoplay === 'drift' && !paused && !state.intro ? speed * state.dir : 0;
      var busy = Boolean(state.intro) || state.drag;

      if (state.drag || state.intro) {
        state.velocity = state.drag ? state.velocity : 0;
      } else if (state.target !== null) {
        var remaining = dt;
        var damping = 2 * Math.sqrt(SPRING);
        while (remaining > 0) {
          var h = Math.min(remaining, 1 / 240);
          var accel = SPRING * (state.target - state.angle) - damping * state.velocity;
          state.velocity += accel * h;
          state.angle += state.velocity * h;
          remaining -= h;
        }
        if (Math.abs(state.target - state.angle) < 0.004 && Math.abs(state.velocity) < 0.03) {
          state.angle = state.target;
          state.velocity = 0;
          state.target = null;
        }
        busy = true;
      } else {
        var tau = 0.18 + momentum * 1.5;
        state.velocity += (cruise - state.velocity) * (1 - Math.exp(-dt / tau));
        state.angle += state.velocity * dt;
        if (cruise === 0 && snap && Math.abs(state.velocity) < SETTLE_SPEED) {
          state.target = nearest(state.angle);
        }
        busy = busy || cruise !== 0 || Math.abs(state.velocity) > 0.01 || state.target !== null;
      }

      if (now < state.holdUntil) busy = true;

      var ease = 1 - Math.exp(-dt / 0.35);
      var aimYaw = state.pointer.inside ? state.pointer.x * parallax * 9 : 0;
      var aimPitch = state.pointer.inside ? -state.pointer.y * parallax * 6 : 0;
      state.yaw += (aimYaw - state.yaw) * ease;
      state.pitch += (aimPitch - state.pitch) * ease;
      if (Math.abs(aimYaw - state.yaw) > 0.01 || Math.abs(aimPitch - state.pitch) > 0.01) busy = true;

      return busy;
    }

    function render(now) {
      var elapsed = state.intro ? now - state.intro.start : 0;
      var swell = 1 + stretch * 0.12 * Math.min(1, Math.abs(state.velocity) / 420);
      var R = radius * swell;
      camera.style.transform =
        'translate3d(0, 0, ' + (-R) + 'px) rotateX(' + (tilt + state.pitch) + 'deg) rotateY(' + state.yaw + 'deg)';
      ring.style.transform = 'rotateY(' + state.angle + 'deg)';

      for (var index = 0; index < count; index++) {
        var card = cards[index];
        if (!card) continue;
        var base = index * step;
        var mod = introCard(elapsed, base);
        var r = R * mod.radius;
        var transform = 'rotateY(' + base + 'deg) translateZ(' + r + 'px)';
        if (mod.lift) transform += ' translateY(' + mod.lift + 'px)';
        card.style.transform = transform;

        var world = wrap(base + state.angle);
        var facing = Math.cos(world * TO_RAD);
        var fade = depthFade * Math.pow((1 - facing) / 2, 1.25);
        card.style.setProperty('--cc-depth', fade.toFixed(3));
      }

      var active = ((Math.round(-state.angle / step) % count) + count) % count || 0;
      if (active !== state.active) {
        state.active = active;
        var cur = items[active];
        live.textContent =
          (cur.title || cur.alt || 'Image') + ', ' + (active + 1) + ' of ' + count;
      }
    }

    function frame(now) {
      raf = 0;
      var dt = state.last ? Math.min((now - state.last) / 1000, 0.05) : 1 / 60;
      state.last = now;
      var busy = advance(dt, now);
      render(now);
      if (busy && visible && !document.hidden) raf = requestAnimationFrame(frame);
      else state.last = 0;
    }

    function wake() {
      if (!raf && visible && !document.hidden) raf = requestAnimationFrame(frame);
    }

    function focusIndex(index) {
      var target = -index * step;
      target += 360 * Math.round((state.angle - target) / 360);
      state.target = target;
      state.holdUntil = performance.now() + 2800;
      wake();
    }

    function updatePointer(event) {
      var rect = root.getBoundingClientRect();
      state.pointer.x = clamp(((event.clientX - rect.left) / rect.width) * 2 - 1, -1, 1);
      state.pointer.y = clamp(((event.clientY - rect.top) / rect.height) * 2 - 1, -1, 1);
    }

    root.addEventListener('pointerdown', function (event) {
      state.suppressClick = false;
      if (!draggable || event.button !== 0) return;
      state.press = {
        id: event.pointerId,
        x: event.clientX,
        angle: state.angle,
        moved: false,
        origin: 0,
        samples: [{ time: performance.now(), angle: state.angle }]
      };
    });

    root.addEventListener('pointermove', function (event) {
      if (event.pointerType === 'mouse') {
        state.pointer.inside = true;
        updatePointer(event);
      }
      var press = state.press;
      if (!press || press.id !== event.pointerId) {
        wake();
        return;
      }
      var delta = event.clientX - press.x;
      if (!press.moved) {
        if (Math.abs(delta) < DRAG_THRESHOLD) return;
        press.moved = true;
        press.origin = delta;
        state.drag = true;
        state.target = null;
        state.velocity = 0;
        root.setAttribute('data-dragging', '');
        try { root.setPointerCapture(event.pointerId); } catch (e) {}
      }
      var perPixel = 180 / (Math.PI * radius * state.fit);
      state.angle = press.angle + (delta - press.origin) * perPixel;
      var now = performance.now();
      press.samples.push({ time: now, angle: state.angle });
      while (press.samples.length > 2 && now - press.samples[0].time > 110) press.samples.shift();
      wake();
    });

    function releasePointer(event) {
      var press = state.press;
      if (!press || press.id !== event.pointerId) return;
      state.press = null;
      if (!press.moved) return;
      state.drag = false;
      root.removeAttribute('data-dragging');
      state.suppressClick = true;
      var first = press.samples[0];
      var last = press.samples[press.samples.length - 1];
      var span = (last.time - first.time) / 1000;
      var velocity = span > 0.008 ? clamp((last.angle - first.angle) / span, -1400, 1400) : 0;
      state.velocity = velocity;
      if (Math.abs(velocity) > 60) state.dir = Math.sign(velocity);
      var coasting = autoplay === 'drift' && !(pauseOnHover && state.hover);
      if (snap && !coasting) {
        var tau = 0.18 + momentum * 1.5;
        state.target = Math.round((state.angle + velocity * tau * 0.55) / step) * step;
      }
      wake();
    }

    root.addEventListener('pointerup', releasePointer);
    root.addEventListener('pointercancel', releasePointer);
    root.addEventListener('pointerenter', function (event) {
      if (event.pointerType !== 'mouse') return;
      state.hover = true;
      wake();
    });
    root.addEventListener('pointerleave', function (event) {
      if (event.pointerType === 'mouse') {
        state.hover = false;
        state.pointer.inside = false;
      }
      wake();
    });
    root.addEventListener('click', function (event) {
      if (state.suppressClick) {
        state.suppressClick = false;
        return;
      }
      var card = event.target.closest('[data-cc-index]');
      if (!card) return;
      var index = Number(card.getAttribute('data-cc-index'));
      if (focusOnClick) focusIndex(index);
    });
    root.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowRight') {
        state.target = (state.target ?? nearest(state.angle)) - step;
        state.holdUntil = performance.now() + 2800;
        wake();
        event.preventDefault();
      } else if (event.key === 'ArrowLeft') {
        state.target = (state.target ?? nearest(state.angle)) + step;
        state.holdUntil = performance.now() + 2800;
        wake();
        event.preventDefault();
      }
    });

    var resize = new ResizeObserver(function () {
      measure();
      wake();
    });
    resize.observe(root);

    var io = new IntersectionObserver(function (entries) {
      visible = entries[0] && entries[0].isIntersecting;
      if (visible) wake();
      else {
        cancelAnimationFrame(raf);
        raf = 0;
        state.last = 0;
      }
    });
    io.observe(root);

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        cancelAnimationFrame(raf);
        raf = 0;
        state.last = 0;
      } else wake();
    });

    measure();
    preload();

    return {
      destroy: function () {
        cancelAnimationFrame(raf);
        try { resize.disconnect(); } catch (e) {}
        try { io.disconnect(); } catch (e) {}
        root.innerHTML = '';
        root.removeAttribute('data-ready');
        root.removeAttribute('data-dragging');
        root.removeAttribute('data-draggable');
      },
      measure: measure,
      wake: wake
    };
  }

  global.CircularCarousel = CircularCarousel;
})(window);
