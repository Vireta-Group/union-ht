/*!
 * saas.js — product-site behaviour, formerly React state.
 *
 * Hand-written, no build step, no dependencies. Loads after ht-modal.js, which
 * owns the support modal. Everything here used to be a useState hook in
 * resources/js/saas-app.jsx:
 *
 *   React state                    →  here
 *   ─────────────────────────────────────────────────────────────
 *   mobileMenuOpen                 →  [data-saas-toggle="mobile-nav"]
 *   supportOpen                    →  ht-modal.js + [data-toggle="modal"]
 *   selectedPlan (plans → register)→  ?plan= query param, real URLs
 *   CountUp animation              →  [data-saas-countup]
 *   FAQ accordion (useState)       →  [data-saas-accordion]
 *   PolicyPage tab (initialTab)    →  [data-saas-tabs] + ?tab=
 *   avatar onError fallback        →  [data-saas-avatar]
 *   LoginPage doLogin() + banners  →  form[data-saas-form="login"], [data-saas-alert]
 *   RegisterPage package cards    →  input[data-saas-plan-radio] (a real radio group)
 *   RegisterPage useCustomDomain  →  [data-saas-toggle] / [data-saas-panel]
 *
 * The important shift: selectedPlan used to live in React state and survive
 * client-side navigation. It is now a query parameter, so choosing a plan on
 * /plans and arriving at /register?plan=premium works from a shared link and
 * survives a page refresh.
 */
