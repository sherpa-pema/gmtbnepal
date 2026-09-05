# Frontend UI & Design System Specification

A high-performance, dark-first visual identity and design system engineered for high-octane tours, rugged adventure tours, and premium outdoor experiences.

---

## 1. Design Philosophy & Visual Archetype

### 1.1 Aesthetic Identity: *"Rugged Editorial & High-Octane Cinematic"*
* **Dark-First Canvas**: Deep, obsidian-black and dark charcoal tones establish an immersive, cinema-grade atmosphere that makes high-resolution photography and video pop.
* **High-Voltage Accent**: A single warm beige/ivory (`#F5EFEB`) acts as the high-visibility focal accent—reminiscent of tour gear, caution trail markings, and tactical instruments.
* **Industrial Condensed Typography**: High-impact, tall headline typefaces paired with tracked uppercase subheadings deliver an editorial, tour-poster energy.
* **Atmospheric Motion & Layering**: Subtle viewport scale-ups, continuous horizontal ticker marquees, parallax background shifts, and video-on-hover card interactions bring life without sacrificing performance.
* **Tactical Hairline Borders**: Subtle low-opacity borders (`rgba(255, 255, 255, 0.08)`) and frosted glass surfaces (`backdrop-filter: blur(1px)` to `blur(12px)`) provide structure and depth without visual clutter.

---

## 2. Color System & Design Tokens

### 2.1 Palette Breakdown

| Token Name | Value | Usage / Semantic Role |
| :--- | :--- | :--- |
| `--brand-primary` | `#F5EFEB` | Primary CTA buttons, active states, highlights, ticker text, key badges, breadcrumb active links. |
| `--brand-primary-hover` | `#E8DFD8` / `#FFFFFF` | Button hover shifts, accented borders, active glow. |
| `--bg-canvas` | `#2A4E7A` | Global background canvas, hero background, section backdrops. |
| `--bg-surface-primary` | `#1E3A5F` | Main section backgrounds, cards, modal dialogs, large content panels. |
| `--bg-surface-secondary` | `#162E4D` | Form inputs, footers, secondary container blocks. |
| `--bg-surface-elevated` | `#345C8C` | Card hover elevation, scrollbar thumbs, subtle pills. |
| `--bg-frosted-nav` | `rgba(42, 78, 122, 0.85)` | Sticky header backdrop with `backdrop-filter: blur(10px)`. |
| `--border-subtle` | `rgba(255, 255, 255, 0.12)` | Card borders, table dividers, structural hairline rules. |
| `--border-strong` | `rgba(255, 255, 255, 0.25)` | Button borders (ghost mode), active outlines. |
| `--text-primary` | `#FFFFFF` | Primary headlines, hero text, active button labels. |
| `--text-secondary` | `#E2E8F0` / `#D1D5DB` | Subheadings, body copy, itinerary descriptions. |
| `--text-muted` | `#94A3B8` / `#CBD5E1` | Metadata labels, specs, duration tags, fine print. |
| `--text-accent` | `#F5EFEB` | Accented keywords, ratings, prices, category tags. |
| `--state-disabled` | `rgba(255, 255, 255, 0.40)` | "Sold Out" state, disabled triggers. |

### 2.2 Color Utility Rules
* **Never use pure white backgrounds** for containers; contrast must always be light-on-dark.
* **Accent Discipline**: Use `#F5EFEB` deliberately for high-priority actions, active navigation states, and micro-tags. Do not overuse on long body paragraphs.
* **Dividers**: Prefer `1px solid rgba(255, 255, 255, 0.08)` over solid gray lines.

---

## 3. Typography Hierarchy & Font Stacks

### 3.1 Font Families

```css
:root {
  --font-display: "Anton", -apple-system, BlinkMacSystemFont, "Impact", sans-serif;
  --font-heading: "Oswald", -apple-system, BlinkMacSystemFont, "Arial Narrow", sans-serif;
  --font-body: "Roboto", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  --font-mono: ui-monospace, Menlo, Monaco, Consolas, monospace;
}
```

