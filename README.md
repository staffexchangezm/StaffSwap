# StaffSwap

StaffSwap is an installable WordPress foundation for StaffExchangeHub, a professional workplace exchange marketplace based on the supplied UI samples.

For the complete installation, Elementor, WooCommerce, database, import, cache, and troubleshooting procedure, see [SETUP.md](SETUP.md).

## Included

- `wp-content/themes/staffswap`: responsive marketplace theme with landing page, navigation, footer, forms, listing cards, a dedicated listing view with similar-listings suggestions, an editable Success Stories post type (`staff_testimonial`), a working "Get Swap Alerts" email signup, and a single tabbed **Appearance > Theme Options** page for all brand, plan, payment, and SMS settings.
- `wp-content/plugins/staffswap-core`: `swap_listing` custom post type, secure listing metadata, admin editing fields, filterable `[staffswap_listings]` shortcode with sort/filter chips/pagination, authenticated `[staffswap_create_form]` submission flow, `[staffswap_save_button]`, `[staffswap_saved_listings]`, a Listing Moderation queue with author verification/waiting-time context, an Analytics dashboard, and a new-listings subscriber digest.
- `wp-content/plugins/staffswap-resources`: searchable `staff_resource` content type, resource categories with a filter dropdown, an admin media-library file picker for downloads, `[staffswap_resources]` Resources Centre layout with real download counts, secure download tracking, and `[staffswap_resource_download]`.
- `wp-content/plugins/staffswap-profiles`: member profession, location, employer, phone number, and notification-preference fields with `[staffswap_profile]` display output and a Verification Queue that emails members the outcome.
- `wp-content/plugins/staffswap-messaging`: private `staff_message` content type, `[staffswap_contact listing="123"]` contact form, `[staffswap_inbox]` inbox, and branded HTML email + optional SMS notifications for messages, replies, offers, and offer status changes. Activating it creates a Messages page.
- `wp-content/plugins/staffswap-woocommerce`: optional WooCommerce bridge with `[staffswap_upgrade]`, configurable VIP Gold plans, a Lenco Zambia Mobile Money gateway, checkout CTA, membership activation with automatic expiry/renewal reminders for term plans, and a "Current plan" state once a member is subscribed.
- `wp-content/themes/staffswap/elementor-templates/staffswap-homepage.json`: importable Elementor homepage starter with hero, workflow, and CTA sections.

The theme is compatible with Elementor and other page builders through normal WordPress templates, menus, widgets, and shortcodes. Elementor is not bundled with this repository: install and activate the Elementor plugin separately before looking for Elementor admin menus. Use an Elementor Shortcode widget with `[staffswap_listings]`, `[staffswap_search]`, `[staffswap_create_form]`, `[staffswap_resources]`, `[staffswap_profile]`, or `[staffswap_contact listing="123"]`. The theme's header, footer, fonts, CSS variables, and scripts remain available to Elementor pages.

The theme follows Elementor's standard location contract. If an Elementor Header or Footer template is assigned, `elementor_theme_do_location()` renders it; if no template is assigned, the native StaffSwap header and footer render automatically. This prevents empty builder templates from silently replacing the site chrome.

Dedicated native and Elementor-ready route templates are included for `swaps`, `search`, `create-swap`, `register`, `sign-in`, `my-profile`, and `resources`. Each route renders its designed StaffSwap UI when no Elementor content exists, and hands the content area to Elementor when the page has builder data or is in preview mode.

All custom page templates are builder-aware. Pricing, How It Works, and Success Stories use the page's Elementor content when it exists; blog and swap listing archives can be replaced through Elementor's Archive location; single blog posts and swap listings can be replaced through the Single location. Without a builder template, each page falls back to its StaffSwap-designed native UI.

For a designed Elementor starting point, import `elementor-templates/staffswap-homepage.json` from **Templates > Saved Templates > Import Templates**, then insert it into the Home page. Use an Elementor Shortcode widget with `[staffswap_listings limit="3"]` for dynamic featured listings.

## Local setup

1. Copy the `wp-content` directory into a WordPress installation.
2. Activate **StaffSwap Core** in Plugins.
3. Activate **StaffSwap Resources**, **StaffSwap Profiles**, **StaffSwap Messaging**, and **StaffSwap WooCommerce Bridge** when those modules are needed.
4. Activate **StaffSwap** in Appearance > Themes.
5. Open Appearance > Theme Options and use the **Setup & Health** tab to click **Run setup**. This creates the essential pages, sets the homepage, and builds the StaffSwap Main Menu. The same page can be reopened at any time.
6. Activating the core plugin automatically creates these pages with their shortcodes:
	- `swaps`: `[staffswap_listings]`
	- `create-swap`: `[staffswap_create_form]`
	- `search`: `[staffswap_search]`
	- `register`: `[staffswap_register]`
	- `sign-in`: `[staffswap_login]`
	- `my-profile`: `[staffswap_dashboard]`
	- `resources`: `[staffswap_resources]`
	- `blog`: normal WordPress posts archive
	- `how-it-works`, `success-stories`, and `pricing`: starter informational pages
