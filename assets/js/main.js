       document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const navToggle = document.querySelector('.nav-toggle');
            const navLinks = document.querySelector('.nav-links');
            const navIcon = navToggle.querySelector('i');
            
            navToggle.addEventListener('click', function() {
                const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
                navToggle.setAttribute('aria-expanded', !isExpanded);
                navLinks.classList.toggle('active');
                
                // Change icon based on menu state
                if (isExpanded) {
                    navIcon.className = 'fa-solid fa-bars';
                } else {
                    navIcon.className = 'fa-solid fa-xmark';
                }
                
                // Prevent body scroll when menu is open
                document.body.style.overflow = isExpanded ? 'auto' : 'hidden';
            });
            
            // Dropdown toggle for mobile
            const dropdownToggle = document.querySelector('.nav-dropdown-toggle');
            const dropdown = document.querySelector('.nav-dropdown');
            
            if (dropdownToggle && dropdown) {
                dropdownToggle.addEventListener('click', function() {
                    const isExpanded = dropdownToggle.getAttribute('aria-expanded') === 'true';
                    dropdownToggle.setAttribute('aria-expanded', !isExpanded);
                    dropdown.classList.toggle('active');
                });
            }
            
            // Close mobile menu when clicking on a link
            const navItems = document.querySelectorAll('.nav-links a');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        navToggle.setAttribute('aria-expanded', 'false');
                        navLinks.classList.remove('active');
                        navIcon.className = 'fa-solid fa-bars';
                        document.body.style.overflow = 'auto';
                        
                        // Close dropdown if open
                        if (dropdown) {
                            dropdownToggle.setAttribute('aria-expanded', 'false');
                            dropdown.classList.remove('active');
                        }
                    }
                });
            });
            
            // Header scroll effect
            const header = document.querySelector('.header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            
            // Close menu when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    const isClickInsideNav = navLinks.contains(event.target);
                    const isClickOnToggle = navToggle.contains(event.target);
                    
                    if (!isClickInsideNav && !isClickOnToggle && navLinks.classList.contains('active')) {
                        navToggle.setAttribute('aria-expanded', 'false');
                        navLinks.classList.remove('active');
                        navIcon.className = 'fa-solid fa-bars';
                        document.body.style.overflow = 'auto';
                        
                        // Close dropdown if open
                        if (dropdown) {
                            dropdownToggle.setAttribute('aria-expanded', 'false');
                            dropdown.classList.remove('active');
                        }
                    }
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    // Reset mobile menu state on larger screens
                    navToggle.setAttribute('aria-expanded', 'false');
                    navLinks.classList.remove('active');
                    navIcon.className = 'fa-solid fa-bars';
                    document.body.style.overflow = 'auto';
                    
                    if (dropdown) {
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                        dropdown.classList.remove('active');
                    }
                }
            });
        });
// document.addEventListener('DOMContentLoaded', function () {
//   const toggle = document.querySelector('.nav-toggle');
//   const navLinks = document.querySelector('.nav-links');
//   const applyBtn = document.querySelector('.apply-now-btn');

//   if (!toggle || !navLinks) return;

//   const CLOSING_MS = 720;
//   const DESKTOP_BREAKPOINT = 993;
//   const dropdownItem = navLinks.querySelector('.nav-item--dropdown');
//   const dropdownToggle = dropdownItem ? dropdownItem.querySelector('.nav-dropdown-toggle') : null;

//   const isDesktopView = () => window.innerWidth >= DESKTOP_BREAKPOINT;

//   const setDropdownState = (shouldOpen) => {
//     if (!dropdownItem) return;
//     dropdownItem.classList.toggle('is-open', shouldOpen);
//     if (dropdownToggle) {
//       dropdownToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
//     }
//   };

//   const closeDropdown = () => setDropdownState(false);
//   const openDropdown = () => setDropdownState(true);

//   function moveApplyButtonToMenu() {
//     if (window.innerWidth <= 992 && applyBtn && navLinks) {
//       const existingMenuBtn = navLinks.querySelector('.apply-now-btn');
//       if (!existingMenuBtn) {
//         const clonedBtn = applyBtn.cloneNode(true);
//         clonedBtn.classList.add('apply-now-btn');
//         navLinks.appendChild(clonedBtn);
//       }
//     } else {
//       const menuBtn = navLinks.querySelector('.apply-now-btn');
//       if (menuBtn) {
//         menuBtn.remove();
//       }
//     }
//   }

//   moveApplyButtonToMenu();

//   let resizeTimer;
//   window.addEventListener('resize', function () {
//     clearTimeout(resizeTimer);
//     resizeTimer = setTimeout(() => {
//       moveApplyButtonToMenu();
//       closeDropdown();
//     }, 100);
//   });