### 3.2 Type Scale & Applications

| Element / Class | Font Family | Size (Desktop / Mobile) | Weight | Transform | Tracking (Letter-Spacing) | Line Height |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Hero Title (`.hero-title`)** | Anton | `72px - 96px` / `44px - 54px` | 400 | Uppercase | `0.02em` | `0.95 - 1.05` |
| **Section Header (`.h1 / .uk-h1`)** | Anton | `48px - 64px` / `32px - 40px` | 400 | Uppercase | `0.03em` | `1.1` |
| **Trip / Feature Title (`.h2`)** | Oswald | `32px - 40px` / `24px - 28px` | 700 | Uppercase | `0.05em` | `1.2` |
| **Subheading / Meta Header (`.h3`)** | Oswald | `20px - 24px` / `18px - 20px` | 600 | Uppercase | `0.08em` | `1.3` |
| **Ticker / Marquee Headline** | Oswald / Anton | `30px` / `50px - 60px` | 700 | Uppercase | `0.06em` | `1.1` |
| **Nav Links & Buttons** | Oswald | `14px - 16px` | 500 / 600 | Uppercase | `0.15em - 0.20em` | `1.2` |
| **Meta Tag / Pill / Badge** | Oswald / Roboto | `11px - 13px` | 600 / 700 | Uppercase | `0.10em` | `1.2` |
| **Body Lead / Intro** | Roboto | `18px - 20px` / `16px - 18px` | 400 | Normal | `0` | `1.6 - 1.7` |
| **Standard Body Copy** | Roboto | `15px - 16px` | 400 | Normal | `0` | `1.6 - 1.8` |
| **Micro Footer / Legal** | Roboto | `8px - 10px` | 400 | Uppercase | `0.08em` | `1.4` |

---

## 4. Spacing, Grid & Layout Architecture

### 4.1 Container Widths
* **Fluid Expanded (`.container-expand`)**: `100%` width with `20px` mobile / `40px` desktop gutter.
* **Extra Large Display (`.container-xlarge`)**: Max-width `1440px` (standard for full-bleed tour galleries and trip grids).
* **Standard Container (`.container`)**: Max-width `1200px` (used for editorial stories, blog posts, and forms).
* **Narrow / Modal Container (`.container-small`)**: Max-width `780px` (dialogs, booking forms, focused content).

### 4.2 Grid Breakpoints

```css
/* Breakpoint tokens */
--breakpoint-xs: 480px;   /* Small Mobile */
--breakpoint-s:  590px;   /* Large Mobile / Phablet */
--breakpoint-m:  768px;   /* Tablet Portrait */
--breakpoint-l:  1024px;  /* Tablet Landscape / Small Desktop */
--breakpoint-xl: 1440px;  /* Widescreen Desktop */
```

### 4.3 Section Padding Hierarchy
* **Hero / Feature Viewport Sections**: `min-height: 100svh` or `65vh - 85vh`.
* **Standard Section Padding**: `80px 0` desktop / `48px 0` mobile.
* **Compact / Card Section Padding**: `40px 0` desktop / `24px 0` mobile.
* **Card Interior Padding**: `24px - 32px` desktop / `16px - 20px` mobile.

---

## 5. Animation, Motion & Interaction Specifications

### 5.1 Scroll-Triggered Entrance Reveals
All major sections and card grids execute scrollspy entrance animations when entering the viewport.

* **Scale-Up Entrance (`.animation-scale-up`)**:
  * **Initial State**: `opacity: 0; transform: scale(0.96) translateY(20px);`
  * **Revealed State**: `opacity: 1; transform: scale(1) translateY(0);`
  * **Duration**: `600ms`
  * **Easing**: `cubic-bezier(0.16, 1, 0.3, 1)` (easeOutExpo / spring-like settle)
  * **Staggered Delays**: `200ms`, `300ms`, `500ms` applied across sibling cards.

