/**
 * GNARLY MTB NEPAL - Frontend Application Scripts
 * High-octane interactive controls, video triggers, modal dialogues, and responsive logic.
 */

// Prevent browser from executing jarring animated scroll restorations
if ("scrollRestoration" in history) {
  history.scrollRestoration = "manual";
}

function handleInitialScroll() {
  const hash = window.location.hash;
  if (hash && hash !== "#" && !hash.startsWith("#booking-modal")) {
    try {
      const targetElement = document.querySelector(hash);
      if (targetElement) {
        setTimeout(() => {
          targetElement.scrollIntoView({ behavior: "smooth", block: "start" });
        }, 150);
      }
    } catch (e) {}
  }
}

function initAll() {
  initHeroVideo();
  initStickyNav();
  initMobileMenu();
  initScrollspy();
  initVideoHover();
  initTourSlideshows();
  initAccordions();
  initBookingModal();
  initTestimonialSlider();
  initCrewCarousel();
  initScheduleFilters();
  initScrollChevron();
  initSmoothScrollLinks();
  initFaqFeatures();
  handleInitialScroll();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initAll);
} else {
  initAll();
}

/* 0. Hero Background Video Smooth Handler */
function initHeroVideo() {
  const heroVideo = document.getElementById("hero-video");
  if (!heroVideo) return;

  heroVideo.muted = true;
  heroVideo.defaultMuted = true;

  const playVideo = () => {
    const playPromise = heroVideo.play();
    if (playPromise !== undefined) {
      playPromise
        .then(() => {
          heroVideo.style.opacity = "1";
        })
        .catch(() => {
          // If browser strictly blocks autoplay, trigger upon first touch or click
          const onFirstTouch = () => {
            heroVideo.play().then(() => {
              heroVideo.style.opacity = "1";
            }).catch(() => {});
            window.removeEventListener("touchstart", onFirstTouch);
            window.removeEventListener("click", onFirstTouch);
          };
          window.addEventListener("touchstart", onFirstTouch, { once: true, passive: true });
          window.addEventListener("click", onFirstTouch, { once: true, passive: true });
        });
    }
  };

  if (heroVideo.readyState >= 2) {
    playVideo();
  } else {
    heroVideo.addEventListener("canplay", playVideo, { once: true });
  }

  // Optimize battery & performance when user switches tabs
  document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
      heroVideo.pause();
    } else {
      heroVideo.play().catch(() => {});
    }
  });
}

/* 1. Sticky Frosted Header */
function initStickyNav() {
  const navbar = document.getElementById("main-navbar");
  if (!navbar) return;

  const handleScroll = () => {
    if (window.scrollY > 50) {
      navbar.classList.add("navbar-scrolled");
      navbar.classList.remove("navbar-transparent");
    } else {
      navbar.classList.remove("navbar-scrolled");
      navbar.classList.add("navbar-transparent");
    }
  };

  window.addEventListener("scroll", handleScroll, { passive: true });
  handleScroll();
}

/* 2. Mobile Drawer Navigation */
function initMobileMenu() {
  const toggleBtn = document.getElementById("mobile-menu-toggle");
  const drawer = document.getElementById("mobile-drawer");
  const backdrop = document.getElementById("mobile-drawer-backdrop");
  const closeBtn = document.getElementById("mobile-drawer-close");
  const drawerLinks = document.querySelectorAll(".mobile-drawer-link");

  if (!toggleBtn || !drawer) return;

  const openDrawer = () => {
    drawer.classList.remove("translate-x-full");
    if (backdrop) {
      backdrop.classList.remove("opacity-0", "pointer-events-none");
      backdrop.classList.add("opacity-100", "pointer-events-auto");
    }
    document.body.style.overflow = "hidden";
  };

  const closeDrawer = () => {
    drawer.classList.add("translate-x-full");
    if (backdrop) {
      backdrop.classList.add("opacity-0", "pointer-events-none");
      backdrop.classList.remove("opacity-100", "pointer-events-auto");
    }
    // Only restore scroll if booking modal is not open
    const modal = document.getElementById("booking-modal");
    if (!modal || !modal.classList.contains("open")) {
      document.body.style.overflow = "";
    }
  };

  toggleBtn.addEventListener("click", openDrawer);
  if (closeBtn) closeBtn.addEventListener("click", closeDrawer);
  if (backdrop) backdrop.addEventListener("click", closeDrawer);
  drawerLinks.forEach(link => link.addEventListener("click", closeDrawer));

  // If user clicks a modal button inside drawer, close the drawer first
  const drawerModalTriggers = drawer.querySelectorAll("[data-open-modal]");
  drawerModalTriggers.forEach(btn => btn.addEventListener("click", closeDrawer));

  // Escape key handler
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !drawer.classList.contains("translate-x-full")) {
      closeDrawer();
    }
  });
}

