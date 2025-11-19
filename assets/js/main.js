document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  const applyBtn = document.querySelector('.apply-now-btn');

  if (!toggle || !navLinks) return;

  const CLOSING_MS = 720; // keep in sync with .nav-links.closing transition

  // Move Apply Now button into mobile menu on mobile/tablet
  function moveApplyButtonToMenu() {
    if (window.innerWidth <= 992 && applyBtn && navLinks) {
      // Check if button clone already exists in menu
      const existingMenuBtn = navLinks.querySelector('.apply-now-btn');
      if (!existingMenuBtn) {
        // Clone the button and add to menu
        const clonedBtn = applyBtn.cloneNode(true);
        clonedBtn.classList.add('apply-now-btn');
        navLinks.appendChild(clonedBtn);
      }
    } else {
      // Remove button clone from menu on desktop
      const menuBtn = navLinks.querySelector('.apply-now-btn');
      if (menuBtn) {
        menuBtn.remove();
      }
    }
  }

  // Initial check
  moveApplyButtonToMenu();

  // Update on resize
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(moveApplyButtonToMenu, 100);
  });

  function openMenu() {
    // ensure no leftover closing state
    navLinks.classList.remove('closing');
    navLinks.classList.add('open');
    toggle.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    // prevent background scroll while open
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
    // Ensure button is in menu
    moveApplyButtonToMenu();
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

  // close menu when clicking a navigation link or apply button
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




// video mode js 
const openBtn = document.getElementById("openVideoBtn");
const modal = document.getElementById("videoModal");
const closeBtn = document.getElementById("closeModal");
const youtubeFrame = document.getElementById("youtubeFrame");

// 👉 Your YouTube Video Link
const videoURL = "https://www.youtube.com/embed/YOUR_VIDEO_ID";

openBtn.addEventListener("click", () => {
  modal.classList.add("active");
  youtubeFrame.src = videoURL + "?autoplay=1";
});

closeBtn.addEventListener("click", closeModalFunc);

window.addEventListener("click", (e) => {
  if (e.target === modal) closeModalFunc();
});

function closeModalFunc() {
  modal.classList.remove("active");
  setTimeout(() => {
    youtubeFrame.src = "";
  }, 300); // removes video after close animation
}



// faq section 
  document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', () => {
                    // Close all other FAQ items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // Toggle current FAQ item
                    item.classList.toggle('active');
                });
            });
        });

        // carousil js starts 


        const carousel = document.querySelector('.carousel');
const leftArrow = document.querySelector('.left-arrow');
const rightArrow = document.querySelector('.right-arrow');

leftArrow.addEventListener('click', () => {
  carousel.scrollBy({ left: -300, behavior: 'smooth' });
});

rightArrow.addEventListener('click', () => {
  carousel.scrollBy({ left: 300, behavior: 'smooth' });
});