* **Slide-Top / Fade Entrance (`.animation-fade-top`)**:
  * **Initial State**: `opacity: 0; transform: translateY(30px);`
  * **Revealed State**: `opacity: 1; transform: translateY(0);`
  * **Duration**: `500ms`

### 5.2 Parallax Multi-Plane Depth
* **Background Parallax (`bgy: 0, -60px`)**: Background images move at `40% - 60%` scroll velocity to create depth behind floating cards and typography.
* **Opposing Element Offsets**: Asymmetrical text vs image translation (`x: -50px -> -20px; y: 30px -> 60px`) giving layered physical presence.

### 5.3 Video-on-Hover Dynamic Previews
* Trip and tour preview cards feature an embedded video with `preload="none"`, `muted`, `loop`, `playsinline`.
* On card hover / desktop cursor focus:
  * Poster image smoothly dims (`opacity: 0` via `transition: opacity 0.4s ease`).
  * Video activates and plays smoothly beneath a dark vignette gradient (`rgba(0,0,0,0.3)` to `rgba(0,0,0,0.85)`).

### 5.4 Continuous Infinite Marquee Ticker
* A continuous banner marquee with warm beige accent text:
  ```css
  @keyframes marqueeAnimation {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }
  .marquee-wrapper {
    display: flex;
    width: max-content;
    animation: marqueeAnimation 35s linear infinite;
  }
  ```

### 5.5 Floating Hero Scroll Indicator
* A bouncing double-chevron icon located at `bottom: 25px`, `left: 50%`.
* **Animation**:
  ```css
  @keyframes heroBounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-8px); }
    60% { transform: translateX(-50%) translateY(-4px); }
  }
  ```
* **Interaction**: Clicking triggers smooth scrolling down by `window.innerHeight`.

---

## 6. Component Library & Anatomy

### 6.1 Navigation Bar (Header)
* **Default State**: Floats transparently over hero media (`position: absolute; top: 0; left: 0; width: 100%; z-index: 1000;`).
* **Sticky State**: When scrolled past hero, snaps to sticky with frosted blur:
  ```css
  .navbar-sticky {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    transition: all 0.3s ease;
  }
  ```
* **Nav Links**: Uppercase Oswald (`14px`, tracking `2px`, bold).
  * Inactive: `#FFFFFF` with `opacity: 0.85`.
  * Hover / Active: `#F5EFEB` with instant color shift.
* **Action CTAs**: Ghost white outline button (`1px solid #FFFFFF`), transitioning to solid white with black text on hover.

---

### 6.2 Fullscreen Immersive Hero Section
* **Height**: `100svh` (dynamic viewport units).
* **Layer 1 (Media)**: High-resolution cover poster image + lazy-loaded background video with smooth crossfade (`opacity 1.2s ease-in-out`).
* **Layer 2 (Gradient Overlay)**: `linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 40%, rgba(0,0,0,0.8) 100%)`.
* **Layer 3 (Typography & Content)**:
  * Giant Display Title in Anton (e.g. `EARTH'S HIGHEST PLAYGROUND`).
  * Italic emphasis on key nouns (`<em>Playground</em>`).
  * Action button cluster: Primary Yellow CTA + Secondary Ghost CTA.
  * Bouncing SVG chevron indicator at bottom center.

---

### 6.3 Tour / Tour Card Component
The centerpiece component used to display trips, tours, and experiences.

* **Card Surface**: `#141414` base background, `1px solid rgba(255, 255, 255, 0.08)`, `overflow: hidden`, `border-radius: 4px`.
* **Media Aspect Ratio**: `16:9` or `4:3` container with cover image and video element.
* **Hover Interaction**:
  * Card image scales subtly (`transform: scale(1.04); transition: transform 0.5s ease;`).
  * Video plays smoothly on hover.
  * Card border brightens to `rgba(255, 255, 255, 0.25)`.
