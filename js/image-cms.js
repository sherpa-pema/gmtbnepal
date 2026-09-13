/**
 * GNARLY MTB NEPAL - Frontend Image CMS Hydration Engine
 * Dynamically swaps image slots and renders custom gallery dispatches
 * with zero flicker and 100% resilient fallback to static assets.
 */

(function () {
  'use strict';

  const CACHE_KEY = 'gnarly_site_images_cache';
  const CACHE_TIME_KEY = 'gnarly_site_images_time';
  const CACHE_TTL = 60 * 1000; // 1 minute revalidation interval

  // Determine root relative prefix based on current pathname
  function getRootPrefix() {
    const path = window.location.pathname;
    const depth = (path.match(/\//g) || []).length;
    // If running in root or at index.html
    return '';
  }

  // Hydrate DOM elements matching data-cms-slot
  function hydrateSlots(slots) {
    if (!slots || typeof slots !== 'object') return;

    for (const [slotKey, slotData] of Object.entries(slots)) {
      if (!slotData || !slotData.url) continue;

      const elements = document.querySelectorAll(`[data-cms-slot="${slotKey}"]`);
      elements.forEach((el) => {
        const customUrl = slotData.url;

        if (el.tagName === 'IMG') {
          if (el.getAttribute('src') !== customUrl) {
            el.src = customUrl;
          }
          if (slotData.alt && (!el.getAttribute('alt') || el.getAttribute('data-cms-auto-alt'))) {
            el.alt = slotData.alt;
            el.setAttribute('data-cms-auto-alt', 'true');
          }
        } else if (el.tagName === 'SOURCE') {
          el.srcset = customUrl;
        } else if (el.tagName === 'VIDEO') {
          el.poster = customUrl;
        } else {
          el.style.backgroundImage = `url('${customUrl}')`;
        }
      });
    }
  }

  // Hydrate Gallery if on gallery.html
  function hydrateGallery(galleryItems) {
    if (!galleryItems || !Array.isArray(galleryItems) || galleryItems.length === 0) return;

    const galleryContainer = document.getElementById('gallery-container');
    if (!galleryContainer) return;

    galleryItems.forEach((item, index) => {
      const existing = galleryContainer.querySelector(`[data-custom-gal-id="${item.id}"]`);
      if (existing) return;

      const card = document.createElement('div');
      card.className = 'gallery-card group';
      card.setAttribute('data-custom-gal-id', item.id);
      card.setAttribute('data-category', item.category || 'tours-2024');
      card.setAttribute('data-title', item.title || 'Himalayan Tour Dispatch');
      card.setAttribute('data-src', item.url);

      card.innerHTML = `
        <div class="relative overflow-hidden bg-[#1E3A5F]">
          <img 
            src="${item.url}" 
            alt="${item.alt || item.title || 'Himalayan Tour Dispatch'}" 
            loading="lazy" 
            decoding="async" 
            class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500" 
          />
          <div class="gallery-overlay">
            <div class="w-10 h-10 rounded-full bg-black/60 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white group-hover:text-[#F5EFEB] group-hover:border-[#F5EFEB] group-hover:scale-110 transition-all duration-300 shadow-xl">
              <i data-lucide="maximize-2" class="w-4 h-4"></i>
            </div>
          </div>
        </div>
      `;

      // Prepend to show custom dispatches at top of masonry grid
      galleryContainer.prepend(card);
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  // Step 1: Apply immediately from localStorage (instant zero-flicker load)
  try {
    const cached = localStorage.getItem(CACHE_KEY);
    if (cached) {
      const data = JSON.parse(cached);
      if (data && data.slots) hydrateSlots(data.slots);
      if (data && data.gallery) hydrateGallery(data.gallery);
    }
  } catch (e) {
    // LocalStorage failure fallback
  }

  // Step 2: Fetch fresh data from backend API
  async function fetchFreshImages() {
    try {
      const apiUrl = 'api/get-images.php?v=' + Date.now();
      const res = await fetch(apiUrl, { cache: 'no-store' });
      if (!res.ok) return;

      const data = await res.json();
      if (data && data.success) {
        try {
          localStorage.setItem(CACHE_KEY, JSON.stringify(data));
          localStorage.setItem(CACHE_TIME_KEY, Date.now().toString());
        } catch (e) {}

        hydrateSlots(data.slots);
        hydrateGallery(data.gallery);
      }
    } catch (err) {
      // Offline or network error: existing static files continue to render seamlessly
    }
  }

  // Initialize on DOM ready or window load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fetchFreshImages);
  } else {
    fetchFreshImages();
  }
})();