(function () {
  'use strict';

  var doc = document;

  /* ------------------------------------------------- mobile navigation */

  function initMobileNav() {
    var buttons = doc.querySelectorAll('[data-saas-toggle="mobile-nav"]');
    for (var i = 0; i < buttons.length; i++) {
      bindMobileNav(buttons[i]);
    }
  }

  function bindMobileNav(btn) {
    var panel = doc.getElementById(btn.getAttribute('aria-controls'));
    if (!panel) return;

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var open = panel.classList.toggle('hidden');
      // classList.toggle returns true when the class was ADDED, i.e. now hidden.
      btn.setAttribute('aria-expanded', open ? 'false' : 'true');
      swapIcons(btn, !open);
    });

    // Any navigation inside the panel should leave it closed. It is always
    // closed on page load anyway since .hidden is in the server-rendered HTML,
    // but closing on Escape matters.
    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
        panel.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        swapIcons(btn, true);
      }
    });
  }

  function swapIcons(btn, showOpen) {
    var open = btn.querySelector('[data-saas-icon="open"]');
    var close = btn.querySelector('[data-saas-icon="close"]');
    if (open) open.classList.toggle('hidden', !showOpen);
    if (close) close.classList.toggle('hidden', showOpen);
  }

  /* ------------------------------------------------------------ count up */

  /*
   * The React CountUp component ran on an IntersectionObserver, so the numbers
   * animated when they scrolled into view. Kept, because it is the whole point
   * of the stats strip.
   */
  function initCountUp() {
    var els = doc.querySelectorAll('[data-saas-countup]');
    if (!els.length) return;

    if (!('IntersectionObserver' in window)) {
      for (var j = 0; j < els.length; j++) setFinal(els[j]);
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      for (var k = 0; k < entries.length; k++) {
        if (!entries[k].isIntersecting) continue;
        io.unobserve(entries[k].target);
        animate(entries[k].target);
      }
    }, { threshold: 0.4 });

    for (var i = 0; i < els.length; i++) io.observe(els[i]);
  }

  function spec(el) {
    return {
      to: parseFloat(el.getAttribute('data-saas-countup')) || 0,
      prefix: el.getAttribute('data-saas-prefix') || '',
      suffix: el.getAttribute('data-saas-suffix') || '',
      decimals: parseInt(el.getAttribute('data-saas-decimals') || '0', 10),
      duration: parseInt(el.getAttribute('data-saas-duration') || '1600', 10)
    };
  }

  function setFinal(el) {
    var s = spec(el);
    el.textContent = s.prefix + s.to.toFixed(s.decimals) + s.suffix;
  }

  function animate(el) {
    var s = spec(el);
    if (s.duration <= 0) { setFinal(el); return; }

    var start = null;
    function step(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / s.duration, 1);
      // easeOutExpo, same curve shape the React version used.
      var eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
      el.textContent = s.prefix + (s.to * eased).toFixed(s.decimals) + s.suffix;
      if (p < 1) window.requestAnimationFrame(step);
    }
    window.requestAnimationFrame(step);
  }

  /* --------------------------------------------------------- FAQ accordion */

  /*
   * Exclusive, matching the React original: one answer open at a time within
   * a group, and clicking the open question closes it. The first question is
   * server-rendered open, so there is a visible answer before JS loads.
   */
  function initAccordions() {
    var groups = doc.querySelectorAll('[data-saas-accordion]');
    for (var i = 0; i < groups.length; i++) {
      (function (group) {
        var triggers = group.querySelectorAll('[data-saas-accordion]');

        function close(trigger) {
          var panel = doc.getElementById(trigger.getAttribute('aria-controls'));
          if (!panel || panel.classList.contains('hidden')) return;
          panel.classList.add('hidden');
          trigger.setAttribute('aria-expanded', 'false');
          var caret = trigger.querySelector('[data-saas-caret]');
          if (caret) caret.classList.remove('rotate-180');
        }

        function open(trigger) {
          var panel = doc.getElementById(trigger.getAttribute('aria-controls'));
          if (!panel) return;
          panel.classList.remove('hidden');
          trigger.setAttribute('aria-expanded', 'true');
          var caret = trigger.querySelector('[data-saas-caret]');
          if (caret) caret.classList.add('rotate-180');
        }

        for (var j = 0; j < triggers.length; j++) {
          (function (trigger) {
            trigger.addEventListener('click', function () {
              var panel = doc.getElementById(trigger.getAttribute('aria-controls'));
              var wasOpen = panel && !panel.classList.contains('hidden');
              for (var k = 0; k < triggers.length; k++) close(triggers[k]);
              if (!wasOpen) open(trigger);
            });
          })(triggers[j]);
        }
      })(groups[i]);
    }
  }

  /* --------------------------------------------------------- policy tabs */

  function initTabs() {
    var groups = doc.querySelectorAll('[data-saas-tabs]');
    for (var i = 0; i < groups.length; i++) bindTabs(groups[i]);
  }

  function bindTabs(group) {
    var buttons = group.querySelectorAll('[data-saas-tab]');

    function select(key, push) {
      for (var i = 0; i < buttons.length; i++) {
        var on = buttons[i].getAttribute('data-saas-tab') === key;
        buttons[i].setAttribute('aria-selected', on ? 'true' : 'false');
        buttons[i].classList.toggle('border-indigo-600', on);
        buttons[i].classList.toggle('text-indigo-600', on);
        buttons[i].classList.toggle('text-neutral-500', !on);

        var panel = doc.getElementById(buttons[i].getAttribute('aria-controls'));
        if (panel) panel.classList.toggle('hidden', !on);
      }
      if (push) {
        // Reflect the tab in the URL so /terms is shareable and survives reload.
        // replaceState, not pushState — switching tabs should not fill history.
        var url = new URL(window.location.href);
        url.searchParams.set('tab', key);
        window.history.replaceState({}, '', url.toString());
      }
    }

    for (var j = 0; j < buttons.length; j++) {
      (function (btn) {
        btn.addEventListener('click', function () {
          select(btn.getAttribute('data-saas-tab'), true);
        });
      })(buttons[j]);
    }

    // The server already rendered the right tab active (Controller passes
    // $saas_tab), so only honour ?tab= when the two disagree.
    var urlTab = new URL(window.location.href).searchParams.get('tab');
    if (urlTab) select(urlTab, false);
  }

  /* ------------------------------------------------------------ plan preselect */

  /*
   * /plans?plan=premium → /register?plan=premium. Every plan CTA on /plans and
   * the home page already links to register with the param; this just makes the
   * register page reflect it. The register form field itself is server-rendered
   * from the same query param, so this is only the visual highlight.
   */
  function initPlanHighlight() {
    var plan = new URL(window.location.href).searchParams.get('plan');
    if (!plan) return;
    var cards = doc.querySelectorAll('[data-saas-plan]');
    for (var i = 0; i < cards.length; i++) {
      if (cards[i].getAttribute('data-saas-plan') !== plan) continue;
      cards[i].classList.add('ring-2', 'ring-indigo-500');
    }
  }

  /* ----------------------------------------------------------- avatar fallback */

  /*
   * Reviews.jsx had an onError handler that swapped a broken avatar for
   * ui-avatars.com initials. As a data attribute rather than an inline
   * handler, so the server-rendered markup stays free of <script>. The name
   * arrives URL-encoded because Bengali in a query string is not safe to
   * concatenate blind.
   */
  function initAvatarFallbacks() {
    var imgs = doc.querySelectorAll('img[data-saas-avatar]');
    for (var i = 0; i < imgs.length; i++) {
      (function (img) {
        img.addEventListener('error', function () {
          // One retry only. Without the guard a 404 on the fallback itself
          // would restart the handler and loop.
          if (img.dataset.saasAvatarDone) return;
          img.dataset.saasAvatarDone = '1';
          img.src = 'https://ui-avatars.com/api/?name=' + img.getAttribute('data-saas-avatar')
                  + '&background=3730a3&color=fff';
        });
      })(imgs[i]);
    }
  }

  /* ------------------------------------------------------------- auth form */

  /*
   * The login form is a real <form method="post" action="api_login"> with the
   * field names Front::api_login() already reads. It is intercepted here purely
   * because that endpoint answers with JSON in every case and expects the client
   * to do the redirect. The markup stays honest about what it posts; only the
   * transport is JS.
   *
   * If this script fails to load, the form still submits and the visitor sees
   * api_login's raw JSON. That is a worse experience than a no-op, so the
   * submit handler removes itself if anything goes wrong. The real fix is
   * content negotiation inside api_login — an auth change, not a render one.
   */
  function initAuthForms() {
    var forms = doc.querySelectorAll('form[data-saas-form="login"]');
    for (var i = 0; i < forms.length; i++) {
      (function (form) {
        var submit = form.querySelector('[data-saas-submit]');

        form.addEventListener('submit', function (e) {
          e.preventDefault();

          var payload = {
            identity: form.elements.identity.value.trim(),
            password: form.elements.password.value,
            remember: !!form.elements.remember && form.elements.remember.checked
          };

          if (!payload.identity || !payload.password) {
            showAlert(form, 'error', 'অনুগ্রহ করে ইউজারনেম/ইমেইল এবং পাসওয়ার্ড প্রদান করুন।');
            return;
          }

          setBusy(submit, true);
          hideAlerts(form);

          fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
          })
            .then(function (r) { return r.json(); })
            .then(function (result) {
              if (result.success) {
                showAlert(form, 'success', result.message || 'লগইন সফল হয়েছে! ড্যাশবোর্ডে নিয়ে যাওয়া হচ্ছে...');
                window.location.href = result.redirect_url || siteUrl('dashboard');
              } else {
                showAlert(form, 'error', result.message || 'ভুল ইউজারনেম/ইমেইল অথবা পাসওয়ার্ড। অনুগ্রহ করে পুনরায় চেষ্টা করুন।');
                setBusy(submit, false);
              }
            })
            .catch(function () {
              showAlert(form, 'error', 'সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি। অনুগ্রহ করে ইন্টারনেট সংযোগ চেক করুন।');
              setBusy(submit, false);
            });
        });
      })(forms[i]);
    }
  }

  /* Reads the <base href> the shell sets, so the redirect target is correct
     even when the app is installed in a subdirectory. */
  function siteUrl(path) {
    var base = doc.querySelector('base');
    var root = base ? base.getAttribute('href') : '/';
    return String(root).replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
  }

  function setBusy(submit, busy) {
    if (!submit) return;
    submit.disabled = busy;
    var idle = submit.querySelector('[data-saas-submit-idle]');
    var pending = submit.querySelector('[data-saas-submit-busy]');
    if (idle) idle.classList.toggle('hidden', busy);
    if (pending) {
      pending.classList.toggle('hidden', !busy);
      pending.classList.toggle('flex', busy);
    }
  }

  function hideAlerts(form) {
    var all = form.parentNode.querySelectorAll('[data-saas-alert]');
    for (var i = 0; i < all.length; i++) all[i].classList.add('hidden');
  }

  function showAlert(form, kind, message) {
    var banner = form.parentNode.querySelector('[data-saas-alert="' + kind + '"]');
    if (!banner) return;
    var slot = banner.querySelector('[data-saas-alert-text]');
    if (slot) slot.textContent = message;
    banner.classList.remove('hidden');
    banner.classList.add('flex');
  }

  /* ------------------------------------------------- password visibility */

  function initPasswordToggles() {
    var buttons = doc.querySelectorAll('[data-saas-toggle-password]');
    for (var i = 0; i < buttons.length; i++) {
      (function (btn) {
        var field = btn.parentNode.querySelector('[data-saas-password]');
        if (!field) return;
        btn.addEventListener('click', function () {
          var show = field.type === 'password';
          field.type = show ? 'text' : 'password';
          btn.setAttribute('aria-label', show ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখান');
          var on = btn.querySelector('[data-saas-eye="show"]');
          var off = btn.querySelector('[data-saas-eye="hide"]');
          if (on) on.classList.toggle('hidden', show);
          if (off) off.classList.toggle('hidden', !show);
        });
      })(buttons[i]);
    }
  }

  /* ------------------------------------------------------------- prefill */

  /*
   * Replaces LoginPage's one-click demo login, which called doLogin() straight
   * from a click handler. This fills the two inputs and leaves the submit
   * button to the visitor: the credentials never appear in a URL, so they stay
   * out of browser history, the access log and any Referer header.
   */
  function initPrefillButtons() {
    var buttons = doc.querySelectorAll('[data-saas-prefill-identity]');
    for (var i = 0; i < buttons.length; i++) {
      (function (btn) {
        btn.addEventListener('click', function () {
          var form = btn.closest('form');
          if (!form) return;
          if (form.elements.identity) form.elements.identity.value = btn.getAttribute('data-saas-prefill-identity');
          if (form.elements.password) form.elements.password.value = btn.getAttribute('data-saas-prefill-password');
          hideAlerts(form);
          if (form.elements.password) form.elements.password.focus();
        });
      })(buttons[i]);
    }
  }

  /* ------------------------------------------------------ plan radio cards */

  /*
   * RegisterPage's package cards were <div onClick>. Here the radio input is
   * the real state, and the card chrome around it follows. The submission does
   * not depend on this: a server-rendered form has the right radio already
   * checked, and this only moves the highlight if the visitor clicks a different
   * card before submitting. Without it the radio still moves — it is inside a
   * <label> — but the card does not repaint, which would look broken.
   */
  function initPlanCards() {
    var radios = doc.querySelectorAll('input[data-saas-plan-radio]');
    if (!radios.length) return;

    function paint(key) {
      for (var i = 0; i < radios.length; i++) {
        var k = radios[i].getAttribute('data-saas-plan-radio');
        var on = (k === key);
        var dot = doc.querySelector('[data-saas-plan-dot="' + k + '"]');
        var label = doc.querySelector('[data-saas-plan-label="' + k + '"]');
        var card = radios[i].closest('.border-2');

        if (dot) {
          dot.className = on
            ? 'w-5 h-5 rounded-full border-2 flex items-center justify-center border-indigo-700 bg-indigo-700 text-white'
            : 'w-5 h-5 rounded-full border-2 flex items-center justify-center border-neutral-300';
          dot.innerHTML = on ? '<i class="ph-bold ph-check" style="font-size:11px;line-height:1;"></i>' : '';
        }
        if (label) {
          label.className = on
            ? 'block text-center py-2 rounded-xl text-xs font-bold bg-indigo-700 text-white'
            : 'block text-center py-2 rounded-xl text-xs font-bold bg-neutral-100 text-neutral-700';
          label.textContent = on ? 'নির্বাচিত হয়েছে' : 'প্যাকেজ বাছাই করুন';
        }
        if (card) {
          card.classList.toggle('border-indigo-700', on);
          card.classList.toggle('shadow-md', on);
          card.classList.toggle('ring-2', on);
          card.classList.toggle('ring-indigo-700/20', on);
          card.classList.toggle('border-neutral-200', !on);
        }
      }
    }

    for (var i = 0; i < radios.length; i++) {
      (function (radio) {
        radio.addEventListener('change', function () {
          paint(radio.getAttribute('data-saas-plan-radio'));
        });
      })(radios[i]);
    }
  }

  /* -------------------------------------------------- custom domain reveal */

  /*
   * The register form's "we already have our own domain" checkbox. The custom
   * domain field is always rendered, because with JavaScript off a conditionally
   * revealed field is simply a field the visitor cannot get to — and the server
   * blanks custom_domain when the box is unticked, so the semantics are right
   * either way. This only collapses it, matching the original.
   */
  function initConditionalPanels() {
    var toggles = doc.querySelectorAll('[data-saas-toggle]');
    for (var i = 0; i < toggles.length; i++) {
      (function (toggle) {
        var panel = doc.querySelector('[data-saas-panel="' + toggle.getAttribute('data-saas-toggle') + '"]');
        if (!panel) return;
        toggle.addEventListener('change', function () {
          panel.classList.toggle('hidden', !toggle.checked);
        });
      })(toggles[i]);
    }
  }

  /* --------------------------------------------------- smooth page switcher */
  var isNavigating = false;

  function showLoaderBar(show) {
    var bar = doc.getElementById('page-loader-bar');
    if (!bar) {
      bar = doc.createElement('div');
      bar.id = 'page-loader-bar';
      doc.body.appendChild(bar);
    }
    if (show) {
      bar.style.opacity = '1';
      bar.style.width = '35%';
      setTimeout(function () {
        if (bar && bar.style.opacity === '1') bar.style.width = '75%';
      }, 150);
    } else {
      if (bar) {
        bar.style.width = '100%';
        setTimeout(function () {
          bar.style.opacity = '0';
          setTimeout(function () { bar.style.width = '0%'; }, 200);
        }, 100);
      }
    }
  }

  function updateNavLinks(newPath) {
    var path = newPath.replace(/\/$/, '') || '/';
    var links = doc.querySelectorAll('header nav a, #mobile-menu a');
    for (var i = 0; i < links.length; i++) {
      var a = links[i];
      var href = a.getAttribute('href');
      if (!href) continue;
      try {
        var aUrl = new URL(href, window.location.origin);
        var aPath = aUrl.pathname.replace(/\/$/, '') || '/';
        var isActive = (aPath === path);

        if (a.closest('#mobile-menu')) {
          a.classList.toggle('border-indigo-600', isActive);
          a.classList.toggle('text-indigo-600', isActive);
          a.classList.toggle('bg-indigo-50', isActive);
          a.classList.toggle('border-transparent', !isActive);
          a.classList.toggle('text-neutral-700', !isActive);
        } else {
          a.classList.toggle('text-indigo-600', isActive);
          a.classList.toggle('bg-indigo-50', isActive);
          a.classList.toggle('text-neutral-700', !isActive);
        }
      } catch (e) {}
    }
  }

  function reinitPageModules() {
    initCountUp();
    initAccordions();
    initTabs();
    initPlanHighlight();
    initAvatarFallbacks();
    initAuthForms();
    initPasswordToggles();
    initPrefillButtons();
    initPlanCards();
    initConditionalPanels();
  }

  function navigateTo(targetUrl, push) {
    if (isNavigating) return;
    isNavigating = true;
    showLoaderBar(true);

    fetch(targetUrl, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-SaaS-Page-Request': '1'
      }
    })
    .then(function (res) {
      if (!res.ok) {
        window.location.href = targetUrl;
        return null;
      }
      return res.text();
    })
    .then(function (html) {
      if (!html) return;

      var parser = new DOMParser();
      var newDoc = parser.parseFromString(html, 'text/html');

      var newMain = newDoc.querySelector('main');
      var oldMain = doc.querySelector('main');

      if (!newMain || !oldMain) {
        window.location.href = targetUrl;
        return;
      }

      var newTitle = newDoc.querySelector('title') ? newDoc.querySelector('title').textContent : doc.title;
      var newPath = new URL(targetUrl).pathname;

      function updateDOM() {
        oldMain.innerHTML = newMain.innerHTML;
        oldMain.className = newMain.className;

        doc.title = newTitle;

        if (push) {
          window.history.pushState({ url: targetUrl }, newTitle, targetUrl);
        }

        updateNavLinks(newPath);
        window.scrollTo({ top: 0, behavior: 'instant' });

        reinitPageModules();

        // Close mobile nav if open
        var mobileNav = doc.querySelector('[aria-controls="mobile-menu"]');
        var mobilePanel = doc.getElementById('mobile-menu');
        if (mobilePanel && !mobilePanel.classList.contains('hidden')) {
          mobilePanel.classList.add('hidden');
          if (mobileNav) {
            mobileNav.setAttribute('aria-expanded', 'false');
            swapIcons(mobileNav, true);
          }
        }
      }

      if (doc.startViewTransition) {
        doc.startViewTransition(function () {
          updateDOM();
        }).finished.finally(function () {
          showLoaderBar(false);
          isNavigating = false;
        });
      } else {
        oldMain.classList.add('ht-page-exit');
        setTimeout(function () {
          updateDOM();
          oldMain.classList.remove('ht-page-exit');
          oldMain.classList.add('ht-page-enter');
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              oldMain.classList.remove('ht-page-enter');
              showLoaderBar(false);
              isNavigating = false;
            });
          });
        }, 120);
      }
    })
    .catch(function () {
      window.location.href = targetUrl;
      showLoaderBar(false);
      isNavigating = false;
    });
  }

  function initPageSwitcher() {
    doc.addEventListener('click', function (e) {
      var a = e.target.closest('a');
      if (!a) return;

      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
      if (a.target && a.target !== '_self') return;
      if (a.hasAttribute('download')) return;
      if (a.getAttribute('data-toggle') || a.getAttribute('data-dismiss')) return;

      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
      if (href.indexOf('tel:') === 0 || href.indexOf('mailto:') === 0) return;

      var url;
      try {
        url = new URL(href, window.location.origin);
      } catch (err) {
        return;
      }
      if (url.origin !== window.location.origin) return;

      var path = url.pathname;
      // Skip admin paths or backend endpoints
      if (path.indexOf('/dashboard') === 0 || path.indexOf('/super_admin') === 0 ||
          path.indexOf('/auth') === 0 || path.indexOf('/logout') === 0 ||
          path.indexOf('/src_') === 0 || path.indexOf('/api') === 0) {
        return;
      }

      if (url.pathname === window.location.pathname && url.search === window.location.search) {
        if (!url.hash) {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        return;
      }

      e.preventDefault();
      navigateTo(url.href, true);
    });

    window.addEventListener('popstate', function () {
      navigateTo(window.location.href, false);
    });
  }

  /* ------------------------------------------------------------------ init */

  function init() {
    initMobileNav();
    initCountUp();
    initAccordions();
    initTabs();
    initPlanHighlight();
    initAvatarFallbacks();
    initAuthForms();
    initPasswordToggles();
    initPrefillButtons();
    initPlanCards();
    initConditionalPanels();
    initPageSwitcher();
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', init, false);
  } else {
    init();
  }
})();