7. Add a Resources page with `[staffswap_resources]` and a Profile page with `[staffswap_profile]` if they were not included by the setup screen. Set the home page in Settings > Reading if you want a different page. Saving Permalinks once after activation refreshes the custom listing routes. The `/swap/` archive is also available directly.

## Assets

- `wp-content/themes/staffswap/style.css`: theme and component styles.
- `wp-content/themes/staffswap/assets/js/main.js`: mobile navigation and small form interactions.
- `wp-content/themes/staffswap/languages/staffswap.pot`: translation template for the theme text domain `staffswap`; create locale `.po`/`.mo` files from this template with Poedit or WP-CLI.
- `wp-content/themes/staffswap/theme.json`: editor design tokens for WordPress and builder-aware content width, colors, spacing, and fonts.
- External Google Fonts are enqueued by the theme; replace them with local font files for a fully self-hosted production deployment.

New listings are submitted as `pending` for moderation. Administrators can complete match score, verification, urgency, housing, employer, and location details from the Swap Listings editor.

Theme content is editable from **Appearance > Theme Options**, a single tabbed page covering Brand & Homepage, Membership Plans, Payment Gateway, SMS Notifications, Setup & Health, and Quick Links, so administrators never have to hunt across separate settings screens. Appearance > Customize > StaffSwap Homepage remains available for live visual previews of the same hero/color fields.

WooCommerce is optional. When active, the bridge creates virtual VIP Gold products per plan and `[staffswap_upgrade]` links to checkout; plan titles/prices are edited from Theme Options rather than the WooCommerce product screen. Completed payments set `staffswap_plus_active` on the customer along with an expiry date for term plans (month/quarter); a daily check emails renewal reminders and expiry notices, then deactivates the flag once a plan lapses. Lifetime plans never expire. Guests are prompted to sign in before checkout so a purchase is never orphaned from an account.

StaffSwap Core creates versioned relational tables for matches, saved searches, activity events, and alert subscribers using the site's WordPress database prefix. See [SETUP.md](SETUP.md) for the schema contract and migration rules.

## Elementor cache refresh

After Elementor is installed and active, use **Elementor > Tools > General > CSS & Data > Regenerate Files & Data**. In some Elementor versions this appears as **Elementor > Tools > Regenerate CSS & Data**. If Elementor is not installed or activated, neither menu exists; use the StaffSwap Customizer and normal WordPress cache controls instead. For the homepage, use the **Default** or **Elementor Full Width** page template, not **Elementor Canvas**, because Canvas intentionally removes the theme header and footer.

## Add the menu

After running StaffSwap Setup, go to **Appearance > Menus**, select **StaffSwap Main Menu**, and confirm **Primary Menu** is checked under Menu Settings. Click **Save Menu**. To edit the header with Elementor, install Elementor Pro or a header/footer extension such as ElementsKit, create a header template, add a WordPress **Nav Menu** widget, select **StaffSwap Main Menu**, and set its display condition to **Entire Site**. Do not leave an empty header template assigned, because an empty ElementsKit/Elementor header replaces the theme header. For a normal theme header, use **Default** or **Elementor Full Width**, not **Elementor Canvas**.

## Next production integrations

The foundation is ready for payment subscriptions, identity verification, direct messaging, notifications, and a richer member dashboard. Those should be added as separate modules so the core listing data remains portable.

## Contributor issue backlog

These issues are suitable for GitHub contributors. Each contributor should keep changes focused, add tests where practical, and update the relevant documentation.

### 1. Add automated tests for core listing workflows

**Labels:** `good first issue`, `testing`, `core`

Add PHPUnit coverage for listing creation, metadata saving, verification status, pending/published states, and ownership permissions.

**Acceptance criteria**

- Verified and unverified users are covered.
- Unauthorized users cannot modify listings.
- Tests and the test command are documented.

### 2. Improve profile dashboard tab navigation

**Labels:** `frontend`, `accessibility`, `enhancement`

Improve the member workspace tabs with keyboard navigation, reliable active states, browser history support, and responsive behavior.

**Acceptance criteria**

- The active tab survives refresh.
- Browser back and forward navigation work.
- Tabs expose appropriate ARIA state.
- Mobile navigation remains easy to use.

### 3. Add profile completion progress ✓ Completed

**Labels:** `frontend`, `profiles`, `enhancement`

Add a profile completion percentage and a clear list of missing professional details. Implemented via `[staffswap_profile_completion]`.

**Acceptance criteria**

- Progress is calculated from profile fields.
- Missing fields link to profile settings.
- Empty and partially completed profiles have useful states.

### 4. Add advanced swap matching filters

**Labels:** `good first issue`, `core`, `search`

Add combined filters for profession, province, employer, experience, housing, verification, and urgency.

**Acceptance criteria**

- Filters can be combined and preserved in the URL.
- Queries are sanitized and performant.
- Empty results have a useful state.
- The filter interface works on mobile.

### 5. Build a match explanation component ✓ Completed

**Labels:** `core`, `frontend`, `matching`

