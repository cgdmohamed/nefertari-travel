# Nefertari Travel — WordPress Theme

Custom WordPress theme for **Nefertari Travel**, an Egypt excursion booking site
(desert safari, Cairo, Luxor, Aswan, sea trips, diving, aqua park, glass boat).
Built from a Claude Design export — see the original prototype's chat history
for the full design brief.

## Requires

**[Nefertari Booking Core](https://github.com/cgdmohamed/nefertari-travel-core)**
plugin — the theme no longer registers its own `excursion` post type or demo
booking flow. It reads real excursion data and drives real bookings/payments
through that plugin's post type and REST API. Without it active, excursion
pages, the booking modal, and account booking history won't work (the theme
shows an admin notice, and "Book" buttons fall back to a WhatsApp inquiry
link so nothing is dead).

## Install

1. Install and activate the **Nefertari Booking Core** plugin first.
2. Copy this folder into `wp-content/themes/nefertari-travel` and activate
   **Nefertari Travel** under Appearance → Themes.
3. On activation the theme seeds its own demo content: 3 testimonials, 6 blog
   posts, and 4 account pages (Login, Create Account, My Account, Payment
   Result). For demo excursions (with real slots), use the plugin's own
   **Tools → Import Prototype Demo Data**.
4. Customize branding, copy, and contact info under
   **Appearance → Customize → Nefertari Travel Settings** (see below).
5. If public registration should be open, make sure *Settings → General →
   "Anyone can register"* is checked (the theme's registration page respects it).

## Customization (Appearance → Customize → Nefertari Travel Settings)

Everything a site owner would want to change themselves lives here — no
code editing required:

- **Site Identity** (core WP, appears above the theme's panel) — logo
  upload. Falls back to a colored monogram of the site's first letter if
  no logo is set.
- **Branding & Colors** — 3 color pickers (primary/secondary/accent) that
  re-theme every gradient, button, and accent across the whole site via
  CSS custom property overrides — no CSS editing needed.
- **Contact & Hours** — phone, WhatsApp number, email, address, hours.
- **Social Links** — Facebook, Instagram, TikTok, X.
- **Trust & Stats** — rating, review count, traveller count, license number.
- **Homepage Hero** — image, badge text, all three heading lines, subheading,
  and the floating "free hotel pickup" badge text.
- **Why Choose Us Section** — section heading/subheading and all 4 tiles'
  title + text (icons stay fixed).
- **Contact CTA Section** — heading, subheading, button label.
- **Footer** — brand description paragraph, and Privacy/Terms/Cancellation
  policy links (pick any existing Page from a dropdown).

## Booking flow

The booking modal is a real, account-gated flow against the plugin's REST
API (`nefertari/v1`): select an excursion → real departure slot (with live
remaining capacity) → passenger/passport details for each guest → contact
details → a live price from the plugin's `Pricing_Service` → redirect to a
real Kashier checkout. Logged-out visitors see a login/register prompt
instead of the form (the plugin has no guest checkout). After payment,
Kashier redirects to the theme's Payment Result page, which polls the
plugin's booking-status endpoint for the real outcome (the redirect itself
proves nothing — the webhook is the source of truth and can land a moment
later).

## Structure

- `functions.php` + `inc/` — theme bootstrap:
  - `plugin-bridge.php` — the plugin dependency check/admin notice, plus the
    theme's own supplementary "trust signal" fields (rating, review count,
    "booked this month") added to the plugin's `excursion` post type — the
    plugin doesn't model marketing stats, so these stay theme-owned.
  - `post-types.php` — just `testimonial` now; `excursion` belongs to the plugin.
  - `accounts.php` — login/registration/account-page logic, and seeding the
    four account pages (`page-login.php`, `page-register.php`,
    `page-account.php`, `page-payment-result.php`) by slug.
  - `template-tags.php` — excursion helpers adapted to the plugin's field
    format (flat newline-joined lists, no day-grouped itinerary, `_nefertari_*`
    meta keys) plus the theme's own icons/Customizer/testimonial helpers.
  - `customizer.php`, `seed-content.php` (testimonials + blog posts only), `meta-boxes.php` (testimonial only).
- `template-parts/home|excursion|blog|modal/` — one file per section of the
  design (hero, excursion grid, itinerary, the real booking modal, etc.).
- `assets/css/style.css` — all styling, including the auth/account pages.
- `assets/js/` — `main.js` (gallery thumbnails), `booking.js` (the real
  booking flow), `payment-result.js` (status polling after Kashier redirect).

## Notes

- Itinerary display: the plugin stores itinerary as a flat list of lines
  (no day/label grouping), so multi-day trips show as one "Itinerary" panel
  instead of Day 1 / Day 2 — a straightforward consequence of the plugin's
  data model, not a bug.
- The theme's floating WhatsApp button and header/footer contact links are
  unrelated to the booking flow — general inquiry channels, unaffected by
  whether the plugin is active.
