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
  <!-- Cropper.js CSS -->
  <link rel="stylesheet" href="../css/vendor/cropper.min.css" onerror="this.onerror=null;this.href='https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css';" />

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
    /* Cropper styling enhancements */
    .cropper-view-box {
      outline: 2px solid #F5EFEB !important;
      outline-color: rgba(245, 239, 235, 0.95) !important;
    }
    .cropper-line {
      background-color: rgba(245, 239, 235, 0.35) !important;
    }
    .cropper-point {
      background-color: #F5EFEB !important;
    }
    .cropper-bg {
      background-image: none !important;
      background-color: #0b1420 !important;
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
            placeholder="Search images, tours, guides..." 
            oninput="filterSlots()"
            class="pl-8 pr-3 py-1.5 bg-[#1E3A5F] border border-white/15 rounded text-xs text-white placeholder-gray-400 focus:outline-none focus:border-[#F5EFEB] w-48 sm:w-64"
          />
        </div>
      </div>
    </div>

    <!-- =========================================================================
         PANEL 1: CORE WEBSITE IMAGE SLOTS
         ========================================================================= -->
    <div id="panel-slots" class="tab-panel space-y-8">
      <!-- Dynamic Content Root Container -->
      <div id="slots-sections-container" class="space-y-12">
        <!-- Populated dynamically by renderSlots() -->
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
       DRAG-AND-ZOOM IMAGE CROP MODAL
       ========================================================================= -->
  <div id="crop-modal" class="fixed inset-0 z-[100] bg-black/85 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-6 transition-all duration-200">
    <div class="bg-[#162E4D] border border-white/20 rounded-2xl max-w-4xl w-full max-h-[95vh] flex flex-col shadow-2xl overflow-hidden">
      
      <!-- Modal Header -->
      <div class="p-4 border-b border-white/10 bg-[#1E3A5F] flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-[#2A4E7A] flex items-center justify-center text-[#F5EFEB] shrink-0">
            <i data-lucide="crop" class="w-5 h-5"></i>
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-heading text-base font-bold uppercase tracking-wider text-white" id="crop-modal-title">
                Crop &amp; Frame Image
              </h3>
              <span id="crop-badge-dimension" class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/40 text-[#F5EFEB] border border-white/10">
                1600 × 1000 px (16:10)
              </span>
              <span id="crop-badge-mismatch" class="hidden px-2 py-0.5 rounded text-[10px] font-heading font-semibold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/40 items-center gap-1">
                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                <span>Orientation Mismatch</span>
              </span>
            </div>
            <p class="text-xs text-gray-300 mt-0.5" id="crop-modal-subtitle">
              Drag image to reposition • Scroll wheel or slider to zoom
            </p>
          </div>
        </div>

        <button type="button" onclick="closeCropModal()" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-colors" title="Cancel &amp; Close">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <!-- Cropper Viewport -->
      <div class="relative bg-black/90 flex-1 min-h-[340px] sm:min-h-[440px] max-h-[58vh] overflow-hidden flex items-center justify-center p-2">
        <div class="w-full h-full max-h-[56vh] flex items-center justify-center">
          <img id="cropper-target-img" src="" alt="Crop Source" class="max-w-full block" />
        </div>
      </div>

      <!-- Controls Toolbar -->
      <div class="p-3 bg-[#193254] border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs">
        <!-- Zoom Controls -->
        <div class="flex items-center gap-2">
          <span class="text-gray-400 font-heading uppercase text-[11px] tracking-wider">Zoom:</span>
          <button type="button" onclick="cropZoom(-0.1)" class="w-7 h-7 rounded bg-[#1E3A5F] hover:bg-[#2A4E7A] text-white flex items-center justify-center font-bold border border-white/10" title="Zoom Out">-</button>
          <input type="range" id="crop-zoom-range" min="0.1" max="3" step="0.05" value="1" oninput="handleZoomSlider(this.value)" class="w-28 sm:w-40 accent-[#F5EFEB]" />
          <button type="button" onclick="cropZoom(0.1)" class="w-7 h-7 rounded bg-[#1E3A5F] hover:bg-[#2A4E7A] text-white flex items-center justify-center font-bold border border-white/10" title="Zoom In">+</button>
        </div>

        <!-- Rotate & Reset Controls -->
        <div class="flex items-center gap-2">
          <button type="button" onclick="cropRotate(-90)" class="px-2.5 py-1.5 rounded bg-[#1E3A5F] hover:bg-[#2A4E7A] text-gray-200 hover:text-white border border-white/10 flex items-center gap-1 font-heading text-[11px] uppercase tracking-wider transition-colors" title="Rotate 90° Left">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
            <span>-90°</span>
          </button>
          <button type="button" onclick="cropRotate(90)" class="px-2.5 py-1.5 rounded bg-[#1E3A5F] hover:bg-[#2A4E7A] text-gray-200 hover:text-white border border-white/10 flex items-center gap-1 font-heading text-[11px] uppercase tracking-wider transition-colors" title="Rotate 90° Right">
            <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i>
            <span>+90°</span>
          </button>
          <button type="button" onclick="cropReset()" class="px-2.5 py-1.5 rounded bg-[#1E3A5F] hover:bg-[#2A4E7A] text-gray-200 hover:text-white border border-white/10 flex items-center gap-1 font-heading text-[11px] uppercase tracking-wider transition-colors" title="Reset to center">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
            <span>Reset</span>
          </button>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-4 bg-[#1E3A5F] border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
        <div class="text-[11px] text-gray-400">
          Standard Output: <span id="crop-footer-dim" class="text-[#F5EFEB] font-semibold">1600 × 1000 px</span>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" onclick="closeCropModal()" class="px-4 py-2 rounded-lg border border-white/20 text-gray-300 hover:text-white hover:bg-white/5 font-heading text-xs uppercase tracking-wider transition-colors">
            Cancel
          </button>
          <button type="button" id="crop-apply-btn" onclick="applyCropAndSave()" class="px-5 py-2 rounded-lg bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-lg transition-all">
            <i data-lucide="check" class="w-4 h-4"></i>
            <span>Apply Crop &amp; Save</span>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       4. TOAST NOTIFICATION SYSTEM
       ========================================================================= -->
  <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none max-w-sm">
    <div id="toast-inner" class="flex items-center gap-3 p-4 rounded-xl shadow-2xl border text-sm">
      <i id="toast-icon" data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
      <span id="toast-message" class="font-body"></span>
    </div>
  </div>

  <!-- Global CSRF Token & Initial Registry for fetch requests -->
  <script>
    const CSRF_TOKEN = <?php echo json_encode($csrfToken); ?>;
    const INITIAL_SLOTS = <?php echo json_encode($registry['slots'] ?? new stdClass()); ?>;
    const INITIAL_GALLERY = <?php echo json_encode($registry['gallery'] ?? []); ?>;
  </script>

  <!-- Vendor Cropper.js Library with CDN Fallback -->
  <script src="../js/vendor/cropper.min.js"></script>
  <script>
    if (typeof Cropper === 'undefined') {
      document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"><\/script>');
    }
  </script>

  <!-- =========================================================================
       5. JAVASCRIPT LOGIC FOR BACKEND OPERATIONS
       ========================================================================= -->
  <script>
    const DEFAULT_SLOTS = {
      "hero-bg": { name: "Hero Section Media Fallback", section: "Hero", aspectRatio: "16/9", standardWidth: 1920, standardHeight: 1080, orientation: "landscape", default: "assets/hero/hero-poster.jpg", alt: "GNARLY MTB Nepal Himalayan Action" },
      
      "tour-thin-air-1": { name: "Enduro Thin Air — Slide 1 (Cover)", section: "Tour: Enduro Thin Air", tourGroup: "thin-air", slideNum: 1, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/enduro-thin-air/card-slide-1.jpg", alt: "Enduro Thin Air Ultimate Lo Manthang Traverse" },
      "tour-thin-air-2": { name: "Enduro Thin Air — Slide 2", section: "Tour: Enduro Thin Air", tourGroup: "thin-air", slideNum: 2, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/enduro-thin-air/card-slide-2.jpg", alt: "Mustang Desert Ridge Singletrack Descent" },
      "tour-thin-air-3": { name: "Enduro Thin Air — Slide 3", section: "Tour: Enduro Thin Air", tourGroup: "thin-air", slideNum: 3, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/enduro-thin-air/card-slide-3.jpg", alt: "High Alpine Scree & Rocky Enduro Ride" },
      "tour-thin-air-4": { name: "Enduro Thin Air — Slide 4", section: "Tour: Enduro Thin Air", tourGroup: "thin-air", slideNum: 4, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/enduro-thin-air/card-slide-4.jpg", alt: "Upper Mustang Canyon Pass and Sky Caves" },
      "tour-thin-air-5": { name: "Enduro Thin Air — Slide 5", section: "Tour: Enduro Thin Air", tourGroup: "thin-air", slideNum: 5, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/enduro-thin-air/card-slide-5.jpg", alt: "Upper Mustang High-Altitude Singletrack" },
      "tour-thin-air-6": { name: "Enduro Thin Air — Slide 6", section: "Tour: Enduro Thin Air", tourGroup: "thin-air", slideNum: 6, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/enduro-thin-air/card-slide-6.jpg", alt: "High Himalayan Mountain Pass Enduro" },

      "tour-everest-1": { name: "Everest Express — Slide 1 (Cover)", section: "Tour: Everest Express", tourGroup: "everest", slideNum: 1, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/everest-express/card-slide-1.jpg", alt: "Everest Express Khumbu Mountain Singletrack" },
      "tour-everest-2": { name: "Everest Express — Slide 2", section: "Tour: Everest Express", tourGroup: "everest", slideNum: 2, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/everest-express/card-slide-2.jpg", alt: "Alpine Downhill Rider on Khumbu Ridge" },
      "tour-everest-3": { name: "Everest Express — Slide 3", section: "Tour: Everest Express", tourGroup: "everest", slideNum: 3, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/everest-express/card-slide-3.jpg", alt: "Solukhumbu High Valley Singletrack" },
      "tour-everest-4": { name: "Everest Express — Slide 4", section: "Tour: Everest Express", tourGroup: "everest", slideNum: 4, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/everest-express/card-slide-4.jpg", alt: "Himalayan Mountain Range under Everest" },
      "tour-everest-5": { name: "Everest Express — Slide 5", section: "Tour: Everest Express", tourGroup: "everest", slideNum: 5, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/everest-express/card-slide-5.jpg", alt: "Himalayan Sherpa Valley Trail Riding" },
      "tour-everest-6": { name: "Everest Express — Slide 6", section: "Tour: Everest Express", tourGroup: "everest", slideNum: 6, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/everest-express/card-slide-6.jpg", alt: "High Altitude Everest Panorama Descent" },

      "tour-moto-1": { name: "Hello Moto — Slide 1 (Cover)", section: "Tour: Hello Moto", tourGroup: "hello-moto", slideNum: 1, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/hello-moto/card-slide-1.jpg", alt: "Himalayan Moto Holidays Enduro Dual-Sport Rider" },
      "tour-moto-2": { name: "Hello Moto — Slide 2", section: "Tour: Hello Moto", tourGroup: "hello-moto", slideNum: 2, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/hello-moto/card-slide-2.jpg", alt: "Dual Sport Motorcycle on Dirt Riverbed" },
      "tour-moto-3": { name: "Hello Moto — Slide 3", section: "Tour: Hello Moto", tourGroup: "hello-moto", slideNum: 3, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/hello-moto/card-slide-3.jpg", alt: "Mustang Valley Dirt Gorge Crossing" },
      "tour-moto-4": { name: "Hello Moto — Slide 4", section: "Tour: Hello Moto", tourGroup: "hello-moto", slideNum: 4, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/hello-moto/card-slide-4.jpg", alt: "Himalayan Mountain Pass Route" },
      "tour-moto-5": { name: "Hello Moto — Slide 5", section: "Tour: Hello Moto", tourGroup: "hello-moto", slideNum: 5, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/hello-moto/card-slide-5.jpg", alt: "Mustang Plateau Moto Exploration" },
      "tour-moto-6": { name: "Hello Moto — Slide 6", section: "Tour: Hello Moto", tourGroup: "hello-moto", slideNum: 6, aspectRatio: "16/10", standardWidth: 1600, standardHeight: 1000, orientation: "landscape", default: "assets/tours/hello-moto/card-slide-6.jpg", alt: "High Altitude Himalayan Dirt Bike Tour" },

      "why-ride-1": { name: "Trails For Every Skill", section: "Why Ride With Us", aspectRatio: "9/16", standardWidth: 1080, standardHeight: 1920, orientation: "portrait", default: "assets/why-ride-with-us/01-trails-every-skill.jpg", alt: "Trails for every skill" },
      "why-ride-2": { name: "All-Mountain & Enduro", section: "Why Ride With Us", aspectRatio: "9/16", standardWidth: 1080, standardHeight: 1920, orientation: "portrait", default: "assets/why-ride-with-us/02-all-mountain-enduro.jpg", alt: "All-mountain and enduro adventures" },
      "why-ride-3": { name: "Family Focused", section: "Why Ride With Us", aspectRatio: "9/16", standardWidth: 1080, standardHeight: 1920, orientation: "portrait", default: "assets/why-ride-with-us/03-family-focused.jpg", alt: "Family focused mountain biking" },
      "why-ride-4": { name: "Customised Tours", section: "Why Ride With Us", aspectRatio: "9/16", standardWidth: 1080, standardHeight: 1920, orientation: "portrait", default: "assets/why-ride-with-us/04-customised-tours.jpg", alt: "Customised Himalayan tours" },
      "why-ride-5": { name: "Fully Supported", section: "Why Ride With Us", aspectRatio: "9/16", standardWidth: 1080, standardHeight: 1920, orientation: "portrait", default: "assets/why-ride-with-us/05-fully-supported.jpg", alt: "Fully supported expeditions" },

      "crew-shyam": { name: "Shyam Gyan Limbu", section: "The Crew", aspectRatio: "4/5", standardWidth: 1200, standardHeight: 1500, orientation: "portrait", default: "assets/crew/shyam-gyan-limbu.jpg", alt: "Shyam Gyan Limbu - Head Guide" },
      "crew-prachit": { name: "Prachit Thapa Magar", section: "The Crew", aspectRatio: "4/5", standardWidth: 1200, standardHeight: 1500, orientation: "portrait", default: "assets/crew/prachit-thapa-magar.jpg", alt: "Prachit Thapa Magar - Senior Guide" },
      "crew-tek": { name: "Tek Shrestha", section: "The Crew", aspectRatio: "4/5", standardWidth: 1200, standardHeight: 1500, orientation: "portrait", default: "assets/crew/tek-shrestha.jpg", alt: "Tek Shrestha - Logistics" }
    };

    const TOURS_CONFIG = [
      {
        id: 'thin-air',
        title: 'Enduro Thin Air',
        sub: 'Lo Manthang Traverse • 12 Days (Upper & Lower Mustang)',
        section: 'Tour: Enduro Thin Air',
        badge: '16:10 Landscape',
        prefix: 'tour-thin-air-',
        slidesCount: 6
      },
      {
        id: 'everest',
        title: 'Everest Express',
        sub: 'Khumbu & Phaplu Singletrack • 10 Days',
        section: 'Tour: Everest Express',
        badge: '16:10 Landscape',
        prefix: 'tour-everest-',
        slidesCount: 6
      },
      {
        id: 'hello-moto',
        title: 'Himalayan Moto Holidays “Hello Moto”',
        sub: 'Dual-Sport Enduro Expedition • 11 Days',
        section: 'Tour: Hello Moto',
        badge: '16:10 Landscape',
        prefix: 'tour-moto-',
        slidesCount: 6
      }
    ];

    const WHY_RIDE_KEYS = ['why-ride-1', 'why-ride-2', 'why-ride-3', 'why-ride-4', 'why-ride-5'];
    const CREW_KEYS = ['crew-shyam', 'crew-prachit', 'crew-tek'];
    const HERO_KEYS = ['hero-bg'];

    let currentRegistry = { slots: INITIAL_SLOTS || {}, gallery: INITIAL_GALLERY || [] };
    const tourActiveSlide = { 'thin-air': 1, 'everest': 1, 'hello-moto': 1 };
    let tourSlideView = 'card';

    // -----------------------------------------------------------------
    // Interactive Drag-and-Zoom Cropper Engine
    // -----------------------------------------------------------------
    let activeCropper = null;
    let cropSlotKey = null;
    let cropTourId = null;
    let cropAltText = '';
    let cropSlotConfig = null;

    function openCropModal(imageSrc, slotKey, isMismatch = false, tourId = null, altText = '') {
      cropSlotKey = slotKey;
      cropTourId = tourId;
      cropAltText = altText;
      cropSlotConfig = currentRegistry.slots[slotKey] || DEFAULT_SLOTS[slotKey] || {};

      const stdWidth = cropSlotConfig.standardWidth || (cropSlotConfig.aspectRatio === '9/16' ? 1080 : (cropSlotConfig.aspectRatio === '4/5' ? 1200 : (cropSlotConfig.aspectRatio === '16/9' ? 1920 : 1600)));
      const stdHeight = cropSlotConfig.standardHeight || (cropSlotConfig.aspectRatio === '9/16' ? 1920 : (cropSlotConfig.aspectRatio === '4/5' ? 1500 : (cropSlotConfig.aspectRatio === '16/9' ? 1080 : 1000)));
      const ratio = stdWidth / stdHeight;
      const ratioStr = cropSlotConfig.aspectRatio || `${stdWidth}:${stdHeight}`;
      const orientationStr = stdWidth >= stdHeight ? 'Landscape' : 'Portrait';

      document.getElementById('crop-modal-title').textContent = `Crop Image: ${cropSlotConfig.name || slotKey}`;
      document.getElementById('crop-badge-dimension').textContent = `Standard: ${stdWidth} × ${stdHeight} px (${ratioStr} ${orientationStr})`;
      document.getElementById('crop-footer-dim').textContent = `${stdWidth} × ${stdHeight} px (${ratioStr})`;

      const mismatchBadge = document.getElementById('crop-badge-mismatch');
      if (isMismatch) {
        mismatchBadge.classList.remove('hidden');
        mismatchBadge.classList.add('inline-flex');
      } else {
        mismatchBadge.classList.add('hidden');
        mismatchBadge.classList.remove('inline-flex');
      }

      const modal = document.getElementById('crop-modal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      const imgEl = document.getElementById('cropper-target-img');
      imgEl.src = imageSrc;

      if (activeCropper) {
        activeCropper.destroy();
        activeCropper = null;
      }

      activeCropper = new Cropper(imgEl, {
        aspectRatio: ratio,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: false,
        cropBoxResizable: false,
        toggleDragModeOnDblclick: false,
        ready() {
          document.getElementById('crop-zoom-range').value = 1;
          lucide.createIcons();
        },
        zoom(event) {
          const slider = document.getElementById('crop-zoom-range');
          if (slider && event.detail.ratio) {
            slider.value = Math.min(3, Math.max(0.1, event.detail.ratio));
          }
        }
      });
    }

    function cropZoom(delta) {
      if (!activeCropper) return;
      activeCropper.zoom(delta);
    }

    function handleZoomSlider(val) {
      if (!activeCropper) return;
      activeCropper.zoomTo(parseFloat(val));
    }

    function cropRotate(deg) {
      if (!activeCropper) return;
      activeCropper.rotate(deg);
    }

    function cropReset() {
      if (!activeCropper) return;
      activeCropper.reset();
      document.getElementById('crop-zoom-range').value = 1;
    }

    function closeCropModal() {
      if (activeCropper) {
        activeCropper.destroy();
        activeCropper = null;
      }
      const modal = document.getElementById('crop-modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    async function applyCropAndSave() {
      if (!activeCropper || !cropSlotKey) return;
      
      const stdWidth = cropSlotConfig.standardWidth || (cropSlotConfig.aspectRatio === '9/16' ? 1080 : (cropSlotConfig.aspectRatio === '4/5' ? 1200 : (cropSlotConfig.aspectRatio === '16/9' ? 1920 : 1600)));
      const stdHeight = cropSlotConfig.standardHeight || (cropSlotConfig.aspectRatio === '9/16' ? 1920 : (cropSlotConfig.aspectRatio === '4/5' ? 1500 : (cropSlotConfig.aspectRatio === '16/9' ? 1080 : 1000)));

      const btn = document.getElementById('crop-apply-btn');
      btn.disabled = true;
      btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Saving...</span>`;
      lucide.createIcons();

      const croppedCanvas = activeCropper.getCroppedCanvas({
        width: stdWidth,
        height: stdHeight,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
      });

      croppedCanvas.toBlob(async (blob) => {
        try {
          const formData = new FormData();
          formData.append('slot_key', cropSlotKey);
          formData.append('image', blob, `${cropSlotKey}.jpg`);
          if (cropAltText) formData.append('alt', cropAltText);
          formData.append('csrf_token', CSRF_TOKEN);

          const response = await fetch('../api/upload.php', { method: 'POST', body: formData });
          const res = await response.json();
          if (res.success) {
            if (!currentRegistry.slots[cropSlotKey]) currentRegistry.slots[cropSlotKey] = { ...cropSlotConfig };
            currentRegistry.slots[cropSlotKey].url = res.url;
            if (cropAltText) currentRegistry.slots[cropSlotKey].alt = cropAltText;
            closeCropModal();
            renderSlots();
            showToast(res.message || `Image cropped to standard ${stdWidth}×${stdHeight} px and saved!`);
            refreshStats();
          } else {
            showToast(res.error || 'Failed to upload cropped image.', false);
          }
        } catch (err) {
          showToast('Network error during upload: ' + err.message, false);
        } finally {
          btn.disabled = false;
          btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Apply Crop & Save</span>`;
          lucide.createIcons();
        }
      }, 'image/jpeg', 0.88);
    }

    // Inspect image for orientation/ratio mismatch
    function inspectAndProcessImage(file, slotKey, tourId = null, forceCrop = false) {
      if (!file) return;

      const slot = currentRegistry.slots[slotKey] || DEFAULT_SLOTS[slotKey] || {};
      const stdWidth = slot.standardWidth || (slot.aspectRatio === '9/16' ? 1080 : (slot.aspectRatio === '4/5' ? 1200 : (slot.aspectRatio === '16/9' ? 1920 : 1600)));
      const stdHeight = slot.standardHeight || (slot.aspectRatio === '9/16' ? 1920 : (slot.aspectRatio === '4/5' ? 1500 : (slot.aspectRatio === '16/9' ? 1080 : 1000)));
      const targetRatio = stdWidth / stdHeight;
      const targetIsPortrait = stdHeight > stdWidth;

      const altInput = document.getElementById(tourId ? `tour-alt-input-${tourId}` : `alt-input-${slotKey}`);
      const altVal = altInput ? altInput.value : (slot.alt || '');

      const reader = new FileReader();
      reader.onload = (e) => {
        const dataUrl = e.target.result;
        const img = new Image();
        img.onload = () => {
          const uploadRatio = img.naturalWidth / img.naturalHeight;
          const uploadIsPortrait = img.naturalHeight > img.naturalWidth;
          const isOrientationMismatch = (uploadIsPortrait !== targetIsPortrait);
          const ratioDiff = Math.abs(uploadRatio - targetRatio) / targetRatio;
          const isRatioMismatch = isOrientationMismatch || ratioDiff > 0.03;

          if (forceCrop || isRatioMismatch) {
            openCropModal(dataUrl, slotKey, isOrientationMismatch, tourId, altVal);
          } else {
            // Direct save if aspect ratio matches
            saveDirectSlotImage(file, slotKey, tourId, altVal);
          }
        };
        img.src = dataUrl;
      };
      reader.readAsDataURL(file);
    }

    function openCropForCurrentSlot(slotKey, tourId = null) {
      const slot = currentRegistry.slots[slotKey] || DEFAULT_SLOTS[slotKey] || {};
      const isCustom = Boolean(slot.url);
      const activeUrl = isCustom ? (slot.url.startsWith('data:') ? slot.url : '../' + slot.url) : '../' + slot.default;
      const altInput = document.getElementById(tourId ? `tour-alt-input-${tourId}` : `alt-input-${slotKey}`);
      const altVal = altInput ? altInput.value : (slot.alt || '');
      openCropModal(activeUrl, slotKey, false, tourId, altVal);
    }

    function openCropForTourSlide(tourId) {
      const curSlide = tourActiveSlide[tourId] || 1;
      const tour = TOURS_CONFIG.find(t => t.id === tourId);
      if (!tour) return;
      const slotKey = `${tour.prefix}${curSlide}`;
      openCropForCurrentSlot(slotKey, tourId);
    }

    async function saveDirectSlotImage(file, slotKey, tourId, altVal) {
      const loader = document.getElementById(tourId ? `tour-loader-${tourId}` : `loader-${slotKey}`);
      if (loader) { loader.classList.remove('hidden'); loader.classList.add('flex'); }

      try {
        const optimizedFile = await optimizeImage(file);
        const formData = new FormData();
        formData.append('slot_key', slotKey);
        formData.append('image', optimizedFile);
        formData.append('alt', altVal);
        formData.append('csrf_token', CSRF_TOKEN);

        const res = await fetch('../api/upload.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          if (!currentRegistry.slots[slotKey]) currentRegistry.slots[slotKey] = { ...DEFAULT_SLOTS[slotKey] };
          currentRegistry.slots[slotKey].url = data.url;
          currentRegistry.slots[slotKey].alt = altVal;
          renderSlots();
          showToast(data.message || 'Image replaced and saved to server storage!');
          refreshStats();
        } else {
          showToast(data.error || 'Failed to replace image.', false);
        }
      } catch (err) {
        showToast('Error replacing image: ' + err.message, false);
      } finally {
        if (loader) { loader.classList.add('hidden'); loader.classList.remove('flex'); }
      }
    }

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
    // Tour Card Slide Navigation Functions
    // -----------------------------------------------------------------
    function setTourSlide(tourId, slideNum) {
      tourActiveSlide[tourId] = slideNum;
      updateTourCardDOM(tourId);
    }

    function prevTourSlide(tourId) {
      let cur = tourActiveSlide[tourId] || 1;
      cur = cur > 1 ? cur - 1 : 6;
      setTourSlide(tourId, cur);
    }

    function nextTourSlide(tourId) {
      let cur = tourActiveSlide[tourId] || 1;
      cur = cur < 6 ? cur + 1 : 1;
      setTourSlide(tourId, cur);
    }

    function toggleTourViewMode() {
      tourSlideView = (tourSlideView === 'card') ? 'grid' : 'card';
      renderSlots();
    }

    function updateTourCardDOM(tourId) {
      const tour = TOURS_CONFIG.find(t => t.id === tourId);
      if (!tour) return;
      const curSlide = tourActiveSlide[tourId] || 1;
      const slotKey = `${tour.prefix}${curSlide}`;
      const slot = currentRegistry.slots[slotKey] || DEFAULT_SLOTS[slotKey] || {};
      const isCustom = Boolean(slot.url);
      const activeUrl = isCustom ? '../' + slot.url : '../' + slot.default;

      // Update Preview Image
      const previewImg = document.getElementById(`tour-preview-${tourId}`);
      if (previewImg) {
        previewImg.src = activeUrl;
        previewImg.alt = slot.alt || '';
      }

      // Update Max Link
      const maxLink = document.getElementById(`tour-max-link-${tourId}`);
      if (maxLink) maxLink.href = activeUrl;

      // Update Badge Text
      const badgeText = document.getElementById(`tour-badge-text-${tourId}`);
      if (badgeText) {
        badgeText.textContent = `Slide ${curSlide} of 6 ${isCustom ? '(Custom)' : '(Default)'}`;
      }

      // Update Strip Status
      const stripStatus = document.getElementById(`tour-strip-status-${tourId}`);
      if (stripStatus) {
        stripStatus.textContent = `Slide ${curSlide} Selected`;
      }

      // Update Progression Dots
      for (let s = 1; s <= 6; s++) {
        const dot = document.getElementById(`tour-dot-${tourId}-${s}`);
        if (dot) {
          if (s === curSlide) {
            dot.className = 'w-2 h-2 rounded-full bg-[#F5EFEB] scale-125 shadow-md transition-all duration-300';
          } else {
            dot.className = 'w-2 h-2 rounded-full bg-white/40 shadow-md transition-all duration-300';
          }
        }
      }

      // Update Thumbnails Selection
      for (let s = 1; s <= 6; s++) {
        const thumbBtn = document.getElementById(`tour-thumb-${tourId}-${s}`);
        if (thumbBtn) {
          if (s === curSlide) {
            thumbBtn.className = 'relative rounded overflow-hidden transition-all duration-200 ring-2 ring-[#F5EFEB] scale-105 shadow-md';
          } else {
            thumbBtn.className = 'relative rounded overflow-hidden transition-all duration-200 opacity-65 hover:opacity-100 hover:ring-1 hover:ring-white/30';
          }
        }
      }

      // Update Active Slide Label
      const activeLabel = document.getElementById(`tour-active-label-${tourId}`);
      if (activeLabel) {
        activeLabel.textContent = `Slide ${curSlide}: ${slot.name ? slot.name.replace(/^Tour: [^—]+ — /, '') : ''}`;
      }

      const slotKeyEl = document.getElementById(`tour-slot-key-${tourId}`);
      if (slotKeyEl) {
        slotKeyEl.textContent = `Slot ID: ${slotKey}`;
      }

      const activeStatusEl = document.getElementById(`tour-active-status-${tourId}`);
      if (activeStatusEl) {
        activeStatusEl.innerHTML = isCustom 
          ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Custom Image</span>`
          : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">Default Asset</span>`;
      }

      const altInput = document.getElementById(`tour-alt-input-${tourId}`);
      if (altInput) altInput.value = slot.alt || '';

      const resetBtn = document.getElementById(`tour-reset-btn-${tourId}`);
      if (resetBtn) {
        if (isCustom) {
          resetBtn.className = 'py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer';
          resetBtn.disabled = false;
        } else {
          resetBtn.className = 'py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors text-gray-500 opacity-40 cursor-not-allowed';
          resetBtn.disabled = true;
        }
      }
    }

    function handleTourSlideFilePicked(tourId, file) {
      if (!file) return;
      const curSlide = tourActiveSlide[tourId] || 1;
      const tour = TOURS_CONFIG.find(t => t.id === tourId);
      if (!tour) return;
      const slotKey = `${tour.prefix}${curSlide}`;
      inspectAndProcessImage(file, slotKey, tourId);
    }

    async function resetTourSlide(tourId) {
      const curSlide = tourActiveSlide[tourId] || 1;
      const tour = TOURS_CONFIG.find(t => t.id === tourId);
      if (!tour) return;
      const slotKey = `${tour.prefix}${curSlide}`;
      await confirmResetSlot(slotKey);
    }

    function saveTourSlideAlt(tourId) {
      const curSlide = tourActiveSlide[tourId] || 1;
      const tour = TOURS_CONFIG.find(t => t.id === tourId);
      if (!tour) return;
      const slotKey = `${tour.prefix}${curSlide}`;
      const altInput = document.getElementById(`tour-alt-input-${tourId}`);
      if (!altInput) return;
      saveAltText(slotKey, altInput.value);
    }

    // -----------------------------------------------------------------
    // Render Core Slots (Homepage Order with True Aspect Ratios)
    // -----------------------------------------------------------------
    function renderSlots() {
      const container = document.getElementById('slots-sections-container');
      if (!container) return;
      container.innerHTML = '';

      // -------------------------------------------------------------
      // SECTION 1: FEATURED TOUR CARDS (16:10 Landscape)
      // -------------------------------------------------------------
      const toursSection = document.createElement('section');
      toursSection.id = 'sec-tours';
      toursSection.className = 'slots-group-section space-y-5';
      toursSection.setAttribute('data-section-group', 'tours');

      let toursHtml = `
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
          <button type="button" onclick="toggleTourViewMode()" id="tour-view-toggle-btn" class="px-4 py-2.5 rounded-lg bg-[#162E4D] hover:bg-[#1E3A5F] text-xs font-heading uppercase tracking-wider text-[#F5EFEB] border border-white/20 flex items-center gap-2 transition-all shadow-md">
            <i data-lucide="${tourSlideView === 'card' ? 'grid' : 'layers'}" class="w-4 h-4 text-[#F5EFEB]"></i>
            <span class="font-bold">${tourSlideView === 'card' ? 'View All 18 Slides Grid' : 'Tour Card View'}</span>
          </button>
        </div>
      `;

      if (tourSlideView === 'card') {
        toursHtml += `<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">`;
        TOURS_CONFIG.forEach(tour => {
          const curSlide = tourActiveSlide[tour.id] || 1;
          const slotKey = `${tour.prefix}${curSlide}`;
          const slot = currentRegistry.slots[slotKey] || DEFAULT_SLOTS[slotKey] || {};
          const isCustom = Boolean(slot.url);
          const activeUrl = isCustom ? '../' + slot.url : '../' + slot.default;

          let customCount = 0;
          for (let s = 1; s <= tour.slidesCount; s++) {
            const k = `${tour.prefix}${s}`;
            if (currentRegistry.slots[k] && currentRegistry.slots[k].url) customCount++;
          }

          toursHtml += `
            <div class="tour-cms-card bg-[#1E3A5F] border border-white/15 rounded-xl overflow-hidden shadow-xl flex flex-col justify-between" data-tour-id="${tour.id}">
              <!-- Header -->
              <div class="p-4 border-b border-white/10 bg-[#162E4D]/80 flex items-start justify-between gap-2">
                <div>
                  <span class="inline-block px-2 py-0.5 rounded text-[9px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-1">
                    ${tour.badge}
                  </span>
                  <h4 class="font-heading text-base font-bold uppercase tracking-wide text-white leading-tight">
                    ${tour.title}
                  </h4>
                  <p class="text-[11px] text-gray-300 mt-0.5">${tour.sub}</p>
                </div>
                <div class="shrink-0">
                  ${customCount > 0 
                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>${customCount}/6 Custom
                       </span>`
                    : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">
                        Default
                       </span>`
                  }
                </div>
              </div>

              <!-- Main Media Box in 16:10 Landscape -->
              <div class="relative w-full overflow-hidden bg-black/60 group" style="aspect-ratio: 16 / 10;">
                <img 
                  id="tour-preview-${tour.id}" 
                  src="${activeUrl}" 
                  alt="${slot.alt || ''}" 
                  class="w-full h-full object-cover transition-all duration-300" 
                  loading="lazy" 
                />
                
                <!-- Slide label pill -->
                <div class="absolute top-2.5 left-2.5 z-10 flex items-center gap-1.5 px-2 py-1 rounded bg-black/70 backdrop-blur-sm text-[10px] font-heading uppercase tracking-wider text-white border border-white/20">
                  <span id="tour-badge-text-${tour.id}">Slide ${curSlide} of 6 ${isCustom ? '(Custom)' : '(Default)'}</span>
                </div>

                <!-- Maximize view -->
                <a id="tour-max-link-${tour.id}" href="${activeUrl}" target="_blank" class="absolute top-2.5 right-2.5 z-10 p-1.5 rounded bg-black/70 hover:bg-black text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity" title="Open High-Res Image">
                  <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                </a>

                <!-- Next / Prev Overlay Controls -->
                <button type="button" onclick="prevTourSlide('${tour.id}')" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" title="Previous Slide">
                  <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button type="button" onclick="nextTourSlide('${tour.id}')" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" title="Next Slide">
                  <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>

                <!-- Progression Dots (Matching Homepage) -->
                <div class="slideshow-indicators absolute bottom-2.5 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-10 pointer-events-none">
                  ${Array.from({length: 6}, (_, i) => {
                    const active = (i + 1) === curSlide;
                    return `<span id="tour-dot-${tour.id}-${i+1}" class="w-2 h-2 rounded-full transition-all duration-300 ${active ? 'bg-[#F5EFEB] scale-125 shadow-md' : 'bg-white/40'}"></span>`;
                  }).join('')}
                </div>

                <!-- Loader -->
                <div id="tour-loader-${tour.id}" class="absolute inset-0 bg-black/80 flex-col items-center justify-center gap-2 hidden z-20">
                  <div class="w-8 h-8 border-2 border-[#F5EFEB] border-t-transparent rounded-full animate-spin"></div>
                  <span class="text-xs text-white font-heading uppercase tracking-wider">Updating Slide...</span>
                </div>
              </div>

              <!-- Interactive 6-Slide Thumbnails Strip -->
              <div class="p-3 bg-[#162E4D]/80 border-t border-b border-white/10">
                <div class="flex items-center justify-between text-[10px] font-heading uppercase tracking-wider text-gray-400 mb-2">
                  <span>Switch Slide (1 to 6):</span>
                  <span id="tour-strip-status-${tour.id}" class="text-[#F5EFEB] font-semibold">Slide ${curSlide} Selected</span>
                </div>
                <div class="grid grid-cols-6 gap-1.5 sm:gap-2">
                  ${Array.from({length: 6}, (_, i) => {
                    const s = i + 1;
                    const sKey = `${tour.prefix}${s}`;
                    const sSlot = currentRegistry.slots[sKey] || DEFAULT_SLOTS[sKey] || {};
                    const sCustom = Boolean(sSlot.url);
                    const sUrl = sCustom ? '../' + sSlot.url : '../' + sSlot.default;
                    const sActive = s === curSlide;

                    return `
                      <button 
                        type="button" 
                        onclick="setTourSlide('${tour.id}', ${s})" 
                        id="tour-thumb-${tour.id}-${s}" 
                        class="relative rounded overflow-hidden transition-all duration-200 group/thumb ${sActive ? 'ring-2 ring-[#F5EFEB] scale-105 shadow-md' : 'opacity-65 hover:opacity-100 hover:ring-1 hover:ring-white/30'}" 
                        style="aspect-ratio: 16 / 10;" 
                        title="View Slide ${s}">
                        <img id="tour-thumb-img-${tour.id}-${s}" src="${sUrl}" alt="Slide ${s}" class="w-full h-full object-cover" loading="lazy" />
                        <span class="absolute bottom-0 inset-x-0 bg-black/75 text-[8px] font-heading font-bold text-center py-0.5 text-white">#${s}</span>
                        ${sCustom ? `<span id="tour-thumb-custom-${tour.id}-${s}" class="absolute top-0.5 right-0.5 w-2 h-2 rounded-full bg-emerald-400 ring-1 ring-black" title="Custom Override"></span>` : `<span id="tour-thumb-custom-${tour.id}-${s}" class="hidden absolute top-0.5 right-0.5 w-2 h-2 rounded-full bg-emerald-400 ring-1 ring-black"></span>`}
                      </button>
                    `;
                  }).join('')}
                </div>
              </div>

              <!-- Active Slide Controls -->
              <div class="p-4 space-y-3 bg-[#1E3A5F] flex-1 flex flex-col justify-between">
                <div>
                  <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                      <span id="tour-active-label-${tour.id}" class="block text-xs font-heading font-bold uppercase tracking-wider text-white">
                        Slide ${curSlide}: ${slot.name ? slot.name.replace(/^Tour: [^—]+ — /, '') : ''}
                      </span>
                      <span id="tour-slot-key-${tour.id}" class="text-[10px] text-gray-400 font-mono">Slot: ${slotKey}</span>
                    </div>
                    <div id="tour-active-status-${tour.id}">
                      ${isCustom 
                        ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Custom Image</span>`
                        : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">Default Asset</span>`
                      }
                    </div>
                  </div>

                  <!-- Alt Text -->
                  <div>
                    <label class="block text-[10px] font-heading uppercase tracking-wider text-gray-400 mb-1">Slide SEO Alt Text</label>
                    <div class="flex items-center gap-1.5">
                      <input 
                        type="text" 
                        id="tour-alt-input-${tour.id}" 
                        value="${slot.alt || ''}" 
                        placeholder="Image description for SEO" 
                        class="flex-1 px-2.5 py-1.5 bg-[#162E4D] border border-white/15 rounded text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]" 
                      />
                      <button type="button" onclick="saveTourSlideAlt('${tour.id}')" class="p-1.5 rounded bg-white/10 hover:bg-white/20 text-white transition-colors text-xs" title="Save Alt">
                        <i data-lucide="check" class="w-4 h-4"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Replace, Crop & Reset -->
                <div class="pt-2 border-t border-white/10 flex items-center gap-2">
                  <input type="file" id="tour-file-input-${tour.id}" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleTourSlideFilePicked('${tour.id}', this.files[0])" />
                  <button type="button" onclick="document.getElementById('tour-file-input-${tour.id}').click()" class="flex-1 py-2 px-3 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-1.5 shadow">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                    <span>Replace Slide</span>
                  </button>
                  <button type="button" onclick="openCropForTourSlide('${tour.id}')" class="py-2 px-2.5 rounded bg-[#162E4D] hover:bg-[#2A4E7A] text-[#F5EFEB] border border-white/20 text-xs font-heading uppercase tracking-wider transition-colors flex items-center gap-1 shadow" title="Crop & Re-frame This Slide">
                    <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Crop</span>
                  </button>
                  <button 
                    type="button" 
                    id="tour-reset-btn-${tour.id}" 
                    onclick="resetTourSlide('${tour.id}')" 
                    class="py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors ${isCustom ? 'text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer' : 'text-gray-500 opacity-40 cursor-not-allowed'}" 
                    ${isCustom ? '' : 'disabled'} 
                    title="Revert slide to original default">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                  </button>
                </div>

              </div>
            </div>
          `;
        });
        toursHtml += `</div>`;
      } else {
        // Render All 18 Tour Slides in 16:10 Grid
        toursHtml += `<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">`;
        TOURS_CONFIG.forEach(tour => {
          for (let s = 1; s <= tour.slidesCount; s++) {
            const slotKey = `${tour.prefix}${s}`;
            const slot = currentRegistry.slots[slotKey] || DEFAULT_SLOTS[slotKey] || {};
            const isCustom = Boolean(slot.url);
            const activeUrl = isCustom ? '../' + slot.url : '../' + slot.default;

            toursHtml += `
              <div class="slot-card bg-[#1E3A5F] border border-white/10 rounded-xl overflow-hidden shadow flex flex-col justify-between" data-slot-key="${slotKey}">
                <div class="p-2.5 bg-[#162E4D]/80 border-b border-white/10 flex items-center justify-between">
                  <span class="text-[10px] font-heading font-bold uppercase text-white truncate">${tour.title} #${s}</span>
                  ${isCustom ? `<span class="w-2 h-2 rounded-full bg-emerald-400"></span>` : `<span class="w-2 h-2 rounded-full bg-gray-500"></span>`}
                </div>
                <div class="relative w-full overflow-hidden bg-black/40 group" style="aspect-ratio: 16 / 10;">
                  <img id="preview-img-${slotKey}" src="${activeUrl}" alt="${slot.alt || ''}" class="w-full h-full object-cover" loading="lazy" />
                  <a href="${activeUrl}" target="_blank" class="absolute top-1.5 right-1.5 p-1 rounded bg-black/70 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                  </a>
                  <div id="loader-${slotKey}" class="absolute inset-0 bg-black/80 flex-col items-center justify-center gap-1 hidden">
                    <div class="w-5 h-5 border-2 border-[#F5EFEB] border-t-transparent rounded-full animate-spin"></div>
                  </div>
                </div>
                <div class="p-2.5 space-y-2">
                  <input type="file" id="file-input-${slotKey}" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleSlotFileUpload('${slotKey}', this.files[0])" />
                  <div class="flex items-center gap-1">
                    <button type="button" onclick="document.getElementById('file-input-${slotKey}').click()" class="flex-1 py-1.5 px-2 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-[10px] font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-1">
                      <i data-lucide="upload" class="w-3 h-3"></i>
                      <span>Replace</span>
                    </button>
                    <button type="button" onclick="openCropForCurrentSlot('${slotKey}')" class="py-1.5 px-2 rounded bg-[#162E4D] hover:bg-[#2A4E7A] text-[#F5EFEB] border border-white/15 text-[10px] font-heading uppercase tracking-wider transition-colors flex items-center justify-center" title="Crop & Re-frame">
                      <i data-lucide="crop" class="w-3 h-3"></i>
                    </button>
                  </div>
                  ${isCustom ? `<button type="button" onclick="confirmResetSlot('${slotKey}')" class="w-full py-1 text-[10px] text-red-300 hover:underline">Reset</button>` : ''}
                </div>
              </div>
            `;
          }
        });
        toursHtml += `</div>`;
      }

      toursSection.innerHTML = toursHtml;
      container.appendChild(toursSection);

      // -------------------------------------------------------------
      // SECTION 2: WHY RIDE WITH US (9:16 Vertical Portrait)
      // -------------------------------------------------------------
      const whyRideSection = document.createElement('section');
      whyRideSection.id = 'sec-why-ride';
      whyRideSection.className = 'slots-group-section space-y-5';
      whyRideSection.setAttribute('data-section-group', 'why-ride');

      let whyRideHtml = `
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/10">
          <div>
            <div class="flex items-center gap-2 mb-0.5">
              <span class="px-2 py-0.5 rounded text-[10px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] border border-white/10 font-semibold">9:16 Portrait</span>
              <h3 class="font-heading text-lg sm:text-xl font-bold uppercase tracking-wider text-white">
                Why Ride With Us (5 Vertical Portrait Cards)
              </h3>
            </div>
            <p class="text-xs text-gray-400">
              Matches the tall 9:16 vertical cards on the homepage. Rendered at full height without top or bottom cropping.
            </p>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
      `;

      WHY_RIDE_KEYS.forEach((key, idx) => {
        const slot = currentRegistry.slots[key] || DEFAULT_SLOTS[key] || {};
        const isCustom = Boolean(slot.url);
        const activeUrl = isCustom ? '../' + slot.url : '../' + slot.default;

        whyRideHtml += `
          <div class="slot-card bg-[#1E3A5F] border border-white/15 rounded-xl overflow-hidden shadow-lg flex flex-col justify-between" data-slot-key="${key}" data-section="Why Ride With Us">
            <!-- Header -->
            <div class="p-3 bg-[#162E4D]/80 border-b border-white/10 flex items-start justify-between gap-1.5">
              <div>
                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-0.5">
                  Card 0${idx + 1}
                </span>
                <h4 class="font-heading text-xs font-bold uppercase tracking-wide text-white leading-tight">
                  ${slot.name.replace(/^Why Ride: /, '')}
                </h4>
              </div>
              <div class="shrink-0">
                ${isCustom 
                  ? `<span class="inline-block w-2 h-2 rounded-full bg-green-400" title="Custom Upload"></span>`
                  : `<span class="inline-block w-2 h-2 rounded-full bg-gray-500" title="Default"></span>`
                }
              </div>
            </div>

            <!-- True 9:16 Portrait Container -->
            <div class="relative w-full overflow-hidden bg-black/50 group" style="aspect-ratio: 9 / 16;">
              <img 
                id="preview-img-${key}" 
                src="${activeUrl}" 
                alt="${slot.alt || ''}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                loading="lazy" 
              />
              <a href="${activeUrl}" target="_blank" class="absolute top-2 right-2 p-1.5 rounded bg-black/70 hover:bg-black text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity" title="Open Full-Size">
                <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
              </a>
              <div id="loader-${key}" class="absolute inset-0 bg-black/80 flex-col items-center justify-center gap-2 hidden z-20">
                <div class="w-7 h-7 border-2 border-[#F5EFEB] border-t-transparent rounded-full animate-spin"></div>
                <span class="text-[10px] text-white font-heading uppercase tracking-wider">Updating...</span>
              </div>
            </div>

            <!-- Body & Actions -->
            <div class="p-3 space-y-2.5 bg-[#1E3A5F]">
              <div>
                <label class="block text-[9px] font-heading uppercase tracking-wider text-gray-400 mb-1">Alt Text</label>
                <div class="flex items-center gap-1">
                  <input 
                    type="text" 
                    id="alt-input-${key}" 
                    value="${slot.alt || ''}" 
                    placeholder="SEO description" 
                    class="flex-1 px-2 py-1 bg-[#162E4D] border border-white/15 rounded text-[11px] text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]" 
                  />
                  <button type="button" onclick="saveAltText('${key}')" class="p-1 rounded bg-white/10 hover:bg-white/20 text-white transition-colors" title="Save">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                  </button>
                </div>
              </div>

              <div class="pt-1.5 border-t border-white/10 flex items-center gap-1.5">
                <input type="file" id="file-input-${key}" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleSlotFileUpload('${key}', this.files[0])" />
                <button type="button" onclick="document.getElementById('file-input-${key}').click()" class="flex-1 py-1.5 px-2 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-[10px] font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-1 shadow">
                  <i data-lucide="upload" class="w-3 h-3"></i>
                  <span>Replace</span>
                </button>
                <button type="button" onclick="openCropForCurrentSlot('${key}')" class="py-1.5 px-2 rounded bg-[#162E4D] hover:bg-[#2A4E7A] text-[#F5EFEB] border border-white/15 text-[10px] font-heading uppercase tracking-wider transition-colors flex items-center justify-center gap-1" title="Crop & Re-frame">
                  <i data-lucide="crop" class="w-3 h-3"></i>
                  <span>Crop</span>
                </button>
                <button 
                  type="button" 
                  id="reset-btn-${key}" 
                  onclick="confirmResetSlot('${key}')" 
                  class="py-1.5 px-2 rounded border border-white/15 text-[10px] font-heading uppercase tracking-wider transition-colors ${isCustom ? 'text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer' : 'text-gray-500 opacity-40 cursor-not-allowed'}" 
                  ${isCustom ? '' : 'disabled'} 
                  title="Revert to default">
                  <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      });
      whyRideHtml += `</div>`;
      whyRideSection.innerHTML = whyRideHtml;
      container.appendChild(whyRideSection);

      // -------------------------------------------------------------
      // SECTION 3: THE CREW (4:5 Portrait)
      // -------------------------------------------------------------
      const crewSection = document.createElement('section');
      crewSection.id = 'sec-crew';
      crewSection.className = 'slots-group-section space-y-5';
      crewSection.setAttribute('data-section-group', 'crew');

      let crewHtml = `
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/10">
          <div>
            <div class="flex items-center gap-2 mb-0.5">
              <span class="px-2 py-0.5 rounded text-[10px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] border border-white/10 font-semibold">4:5 Portrait</span>
              <h3 class="font-heading text-lg sm:text-xl font-bold uppercase tracking-wider text-white">
                The Crew &amp; Guides (3 Profiles)
              </h3>
            </div>
            <p class="text-xs text-gray-400">
              Matches the 4:5 portrait guide photos on the homepage.
            </p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      `;

      CREW_KEYS.forEach(key => {
        const slot = currentRegistry.slots[key] || DEFAULT_SLOTS[key] || {};
        const isCustom = Boolean(slot.url);
        const activeUrl = isCustom ? '../' + slot.url : '../' + slot.default;

        crewHtml += `
          <div class="slot-card bg-[#1E3A5F] border border-white/15 rounded-xl overflow-hidden shadow-lg flex flex-col justify-between" data-slot-key="${key}" data-section="The Crew">
            <!-- Header -->
            <div class="p-3.5 bg-[#162E4D]/80 border-b border-white/10 flex items-start justify-between gap-2">
              <div>
                <span class="inline-block px-2 py-0.5 rounded text-[9px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-0.5">
                  Guide Profile
                </span>
                <h4 class="font-heading text-sm font-bold uppercase tracking-wide text-white leading-tight">
                  ${slot.name}
                </h4>
              </div>
              <div class="shrink-0">
                ${isCustom 
                  ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Custom</span>`
                  : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">Default</span>`
                }
              </div>
            </div>

            <!-- 4:5 Portrait Container -->
            <div class="relative w-full overflow-hidden bg-black/50 group" style="aspect-ratio: 4 / 5;">
              <img 
                id="preview-img-${key}" 
                src="${activeUrl}" 
                alt="${slot.alt || ''}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                loading="lazy" 
              />
              <a href="${activeUrl}" target="_blank" class="absolute top-2 right-2 p-1.5 rounded bg-black/70 hover:bg-black text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity" title="Open Full-Size">
                <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
              </a>
              <div id="loader-${key}" class="absolute inset-0 bg-black/80 flex-col items-center justify-center gap-2 hidden z-20">
                <div class="w-7 h-7 border-2 border-[#F5EFEB] border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs text-white font-heading uppercase tracking-wider">Updating...</span>
              </div>
            </div>

            <!-- Body & Actions -->
            <div class="p-4 space-y-3 bg-[#1E3A5F]">
              <div>
                <label class="block text-[10px] font-heading uppercase tracking-wider text-gray-400 mb-1">Alt Text / Description</label>
                <div class="flex items-center gap-1.5">
                  <input 
                    type="text" 
                    id="alt-input-${key}" 
                    value="${slot.alt || ''}" 
                    placeholder="SEO description" 
                    class="flex-1 px-2.5 py-1.5 bg-[#162E4D] border border-white/15 rounded text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]" 
                  />
                  <button type="button" onclick="saveAltText('${key}')" class="p-1.5 rounded bg-white/10 hover:bg-white/20 text-white transition-colors text-xs" title="Save">
                    <i data-lucide="check" class="w-4 h-4"></i>
                  </button>
                </div>
              </div>

              <div class="pt-2 border-t border-white/10 flex items-center gap-2">
                <input type="file" id="file-input-${key}" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleSlotFileUpload('${key}', this.files[0])" />
                <button type="button" onclick="document.getElementById('file-input-${key}').click()" class="flex-1 py-2 px-3 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-1.5 shadow">
                  <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                  <span>Replace Photo</span>
                </button>
                <button type="button" onclick="openCropForCurrentSlot('${key}')" class="py-2 px-2.5 rounded bg-[#162E4D] hover:bg-[#2A4E7A] text-[#F5EFEB] border border-white/20 text-xs font-heading uppercase tracking-wider transition-colors flex items-center gap-1 shadow" title="Crop & Re-frame Photo">
                  <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                  <span>Crop</span>
                </button>
                <button 
                  type="button" 
                  id="reset-btn-${key}" 
                  onclick="confirmResetSlot('${key}')" 
                  class="py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors ${isCustom ? 'text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer' : 'text-gray-500 opacity-40 cursor-not-allowed'}" 
                  ${isCustom ? '' : 'disabled'} 
                  title="Revert to default">
                  <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      });
      crewHtml += `</div>`;
      crewSection.innerHTML = crewHtml;
      container.appendChild(crewSection);

      // -------------------------------------------------------------
      // SECTION 4: HERO SECTION (16:9 Landscape)
      // -------------------------------------------------------------
      const heroSection = document.createElement('section');
      heroSection.id = 'sec-hero';
      heroSection.className = 'slots-group-section space-y-5';
      heroSection.setAttribute('data-section-group', 'hero');

      const heroKey = 'hero-bg';
      const heroSlot = currentRegistry.slots[heroKey] || DEFAULT_SLOTS[heroKey] || {};
      const heroCustom = Boolean(heroSlot.url);
      const heroActiveUrl = heroCustom ? '../' + heroSlot.url : '../' + heroSlot.default;

      heroSection.innerHTML = `
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/10">
          <div>
            <div class="flex items-center gap-2 mb-0.5">
              <span class="px-2 py-0.5 rounded text-[10px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] border border-white/10 font-semibold">16:9 Widescreen</span>
              <h3 class="font-heading text-lg sm:text-xl font-bold uppercase tracking-wider text-white">
                Hero Section Video Fallback &amp; Poster
              </h3>
            </div>
            <p class="text-xs text-gray-400">
              Widescreen 16:9 poster shown behind the main "NAMASTE" hero title when loading or on mobile data saver.
            </p>
          </div>
        </div>
        <div class="max-w-2xl mx-auto">
          <div class="slot-card bg-[#1E3A5F] border border-white/15 rounded-xl overflow-hidden shadow-xl" data-slot-key="${heroKey}" data-section="Hero">
            <div class="p-4 bg-[#162E4D]/80 border-b border-white/10 flex items-center justify-between">
              <div>
                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-heading uppercase tracking-wider bg-[#2A4E7A] text-[#F5EFEB] mb-0.5">Hero Media</span>
                <h4 class="font-heading text-sm font-bold uppercase tracking-wide text-white">Hero Video Poster Image</h4>
              </div>
              <div>
                ${heroCustom 
                  ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-950 border border-green-500/40 text-green-300"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Custom</span>`
                  : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-800 border border-white/10 text-gray-400">Default</span>`
                }
              </div>
            </div>
            <div class="relative w-full overflow-hidden bg-black/60 group" style="aspect-ratio: 16 / 9;">
              <img id="preview-img-${heroKey}" src="${heroActiveUrl}" alt="${heroSlot.alt || ''}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
              <a href="${heroActiveUrl}" target="_blank" class="absolute top-2 right-2 p-1.5 rounded bg-black/70 hover:bg-black text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
              </a>
              <div id="loader-${heroKey}" class="absolute inset-0 bg-black/80 flex-col items-center justify-center gap-2 hidden z-20">
                <div class="w-7 h-7 border-2 border-[#F5EFEB] border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs text-white font-heading uppercase tracking-wider">Updating...</span>
              </div>
            </div>
            <div class="p-4 space-y-3 bg-[#1E3A5F]">
              <div>
                <label class="block text-[10px] font-heading uppercase tracking-wider text-gray-400 mb-1">Alt Text / SEO</label>
                <div class="flex items-center gap-1.5">
                  <input type="text" id="alt-input-${heroKey}" value="${heroSlot.alt || ''}" class="flex-1 px-2.5 py-1.5 bg-[#162E4D] border border-white/15 rounded text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#F5EFEB]" />
                  <button type="button" onclick="saveAltText('${heroKey}')" class="p-1.5 rounded bg-white/10 hover:bg-white/20 text-white transition-colors text-xs"><i data-lucide="check" class="w-4 h-4"></i></button>
                </div>
              </div>
              <div class="pt-2 border-t border-white/10 flex items-center gap-2">
                <input type="file" id="file-input-${heroKey}" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleSlotFileUpload('${heroKey}', this.files[0])" />
                <button type="button" onclick="document.getElementById('file-input-${heroKey}').click()" class="flex-1 py-2 px-3 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-xs font-bold uppercase tracking-wider rounded transition-all flex items-center justify-center gap-1.5 shadow">
                  <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                  <span>Replace Hero Media</span>
                </button>
                <button type="button" onclick="openCropForCurrentSlot('${heroKey}')" class="py-2 px-2.5 rounded bg-[#162E4D] hover:bg-[#2A4E7A] text-[#F5EFEB] border border-white/20 text-xs font-heading uppercase tracking-wider transition-colors flex items-center gap-1 shadow" title="Crop & Re-frame Hero Media">
                  <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                  <span>Crop</span>
                </button>
                <button type="button" id="reset-btn-${heroKey}" onclick="confirmResetSlot('${heroKey}')" class="py-2 px-3 rounded border border-white/15 text-xs font-heading uppercase tracking-wider transition-colors ${heroCustom ? 'text-red-400 hover:border-red-400 hover:bg-red-950/30 cursor-pointer' : 'text-gray-500 opacity-40 cursor-not-allowed'}" ${heroCustom ? '' : 'disabled'}>
                  <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
      container.appendChild(heroSection);

      lucide.createIcons();
    }

    // -----------------------------------------------------------------
    // Filter & Search Slots
    // -----------------------------------------------------------------
    function setSectionFilter(secKey) {
      const secEl = document.getElementById('section-filter');
      if (secEl) secEl.value = secKey;
      filterSlots();
    }

    function filterSlots() {
      const searchEl = document.getElementById('slot-search');
      const q = (searchEl ? searchEl.value : '').toLowerCase().trim();
      const secEl = document.getElementById('section-filter');
      const sec = secEl ? secEl.value : 'all';

      // Update Quick Jump Pills
      document.querySelectorAll('.sec-pill').forEach(btn => {
        btn.classList.remove('bg-[#F5EFEB]', 'text-black', 'font-semibold');
        btn.classList.add('bg-[#162E4D]', 'text-gray-300');
      });
      const activePill = document.getElementById('pill-' + sec);
      if (activePill) {
        activePill.classList.add('bg-[#F5EFEB]', 'text-black', 'font-semibold');
        activePill.classList.remove('bg-[#162E4D]', 'text-gray-300');
      }

      // Check sections
      const sections = [
        { id: 'sec-tours', group: 'tours' },
        { id: 'sec-why-ride', group: 'why-ride' },
        { id: 'sec-crew', group: 'crew' },
        { id: 'sec-hero', group: 'hero' }
      ];

      sections.forEach(s => {
        const el = document.getElementById(s.id);
        if (!el) return;

        let visible = (sec === 'all' || sec === s.group);

        if (sec === 'custom-only') {
          if (s.group === 'tours') {
            visible = TOURS_CONFIG.some(t => {
              for (let i = 1; i <= t.slidesCount; i++) {
                const k = `${t.prefix}${i}`;
                if (currentRegistry.slots[k] && currentRegistry.slots[k].url) return true;
              }
              return false;
            });
          } else if (s.group === 'why-ride') {
            visible = WHY_RIDE_KEYS.some(k => currentRegistry.slots[k] && currentRegistry.slots[k].url);
          } else if (s.group === 'crew') {
            visible = CREW_KEYS.some(k => currentRegistry.slots[k] && currentRegistry.slots[k].url);
          } else if (s.group === 'hero') {
            visible = Boolean(currentRegistry.slots['hero-bg'] && currentRegistry.slots['hero-bg'].url);
          }
        }

        if (q) {
          const text = el.textContent.toLowerCase();
          visible = visible && text.includes(q);
        }

        el.style.display = visible ? 'block' : 'none';
      });
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
                resolve(file);
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
    function handleSlotFileUpload(slotKey, file) {
      if (!file) return;
      inspectAndProcessImage(file, slotKey, null);
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
          if (currentRegistry.slots[slotKey]) {
            currentRegistry.slots[slotKey].url = '';
          }
          renderSlots();
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
    async function saveAltText(slotKey, explicitVal = null) {
      const altVal = explicitVal !== null ? explicitVal : (document.getElementById('alt-input-' + slotKey)?.value || '');

      try {
        const formData = new FormData();
        formData.append('slot_key', slotKey);
        formData.append('alt', altVal);
        formData.append('csrf_token', CSRF_TOKEN);

        const response = await fetch('../api/update-slot.php', {
          method: 'POST',
          body: formData
        });
        const res = await response.json();
        if (res.success) {
          if (currentRegistry.slots[slotKey]) currentRegistry.slots[slotKey].alt = altVal;
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
          const elCustom = document.getElementById('stat-custom-slots');
          if (elCustom) elCustom.textContent = customCount;
          const elGal = document.getElementById('stat-gallery-count');
          if (elGal) elGal.textContent = data.gallery ? data.gallery.length : 0;
          const galCountEl = document.getElementById('gallery-items-count');
          if (galCountEl) galCountEl.textContent = data.gallery ? data.gallery.length : 0;

          if (data.stats) {
            const elDisk = document.getElementById('stat-disk-usage');
            if (elDisk) elDisk.innerHTML = `${data.stats.total_size_mb} <span class="text-xs font-normal text-gray-400">MB</span>`;
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
