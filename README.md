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
- **Social Login** — turns on "Continue with Google"/"Continue with
  Facebook" on the Login/Create Account pages once you paste in that
  provider's client ID/secret (created in their own developer console —
  the section shows the exact redirect URI to register there). Off by
  default; each provider is independent, so you can enable just one.

## Social login

Standard OAuth2 authorization-code flow against Google's and Facebook's
own endpoints directly (no SDK, matching how Kashier/PayPal are
integrated in the plugin) — see `inc/social-login.php`. Account matching
on callback, in order: an existing user already linked to that provider
ID → an existing user with the same email (gets linked automatically) →
a brand new account (same role assignment as normal registration). Only
a *verified* email is trusted for matching/creating an account — Google
exposes `email_verified` on its profile response and an unverified one
is treated as no email at all; Facebook's is always used since its Graph
API doesn't expose an equivalent flag.

Apple Sign-In isn't implemented — meaningfully more setup (a paid Apple
Developer Program membership, a Services ID, a private key, and signing
your own JWT client secret) than Google/Facebook, deliberately left for
a follow-up.

## Booking flow

The booking modal is a real, account-gated, three-step wizard (Trip →
Travelers → Contact & pay) against the plugin's REST API (`nefertari/v1`):
excursion (auto-locked to the excursion you booked from) → real departure
slot (with live remaining capacity) → passenger/passport details for each
guest → contact details → a live price from the plugin's `Pricing_Service`
→ redirect to checkout. Logged-out visitors see a login/register prompt
instead of the form (the plugin has no guest checkout).

Two payment methods, both real: **Kashier** (card, always on) and
**PayPal** (optional — a "Card / PayPal" choice only appears once the
plugin's Settings → PayPal Settings has it enabled). Whichever the
customer picks, they land on the theme's Payment Result page afterward,
which polls the plugin's booking-status endpoint for the real outcome —
for Kashier the redirect proves nothing (its webhook is the source of
truth and can land a moment later); for PayPal that page also fires the
capture call first (a PayPal order isn't paid until captured), then polls
the same way.

## My Account

Beyond editing your profile, the account page (`page-account.php`) is a
full booking management area:

- **Bookings list** — every booking, with a quick "Retry payment" button
  right in the row for anything still `awaiting_payment`, `payment_failed`,
  or `payment_expired`.
- **Booking detail** (`?booking=ID`) — full info, the traveler list, and
  complete payment history (every attempt, not just the latest), plus
  whichever action fits the booking's status: retry payment (with the
  same Kashier/PayPal choice as the booking modal), or request
  cancellation for a `confirmed` booking (shown alongside the excursion's
  cancellation policy).

Retry re-uses the *existing* booking rather than creating a new one — if
its original seat hold already expired, the plugin re-holds the same
seats first (failing clearly if the slot has since sold out) rather than
silently letting a stale booking be paid for seats no longer available.

**Leave a review** — text + a 5-star rating, no photo. Shown only to
customers with a `confirmed`/`completed` booking, and only lets them pick
from *those* excursions — not a fully open public form. Submits as a
`testimonial` post with `post_status = pending` rather than publishing
immediately, so it only goes live once approved in wp-admin (Testimonials);
the metabox there shows who submitted it, for the admin to cross-check
against real bookings before approving.

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