/* 3. Viewport Scrollspy Reveals */
function initScrollspy() {
  const elements = document.querySelectorAll(".reveal-init");
  if (!elements.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("revealed");
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.01, rootMargin: "0px 0px 80px 0px" }
  );

  elements.forEach((el) => observer.observe(el));
}

/* 4. Video-on-Hover Playback */
function initVideoHover() {
  const tourCards = document.querySelectorAll(".tour-card");

  tourCards.forEach((card) => {
    const video = card.querySelector(".tour-video-preview");
    if (!video) return;

    card.addEventListener("mouseenter", () => {
      video.play().catch(() => {});
    });

    card.addEventListener("mouseleave", () => {
      video.pause();
      video.currentTime = 0;
    });
  });
}

/* 4b. Automated Tour Card Image Slideshow */
function initTourSlideshows() {
  const slideshows = document.querySelectorAll(".tour-slideshow");
  if (!slideshows.length) return;

  slideshows.forEach((slideshow) => {
    if (slideshow.dataset.initialized === "true") return;
    slideshow.dataset.initialized = "true";

    const slides = Array.from(slideshow.querySelectorAll(".tour-slide-img"));
    const dots = Array.from(slideshow.querySelectorAll(".indicator-dot"));
    if (slides.length <= 1) return;

    let currentIndex = 0;
    const intervalMs = parseInt(slideshow.getAttribute("data-interval"), 10) || 2000;

    // Initialize initial active states
    slides.forEach((slide, i) => {
      if (i === 0) {
        slide.classList.add("active");
        slide.style.opacity = "1";
        slide.style.zIndex = "2";
      } else {
        slide.classList.remove("active");
        slide.style.opacity = "0";
        slide.style.zIndex = "1";
      }
    });

    if (dots.length) {
      dots.forEach((dot, i) => {
        if (i === 0) {
          dot.classList.add("active");
          dot.style.backgroundColor = "#FFC700";
          dot.style.borderColor = "#FFC700";
          dot.style.transform = "scale(1.25)";
        } else {
          dot.classList.remove("active");
          dot.style.backgroundColor = "rgba(255, 255, 255, 0.4)";
          dot.style.borderColor = "rgba(255, 255, 255, 0.2)";
          dot.style.transform = "scale(1)";
        }
      });
    }

    const nextSlide = () => {
      const prevIndex = currentIndex;
      currentIndex = (currentIndex + 1) % slides.length;

      // Outgoing slide
      const prevSlide = slides[prevIndex];
      prevSlide.classList.remove("active");
      prevSlide.style.opacity = "0";
      prevSlide.style.zIndex = "1";
      if (dots[prevIndex]) {
        dots[prevIndex].classList.remove("active");
        dots[prevIndex].style.backgroundColor = "rgba(255, 255, 255, 0.4)";
        dots[prevIndex].style.borderColor = "rgba(255, 255, 255, 0.2)";
        dots[prevIndex].style.transform = "scale(1)";
      }

      // Incoming slide
      const currentSlide = slides[currentIndex];
      currentSlide.classList.add("active");
      currentSlide.style.opacity = "1";
      currentSlide.style.zIndex = "2";
      if (dots[currentIndex]) {
        dots[currentIndex].classList.add("active");
        dots[currentIndex].style.backgroundColor = "#FFC700";
        dots[currentIndex].style.borderColor = "#FFC700";
        dots[currentIndex].style.transform = "scale(1.25)";
      }
    };

    setInterval(nextSlide, intervalMs);
  });
}

/* 5. Collapsible Accordions */
function initAccordions() {
  const accordionItems = document.querySelectorAll(".accordion-item");

  accordionItems.forEach((item) => {
    const trigger = item.querySelector(".accordion-trigger");
    if (!trigger) return;

    trigger.addEventListener("click", () => {
      const isActive = item.classList.contains("active");

      // Close other open accordions in the same group
      accordionItems.forEach((other) => {
        if (other !== item) other.classList.remove("active");
      });

      if (!isActive) {
        item.classList.add("active");
      } else {
        item.classList.remove("active");
      }
    });
  });
}

