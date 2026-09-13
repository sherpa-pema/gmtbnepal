<?php
require_once __DIR__ . '/../data/config.php';
require_admin_login('login.php');

$registry = get_images_registry();
$csrfToken = get_csrf_token();
$username = $_SESSION['gnarly_admin_username'] ?? 'admin';

// Calculate storage stats
$uploadFiles = glob(UPLOADS_DIR . '/*.*');
$totalSize = 0;
$uploadCount = 0;
if ($uploadFiles) {
    foreach ($uploadFiles as $f) {
        if (is_file($f) && basename($f) !== '.htaccess') {
            $totalSize += filesize($f);
            $uploadCount++;
        }
    }
}
$storageMb = round($totalSize / (1024 * 1024), 2);

// Calculate active overrides
$activeOverrides = 0;
$totalSlots = count($registry['slots'] ?? []);
foreach (($registry['slots'] ?? []) as $s) {
    if (!empty($s['url'])) {
        $activeOverrides++;
    }
}
$galleryCount = count($registry['gallery'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>IMAGE MANAGEMENT BACKEND | GNARLY MTB NEPAL</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="icon" type="image/png" href="../assets/branding/favicon.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: "#F5EFEB", hover: "#E8DFD8" },
            canvas: "#2A4E7A",
            surface: { DEFAULT: "#1E3A5F", secondary: "#162E4D", elevated: "#345C8C" }
          },
          fontFamily: {
            display: ["Anton", "sans-serif"],
            heading: ["Oswald", "sans-serif"],
            body: ["Roboto", "sans-serif"]
          }
        }
      }
    };
  </script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      background-color: #162E4D;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: rgba(245, 239, 235, 0.2);
      border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: rgba(245, 239, 235, 0.4);
    }
  </style>