//   function openMenu() {
//     closeDropdown();
//     navLinks.classList.remove('closing');
//     navLinks.classList.add('open');
//     toggle.classList.add('is-open');
//     toggle.setAttribute('aria-expanded', 'true');
//     document.documentElement.style.overflow = 'hidden';
//     document.body.style.overflow = 'hidden';
//     moveApplyButtonToMenu();
//   }

//   function closeMenu() {
//     navLinks.classList.add('closing');
//     navLinks.classList.remove('open');
//     toggle.classList.remove('is-open');
//     toggle.setAttribute('aria-expanded', 'false');
//     closeDropdown();

//     setTimeout(() => {
//       navLinks.classList.remove('closing');
//       document.documentElement.style.overflow = '';
//       document.body.style.overflow = '';
//     }, CLOSING_MS);
//   }

//   toggle.addEventListener('click', function () {
//     if (navLinks.classList.contains('open')) closeMenu();
//     else openMenu();
//   });

//   if (dropdownToggle && dropdownItem) {
//     dropdownToggle.addEventListener('click', (event) => {
//       event.preventDefault();
//       const shouldOpen = !dropdownItem.classList.contains('is-open');
//       setDropdownState(shouldOpen);
//     });

//     dropdownToggle.addEventListener('keydown', (event) => {
//       if (event.key === 'Escape') {
//         closeDropdown();
//       }
//     });

//     dropdownItem.addEventListener('mouseenter', () => {
//       if (isDesktopView()) {
//         openDropdown();
//       }
//     });

//     dropdownItem.addEventListener('mouseleave', () => {
//       if (isDesktopView()) {
//         closeDropdown();
//       }
//     });
//   }

//   navLinks.addEventListener('click', function (e) {
//     const el = e.target;
//     if (el.tagName === 'A' || el.closest('a')) {
//       closeDropdown();
//       setTimeout(closeMenu, 50);
//     }
//   });

//   document.addEventListener('keydown', (e) => {
//     if (e.key === 'Escape') {
//       closeDropdown();
//       closeMenu();
//     }
//   });

//   document.addEventListener('click', (e) => {
//     if (!navLinks.classList.contains('open')) return;
//     const path = e.composedPath ? e.composedPath() : (e.path || []);
//     const clickedInside = path.includes(navLinks) || path.includes(toggle);
//     if (!clickedInside) closeMenu();
//   });

//   document.addEventListener('click', (event) => {
//     if (!dropdownItem || !dropdownItem.classList.contains('is-open')) return;
//     if (!dropdownItem.contains(event.target)) {
//       closeDropdown();
//     }
//   });
// });

document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openVideoBtn');
  const modal = document.getElementById('videoModal');
  const closeBtn = document.getElementById('closeModal');
  const youtubeFrame = document.getElementById('youtubeFrame');

  if (!openBtn || !modal || !closeBtn || !youtubeFrame) return;

  const videoURL = openBtn.getAttribute('data-video-url') || 'https://www.youtube.com/embed/YOUR_VIDEO_ID';

  const closeModalFunc = () => {
    modal.classList.remove('active');
    setTimeout(() => {
      youtubeFrame.src = '';
    }, 300);
  };

  openBtn.addEventListener('click', (event) => {
    event.preventDefault();
    modal.classList.add('active');
    youtubeFrame.src = `${videoURL}?autoplay=1`;
  });

  closeBtn.addEventListener('click', closeModalFunc);

  window.addEventListener('click', (e) => {
    if (e.target === modal) closeModalFunc();
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');

    question.addEventListener('click', () => {
      faqItems.forEach(otherItem => {
        if (otherItem !== item) {
          otherItem.classList.remove('active');
        }
      });

      item.classList.toggle('active');
    });
  });
});

const destinationCarousel = document.querySelector('.carousel');
const leftArrow = document.querySelector('.left-arrow');
const rightArrow = document.querySelector('.right-arrow');

if (leftArrow && rightArrow && destinationCarousel) {
  leftArrow.addEventListener('click', () => {
    destinationCarousel.scrollBy({ left: -300, behavior: 'smooth' });
  });

  rightArrow.addEventListener('click', () => {
    destinationCarousel.scrollBy({ left: 300, behavior: 'smooth' });
  });
}

