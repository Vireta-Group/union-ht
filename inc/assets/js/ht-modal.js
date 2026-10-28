/*!
 * ht-modal.js — modal + collapse behaviour for the public pages.
 *
 * Replaces Bootstrap 4's jQuery plugin for the ONLY two behaviours the public
 * markup actually used. Public pages do not load inc/assets/css/app.css any
 * more, so `class="modal fade"` and `data-toggle="collapse"` need a driver.
 *
 * Hand-written, no build step, no dependencies. Loaded by:
 *   application/views/p/h.php        (public shell)
 *   application/views/front/partials/header.php
 *
 * It reads the existing Bootstrap attribute vocabulary on purpose, so the
 * markup in front/partials/speeches.php, front/partials/hero_slider.php and
 * front/add.php did not have to be rewritten:
 *
 *   data-toggle="modal"  data-target="#id"      -> open
 *   data-bs-toggle="modal"  data-bs-target="#id" -> open   (Bootstrap 5 spelling)
 *   data-dismiss="modal"                       -> close the enclosing modal
 *   data-bs-dismiss="modal"                    -> close the enclosing modal
 *   data-toggle="collapse" data-target="#id"   -> toggle
 *
 * Anything else Bootstrap did (dropdowns, tooltips, tabs, carousel, offcanvas)
 * is NOT implemented. Those live in the admin, which still loads Bootstrap.
 */
(function () {
  'use strict';

  var MODAL_OPEN = 'modal-open-count';

  function doc() { return document; }

  function resolve(sel) {
    if (!sel) return null;
    // Only an id selector is ever used by the pages this replaces.
    if (sel.charAt(0) === '#') return doc().getElementById(sel.slice(1));
    return doc().querySelector(sel);
  }

  function attr(el, ...names) {
    for (var i = 0; i < names.length; i++) {
      var v = el.getAttribute(names[i]);
      if (v) return v;
    }
    return null;
  }

  function deepestModal(el) {
    while (el && el !== doc()) {
      if (el.classList && el.classList.contains('modal')) return el;
      el = el.parentElement;
    }
    return null;
  }

  /* ---------------------------------------------------------------- modal */

  function lockScroll(on) {
    var body = doc().body;
    if (!body) return;
    var n = parseInt(body.getAttribute(MODAL_OPEN) || '0', 10);
    n = on ? n + 1 : Math.max(0, n - 1);
    if (on) body.setAttribute(MODAL_OPEN, String(n));
    else body.removeAttribute(MODAL_OPEN);
    body.classList.toggle('ht-modal-open', n > 0);
  }

  function syncModalAria(modal, open) {
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    var triggers = doc().querySelectorAll(
      '[data-toggle="modal"][data-target="#' + modal.id + '"],' +
      '[data-bs-toggle="modal"][data-bs-target="#' + modal.id + '"]'
    );
    for (var i = 0; i < triggers.length; i++) {
      triggers[i].setAttribute('aria-expanded', open ? 'true' : 'false');
    }
  }

  function openModal(modal) {
    if (!modal || modal.classList.contains('show')) return;

    var backdrop = doc().createElement('div');
    backdrop.className = 'modal-backdrop fade';
    doc().body.appendChild(backdrop);
    modal._htBackdrop = backdrop;
    // Force a reflow so the .fade transition actually runs.
    void backdrop.offsetWidth;

    backdrop.classList.add('show');
    modal.classList.add('show');
    modal.style.display = 'block';
    syncModalAria(modal, true);
    lockScroll(true);

    // Bootstrap moves focus to the dialog; the speeches modals put tabindex="-1"
    // on the .modal itself, which is the intended target.
    try { modal.focus({ preventScroll: true }); } catch (e) { modal.focus(); }
  }

  function closeModal(modal) {
    if (!modal || !modal.classList.contains('show')) return;

    modal.classList.remove('show');
    modal.style.display = '';
    syncModalAria(modal, false);
    lockScroll(false);

    var backdrop = modal._htBackdrop;
    delete modal._htBackdrop;
    if (backdrop) {
      backdrop.classList.remove('show');
      window.setTimeout(function () {
        if (backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
      }, 160);
    }
  }

  function closeAllModals() {
    var open = doc().querySelectorAll('.modal.show');
    for (var i = 0; i < open.length; i++) closeModal(open[i]);
  }

  /* ------------------------------------------------------------- collapse */

  function toggleCollapse(el) {
    if (!el) return;
    var shown = el.classList.toggle('show');
    el.setAttribute('aria-expanded', shown ? 'true' : 'false');
    var btn = doc().querySelector(
      '[data-toggle="collapse"][data-target="#' + el.id + '"]'
    );
    if (btn) btn.setAttribute('aria-expanded', shown ? 'true' : 'false');
  }

  /* ------------------------------------------------------------- handlers */

  function onClick(e) {
    var el = e.target;
    if (!el || !el.closest) return;

    var dismissBtn = el.closest('[data-dismiss="modal"], [data-bs-dismiss="modal"]');
    if (dismissBtn) {
      e.preventDefault();
      var owner = deepestModal(dismissBtn);
      // A dismiss button inside a modal closes that modal. One outside closes
      // whatever is open, which is how the legacy pages use it.
      closeModal(owner || doc().querySelector('.modal.show'));
      return;
    }

    var toggleBtn = el.closest('[data-toggle], [data-bs-toggle]');
    if (!toggleBtn) return;

    var toggle = attr(toggleBtn, 'data-toggle', 'data-bs-toggle');
    if (!toggle) return;

    var target = resolve(attr(toggleBtn, 'data-target', 'data-bs-target', 'href'));

    if (toggle === 'modal') {
      e.preventDefault();
      openModal(target);
    } else if (toggle === 'collapse') {
      e.preventDefault();
      toggleCollapse(target);
    }
  }

  function onKeydown(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      var top = doc().querySelector('.modal.show');
      if (top) closeModal(top);
    }
  }

  // Clicking the backdrop closes. The target is the .modal itself only when
  // the click landed outside .modal-dialog, so the dialog body is safe.
  function onBackdropClick(e) {
    var m = deepestModal(e.target);
    if (m && !e.target.closest('.modal-dialog')) closeModal(m);
  }

  function init() {
    doc().addEventListener('click', onClick, false);
    doc().addEventListener('keydown', onKeydown, false);
    doc().addEventListener('click', onBackdropClick, false);
  }

  if (doc().readyState === 'loading') {
    doc().addEventListener('DOMContentLoaded', init, false);
  } else {
    init();
  }

  // Exposed so inline page scripts can drive a modal without Bootstrap's $().
  window.htModal = { open: openModal, close: closeModal, closeAll: closeAllModals };
})();