Explain why two listings match instead of showing only a percentage score. Consider profession, locations, employer, housing, and verification. Implemented via `staffswap_match_explanation()`.

**Acceptance criteria**

- Match factors are understandable to members.
- Score and explanation use the same data.
- Missing data does not break the component.
- The explanation is accessible on mobile.

### 6. Add saved searches to the member dashboard

**Labels:** `profiles`, `search`, `enhancement`

Show saved searches inside the profile workspace and allow members to run or delete them.

**Acceptance criteria**

- Search ownership is enforced.
- Members can run and delete saved searches.
- The empty state links to search creation.

### 7. Add live notification counts ✓ Completed

**Labels:** `core`, `messaging`, `enhancement`

Replace static header notification badges with live unread message and pending offer counts. Implemented: the header now shows live Messages, Offers, and Matches badge counts (`staffswap_unread_message_count()`, `staffswap_pending_offer_count()`, `staffswap_new_match_count()`), scoped to the logged-in user and hidden at zero.

### 8. Improve messaging security and UX

**Labels:** `security`, `messaging`, `enhancement`

Add conversation grouping, unread states, reply controls, and stronger participant permission checks.

**Acceptance criteria**

- Users can only access conversations they belong to.
- Unread messages are visually clear.
- Forms use nonce and capability validation.

### 9. Add a listing moderation workflow ✓ Completed

**Labels:** `admin`, `core`, `moderation`

Create an administrator workflow for approving, rejecting, and requesting changes to listings. Implemented at **Swap Listings > Moderation Queue**, including author verification status, waiting-time indicator, a listing preview link, and an email notification to the author on every decision.

### 10. Add verification document management

**Labels:** `security`, `profiles`, `verification`

Allow members to upload, replace, view, and remove verification documents securely.

**Acceptance criteria**

- File type and size are validated.
- Documents are not publicly accessible by direct URL.
- Administrators can review documents.
- Members can see their verification status.

### 11. Improve relocation planner progress

**Labels:** `frontend`, `profiles`, `enhancement`

Add overall and per-phase progress indicators, saved-state feedback, and completed-task summaries.

**Acceptance criteria**

- Progress persists for the logged-in user.
- Each phase displays its completion state.
- Save feedback is visible.
- The planner works on mobile.

### 12. Add an admin analytics dashboard ✓ Completed

**Labels:** `admin`, `analytics`, `enhancement`

Create an admin dashboard for members, verified members, active listings, pending listings, matches, offers, and registrations. Implemented at **Swap Listings > Analytics**, including pending listings, active VIP Gold members, offer acceptance rate, top professions, and top swap routes.

### 13. Improve Customizer logo controls

**Labels:** `frontend`, `good first issue`, `customization`

Improve header branding controls with logo preview, recommended dimensions, dark-header preview, and logo sizing options.

**Acceptance criteria**

- Logo preview appears in the Customizer.
- Desktop and mobile headers remain stable.
- Text and accessibility fallbacks work when no logo is uploaded.

### 14. Add email notifications ✓ Completed

**Labels:** `messaging`, `notifications`, `enhancement`

Add configurable email notifications for new matches, offers, replies, verification updates, and listing approval. Implemented via `staffswap_notify_user()` (branded HTML email, plus optional SMS through ExciteSMS), covering messages, replies, offers, offer status changes, counter-offers, new matches, listing moderation decisions, and verification updates. Members manage preferences (email opt-out, SMS opt-in) from Profile Settings.

### 15. Expand production deployment documentation

**Labels:** `documentation`, `good first issue`, `production`

Document deployment, staging, backups, cache invalidation, security checks, and rollback procedures.

**Acceptance criteria**

- Theme and plugin deployment is documented.
- Database backup and rollback steps are included.
- Cache-clearing steps are included.
- Recommended PHP and WordPress versions are stated.

### 16. Complete an accessibility audit

**Labels:** `accessibility`, `frontend`

Audit the theme and dashboard against WCAG 2.2 AA and fix the highest-impact issues.

**Acceptance criteria**

- Keyboard-only navigation works.
- Focus indicators are visible.
- Form controls have accessible labels.
- Color contrast passes.
- Dropdown and tab ARIA states are correct.

### 17. Add performance optimization

**Labels:** `performance`, `core`, `production`

Review asset loading, dashboard queries, image sizes, and unnecessary database work.

**Acceptance criteria**

- Assets load only where needed.
- Dashboard queries are reviewed and optimized.
- Images use appropriate sizes.
- Performance checks are documented.

### 18. Complete a security review

**Labels:** `security`, `production`, `core`

Review nonces, capability checks, ownership checks, uploads, SQL queries, escaping, authentication, messaging, and private documents.

**Acceptance criteria**

- Findings are documented.
- High-risk findings are fixed.
- Security-sensitive tests are added.
- Unauthorized users cannot access private content.

**Recommended first issues:** 1, 2, 4, 6, 8, 10, 11, 13, 15, 16, 17, and 18. Items 3, 5, 7, 9, 12, and 14 have been completed. These have clear boundaries and can be completed without redesigning the marketplace data model.