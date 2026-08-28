/**
 * GNARLY MTB NEPAL - Frontend Application Scripts
 * High-octane interactive controls, video triggers, modal dialogues, and responsive logic.
 */

document.addEventListener("DOMContentLoaded", () => {
  initHeroVideo();
  initStickyNav();
  initMobileMenu();
  initScrollspy();
  initVideoHover();
  initAccordions();
  initBookingModal();
  initTestimonialSlider();
  initScheduleFilters();
  initScrollChevron();
});

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
  const closeBtn = document.getElementById("mobile-drawer-close");
  const drawerLinks = document.querySelectorAll(".mobile-drawer-link");

  if (!toggleBtn || !drawer) return;

  const openDrawer = () => {
    drawer.classList.remove("translate-x-full");
    document.body.style.overflow = "hidden";
  };

  const closeDrawer = () => {
    drawer.classList.add("translate-x-full");
    document.body.style.overflow = "";
  };

  toggleBtn.addEventListener("click", openDrawer);
  if (closeBtn) closeBtn.addEventListener("click", closeDrawer);
  drawerLinks.forEach(link => link.addEventListener("click", closeDrawer));
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
    { threshold: 0.15, rootMargin: "0px 0px -50px 0px" }
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
    description: "The signature Lower Mustang singletrack expedition dropping from high alpine desert into ancient pine forests and river gorges. Featuring Lubra Pass, Smooth Criminal, and Marpha.",
    highlights: ["Shuttle-supported descents", "Marpha apple orchards & local culture", "Chasing Capra & Black Yak Trail (3500m+)"]
  },
  "everest-express": {
    title: "EVEREST EXPRESS",
    region: "Solukhumbu / Khumbu, Nepal",
    duration: "9 Days",
    elevation: "Up to 3,900m Peak Elevation",
    terrain: "Alpine Ridge Singletracks, Rocky Steps, Forest Flow",
    season: "Spring & Autumn",
    description: "A fast-paced high-altitude expedition directly under Mount Everest, Ama Dablam, and Lhotse with rich Sherpa cultural hospitality.",
    highlights: ["Panoramic 8,000m peak views", "Ancient monastery visits", "Epic alpine ridge descents"]
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
    title: "MOTO MUSTANG",
    region: "Mustang Valley, Nepal",
    duration: "8 Days",
    elevation: "High Alpine River Crossings",
    terrain: "Broken tarmac, river beds, scree, dirt trails",
    season: "Spring, Summer, Autumn",
    description: "High-octane dual-sport and enduro motorcycle adventure tackling remote riverbeds, high-altitude desert plateaus, and suspension bridges.",
    highlights: ["Fully equipped enduro bikes", "Mechanic & 4x4 support vehicle", "Authentic Himalayan lodge stays"]
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
      const tour = tourSelect ? tourSelect.options[tourSelect.selectedIndex]?.text : "Expedition";
      const date = dateInput?.value || "Flexible";
      const riders = document.getElementById("form-riders")?.value || "1";

      // Form validation & direct WhatsApp or email redirect
      const message = `Namaste Gnarly MTB! I would like to book the ${tour} for ${riders} rider(s) on ${date}. My name is ${name} (${email}).`;
      const waUrl = `https://wa.me/9779803661496?text=${encodeURIComponent(message)}`;

      window.open(waUrl, "_blank");
      closeModal();
      alert("Thank you! Opening WhatsApp to finalize your expedition details with Shyam & the Gnarly crew.");
    });
  }
}

/* 7. Testimonial Slider */
function initTestimonialSlider() {
  const slides = document.querySelectorAll(".testimonial-slide");
  const dots = document.querySelectorAll(".testimonial-dot");
  if (!slides.length) return;

  let current = 0;
  let timer;

  const showSlide = (index) => {
    slides.forEach((s, i) => {
      s.classList.toggle("hidden", i !== index);
      s.classList.toggle("block", i === index);
    });
    dots.forEach((d, i) => {
      if (i === index) {
        d.classList.add("bg-[#FFC700]", "w-8");
        d.classList.remove("bg-white/40", "w-3");
      } else {
        d.classList.remove("bg-[#FFC700]", "w-8");
        d.classList.add("bg-white/40", "w-3");
      }
    });
    current = index;
  };

  dots.forEach((dot, idx) => {
    dot.addEventListener("click", () => {
      clearInterval(timer);
      showSlide(idx);
      startAuto();
    });
  });

  const nextSlide = () => {
    const next = (current + 1) % slides.length;
    showSlide(next);
  };

  const startAuto = () => {
    timer = setInterval(nextSlide, 6500);
  };

  showSlide(0);
  startAuto();
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