* **Card Metadata Structure**:
  1. **Top Badge / Pill**: Duration & Key Route (e.g. `12 DAYS | ZANSKAR & CHANGTHANG`).
  2. **Terrain & Season Tag**: `MOSTLY DIRT, SOME TARMAC | SPRING, AUTUMN` in muted gray/gold.
  3. **Title**: Bold Oswald/Anton headline in pure white (`#FFFFFF`).
  4. **Bottom Full-Width CTA**:
     * Button: `width: 100%; height: 44px; text-transform: uppercase; font-family: "Oswald"; letter-spacing: 2px;`.
     * Outline style: `border: 1px solid #FFFFFF; color: #FFFFFF; background: transparent;`.
     * Hover: `background: #FFFFFF; color: #000000;`.

---

### 6.4 Button Style Ecosystem

```css
/* Base Button */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-heading);
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 2.5px;
  padding: 12px 28px;
  border-radius: 2px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  white-space: nowrap;
}

/* 1. Primary Accent Button */
.btn-primary {
  background-color: #F5EFEB;
  color: #000000;
  border: 1px solid #F5EFEB;
}
.btn-primary:hover {
  background-color: #000000;
  color: #F5EFEB;
  border-color: #F5EFEB;
}

/* 2. Secondary / Ghost Outline Button */
.btn-outline {
  background-color: transparent;
  color: #FFFFFF;
  border: 1px solid #FFFFFF;
}
.btn-outline:hover {
  background-color: #FFFFFF;
  color: #000000;
  border-color: #FFFFFF;
}

/* 3. Dark Secondary Button */
.btn-secondary {
  background-color: #1F1F1F;
  color: #FFFFFF;
  border: 1px solid rgba(255, 255, 255, 0.15);
}
.btn-secondary:hover {
  background-color: #F5EFEB;
  color: #000000;
  border-color: #F5EFEB;
}

/* 4. Disabled / Sold Out State */
.btn-sold-out {
  background-color: transparent !important;
  color: #FFFFFF !important;
  border: 2px solid rgba(255, 255, 255, 0.4) !important;
  opacity: 0.6 !important;
  cursor: not-allowed !important;
  pointer-events: none !important;
}
```

---

### 6.5 Interactive Booking / Departure Dates Table
A high-conversion schedule table designed to format trip dates cleanly across all device widths.

#### Desktop Layout (>1024px)
* Displayed as a multi-column CSS Grid:
  ```css
  .availability-row {
    display: grid;
    grid-template-columns: minmax(260px, max-content) minmax(260px, max-content) 1fr max-content;
    column-gap: 32px;
    align-items: center;
    padding: 16px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }
  ```
* **Column 1**: Start Date (Weekday + Day Month Year)
* **Column 2**: End Date (Weekday + Day Month Year)
* **Column 3**: Flex spacer
* **Column 4**: Fixed width action button (`min-width: 210px; min-height: 44px;`) with "Book Now" or "Sold Out" state.
* **Header Row**: Beige uppercase column titles (`#F5EFEB`, `12px`, tracking `0.08em`).

#### Tablet Layout (591px - 1024px)
* Hides End Date column to prevent wrapping.
* Layout becomes 2-column: `[Flexible Start Date] | [Fixed 210px CTA Button]`.

#### Mobile Layout (≤590px)
* Collapses into a vertical stack:
  ```css
  @media (max-width: 590px) {
    .availability-row {
      display: grid;
      grid-template-columns: 1fr;
      row-gap: 12px;
      padding: 14px 0;
      justify-items: start;
    }
    .availability-row .btn-book {
      width: 100%;
      max-width: 280px;
      min-height: 48px;
    }
  }
  ```

---

### 6.6 Booking Modal Dialog
* **Backdrop**: `rgba(0, 0, 0, 0.85)` with `backdrop-filter: blur(8px)`.
* **Dialog Container**:
  * Background: `#1A1A1A`
  * Border: `1px solid rgba(255, 255, 255, 0.12)`
  * Border Radius: `4px`
  * Padding: `36px`
  * Text: `#FFFFFF`