/* 6. Tour Quick-View & Booking Modal */
const TOURS_DATABASE = {
  "enduro-thin-air": {
    title: "ENDURO THIN AIR",
    region: "Lower Mustang, Nepal",
    duration: "10 Days",
    elevation: "4,200m -> 2,800m Descent",
    terrain: "Singletrack, Big Mountain Scree, Ancient Trade Paths",
    season: "Spring & Autumn",
    description: "The signature Lower Mustang singletrack tour dropping from high alpine desert into ancient pine forests and river gorges. Featuring Lubra Pass, Smooth Criminal, and Marpha.",
    highlights: ["Shuttle-supported descents", "Marpha apple orchards & local culture", "Chasing Capra & Black Yak Trail (3500m+)"]
  },
  "everest-express": {
    title: "EVEREST EXPRESS",
    region: "Solukhumbu / Khumbu, Nepal",
    duration: "09 Nights 10 Days",
    elevation: "Up to 4,050m Peak Elevation (Pikey Peak)",
    terrain: "Alpine Ridge Singletracks, 16+ Ratnange Lines, High Forest Flow",
    season: "Spring & Autumn",
    description: "A fast-paced high-altitude MTB adventure through the heart of the Khumbu region beneath the world's highest peaks, with scenic heli flight and Sherpa hospitality.",
    highlights: ["1-hour Heli flight to Phaplu", "16+ Handcrafted Ratnange singletrack lines", "Pikey Peak 4,050m sunrise & epic descent"]
  },
  "enduro-thin-air-ultimate": {
    title: "ENDURO THIN AIR : ULTIMATE",
    region: "Upper & Lower Mustang, Nepal",
    duration: "11 Nights 12 Days",
    elevation: "4,200m Peak -> 5,000m+ Total Descent",
    terrain: "High Alpine Singletrack, Scree Chutes, Ancient Cliff Trails",
    season: "Spring & Autumn",
    description: "The pinnacle Himalayan enduro tour traversing Upper and Lower Mustang. From the ancient walled kingdom of Lo Manthang down through the dramatic Kali Gandaki gorge.",
    highlights: ["Signature Lubra Trail & Lo Free Ride Heaven", "4,200m Thin Air summit & 5,000m+ descent", "Supported 4x4 shuttle & luggage transfers"]
  },
  "mustang-e-motion": {
    title: "MUSTANG E-MOTION (E-MTB)",
    region: "Upper Mustang & Lo Manthang",
    duration: "12 Days",
    elevation: "High Alpine Passes (4,000m+)",
    terrain: "High-torque canyon trails, dirt roads & remote singletrack",
    season: "Spring, Summer, Autumn",
    description: "The ultimate electric mountain biking adventure conquering dramatic canyons, sky caves, and the ancient walled Kingdom of Lo Manthang.",
    highlights: ["Premium full-suspension E-MTB fleet", "Exploration of forbidden kingdom Lo Manthang", "Supported battery recharge logistics"]
  },
  "moto-mustang": {
    title: "HIMALAYAN MOTO HOLIDAYS “HELLO MOTO”",
    region: "Kathmandu, Pokhara & Mustang Valley",
    duration: "10 Nights 11 Days",
    elevation: "3,700m (Rebel's Hideout & Lo Manthang)",
    terrain: "On and off road, riverbeds, mountain passes & scenic highways",
    season: "Spring (Mar-May) & Autumn (Oct-Dec)",
    description: "An unforgettable journey through Nepal, from Kathmandu and Pokhara to the stunning landscapes of the remote Mustang region.",
    highlights: ["Honda CRF & CF Moto fleet", "Ancient walled kingdom Lo Manthang", "Support 4x4 & mobile mechanic crew"]
  },
  "hello-moto": {
    title: "HIMALAYAN MOTO HOLIDAYS “HELLO MOTO”",
    region: "Kathmandu, Pokhara & Mustang Valley",
    duration: "10 Nights 11 Days",
    elevation: "3,700m (Rebel's Hideout & Lo Manthang)",
    terrain: "On and off road, riverbeds, mountain passes & scenic highways",
    season: "Spring (Mar-May) & Autumn (Oct-Dec)",
    description: "An unforgettable journey through Nepal, from Kathmandu and Pokhara to the stunning landscapes of the remote Mustang region.",
    highlights: ["Honda CRF & CF Moto fleet", "Ancient walled kingdom Lo Manthang", "Support 4x4 & mobile mechanic crew"]
  },
  "himalayan-enduro": {
    title: "THE HIMALAYAN ENDURO RACE",
    region: "Nagarkot Hills & Mustang",
    duration: "5 Days Event",
    elevation: "Multi-stage technical enduro descents",
    terrain: "Loamy pine singletrack, rock gardens, technical drops",
    season: "Autumn 2026",
    description: "Nepal's premier international multi-stage mountain bike enduro race event. Race with global riders across raw Himalayan topography.",
    highlights: ["Chrono-timed stages", "International rider festival", "Full medical & marshal support"]
  },
  "g-mtb-skills": {
    title: "G MTB SKILLS CLINICS",
    region: "Kathmandu Valley & Trail Centers",
    duration: "1 to 3 Days",
    elevation: "Progression Trail Networks",
    terrain: "Pumptrack, berms, switchbacks, steep rock gardens",
    season: "Year-Round",
    description: "Professional PMBIA-certified skills coaching. Master body positioning, braking control, high-speed cornering, and steep alpine terrain.",
    highlights: ["1-on-1 and small group coaching", "Video analysis & feedback", "Beginner to advanced modules"]
  }
};

