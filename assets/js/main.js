document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');

  if (!toggle || !navLinks) return;

  const CLOSING_MS = 720; // keep in sync with .nav-links.closing transition

  function openMenu() {
    // ensure no leftover closing state
    navLinks.classList.remove('closing');
    navLinks.classList.add('open');
    toggle.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    // prevent background scroll while open
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    // apply longer closing transition
    navLinks.classList.add('closing');
    navLinks.classList.remove('open');
    toggle.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');

    // restore scroll after animation completes
    setTimeout(() => {
      navLinks.classList.remove('closing');
      document.documentElement.style.overflow = '';
      document.body.style.overflow = '';
    }, CLOSING_MS);
  }

  toggle.addEventListener('click', function () {
    if (navLinks.classList.contains('open')) closeMenu();
    else openMenu();
  });

  // close menu when clicking a navigation link
  navLinks.addEventListener('click', function (e) {
    const el = e.target;
    if (el.tagName === 'A' || el.closest('a')) {
      // allow the click interaction then close (small delay for UX)
      setTimeout(closeMenu, 50);
    }
  });

  // close on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeMenu();
    }
  });

  // close when clicking outside the open menu (overlay)
  document.addEventListener('click', (e) => {
    if (!navLinks.classList.contains('open')) return;
    const path = e.composedPath ? e.composedPath() : (e.path || []);
    const clickedInside = path.includes(navLinks) || path.includes(toggle);
    if (!clickedInside) closeMenu();
  });
});