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
  document.querySelectorAll('[data-featured-carousel]').forEach(function (carousel) {
    var viewport = carousel.querySelector('[data-featured-carousel-viewport]');
    var track = carousel.querySelector('[data-featured-carousel-track]');
    var controls = carousel.querySelector('[data-featured-carousel-controls]');
    var previous = carousel.querySelector('[data-featured-carousel-prev]');
    var next = carousel.querySelector('[data-featured-carousel-next]');
    var pause = carousel.querySelector('[data-featured-carousel-pause]');
    var cards = track ? Array.prototype.slice.call(track.children) : [];
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var timer;
    var manuallyPaused = false;
    var pointerOver = false;
    var focusWithin = false;

    if (!viewport || !track || !controls || !previous || !next || !pause || cards.length < 2) return;

    function canScroll() {
      return viewport.scrollWidth > viewport.clientWidth + 1;
    }

    function stopRotation() {
      window.clearInterval(timer);
      timer = null;
    }

    function startRotation() {
      stopRotation();
      if (canScroll() && !manuallyPaused && !pointerOver && !focusWithin && !reducedMotion.matches) {
        timer = window.setInterval(advance, 5000);
      }
    }

    function scrollByCard(direction) {
      var firstCard = cards[0];
      var cardStyle = window.getComputedStyle(track);
      var gap = parseFloat(cardStyle.columnGap) || 0;
      var distance = firstCard.getBoundingClientRect().width + gap;
      var maxScroll = viewport.scrollWidth - viewport.clientWidth;

      if (direction > 0 && viewport.scrollLeft + distance >= maxScroll - 1) {
        viewport.scrollTo({ left: 0, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
      } else if (direction < 0 && viewport.scrollLeft <= 1) {
        viewport.scrollTo({ left: maxScroll, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
      } else {
        viewport.scrollBy({ left: direction * distance, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
      }
    }

    function advance() {
      scrollByCard(1);
    }

    function updateCarousel() {
      controls.hidden = !canScroll();
      if (!canScroll()) {
        viewport.scrollTo({ left: 0, behavior: 'auto' });
      }
      startRotation();
    }

    previous.addEventListener('click', function () { scrollByCard(-1); });
    next.addEventListener('click', advance);
    pause.addEventListener('click', function () {
      manuallyPaused = !manuallyPaused;
      pause.setAttribute('aria-pressed', manuallyPaused ? 'true' : 'false');
      pause.textContent = manuallyPaused ? 'Resume rotation' : 'Pause rotation';
      startRotation();
    });
    carousel.addEventListener('pointerenter', function () {
      pointerOver = true;
      stopRotation();
    });
    carousel.addEventListener('pointerleave', function () {
      pointerOver = false;
      startRotation();
    });
    carousel.addEventListener('focusin', function () {
      focusWithin = true;
      stopRotation();
    });
    carousel.addEventListener('focusout', function (event) {
      if (!carousel.contains(event.relatedTarget)) {
        focusWithin = false;
        startRotation();
      }
    });
    window.addEventListener('resize', updateCarousel);
    reducedMotion.addEventListener('change', startRotation);
    updateCarousel();
  });
}());