* **Form Inputs**:
  * Background: `#121212`
  * Border: `1px solid rgba(255, 255, 255, 0.15)`
  * Focus: `border-color: #F5EFEB; outline: none; box-shadow: 0 0 0 2px rgba(245, 239, 235, 0.2);`
  * Typography: Roboto `15px`, color `#FFFFFF`.
* **Submit Action**: Full-width `#F5EFEB` button with black uppercase bold text.

---

### 6.7 Fleet / Machinery Showcase
* Highlighting vehicles/equipment with technical specifications.
* **Card Style**: Charcoal background (`#161616`), isolated product cutout image against transparent/gradient background.
* **Spec Pills Grid**: 2x2 or 3x2 grid of micro-specs:
  * *Displacement / Power*: `450cc / 44 BHP`
  * *Suspension / Clearance*: `220mm Travel`
  * *Dry Weight*: `175 KG`
* Labels in muted gray (`#9CA3AF`), values in bold white/gold.

---

### 6.8 Guide / Team Profile Grid
* **Avatar**: Circular or square aspect-ratio image with `border: 2px solid rgba(255, 255, 255, 0.1)`.
* **Hover State**: Border shifts to `#F5EFEB`.
* **Name**: Oswald `20px` bold, uppercase.
* **Role / Credential**: `#F5EFEB`, `12px`, uppercase, tracking `1.5px`.
* **Short Bio**: Roboto `14px`, line-height `1.6`, `#D1D5DB`.

---

### 6.9 Testimonial & Social Proof Carousel
* **Card Container**: `#161616` background with subtle quote mark watermark in background.
* **Star Ratings**: 5 stars (`#F5EFEB`).
* **Quote Body**: Italicized Roboto `15px`, `#E5E7EB`.
* **Author Info**: Author Name (Oswald Bold) + Country / Tour Year tag (`#9CA3AF`).
* **Dot Navigation**:
  * Inactive Dots: White circle (`width: 8px; height: 8px; border-radius: 50%; opacity: 0.4;`).
  * Active Dot: `#F5EFEB` (`opacity: 1; transform: scale(1.25);`).

---

### 6.10 Editorial Blog Grid ("Tales From The Trail")
* 3 or 4-column responsive card grid.
* Card layout:
  1. Featured image (`16:9`) with hover zoom (`scale(1.05)`).
  2. Category Badge: `#F5EFEB` uppercase micro-text.
  3. Article Title: Oswald `20px` bold, 2-line clamp.
  4. Excerpt: Roboto `14px`, `#9CA3AF`, 3-line clamp.
  5. Footer: Read time (e.g. `5 MIN READ`) + Date.

---

### 6.11 Footer
* **Background**: Deepest black (`#080808` / `#000000`).
* **Top Border**: `1px solid rgba(255, 255, 255, 0.08)`.
* **Structure**: 4-column responsive layout:
  * Column 1: Brand Logo + Mission Statement + Social Icon Cluster.
  * Column 2: Tour Routes / Main Categories.
  * Column 3: Rider Resources & Guides.
  * Column 4: Newsletter Subscription Box (Dark input field with inline `#F5EFEB` submit button).
* **Bottom Sub-footer**: Legal copyright, terms, privacy links formatted in ultra-compact uppercase font (`8px - 10px`, line-height `1.4`).

---

## 7. Responsive Breakpoint Strategy

```
┌──────────────────────────────────────────────────────────────┐
│  Desktop (> 1024px)                                          │
│  - Full multi-column grids (3-4 cols)                        │
│  - Hover video autoplay on tour cards                  │
│  - Expanded departure date tables with separate Start/End    │
│  - Sticky frosted header with full horizontal navigation     │
├──────────────────────────────────────────────────────────────┤
│  Tablet (591px - 1024px)                                     │
│  - 2-column card layouts                                     │
│  - Departure table hides End Date, keeps full-width button   │
│  - Font scales adjust down by ~15-20%                        │
│  - Marquee scales up to 50px for touch graphic punch         │
├──────────────────────────────────────────────────────────────┤
│  Mobile (≤ 590px)                                            │
│  - Single column card stack                                  │
│  - Departure table becomes vertical card format              │
│  - Full-screen slide-down / drawer hamburger menu            │
│  - Hero video lightweight mobile stream or poster fallback   │
│  - Touch targets minimum 48px height                         │
└──────────────────────────────────────────────────────────────┘
```

