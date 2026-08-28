(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-mobile-menu]');
    if (!trigger) return;
    var menu = document.querySelector('.primary-nav');
    if (menu) menu.classList.toggle('is-open');
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