</head>
<body class="text-gray-200 antialiased min-h-screen flex flex-col font-body selection:bg-[#F5EFEB] selection:text-black">

  <!-- =========================================================================
       1. TOP APP BAR
       ========================================================================= -->
  <header class="sticky top-0 z-40 bg-[#1E3A5F] border-b border-white/10 shadow-lg backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20">
        
        <!-- Left Brand & Status -->
        <div class="flex items-center space-x-3 sm:space-x-4">
          <a href="../index.html" target="_blank" class="flex items-center space-x-3 group" title="Preview Live Website">
            <img src="../assets/branding/logo.png" alt="Gnarly MTB" class="w-9 h-9 sm:w-10 sm:h-10 object-contain rounded-full border border-white/20 group-hover:scale-105 transition-transform" />
            <div class="hidden sm:block">
              <span class="font-display text-xl sm:text-2xl text-white tracking-wide">
                GNARLY <span class="text-[#F5EFEB]">BACKEND</span>
              </span>
              <span class="block text-[10px] font-heading tracking-widest uppercase text-gray-400">
                Image Management System
              </span>
            </div>
          </a>
        </div>

        <!-- Right User Actions -->
        <div class="flex items-center space-x-3 sm:space-x-4">
          <a href="../index.html" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded border border-white/20 text-xs font-heading uppercase tracking-wider text-white hover:text-[#F5EFEB] hover:border-[#F5EFEB] transition-colors">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            <span>View Live Site</span>
          </a>

          <div class="flex items-center space-x-2 pl-3 border-l border-white/10">
            <div class="hidden md:flex flex-col text-right">
              <span class="text-xs font-heading uppercase tracking-wider text-white font-semibold">
                <?php echo htmlspecialchars($username); ?>
              </span>
              <span class="text-[10px] text-green-400 flex items-center justify-end gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                Admin Active
              </span>
            </div>
            <a href="logout.php" class="p-2 rounded bg-red-950/40 border border-red-500/30 text-red-300 hover:bg-red-900/60 hover:text-white transition-all flex items-center gap-1.5 text-xs font-heading uppercase tracking-wider" title="Sign Out">
              <i data-lucide="log-out" class="w-4 h-4"></i>
              <span class="hidden sm:inline">Logout</span>
            </a>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- =========================================================================
       2. METRIC HIGHLIGHTS STRIP
       ========================================================================= -->
  <div class="bg-[#193254] border-b border-white/5 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        
        <div class="bg-[#1E3A5F] p-3.5 rounded-lg border border-white/10 flex items-center gap-3">
          <div class="w-10 h-10 rounded bg-[#2A4E7A] flex items-center justify-center text-[#F5EFEB] shrink-0">
            <i data-lucide="layout-grid" class="w-5 h-5"></i>
          </div>
          <div>
            <p class="text-[10px] font-heading uppercase tracking-wider text-gray-400">Total Page Slots</p>
            <p class="text-xl font-display text-white" id="stat-total-slots"><?php echo $totalSlots; ?></p>
          </div>
        </div>

        <div class="bg-[#1E3A5F] p-3.5 rounded-lg border border-white/10 flex items-center gap-3">
          <div class="w-10 h-10 rounded bg-green-900/40 border border-green-500/30 flex items-center justify-center text-green-400 shrink-0">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
          </div>
          <div>
            <p class="text-[10px] font-heading uppercase tracking-wider text-gray-400">Custom Overrides</p>
            <p class="text-xl font-display text-green-400" id="stat-custom-slots"><?php echo $activeOverrides; ?></p>
          </div>
        </div>

        <div class="bg-[#1E3A5F] p-3.5 rounded-lg border border-white/10 flex items-center gap-3">
          <div class="w-10 h-10 rounded bg-[#2A4E7A] flex items-center justify-center text-[#F5EFEB] shrink-0">
            <i data-lucide="images" class="w-5 h-5"></i>
          </div>
          <div>
            <p class="text-[10px] font-heading uppercase tracking-wider text-gray-400">Gallery Uploads</p>
            <p class="text-xl font-display text-white" id="stat-gallery-count"><?php echo $galleryCount; ?></p>
          </div>
        </div>

        <div class="bg-[#1E3A5F] p-3.5 rounded-lg border border-white/10 flex items-center gap-3">
          <div class="w-10 h-10 rounded bg-[#2A4E7A] flex items-center justify-center text-[#F5EFEB] shrink-0">
            <i data-lucide="hard-drive" class="w-5 h-5"></i>
          </div>
          <div>
            <p class="text-[10px] font-heading uppercase tracking-wider text-gray-400">Disk Storage</p>
            <p class="text-xl font-display text-white" id="stat-disk-usage"><?php echo $storageMb; ?> <span class="text-xs font-normal text-gray-400">MB</span></p>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- =========================================================================
       3. MAIN BACKEND DASHBOARD CONTENT
       ========================================================================= -->
  <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <!-- Tab navigation -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-4 mb-6">
      <nav class="flex space-x-2" aria-label="Backend Tabs">
        <button id="tab-btn-slots" onclick="switchTab('slots')" class="tab-btn active px-4 py-2 rounded font-heading text-xs uppercase tracking-wider font-semibold transition-all flex items-center gap-2 bg-[#F5EFEB] text-black">
          <i data-lucide="image" class="w-4 h-4"></i>
          <span>Core Website Images</span>
        </button>
        <button id="tab-btn-gallery" onclick="switchTab('gallery')" class="tab-btn px-4 py-2 rounded font-heading text-xs uppercase tracking-wider font-semibold transition-all flex items-center gap-2 text-gray-300 hover:text-white hover:bg-white/5">
          <i data-lucide="grid" class="w-4 h-4"></i>
          <span>Tour Gallery Dispatches</span>
        </button>
        <button id="tab-btn-settings" onclick="switchTab('settings')" class="tab-btn px-4 py-2 rounded font-heading text-xs uppercase tracking-wider font-semibold transition-all flex items-center gap-2 text-gray-300 hover:text-white hover:bg-white/5">
          <i data-lucide="settings" class="w-4 h-4"></i>
          <span>Settings & Security</span>
        </button>
      </nav>

      <!-- Search & Filters (Slots view) -->
      <div id="slots-controls" class="flex flex-wrap items-center gap-2">
        <div class="relative">
          <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input 
            type="text" 
            id="slot-search" 
            placeholder="Search slots (e.g. Everest, Hero)..." 
            oninput="filterSlots()"
            class="pl-8 pr-3 py-1.5 bg-[#1E3A5F] border border-white/15 rounded text-xs text-white placeholder-gray-400 focus:outline-none focus:border-[#F5EFEB] w-48 sm:w-64"
          />
        </div>
        <select id="section-filter" onchange="filterSlots()" class="px-3 py-1.5 bg-[#1E3A5F] border border-white/15 rounded text-xs text-white focus:outline-none focus:border-[#F5EFEB]">
          <option value="all">All Sections</option>
          <option value="Hero">Hero Section</option>
          <option value="Tour: Enduro Thin Air">Tour: Enduro Thin Air</option>
          <option value="Tour: Everest Express">Tour: Everest Express</option>
          <option value="Tour: Hello Moto">Tour: Hello Moto</option>
          <option value="The Crew">The Crew</option>
          <option value="Why Ride With Us">Why Ride With Us</option>
          <option value="custom-only">Custom Overrides Only</option>
        </select>
      </div>
    </div>

    <!-- =========================================================================
         PANEL 1: CORE WEBSITE IMAGE SLOTS
         ========================================================================= -->
    <div id="panel-slots" class="tab-panel">
      
      <div class="mb-4 flex items-center justify-between">
        <p class="text-xs text-gray-400">
          Showing <span id="visible-slots-count" class="text-white font-semibold"><?php echo $totalSlots; ?></span> image slots across the website.
        </p>
        <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
          <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#F5EFEB]"></i>
          Auto-optimizes images before upload for lightning performance
        </span>
      </div>

      <div id="slots-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach (($registry['slots'] ?? []) as $key => $slot): 
            $isCustom = !empty($slot['url']);
            $activeUrl = $isCustom ? '../' . $slot['url'] : '../' . $slot['default'];
            $section = $slot['section'] ?? 'Website';
            $slotName = $slot['name'] ?? $key;
            $altText = $slot['alt'] ?? '';
        ?>
        <div 
          class="slot-card bg-[#1E3A5F] border border-white/10 rounded-xl overflow-hidden shadow-lg transition-all hover:border-white/20 flex flex-col justify-between"
          data-slot-key="<?php echo htmlspecialchars($key); ?>"
          data-section="<?php echo htmlspecialchars($section); ?>"
          data-name="<?php echo htmlspecialchars(strtolower($slotName)); ?>"
          data-is-custom="<?php echo $isCustom ? 'true' : 'false'; ?>"
        >
          <!-- Card Header -->
          <div class="p-4 border-b border-white/10 bg-[#162E4D]/60 flex items-start justify-between gap-2">
            <div>
              <span class="inline-block px-2 py-0.5 rounded text-[10px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-1">
                <?php echo htmlspecialchars($section); ?>
              </span>
              <h3 class="font-heading text-sm font-bold uppercase tracking-wide text-white leading-tight">
                <?php echo htmlspecialchars($slotName); ?>
              </h3>
            </div>
            
            <div class="shrink-0 status-pill">
              <?php if ($isCustom): ?>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                  Custom
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">
                  Default
                </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Image Preview Area -->
          <div class="relative bg-black/40 h-48 flex items-center justify-center overflow-hidden group">
            <img 
              id="preview-img-<?php echo htmlspecialchars($key); ?>"
              src="<?php echo htmlspecialchars($activeUrl); ?>" 
              alt="<?php echo htmlspecialchars($altText); ?>"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
            />
            
            <!-- Direct view link overlay -->
            <a 
              href="<?php echo htmlspecialchars($activeUrl); ?>" 
              target="_blank" 
              class="absolute top-2 right-2 p-1.5 rounded bg-black/70 hover:bg-black text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity"
              title="Open Full Image"
            >
              <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
            </a>

            <!-- Loading overlay -->
            <div id="loader-<?php echo htmlspecialchars($key); ?>" class="absolute inset-0 bg-black/80 flex-col items-center justify-center gap-2 hidden">
              <div class="w-7 h-7 border-2 border-[#F5EFEB] border-t-transparent rounded-full animate-spin"></div>
              <span class="text-xs text-white font-heading uppercase tracking-wider">Uploading...</span>
            </div>
          </div>

          <!-- Card Body & Controls -->
          <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
            
            <!-- Alt Text Editor -->
            <div>
              <label class="block text-[10px] font-heading uppercase tracking-wider text-gray-400 mb-1">
                Alt Text / SEO Description
              </label>
              <div class="flex items-center gap-1.5">
                <input 
                  type="text" 
                  id="alt-input-<?php echo htmlspecialchars($key); ?>"
                  value="<?php echo htmlspecialchars($altText); ?>" 
                  placeholder="Describe image for SEO & accessibility"
                  class="flex-1 px-2.5 py-1.5 bg-[#162E4D] border border-white/15 rounded text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]"
                />
                <button 
                  type="button" 
                  onclick="saveAltText('<?php echo htmlspecialchars($key); ?>')" 
                  class="p-1.5 rounded bg-white/10 hover:bg-white/20 text-white transition-colors text-xs" 
                  title="Save Alt Text"
                >
                  <i data-lucide="check" class="w-4 h-4"></i>
                </button>
              </div>
            </div>

            <!-- Upload / Replace Actions -->
            <div class="pt-2 border-t border-white/10 flex items-center gap-2">
              
              <!-- Replace file input (hidden) -->
              <input 
                type="file" 
                id="file-input-<?php echo htmlspecialchars($key); ?>" 
                accept="image/jpeg,image/png,image/webp" 
                class="hidden" 
                onchange="handleSlotFileUpload('<?php echo htmlspecialchars($key); ?>', this.files[0])" 
              />
              
              <button 
                type="button" 
                onclick="document.getElementById('file-input-<?php echo htmlspecialchars($key); ?>').click()"
                class="flex-1 py-2 px-3 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-1.5 shadow"
              >
                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                <span>Replace Image</span>
              </button>

              <button 
                type="button" 
                id="reset-btn-<?php echo htmlspecialchars($key); ?>"
                onclick="confirmResetSlot('<?php echo htmlspecialchars($key); ?>')"
                class="py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors <?php echo $isCustom ? 'text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer' : 'text-gray-500 opacity-40 cursor-not-allowed'; ?>"
                <?php echo $isCustom ? '' : 'disabled'; ?>
                title="<?php echo $isCustom ? 'Reset to default image' : 'Already using default image'; ?>"
              >
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
              </button>

            </div>

          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- =========================================================================
         PANEL 2: TOUR GALLERY DISPATCHES (gallery.html)
         ========================================================================= -->
    <div id="panel-gallery" class="tab-panel hidden">
      
      <!-- Upload Box for Gallery -->
      <div class="bg-[#1E3A5F] border border-white/15 rounded-xl p-6 shadow-xl mb-8">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-heading text-lg font-bold uppercase tracking-wide text-white flex items-center gap-2">
              <i data-lucide="plus-circle" class="w-5 h-5 text-[#F5EFEB]"></i>
              Upload New Tour Dispatch Photo
            </h2>
            <p class="text-xs text-gray-300 mt-0.5">
              Newly uploaded photos are immediately shown on <a href="../gallery.html" target="_blank" class="text-[#F5EFEB] underline">gallery.html</a> with lightbox support.
            </p>
          </div>
        </div>

        <form id="gallery-upload-form" onsubmit="handleGalleryUpload(event)" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div>
              <label class="block text-xs font-heading uppercase tracking-wider text-gray-300 mb-1">
                Dispatch Title
              </label>
              <input 
                type="text" 
                id="gal-title" 
                required 
                placeholder="e.g. Upper Mustang High Pass Singletrack" 
                class="w-full px-3 py-2 bg-[#162E4D] border border-white/20 rounded text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]"
              />
            </div>

            <div>
              <label class="block text-xs font-heading uppercase tracking-wider text-gray-300 mb-1">
                Tour Category / Tag
              </label>
              <select id="gal-category" class="w-full px-3 py-2 bg-[#162E4D] border border-white/20 rounded text-sm text-white focus:outline-none focus:border-[#F5EFEB]">
                <option value="tours-2024">Himalayan Tours 2024</option>
                <option value="asian-enduro">Asian Enduro Series</option>
                <option value="lower-mustang">Lower Mustang</option>
                <option value="upper-mustang">Upper Mustang</option>
                <option value="everest">Everest Singletrack</option>
                <option value="kathmandu">Kathmandu Valley</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-heading uppercase tracking-wider text-gray-300 mb-1">
                Alt Text (SEO)
              </label>
              <input 
                type="text" 
                id="gal-alt" 
                placeholder="e.g. Guided mountain biking in Lower Mustang" 
                class="w-full px-3 py-2 bg-[#162E4D] border border-white/20 rounded text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]"
              />
            </div>

          </div>

          <!-- Drag and drop zone -->
          <div 
            id="gal-dropzone" 
            onclick="document.getElementById('gal-file-input').click()"
            class="border-2 border-dashed border-white/20 hover:border-[#F5EFEB] rounded-xl p-6 text-center cursor-pointer transition-colors bg-[#162E4D]/40 group"
          >
            <input 
              type="file" 
              id="gal-file-input" 
              accept="image/jpeg,image/png,image/webp" 
              class="hidden" 
              onchange="handleGalleryFilePicked(this.files[0])"
            />
            <div class="flex flex-col items-center justify-center space-y-2">
              <div class="w-12 h-12 rounded-full bg-[#2A4E7A] flex items-center justify-center text-[#F5EFEB] group-hover:scale-110 transition-transform">
                <i data-lucide="image-plus" class="w-6 h-6"></i>
              </div>
              <p id="gal-file-label" class="font-heading text-sm uppercase tracking-wider text-white">
                Drag & Drop high-res photo here, or <span class="text-[#F5EFEB] underline">Browse</span>
              </p>
              <p class="text-[11px] text-gray-400">JPG, PNG, or WebP up to 15MB • Auto-optimized before storage</p>
            </div>
            
            <!-- Selected preview container -->
            <div id="gal-selected-preview" class="hidden mt-4 pt-4 border-t border-white/10 flex items-center justify-center gap-4">
              <img id="gal-thumb-img" src="" alt="Preview" class="h-24 w-auto object-cover rounded shadow" />
              <div class="text-left text-xs">
                <p id="gal-file-name" class="font-semibold text-white"></p>
                <p id="gal-file-size" class="text-gray-400"></p>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button 
              type="submit" 
              id="gal-upload-btn" 
              class="py-2.5 px-6 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider rounded transition-all flex items-center gap-2 shadow-lg"
            >
              <i data-lucide="cloud-upload" class="w-4 h-4"></i>
              <span>Upload Photo to Gallery</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Existing Dynamic Gallery Items -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-heading text-base font-bold uppercase tracking-wider text-white flex items-center gap-2">
            <i data-lucide="images" class="w-4 h-4 text-[#F5EFEB]"></i>
            Custom Uploaded Dispatches (<span id="gallery-items-count"><?php echo count($registry['gallery'] ?? []); ?></span>)
          </h3>
          <a href="../gallery.html" target="_blank" class="text-xs font-heading uppercase tracking-wider text-[#F5EFEB] hover:underline flex items-center gap-1">
            <span>View 270+ Full Tour Gallery</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php if (empty($registry['gallery'])): ?>
          <div id="gallery-empty-state" class="col-span-full bg-[#1E3A5F] border border-white/10 rounded-xl p-12 text-center text-gray-400">
            <i data-lucide="camera-off" class="w-10 h-10 mx-auto text-gray-500 mb-3"></i>
            <p class="font-heading text-sm uppercase tracking-wider text-white">No custom gallery uploads yet</p>
            <p class="text-xs text-gray-400 mt-1">
              Photos uploaded above will appear here and at the top of <code class="text-[#F5EFEB]">gallery.html</code>.
            </p>
          </div>
          <?php else: ?>
            <?php foreach ($registry['gallery'] as $item): ?>
            <div id="gal-card-<?php echo htmlspecialchars($item['id']); ?>" class="bg-[#1E3A5F] border border-white/10 rounded-lg overflow-hidden shadow-md group">
              <div class="relative h-44 bg-black/30 overflow-hidden">
                <img src="../<?php echo htmlspecialchars($item['url']); ?>" alt="<?php echo htmlspecialchars($item['alt'] ?? ''); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                <button 
                  onclick="deleteGalleryItem('<?php echo htmlspecialchars($item['id']); ?>')"
                  class="absolute top-2 right-2 p-1.5 rounded-full bg-red-900/80 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                  title="Delete from Gallery"
                >
                  <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
              </div>
              <div class="p-3">
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-1">
                  <?php echo htmlspecialchars($item['category'] ?? 'Tour'); ?>
                </span>
                <p class="text-xs font-semibold text-white truncate" title="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                  <?php echo htmlspecialchars($item['title'] ?? 'Untitled'); ?>
                </p>
                <p class="text-[10px] text-gray-400 mt-1">
                  <?php echo !empty($item['created_at']) ? date('M j, Y', strtotime($item['created_at'])) : ''; ?>
                </p>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>

    </div>

    <!-- =========================================================================
         PANEL 3: SETTINGS & SECURITY
         ========================================================================= -->
    <div id="panel-settings" class="tab-panel hidden">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Change Credentials Card -->
        <div class="bg-[#1E3A5F] border border-white/15 rounded-xl p-6 shadow-xl">
          <h2 class="font-heading text-lg font-bold uppercase tracking-wide text-white flex items-center gap-2 mb-1">
            <i data-lucide="lock" class="w-5 h-5 text-[#F5EFEB]"></i>
            Update Backend Credentials
          </h2>
          <p class="text-xs text-gray-300 mb-6">
            Change your admin username or password. Store credentials in a safe place.
          </p>

          <form id="credentials-form" onsubmit="handleCredentialsUpdate(event)" class="space-y-4">
            <div>
              <label class="block text-xs font-heading uppercase tracking-wider text-gray-300 mb-1">
                Current Password
              </label>
              <input 
                type="password" 
                id="current-password" 
                required 
                placeholder="••••••••••••" 
                class="w-full px-3 py-2 bg-[#162E4D] border border-white/20 rounded text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]"
              />
            </div>

            <div>
              <label class="block text-xs font-heading uppercase tracking-wider text-gray-300 mb-1">
                New Username
              </label>
              <input 
                type="text" 
                id="new-username" 
                required 
                value="<?php echo htmlspecialchars($username); ?>" 
                class="w-full px-3 py-2 bg-[#162E4D] border border-white/20 rounded text-sm text-white focus:outline-none focus:border-[#F5EFEB]"
              />
            </div>

            <div>
              <label class="block text-xs font-heading uppercase tracking-wider text-gray-300 mb-1">
                New Password
              </label>
              <input 
                type="password" 
                id="new-password" 
                required 
                minlength="6"
                placeholder="At least 6 characters" 
                class="w-full px-3 py-2 bg-[#162E4D] border border-white/20 rounded text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]"
              />
            </div>

            <button 
              type="submit" 
              class="w-full py-2.5 px-4 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-2 shadow"
            >
              <i data-lucide="save" class="w-4 h-4"></i>
              <span>Save New Credentials</span>
            </button>
          </form>
        </div>

        <!-- Server & Disk Maintenance Card -->
        <div class="bg-[#1E3A5F] border border-white/15 rounded-xl p-6 shadow-xl flex flex-col justify-between">
          <div>
            <h2 class="font-heading text-lg font-bold uppercase tracking-wide text-white flex items-center gap-2 mb-1">
              <i data-lucide="hard-drive" class="w-5 h-5 text-[#F5EFEB]"></i>
              cPanel Storage & Maintenance
            </h2>
            <p class="text-xs text-gray-300 mb-4">
              Monitor image storage inside <code class="text-[#F5EFEB]">public_html/uploads/</code>.
            </p>

            <div class="space-y-3 bg-[#162E4D] p-4 rounded-lg border border-white/10 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-400">Total Uploaded Files:</span>
                <span class="font-semibold text-white" id="info-files-count"><?php echo $uploadCount; ?> file(s)</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Total Storage Consumed:</span>
                <span class="font-semibold text-white" id="info-files-size"><?php echo $storageMb; ?> MB</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Auto File Unlinking:</span>
                <span class="font-semibold text-green-400">Active (cleans previous file on replace)</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Server Environment:</span>
                <span class="font-semibold text-white">PHP <?php echo PHP_VERSION; ?></span>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-white/10">
            <button 
              type="button" 
              onclick="cleanOrphanFiles()"
              class="w-full py-2.5 px-4 rounded border border-white/20 text-gray-300 hover:text-white hover:border-white/40 hover:bg-white/5 font-heading text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2"
            >
              <i data-lucide="trash-2" class="w-4 h-4 text-orange-400"></i>
              <span>Clean Unreferenced Orphan Files</span>
            </button>
            <p class="text-[10px] text-gray-400 text-center mt-2">
              Safely removes any test uploads in <code class="text-gray-300">uploads/</code> that are not active on any slot.
            </p>
          </div>
        </div>

      </div>
    </div>

  </main>

  <!-- =========================================================================
       4. TOAST NOTIFICATION SYSTEM
       ========================================================================= -->
  <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none max-w-sm">
    <div id="toast-inner" class="flex items-center gap-3 p-4 rounded-xl shadow-2xl border text-sm">
      <i id="toast-icon" data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
      <span id="toast-message" class="font-body"></span>
    </div>
  </div>

  <!-- Global CSRF Token for fetch requests -->
  <script>
    const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;
  </script>

  <!-- =========================================================================
       5. JAVASCRIPT LOGIC FOR BACKEND OPERATIONS
       ========================================================================= -->
  <script>
    lucide.createIcons();

    // -----------------------------------------------------------------
    // Tab Switching
    // -----------------------------------------------------------------
    function switchTab(tabId) {
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
      document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-[#F5EFEB]', 'text-black');
        b.classList.add('text-gray-300');
      });

      const activePanel = document.getElementById('panel-' + tabId);
      const activeBtn = document.getElementById('tab-btn-' + tabId);
      const slotsControls = document.getElementById('slots-controls');

      if (activePanel) activePanel.classList.remove('hidden');
      if (activeBtn) {
        activeBtn.classList.add('bg-[#F5EFEB]', 'text-black');
        activeBtn.classList.remove('text-gray-300');
      }

      if (slotsControls) {
        slotsControls.style.display = (tabId === 'slots') ? 'flex' : 'none';
      }
    }

    // -----------------------------------------------------------------
    // Filter & Search Slots
    // -----------------------------------------------------------------
    function filterSlots() {
      const search = (document.getElementById('slot-search').value || '').toLowerCase().trim();
      const section = document.getElementById('section-filter').value;
      const cards = document.querySelectorAll('.slot-card');
      let visible = 0;

      cards.forEach(card => {
        const cardSection = card.getAttribute('data-section');
        const cardName = card.getAttribute('data-name');
        const isCustom = card.getAttribute('data-is-custom') === 'true';

        let matchesSearch = !search || cardName.includes(search) || cardSection.toLowerCase().includes(search);
        let matchesSection = true;

        if (section === 'custom-only') {
          matchesSection = isCustom;
        } else if (section !== 'all') {
          matchesSection = cardSection === section;
        }

        if (matchesSearch && matchesSection) {
          card.style.display = 'flex';
          visible++;
        } else {
          card.style.display = 'none';
        }
      });

      document.getElementById('visible-slots-count').textContent = visible;
    }

    // -----------------------------------------------------------------
    // Toast Alert Helper
    // -----------------------------------------------------------------
    let toastTimeout = null;
    function showToast(message, isSuccess = true) {
      const toast = document.getElementById('toast');
      const inner = document.getElementById('toast-inner');
      const msg = document.getElementById('toast-message');
      const icon = document.getElementById('toast-icon');

      msg.textContent = message;
      if (isSuccess) {
        inner.className = 'flex items-center gap-3 p-4 rounded-xl shadow-2xl border text-sm bg-emerald-950/95 border-emerald-500/50 text-emerald-200';
        icon.setAttribute('data-lucide', 'check-circle-2');
      } else {
        inner.className = 'flex items-center gap-3 p-4 rounded-xl shadow-2xl border text-sm bg-red-950/95 border-red-500/50 text-red-200';
        icon.setAttribute('data-lucide', 'alert-triangle');
      }
      lucide.createIcons();

      toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
      }, 4000);
    }

    // -----------------------------------------------------------------
    // Client-side Image Resizer & Optimizer (Canvas)
    // -----------------------------------------------------------------
    function optimizeImage(file, maxDimension = 2000, quality = 0.85) {
      return new Promise((resolve) => {
        // If file is SVG or under 400KB, no need to resize
        if (file.type === 'image/svg+xml' || file.size < 400 * 1024) {
          resolve(file);
          return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
          const img = new Image();
          img.onload = () => {
            let width = img.width;
            let height = img.height;

            if (width > maxDimension || height > maxDimension) {
              if (width > height) {
                height = Math.round((height * maxDimension) / width);
                width = maxDimension;
              } else {
                width = Math.round((width * maxDimension) / height);
                height = maxDimension;
              }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob((blob) => {
              if (!blob || blob.size >= file.size) {
                resolve(file); // fallback to original if compression didn't help
              } else {
                const optimizedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".webp", {
                  type: "image/webp",
                  lastModified: Date.now()
                });
                resolve(optimizedFile);
              }
            }, 'image/webp', quality);
          };
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      });
    }

    // -----------------------------------------------------------------
    // Slot Image Upload Handler
    // -----------------------------------------------------------------
    async function handleSlotFileUpload(slotKey, file) {
      if (!file) return;

      const loader = document.getElementById('loader-' + slotKey);
      if (loader) loader.classList.remove('hidden'), loader.classList.add('flex');

      try {
        const optimizedFile = await optimizeImage(file);
        const altInput = document.getElementById('alt-input-' + slotKey);
        const altVal = altInput ? altInput.value : '';

        const formData = new FormData();
        formData.append('slot_key', slotKey);
        formData.append('image', optimizedFile);
        formData.append('alt', altVal);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/upload.php', {
          method: 'POST',
          body: formData
        });

        const res = await response.json();
        if (res.success) {
          // Update preview image
          const img = document.getElementById('preview-img-' + slotKey);
          if (img) img.src = '../' + res.url + '?v=' + Date.now();

          // Update card state
          const card = document.querySelector(`[data-slot-key="${slotKey}"]`);
          if (card) {
            card.setAttribute('data-is-custom', 'true');
            const statusPill = card.querySelector('.status-pill');
            if (statusPill) {
              statusPill.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300">
                  <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                  Custom
                </span>
              `;
            }
            const resetBtn = document.getElementById('reset-btn-' + slotKey);
            if (resetBtn) {
              resetBtn.disabled = false;
              resetBtn.className = 'py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer';
            }
          }

          showToast(res.message || 'Image uploaded & live site updated!');
          refreshStats();
        } else {
          showToast(res.error || 'Failed to upload image.', false);
        }
      } catch (err) {
        showToast('Network error during upload: ' + err.message, false);
      } finally {
        if (loader) loader.classList.add('hidden'), loader.classList.remove('flex');
      }
    }

    // -----------------------------------------------------------------
    // Reset Slot to Original Default
    // -----------------------------------------------------------------
    async function confirmResetSlot(slotKey) {
      if (!confirm('Are you sure you want to revert this image back to the original website default? The custom uploaded file will be deleted from storage.')) {
        return;
      }

      try {
        const formData = new FormData();
        formData.append('slot_key', slotKey);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/delete.php', {
          method: 'POST',
          body: formData
        });

        const res = await response.json();
        if (res.success) {
          const img = document.getElementById('preview-img-' + slotKey);
          if (img && res.default_url) {
            img.src = '../' + res.default_url + '?v=' + Date.now();
          }

          const card = document.querySelector(`[data-slot-key="${slotKey}"]`);
          if (card) {
            card.setAttribute('data-is-custom', 'false');
            const statusPill = card.querySelector('.status-pill');
            if (statusPill) {
              statusPill.innerHTML = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">
                  Default
                </span>
              `;
            }
            const resetBtn = document.getElementById('reset-btn-' + slotKey);
            if (resetBtn) {
              resetBtn.disabled = true;
              resetBtn.className = 'py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors text-gray-500 opacity-40 cursor-not-allowed';
            }
          }

          showToast('Image reset to original website default!');
          refreshStats();
        } else {
          showToast(res.error || 'Reset failed.', false);
        }
      } catch (err) {
        showToast('Error resetting image: ' + err.message, false);
      }
    }

    // -----------------------------------------------------------------
    // Save Alt Text
    // -----------------------------------------------------------------
    async function saveAltText(slotKey) {
      const altInput = document.getElementById('alt-input-' + slotKey);
      if (!altInput) return;

      try {
        const formData = new FormData();
        formData.append('slot_key', slotKey);
        formData.append('alt', altInput.value);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/update-slot.php', {
          method: 'POST',
          body: formData
        });
        const res = await response.json();
        if (res.success) {
          showToast('Alt text saved for SEO!');
        } else {
          showToast(res.error || 'Failed to save alt text.', false);
        }
      } catch (err) {
        showToast('Error saving alt text.', false);
      }
    }

    // -----------------------------------------------------------------
    // Gallery Photo Drag & Drop / Upload
    // -----------------------------------------------------------------
    let pickedGalleryFile = null;

    function handleGalleryFilePicked(file) {
      if (!file) return;
      pickedGalleryFile = file;

      const previewBox = document.getElementById('gal-selected-preview');
      const thumb = document.getElementById('gal-thumb-img');
      const nameEl = document.getElementById('gal-file-name');
      const sizeEl = document.getElementById('gal-file-size');

      nameEl.textContent = file.name;
      sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

      const reader = new FileReader();
      reader.onload = (e) => {
        thumb.src = e.target.result;
        previewBox.classList.remove('hidden');
      };
      reader.readAsDataURL(file);

      // Auto-fill title if empty
      const titleInput = document.getElementById('gal-title');
      if (!titleInput.value) {
        titleInput.value = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");
      }
    }

    async function handleGalleryUpload(e) {
      e.preventDefault();
      if (!pickedGalleryFile) {
        showToast('Please select a photo to upload.', false);
        return;
      }

      const uploadBtn = document.getElementById('gal-upload-btn');
      uploadBtn.disabled = true;
      uploadBtn.innerHTML = `
        <div class="w-4 h-4 border-2 border-black border-t-transparent rounded-full animate-spin"></div>
        <span>Uploading Dispatch...</span>
      `;

      try {
        const optimizedFile = await optimizeImage(pickedGalleryFile);
        const title = document.getElementById('gal-title').value;
        const category = document.getElementById('gal-category').value;
        const alt = document.getElementById('gal-alt').value;

        const formData = new FormData();
        formData.append('type', 'gallery');
        formData.append('image', optimizedFile);
        formData.append('title', title);
        formData.append('category', category);
        formData.append('alt', alt);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/upload.php', {
          method: 'POST',
          body: formData
        });

        const res = await response.json();
        if (res.success && res.item) {
          showToast('Gallery dispatch published successfully!');

          // Append to gallery grid
          const grid = document.getElementById('gallery-grid');
          const empty = document.getElementById('gallery-empty-state');
          if (empty) empty.remove();

          const newCard = document.createElement('div');
          newCard.id = 'gal-card-' + res.item.id;
          newCard.className = 'bg-[#1E3A5F] border border-white/10 rounded-lg overflow-hidden shadow-md group';
          newCard.innerHTML = `
            <div class="relative h-44 bg-black/30 overflow-hidden">
              <img src="../${res.item.url}" alt="${res.item.alt || ''}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
              <button 
                onclick="deleteGalleryItem('${res.item.id}')"
                class="absolute top-2 right-2 p-1.5 rounded-full bg-red-900/80 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                title="Delete from Gallery"
              >
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
              </button>
            </div>
            <div class="p-3">
              <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-1">
                ${res.item.category || 'Tour'}
              </span>
              <p class="text-xs font-semibold text-white truncate" title="${res.item.title}">
                ${res.item.title}
              </p>
              <p class="text-[10px] text-gray-400 mt-1">Just now</p>
            </div>
          `;
          grid.prepend(newCard);
          lucide.createIcons();

          // Reset form
          document.getElementById('gallery-upload-form').reset();
          document.getElementById('gal-selected-preview').classList.add('hidden');
          pickedGalleryFile = null;

          refreshStats();
        } else {
          showToast(res.error || 'Gallery upload failed.', false);
        }
      } catch (err) {
        showToast('Upload error: ' + err.message, false);
      } finally {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = `
          <i data-lucide="cloud-upload" class="w-4 h-4"></i>
          <span>Upload Photo to Gallery</span>
        `;
        lucide.createIcons();
      }
    }

    // -----------------------------------------------------------------
    // Delete Gallery Photo
    // -----------------------------------------------------------------
    async function deleteGalleryItem(galleryId) {
      if (!confirm('Are you sure you want to delete this photo from the gallery? This will permanently delete the file from the server.')) {
        return;
      }

      try {
        const formData = new FormData();
        formData.append('gallery_id', galleryId);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/delete.php', {
          method: 'POST',
          body: formData
        });

        const res = await response.json();
        if (res.success) {
          const card = document.getElementById('gal-card-' + galleryId);
          if (card) card.remove();
          showToast('Gallery photo deleted.');
          refreshStats();
        } else {
          showToast(res.error || 'Delete failed.', false);
        }
      } catch (err) {
        showToast('Error deleting gallery item.', false);
      }
    }

    // -----------------------------------------------------------------
    // Update Admin Credentials
    // -----------------------------------------------------------------
    async function handleCredentialsUpdate(e) {
      e.preventDefault();
      const currentPassword = document.getElementById('current-password').value;
      const newUsername = document.getElementById('new-username').value;
      const newPassword = document.getElementById('new-password').value;

      try {
        const formData = new FormData();
        formData.append('action', 'change_credentials');
        formData.append('current_password', currentPassword);
        formData.append('new_username', newUsername);
        formData.append('new_password', newPassword);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/auth.php', {
          method: 'POST',
          body: formData
        });

        const res = await response.json();
        if (res.success) {
          showToast('Credentials updated successfully!');
          document.getElementById('current-password').value = '';
          document.getElementById('new-password').value = '';
        } else {
          showToast(res.error || 'Failed to update credentials.', false);
        }
      } catch (err) {
        showToast('Error updating credentials: ' + err.message, false);
      }
    }

    // -----------------------------------------------------------------
    // Clean Orphan Upload Files
    // -----------------------------------------------------------------
    async function cleanOrphanFiles() {
      if (!confirm('Scan and delete any upload files that are no longer linked to any website slot or gallery dispatch?')) {
        return;
      }

      try {
        const formData = new FormData();
        formData.append('action', 'clean_orphans');
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/delete.php', {
          method: 'POST',
          body: formData
        });

        const res = await response.json();
        if (res.success) {
          showToast(res.message || 'Cleanup completed!');
          refreshStats();
        } else {
          showToast(res.error || 'Cleanup failed.', false);
        }
      } catch (err) {
        showToast('Cleanup error: ' + err.message, false);
      }
    }

    // -----------------------------------------------------------------
    // Refresh Stats from Server
    // -----------------------------------------------------------------
    async function refreshStats() {
      try {
        const res = await fetch('../api/get-images.php?stats=1');
        const data = await res.json();
        if (data.success) {
          let customCount = 0;
          for (let k in data.slots) {
            if (data.slots[k].url) customCount++;
          }
          document.getElementById('stat-custom-slots').textContent = customCount;
          document.getElementById('stat-gallery-count').textContent = data.gallery ? data.gallery.length : 0;
          const galCountEl = document.getElementById('gallery-items-count');
          if (galCountEl) galCountEl.textContent = data.gallery ? data.gallery.length : 0;

          if (data.stats) {
            document.getElementById('stat-disk-usage').innerHTML = `${data.stats.total_size_mb} <span class="text-xs font-normal text-gray-400">MB</span>`;
            const infoCount = document.getElementById('info-files-count');
            if (infoCount) infoCount.textContent = `${data.stats.total_files} file(s)`;
            const infoSize = document.getElementById('info-files-size');
            if (infoSize) infoSize.textContent = `${data.stats.total_size_mb} MB`;
          }
        }
      } catch (e) {}
    }
  </script>
</body>
</html>