---

## 8. Complete Tailwind CSS Configuration Preset

To implement this design system quickly with Tailwind CSS:

```javascript
// tailwind.config.js
module.exports = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: "#F5EFEB",
          hover: "#E8DFD8",
          dark: "#D8CEC4",
        },
        canvas: "#0D0D0D",
        surface: {
          DEFAULT: "#141414",
          secondary: "#1A1A1A",
          elevated: "#242424",
        },
        border: {
          subtle: "rgba(255, 255, 255, 0.08)",
          strong: "rgba(255, 255, 255, 0.20)",
        }
      },
      fontFamily: {
        display: ["Anton", "sans-serif"],
        heading: ["Oswald", "sans-serif"],
        body: ["Roboto", "sans-serif"],
        mono: ["ui-monospace", "Menlo", "monospace"],
      },
      letterSpacing: {
        tightest: "-0.02em",
        widest: "0.2em",
        tactical: "0.15em",
      },
      animation: {
        "marquee": "marquee 35s linear infinite",
        "hero-bounce": "heroBounce 2.5s infinite ease-in-out",
        "scale-up": "scaleUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards",
      },
      keyframes: {
        marquee: {
          "0%": { transform: "translateX(0%)" },
          "100%": { transform: "translateX(-50%)" },
        },
        heroBounce: {
          "0%, 20%, 50%, 80%, 100%": { transform: "translateX(-50%) translateY(0)" },
          "40%": { transform: "translateX(-50%) translateY(-8px)" },
          "60%": { transform: "translateX(-50%) translateY(-4px)" },
        },
        scaleUp: {
          "0%": { opacity: "0", transform: "scale(0.96) translateY(20px)" },
          "100%": { opacity: "1", transform: "scale(1) translateY(0)" },
        }
      }
    },
  },
  plugins: [],
};
```

---

## 9. Ready-to-Use UI Code Snippets

### 9.1 HTML/CSS Tour Card with Video on Hover

```html
<div class="group relative bg-[#141414] border border-white/10 rounded-sm overflow-hidden transition-all duration-300 hover:border-white/30 hover:shadow-2xl">
  <!-- Media Container -->
  <div class="relative aspect-[16/10] overflow-hidden bg-black">
    <!-- Static Cover Poster Image -->
    <img 
      src="https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=800&q=80" 
      alt="Tour Route" 
      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 group-hover:opacity-0"
    />
    
    <!-- Video element (plays on group-hover) -->
    <video 
      class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-400 group-hover:opacity-100"
      muted 
      loop 
      playsinline 
      preload="none"
      onmouseenter="this.play()" 
      onmouseleave="this.pause(); this.currentTime = 0;"
    >
      <source src="your-video-url.mp4" type="video/mp4" />
    </video>

    <!-- Top Badge -->
    <div class="absolute top-3 left-3 bg-black/70 backdrop-blur-sm border border-white/10 px-3 py-1 text-[11px] font-heading font-bold uppercase tracking-widest text-[#F5EFEB]">
      12 Days Tour
    </div>
  </div>

  <!-- Content Body -->
  <div class="p-6">
    <p class="text-xs font-heading font-semibold uppercase tracking-wider text-gray-400 mb-1">
      Mostly Dirt, Some Tarmac | Spring & Autumn
    </p>
    <h3 class="font-heading text-2xl font-bold uppercase tracking-wide text-white group-hover:text-[#F5EFEB] transition-colors duration-200 mb-4">
      The High Mountain Pass Tour
    </h3>
    
    <!-- Action Button -->
    <a 
      href="#" 
      class="w-full inline-flex items-center justify-center py-3 border border-white text-white font-heading font-semibold text-sm uppercase tracking-widest transition-all duration-200 hover:bg-white hover:text-black"
    >
      View Tour
    </a>
  </div>
</div>
```

