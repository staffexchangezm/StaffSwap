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
  document.querySelectorAll('[data-listing-view-controls]').forEach(function (controls) {
    var marketplace = controls.closest('.staffswap-marketplace');
    var listingList = marketplace && marketplace.querySelector('.listing-list');
    var viewInput = marketplace && marketplace.querySelector('input[name="listing_view"]');
    var buttons = controls.querySelectorAll('[data-listing-view]');
    if (!listingList || !viewInput || !buttons.length) return;

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var view = button.getAttribute('data-listing-view');
        if (view !== 'grid' && view !== 'list') return;
        listingList.classList.toggle('listing-list--grid', view === 'grid');
        listingList.classList.toggle('listing-list--list', view === 'list');
        viewInput.value = view;
        buttons.forEach(function (viewButton) {
          viewButton.setAttribute('aria-pressed', viewButton === button ? 'true' : 'false');
        });
        var url = new URL(window.location.href);
        url.searchParams.set('listing_view', view);
        window.history.replaceState(null, '', url.pathname + url.search + url.hash);
      });
    });

    controls.hidden = false;
  });
}());