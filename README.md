# Nefertari Travel — WordPress Theme

Custom WordPress theme for **Nefertari Travel**, an Egypt excursion booking site
(desert safari, Cairo, Luxor, Aswan, sea trips, diving, aqua park, glass boat).
Built from a Claude Design export — see the original prototype's chat history
for the full design brief.

## Install

1. Copy this folder into `wp-content/themes/nefertari-travel` on a WordPress
   install (6.0+, PHP 7.4+).
2. Activate **Nefertari Travel** under Appearance → Themes.
3. On activation the theme seeds demo content automatically: 8 excursions,
   6 blog posts, 3 testimonials, and default site settings — so the site
   looks like the design immediately.
4. Configure contact info, socials, trust stats and the hero image under
   **Appearance → Customize → Nefertari Travel Settings**.

## Structure

- `functions.php` + `inc/` — theme bootstrap: setup/assets, custom post types
  (`excursion`, `testimonial`), hand-rolled admin meta boxes, Customizer
  settings, template helpers/icons, and the demo content seeder.
- `template-parts/home|excursion|blog|modal/` — one file per section of the
  design (hero, excursion grid, itinerary, booking modal, etc.).
- `assets/css/style.css` — all styling, ported into semantic classes with
  CSS custom properties for the brand palette.
- `assets/js/` — `main.js` (gallery thumbnails), `booking.js` (the booking
  modal flow), `admin-repeater.js` (admin repeater fields).

## Notes

- The "Pay online" flow in the booking modal is a **front-end demo only** —
  any card number is accepted and no real charge is made, matching the
  original design's explicit scope. Wire up a real gateway (Stripe, Paymob,
  etc.) before taking real payments.
- Excursion/testimonial fields currently use plain post meta with custom
  meta boxes (no plugin dependency). These are expected to be superseded by
  a dedicated data-management plugin later — keep that in mind if you're
  migrating field storage.