function initBookingModal() {
  const modal = document.getElementById("booking-modal");
  const modalCloseBtn = document.getElementById("modal-close-btn");
  const tourSelect = document.getElementById("modal-tour-select");
  const dateInput = document.getElementById("modal-date-input");
  const bookingForm = document.getElementById("booking-form");

  if (!modal) return;

  // Open modal triggers
  document.querySelectorAll("[data-open-modal]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const tourKey = btn.getAttribute("data-tour");
      const defaultDate = btn.getAttribute("data-date");

      if (tourSelect && tourKey && TOURS_DATABASE[tourKey]) {
        tourSelect.value = tourKey;
      }
      if (dateInput && defaultDate) {
        dateInput.value = defaultDate;
      }

      modal.classList.add("open");
      document.body.style.overflow = "hidden";
    });
  });

  const closeModal = () => {
    modal.classList.remove("open");
    document.body.style.overflow = "";
  };

  if (modalCloseBtn) modalCloseBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  // Handle Form Submission
  if (bookingForm) {
    bookingForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const name = document.getElementById("form-name")?.value || "";
      const email = document.getElementById("form-email")?.value || "";
      const phone = document.getElementById("form-phone")?.value || "";
      const tour = tourSelect ? tourSelect.options[tourSelect.selectedIndex]?.text : "Tour";
      const date = dateInput?.value || "Flexible";
      const riders = document.getElementById("form-riders")?.value || "1";

      // Form validation & direct WhatsApp message redirect
      const message = `Namaste Gnarly MTB! I would like to book the ${tour} for ${riders} rider(s) on ${date}.\n\nName: ${name}\nEmail: ${email}\nWhatsApp: ${phone}`;
      const waUrl = `https://wa.me/9779803661496?text=${encodeURIComponent(message)}`;

      window.open(waUrl, "_blank");
      closeModal();
      alert("Thank you! Opening WhatsApp to finalize your tour details with Shyam & the Gnarly crew.");
    });
  }
}

/* 7. Testimonial Slider */
function initTestimonialSlider() {
  const track = document.querySelector(".testimonial-track");
  const slides = document.querySelectorAll(".testimonial-slide");
  const prevBtns = document.querySelectorAll(".testimonial-prev-btn, #testimonial-prev-btn");
  const nextBtns = document.querySelectorAll(".testimonial-next-btn, #testimonial-next-btn");
  const sliderFrame = track ? track.closest(".bg-\\[\\#141414\\]") || track.parentElement : null;
  const container = track ? track.parentElement : null;
  if (!track || !slides.length) return;

  let current = 0;
  let timer;

  const updateSlideHeight = (index) => {
    if (!container || !slides[index]) return;
    const targetSlide = slides[index];
    const h = targetSlide.offsetHeight || targetSlide.scrollHeight;
    if (h > 0) {
      container.style.minHeight = h + "px";
    }
  };

  const showSlide = (index) => {
    if (index < 0) index = slides.length - 1;
    if (index >= slides.length) index = 0;

    slides.forEach((s, i) => {
      if (i === index) {
        s.classList.add("active");
      } else {
        s.classList.remove("active");
      }
    });

    track.style.transform = `translateX(-${index * 100}%)`;
    current = index;
    updateSlideHeight(index);
  };

  prevBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      clearInterval(timer);
      showSlide(current - 1);
      startAuto();
    });
  });

  nextBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      clearInterval(timer);
      showSlide(current + 1);
      startAuto();
    });
  });

  const nextSlide = () => {
    const next = (current + 1) % slides.length;
    showSlide(next);
  };

  const startAuto = () => {
    clearInterval(timer);
    timer = setInterval(nextSlide, 7000);
  };

  // Pause on hover
  if (sliderFrame) {
    sliderFrame.addEventListener("mouseenter", () => clearInterval(timer));
    sliderFrame.addEventListener("mouseleave", () => startAuto());

    // Touch swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    sliderFrame.addEventListener("touchstart", (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    sliderFrame.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].screenX;
      const swipeDistance = touchEndX - touchStartX;
      if (Math.abs(swipeDistance) > 40) {
        clearInterval(timer);
        if (swipeDistance < 0) {
          // Swiped left -> next slide
          showSlide(current + 1);
        } else {
          // Swiped right -> prev slide
          showSlide(current - 1);
        }
        startAuto();
      }
    }, { passive: true });
  }

  window.addEventListener("resize", () => updateSlideHeight(current), { passive: true });
  window.addEventListener("load", () => updateSlideHeight(current), { passive: true });

  showSlide(0);
  startAuto();
}