---

### 9.2 Running Marquee Ticker Component

```html
<div class="w-full bg-[#0D0D0D] border-y border-white/10 py-3 overflow-hidden select-none">
  <div class="flex w-max animate-marquee">
    <div class="flex items-center space-x-8 text-2xl md:text-3xl font-heading font-bold uppercase text-[#F5EFEB] tracking-wider whitespace-nowrap">
      <span>❖ Guided High Altitude Tours</span>
      <span>❖ Hand-Crafted Routes for True Adventurers</span>
      <span>❖ Fully Supported Crew & Remote Logistics</span>
      <span>❖ Ride the Legend</span>
    </div>
    <div class="flex items-center space-x-8 text-2xl md:text-3xl font-heading font-bold uppercase text-[#F5EFEB] tracking-wider whitespace-nowrap ml-8">
      <span>❖ Guided High Altitude Tours</span>
      <span>❖ Hand-Crafted Routes for True Adventurers</span>
      <span>❖ Fully Supported Crew & Remote Logistics</span>
      <span>❖ Ride the Legend</span>
    </div>
  </div>
</div>
```

---

### 9.3 Responsive Departure Schedule Row

```html
<div class="grid grid-cols-1 lg:grid-cols-[260px_260px_1fr_auto] gap-4 lg:gap-8 items-center py-4 border-t border-white/10 text-white">
  <!-- Start Date -->
  <div>
    <span class="block text-xs font-heading uppercase tracking-wider text-[#F5EFEB] lg:hidden mb-1">Start Date</span>
    <span class="text-sm md:text-base font-body font-medium">Monday, 15 Sep 2026</span>
  </div>

  <!-- End Date (hidden on mobile/tablet) -->
  <div class="hidden lg:block">
    <span class="text-sm md:text-base font-body font-medium">Saturday, 27 Sep 2026</span>
  </div>

  <!-- Spacer / Status -->
  <div class="hidden lg:block"></div>

  <!-- Action CTA -->
  <div class="w-full lg:w-auto">
    <a 
      href="#book" 
      class="inline-flex items-center justify-center w-full lg:min-w-[210px] h-[44px] bg-[#F5EFEB] hover:bg-black hover:text-[#F5EFEB] hover:border hover:border-[#F5EFEB] text-black font-heading font-bold text-sm uppercase tracking-widest transition-all duration-200 rounded-sm"
    >
      Book Now
    </a>
  </div>
</div>
```

---

## 10. Summary Checklist for Replicating the Exact Style

- [x] **Dark Canvas**: Backgrounds locked to `#000000` / `#0D0D0D` / `#141414`.
- [x] **Brand Accent**: Warm Beige (`#F5EFEB`) used on active items, hero badges, star ratings, and primary buttons.
- [x] **Font Trio**: **Anton** (Hero/Main Headlines), **Oswald** (Subheaders, Navigation, Meta, Buttons), **Roboto** (Body Copy, Descriptions).
- [x] **Tracking Rule**: All uppercase Oswald typography must have `letter-spacing: 2px - 3px` (tracking-widest).
- [x] **Frosted Glass Sticky Nav**: Initial transparent overlay -> snaps to frosted sticky bar (`rgba(0,0,0,0.85)` + `backdrop-filter: blur(12px)`).
- [x] **Hero Experience**: Fullscreen `100svh` background video with lazy load, crossfade, and bouncing chevron indicator.
- [x] **Card Interactions**: Aspect ratio media with video preview on desktop hover + subtle scale zoom.
- [x] **Schedule Grid**: Multi-column desktop schedule table that dynamically collapses to single column on mobile.
- [x] **Dividers & Borders**: Ultra-thin hairline borders with `rgba(255, 255, 255, 0.08)`.
