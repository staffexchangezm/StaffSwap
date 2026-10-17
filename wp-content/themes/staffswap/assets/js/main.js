(function () {
  'use strict';
  var nav = document.querySelector('.primary-nav');
  var toggle = document.querySelector('[data-mobile-menu]');
  function setMenu(open) {
    if (!nav || !toggle) return;
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('menu-open', open);
  }
  if (nav && toggle) {
    toggle.addEventListener('click', function () { setMenu(!nav.classList.contains('is-open')); });
    nav.addEventListener('click', function (event) { if (event.target.closest('a')) setMenu(false); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') setMenu(false); });
    window.matchMedia('(min-width: 761px)').addEventListener('change', function (mq) { if (mq.matches) setMenu(false); });
  }
  document.querySelectorAll('[data-profile-menu]').forEach(function (wrapper) {
    var trigger = wrapper.querySelector('[data-profile-trigger]');
    var dropdown = wrapper.querySelector('[data-profile-dropdown]');
    if (!trigger || !dropdown) return;
    trigger.addEventListener('click', function () {
      var isOpen = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      dropdown.hidden = isOpen;
    });
    document.addEventListener('click', function (event) {
      if (!wrapper.contains(event.target)) {
        trigger.setAttribute('aria-expanded', 'false');
        dropdown.hidden = true;
      }
    });
    wrapper.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        trigger.setAttribute('aria-expanded', 'false');
        dropdown.hidden = true;
        trigger.focus();
      }
    });
  });
  document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.getElementById(button.getAttribute('data-password-toggle'));
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      button.setAttribute('aria-pressed', input.type === 'text' ? 'true' : 'false');
      button.textContent = input.type === 'text' ? 'Hide' : 'Show';
    });
  });
}());