/* 7.1 Meet The Crew Carousel & Horizontal Scroll */
function initCrewCarousel() {
  const track = document.getElementById("crew-scroll-track");
  const prevBtn = document.getElementById("crew-prev-btn");
  const nextBtn = document.getElementById("crew-next-btn");
  const dots = document.querySelectorAll(".crew-dot");
  if (!track) return;

  const cards = track.querySelectorAll(".crew-card");
  if (!cards.length) return;

  let currentIndex = 0;

  function getActiveIndex() {
    const scrollLeft = track.scrollLeft;
    let activeIdx = 0;
    let minDistance = Infinity;
    cards.forEach((card, idx) => {
      const cardOffset = card.offsetLeft - track.offsetLeft;
      const distance = Math.abs(cardOffset - scrollLeft);
      if (distance < minDistance) {
        minDistance = distance;
        activeIdx = idx;
      }
    });
    return activeIdx;
  }

  function updateDots(index) {
    currentIndex = index;
    dots.forEach((dot, idx) => {
      if (idx === currentIndex) {
        dot.classList.add("bg-[#FFC700]", "w-8");
        dot.classList.remove("bg-white/40", "w-2.5");
      } else {
        dot.classList.remove("bg-[#FFC700]", "w-8");
        dot.classList.add("bg-white/40", "w-2.5");
      }
    });
  }

  function scrollToIndex(index) {
    if (index < 0) index = cards.length - 1;
    if (index >= cards.length) index = 0;

    currentIndex = index;
    const targetCard = cards[index];
    if (targetCard) {
      const targetLeft = targetCard.offsetLeft - track.offsetLeft;
      track.scrollTo({
        left: targetLeft,
        behavior: "smooth"
      });
    }
    updateDots(index);
  }

  const prevBtns = document.querySelectorAll(".crew-prev-btn, #crew-prev-btn");
  const nextBtns = document.querySelectorAll(".crew-next-btn, #crew-next-btn");

  prevBtns.forEach((btn) => {
    btn.removeAttribute("disabled");
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      const current = getActiveIndex();
      let target = current - 1;
      if (target < 0) target = cards.length - 1;
      scrollToIndex(target);
    });
  });

  nextBtns.forEach((btn) => {
    btn.removeAttribute("disabled");
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      const current = getActiveIndex();
      let target = current + 1;
      if (target >= cards.length) target = 0;
      scrollToIndex(target);
    });
  });

  dots.forEach((dot) => {
    dot.addEventListener("click", function (e) {
      e.preventDefault();
      const idx = parseInt(dot.getAttribute("data-index") || "0", 10);
      scrollToIndex(idx);
    });
  });

  // Track scroll listener
  let scrollTimeout;
  track.addEventListener("scroll", () => {
    if (scrollTimeout) cancelAnimationFrame(scrollTimeout);
    scrollTimeout = requestAnimationFrame(() => {
      const activeIdx = getActiveIndex();
      updateDots(activeIdx);
    });
  }, { passive: true });

  // Mouse Drag support
  let isDown = false;
  let startX = 0;
  let scrollStart = 0;
  let hasDragged = false;

  track.addEventListener("mousedown", (e) => {
    isDown = true;
    hasDragged = false;
    track.classList.add("is-dragging");
    startX = e.pageX - track.offsetLeft;
    scrollStart = track.scrollLeft;
  });

  window.addEventListener("mouseup", () => {
    if (isDown) {
      isDown = false;
      track.classList.remove("is-dragging");
      if (hasDragged) {
        const activeIdx = getActiveIndex();
        scrollToIndex(activeIdx);
      }
    }
  });

  track.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - track.offsetLeft;
    const walk = (x - startX) * 1.3;
    if (Math.abs(walk) > 8) {
      hasDragged = true;
    }
    track.scrollLeft = scrollStart - walk;
  });

  track.addEventListener("click", (e) => {
    if (hasDragged) {
      e.preventDefault();
      e.stopPropagation();
      hasDragged = false;
    }
  }, true);

  updateDots(0);
}

