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
  window.addEventListener('resize', function () {
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
document.addEventListener('DOMContentLoaded', function () {
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

if (leftArrow && rightArrow && carousel) {
  leftArrow.addEventListener('click', () => {
    carousel.scrollBy({ left: -300, behavior: 'smooth' });
  });

  rightArrow.addEventListener('click', () => {
    carousel.scrollBy({ left: 300, behavior: 'smooth' });
  });
}



// YouTube Video Carousel
document.addEventListener('DOMContentLoaded', function () {
  // Fetch videos from the server
  fetch('api/get-videos.php')
    .then(response => response.json())
    .then(videos => {
      const carouselInner = document.getElementById('videoCarouselInner');

      if (videos.length === 0) {
        carouselInner.innerHTML = '<p class="text-center w-full py-8 text-gray-500">No videos available.</p>';
        return;
      }

      videos.forEach(video => {
        const videoItem = document.createElement('div');
        videoItem.className = 'video-carousel-item px-2';
        videoItem.innerHTML = `
                    <div class="mx-2">
                        <div class="video-container">
                            <iframe 
                                src="https://www.youtube.com/embed/${video.youtube_url}?rel=0&showinfo=0" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                loading="lazy"
                                title="${video.title}">
                            </iframe>
                        </div>
                        <h3 class="mt-2 text-lg font-medium text-gray-800">${video.title}</h3>
                    </div>
                `;
        carouselInner.appendChild(videoItem);
      });

      // Initialize carousel
      initVideoCarousel();
    })
    .catch(error => {
      console.error('Error loading videos:', error);
      const carouselInner = document.getElementById('videoCarouselInner');
      carouselInner.innerHTML = '<p class="text-center w-full py-8 text-red-500">Error loading videos. Please try again later.</p>';
    });
});

function initVideoCarousel() {
  const carousel = document.getElementById('videoCarouselInner');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const slides = document.querySelectorAll('.video-carousel-item');
  const slideCount = slides.length;
  let currentIndex = 0;
  let slidesPerView = 1;

  function updateSlidesPerView() {
    if (window.innerWidth >= 1024) {
      slidesPerView = 3;
    } else if (window.innerWidth >= 768) {
      slidesPerView = 2;
    } else {
      slidesPerView = 1;
    }
    updateCarousel();
  }

  function updateCarousel() {
    const slideWidth = 100 / slidesPerView;
    const offset = -currentIndex * slideWidth;
    carousel.style.transform = `translateX(${offset}%)`;
  }

  function goToSlide(index) {
    const maxIndex = Math.max(0, slideCount - slidesPerView);
    currentIndex = Math.max(0, Math.min(index, maxIndex));
    updateCarousel();
  }

  function nextSlide() {
    const maxIndex = Math.max(0, slideCount - slidesPerView);
    if (currentIndex < maxIndex) {
      currentIndex++;
      updateCarousel();
    }
  }

  function prevSlide() {
    if (currentIndex > 0) {
      currentIndex--;
      updateCarousel();
    }
  }

  // Event listeners
  prevBtn.addEventListener('click', prevSlide);
  nextBtn.addEventListener('click', nextSlide);

  // Handle keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      prevSlide();
    } else if (e.key === 'ArrowRight') {
      nextSlide();
    }
  });

  // Handle touch events for mobile
  let touchStartX = 0;
  let touchEndX = 0;

  carousel.addEventListener('touchstart', (e) => {
    touchStartX = e.touches[0].clientX;
  }, { passive: true });

  carousel.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].clientX;
    handleSwipe();
  }, { passive: true });

  function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;

    if (Math.abs(diff) > swipeThreshold) {
      if (diff > 0) {
        nextSlide();
      } else {
        prevSlide();
      }
    }
  }

  // Update on window resize
  window.addEventListener('resize', updateSlidesPerView);

  // Initialize
  updateSlidesPerView();
}




// Handle subscription form
// Handle subscription form
document.addEventListener('DOMContentLoaded', function () {
  const subscribeForm = document.getElementById('subscribeForm');

  if (subscribeForm) {
    subscribeForm.addEventListener('submit', async function (e) {
      e.preventDefault(); // Prevent default form submission

      const emailInput = subscribeForm.querySelector('input[type="email"]');
      const submitBtn = subscribeForm.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;

      try {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Subscribing...';

        // Get form data
        const formData = new FormData(subscribeForm);

        // Send AJAX request
        const response = await fetch('admin/subscribe.php', {
          method: 'POST',
          body: formData,
          headers: {
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        // Show success/error message
        showPopup(data.message, data.success ? 'success' : 'error');

        // Reset form if successful
        if (data.success) {
          subscribeForm.reset();
        }
      } catch (error) {
        console.error('Error:', error);
        showPopup('An error occurred. Please try again later.', 'error');
      } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    });
  }
});

// Function to show popup
function showPopup(message, type = 'success') {
  // Create popup element if it doesn't exist
  let popup = document.getElementById('customPopup');

  if (!popup) {
    popup = document.createElement('div');
    popup.id = 'customPopup';
    popup.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
            max-width: 90%;
            text-align: center;
        `;
    document.body.appendChild(popup);
  }

  // Set message and style based on type
  popup.textContent = message;
  popup.style.backgroundColor = type === 'success' ? '#10B981' : '#EF4444';

  // Show popup
  popup.style.transform = 'translateX(0)';

  // Hide after 5 seconds
  setTimeout(() => {
    popup.style.transform = 'translateX(120%)';
  }, 5000);
}

// Add CSS for the popup
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(120%); }
        to { transform: translateX(0); }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); }
        to { transform: translateX(120%); }
    }
`;
document.head.appendChild(style);