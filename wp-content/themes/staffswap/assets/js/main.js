(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-mobile-menu]');
    if (!trigger) return;
    var menu = document.querySelector('.primary-nav');
    if (menu) menu.classList.toggle('is-open');
  });
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
    });
  });
}());