/* 8. Schedule Filter Tabs */
function initScheduleFilters() {
  const filterBtns = document.querySelectorAll(".schedule-filter-btn");
  const rows = document.querySelectorAll(".schedule-row");

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterBtns.forEach((b) => {
        b.classList.remove("bg-[#FFC700]", "text-black");
        b.classList.add("text-white/70");
      });
      btn.classList.add("bg-[#FFC700]", "text-black");
      btn.classList.remove("text-white/70");

      const filter = btn.getAttribute("data-filter");

      rows.forEach((row) => {
        const season = row.getAttribute("data-season");
        if (filter === "all" || season === filter) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });
  });
}

/* 9. Hero Scroll Chevron Button */
function initScrollChevron() {
  const chevron = document.getElementById("hero-scroll-trigger");
  if (!chevron) return;

  chevron.addEventListener("click", () => {
    const target = document.getElementById("tours-section") || document.getElementById("marquee-banner");
    if (target) {
      target.scrollIntoView({ behavior: "smooth" });
    } else {
      window.scrollBy({ top: window.innerHeight, behavior: "smooth" });
    }
  });
}

/* 10. Smooth Scrolling for In-Page Anchor Links */
function initSmoothScrollLinks() {
  document.addEventListener("click", (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;

    const targetId = link.getAttribute("href");
    if (!targetId || targetId === "#" || targetId.startsWith("#booking-modal")) return;

    try {
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        targetElement.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    } catch (err) {
      // Ignore invalid selectors
    }
  });
}

/* 11. FAQ Search, Category Filtering & Expand/Collapse */
function initFaqFeatures() {
  const faqItems = document.querySelectorAll(".faq-item");
  const searchInput = document.getElementById("faq-search-input");
  const filterBtns = document.querySelectorAll(".faq-filter-btn");
  const expandAllBtn = document.getElementById("faq-expand-all");
  const collapseAllBtn = document.getElementById("faq-collapse-all");
  const noResultsMsg = document.getElementById("faq-no-results");

  if (!faqItems.length) return;

  let currentCategory = "all";
  let currentSearch = "";

  const applyFilters = () => {
    let visibleCount = 0;
    faqItems.forEach((item) => {
      const category = item.getAttribute("data-category") || "";
      const text = item.textContent.toLowerCase();

      const matchesCategory = currentCategory === "all" || category.includes(currentCategory);
      const matchesSearch = !currentSearch || text.includes(currentSearch);

      if (matchesCategory && matchesSearch) {
        item.style.display = "";
        visibleCount++;
      } else {
        item.style.display = "none";
      }
    });

    if (noResultsMsg) {
      if (visibleCount === 0) {
        noResultsMsg.classList.remove("hidden");
      } else {
        noResultsMsg.classList.add("hidden");
      }
    }
  };

  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      currentSearch = e.target.value.trim().toLowerCase();
      applyFilters();
    });
  }

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterBtns.forEach((b) => {
        b.classList.remove("bg-[#FFC700]", "text-black");
        b.classList.add("bg-[#141414]", "text-gray-300");
      });
      btn.classList.add("bg-[#FFC700]", "text-black");
      btn.classList.remove("bg-[#141414]", "text-gray-300");

      currentCategory = btn.getAttribute("data-category") || "all";
      applyFilters();
    });
  });

  if (expandAllBtn) {
    expandAllBtn.addEventListener("click", () => {
      faqItems.forEach((item) => {
        if (item.style.display !== "none") {
          item.classList.add("active");
        }
      });
    });
  }

  if (collapseAllBtn) {
    collapseAllBtn.addEventListener("click", () => {
      faqItems.forEach((item) => {
        item.classList.remove("active");
      });
    });
  }
}