document.addEventListener('DOMContentLoaded', function () {
  const carouselInner = document.getElementById('videoCarouselInner');
  if (!carouselInner) return;

  const renderMessage = (message, variant = 'muted') => {
    const style = variant === 'error' ? 'color: #ef4444;' : 'color: #6b7280;';
    carouselInner.innerHTML = `<p class="video-carousel-message" style="${style}">${message}</p>`;
  };

  fetch('api/get-videos.php', { cache: 'no-store' })
    .then(response => response.json())
    .then(data => {
      const videos = Array.isArray(data) ? data : [];

      if (videos.length === 0) {
        renderMessage('Videos will appear here as soon as the admin adds them.');
        return;
      }

      carouselInner.innerHTML = '';
      videos.forEach(video => {
        const videoCard = document.createElement('a');
        videoCard.className = 'video-carousel-item';
        videoCard.href = video.watch_url || `https://www.youtube.com/watch?v=${video.video_id}`;
        videoCard.target = '_blank';
        videoCard.rel = 'noopener noreferrer';
        videoCard.setAttribute('aria-label', `Watch ${video.title} on YouTube`);

        const thumb = document.createElement('div');
        thumb.className = 'video-thumb';
        thumb.style.backgroundImage = `url('${video.thumbnail_url || ''}')`;

        const playIcon = document.createElement('span');
        playIcon.className = 'video-thumb__play';
        playIcon.innerHTML = '<i class="fa-solid fa-play"></i>';
        thumb.appendChild(playIcon);

        const title = document.createElement('p');
        title.className = 'video-title';
        title.textContent = video.title;

        videoCard.appendChild(thumb);
        videoCard.appendChild(title);
        carouselInner.appendChild(videoCard);
      });

      initVideoCarousel();
    })
    .catch(() => {
      renderMessage('Error loading videos. Please try again later.', 'error');
    });
});

function initVideoCarousel() {
  const carousel = document.getElementById('videoCarouselInner');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');

  if (!carousel || !prevBtn || !nextBtn) return;

  const slides = carousel.children;
  if (!slides.length) return;

  let currentIndex = 0;
  let slidesPerView = 1;
  let autoplayTimer = null;
  let resizeTimer = null;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const calculateSlidesPerView = () => {
    if (window.innerWidth >= 1200) return 4;
    if (window.innerWidth >= 992) return 3;
    if (window.innerWidth >= 640) return 2;
    return 1;
  };

  const updateSlidesPerView = () => {
    slidesPerView = calculateSlidesPerView();
    carousel.style.setProperty('--videos-per-view', slidesPerView);
    goToSlide(currentIndex);
  };

  const goToSlide = (index) => {
    const maxIndex = Math.max(0, slides.length - slidesPerView);
    if (index > maxIndex) index = 0;
    if (index < 0) index = maxIndex;
    currentIndex = index;
    const offset = (100 / slidesPerView) * currentIndex;
    carousel.style.transform = `translateX(-${offset}%)`;
  };

  const nextSlide = () => {
    goToSlide(currentIndex + 1);
  };

  const prevSlide = () => {
    goToSlide(currentIndex - 1);
  };

  const startAutoplay = () => {
    if (prefersReducedMotion) return;
    stopAutoplay();
    autoplayTimer = setInterval(nextSlide, 6000);
  };

  const stopAutoplay = () => {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  };

  const resetAutoplay = () => {
    stopAutoplay();
    startAutoplay();
  };

  prevBtn.addEventListener('click', () => {
    prevSlide();
    resetAutoplay();
  });

  nextBtn.addEventListener('click', () => {
    nextSlide();
    resetAutoplay();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      prevSlide();
      resetAutoplay();
    } else if (e.key === 'ArrowRight') {
      nextSlide();
      resetAutoplay();
    }
  });

  let touchStartX = 0;
  const swipeThreshold = 50;

  carousel.addEventListener('touchstart', (e) => {
    touchStartX = e.touches[0].clientX;
  }, { passive: true });

  carousel.addEventListener('touchend', (e) => {
    const touchEndX = e.changedTouches[0].clientX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > swipeThreshold) {
      if (diff > 0) {
        nextSlide();
      } else {
        prevSlide();
      }
      resetAutoplay();
    }
  }, { passive: true });

  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      updateSlidesPerView();
    }, 150);
  });

  updateSlidesPerView();
  startAutoplay();
}

document.addEventListener('DOMContentLoaded', function () {
  const subscribeForm = document.getElementById('subscribeForm');

  if (subscribeForm) {
    subscribeForm.addEventListener('submit', async function (e) {
      e.preventDefault();

      const submitBtn = subscribeForm.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;

      try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Subscribing...';

        const formData = new FormData(subscribeForm);

        const response = await fetch('admin/subscribe.php', {
          method: 'POST',
          body: formData,
          headers: {
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        showPopup(data.message, data.success ? 'success' : 'error');

        if (data.success) {
          subscribeForm.reset();
        }
      } catch (error) {
        console.error('Error:', error);
        showPopup('An error occurred. Please try again later.', 'error');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    });
  }
});

function showPopup(message, type = 'success') {
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

  popup.textContent = message;
  popup.style.backgroundColor = type === 'success' ? '#10B981' : '#EF4444';
  popup.style.transform = 'translateX(0)';

  setTimeout(() => {
    popup.style.transform = 'translateX(120%)';
  }, 5000);
}

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

// Redirect to enrollment page when clicking enroll button
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll("#courses-section .enroll-btn").forEach(button => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      const courseCard = button.closest(".course-card");
      const courseName = courseCard.getAttribute("data-course");
      
      // Redirect to enrollment page with course parameter
      window.location.href = `enrollment.html?course=${encodeURIComponent(courseName)}`;
    });
  });
});