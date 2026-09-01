# GNARLY MTB NEPAL — High-Octane Tour Frontend

A dark-first, high-performance web application built with the custom tour design system.

---

## Features & Highlights

* **Dark-First High-Octane Aesthetic**: Deep obsidian canvas (`#0D0D0D` / `#000000`), electric golden amber accents (`#FFC700`), and tactical hairline dividers.
* **Typography Hierarchy**: **Anton** display titles, **Oswald** tracked uppercase headers, and **Roboto** body copy.
* **Fullscreen Hero Section**: High-resolution video background, animated bouncing scroll indicator, and frosted sticky header navigation.
* **Continuous Running Ticker**: Infinite marquee looping in `#FFC700` uppercase.
* **Video-on-Hover Tour Cards**: Interactive cards for *Enduro Thin Air*, *Everest Express*, *Enduro Thin Air : Ultimate*, *Mustang E-Motion*, and *Himalayan Moto Holidays “Hello Moto”*.
* **The Himalayan Enduro**: Feature showcase for race registration and event information.
* **Responsive Departure Table**: 4-column desktop layout that collapses to 2 columns on tablet and 1 column on mobile with *Book Now* and *Sold Out* states.
* **Interactive Booking Modal**: `#161616` dark dialog with WhatsApp direct integration.
* **Testimonials Slider**: 5-star gold ratings with dot navigation.

---

## Project Structure

```
├── index.html          # Semantic HTML5 entry point with Tailwind CSS CDN & Lucide Icons
├── css/
│   └── style.css       # Custom design system tokens, animations, table rules, and modals
├── js/
│   └── app.js          # Sticky nav, scrollspy reveals, video triggers, modal & slider logic
├── vercel.json         # Vercel deployment configuration with clean URLs and security headers
├── package.json        # NPM scripts & metadata
└── frontend.md         # Complete UI design system specification
```

---

## Local Development

You can run this project locally with any static server:

```bash
# Using Node.js npx serve
npx serve . -l 3000

# OR using Python
python3 -m http.server 3000
```
Then open `http://localhost:3000` in your browser.

---

## Deploy to Vercel

This repository is **100% Vercel-ready**:

1. Push this repository to GitHub / GitLab / Bitbucket.
2. Go to [vercel.com](https://vercel.com) and import the repository.
3. Keep default settings (`Framework Preset: Other`, `Root Directory: ./`).
4. Click **Deploy**. Your site will be live in seconds!

Alternatively, deploy directly using the Vercel CLI:
```bash
npx vercel
```
