# StaffSwap

StaffSwap is an installable WordPress foundation for StaffExchangeHub, a professional workplace exchange marketplace based on the supplied UI samples.

For the complete installation, Elementor, WooCommerce, database, import, cache, and troubleshooting procedure, see [SETUP.md](SETUP.md).

## Included

- `wp-content/themes/staffswap`: responsive marketplace theme with landing page, navigation, footer, forms, listing cards, and a dedicated listing view.
- `wp-content/plugins/staffswap-core`: `swap_listing` custom post type, secure listing metadata, admin editing fields, filterable `[staffswap_listings]` shortcode, authenticated `[staffswap_create_form]` submission flow, `[staffswap_save_button]`, and `[staffswap_saved_listings]`.
- `wp-content/plugins/staffswap-resources`: searchable `staff_resource` content type, resource categories, `[staffswap_resources]` Resources Centre layout, secure download tracking, and `[staffswap_resource_download]`.
- `wp-content/plugins/staffswap-profiles`: member profession, location, and employer fields with `[staffswap_profile]` display output.
- `wp-content/plugins/staffswap-messaging`: private `staff_message` content type, `[staffswap_contact listing="123"]` contact form, and `[staffswap_inbox]` inbox. Activating it creates a Messages page.
- `wp-content/plugins/staffswap-woocommerce`: optional WooCommerce bridge with `[staffswap_upgrade]`, a StaffSwap Plus product, checkout CTA, and payment-complete membership flag.
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
5. Open Appearance > StaffSwap Setup and click **Run setup**. This creates the essential pages, sets the homepage, and builds the StaffSwap Main Menu. The setup screen can be reopened at any time.
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

Theme content is editable from Appearance > Customize > StaffSwap Homepage. Site administrators can update the hero title, description, CTA labels, homepage stats, and primary action color without editing code.

WooCommerce is optional. When active, the bridge creates a virtual StaffSwap Plus product and `[staffswap_upgrade]` links to checkout. Completed payments set `staffswap_plus_active` on the customer. Future premium visibility rules can use that user flag without coupling swap listings to WooCommerce internals.

StaffSwap Core creates versioned relational tables for matches, saved searches, and activity events using the site's WordPress database prefix. See [SETUP.md](SETUP.md) for the schema contract and migration rules.

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

### 3. Add profile completion progress

**Labels:** `frontend`, `profiles`, `enhancement`

Add a profile completion percentage and a clear list of missing professional details.

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

### 5. Build a match explanation component

**Labels:** `core`, `frontend`, `matching`

Explain why two listings match instead of showing only a percentage score. Consider profession, locations, employer, housing, and verification.

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

### 7. Add live notification counts

**Labels:** `core`, `messaging`, `enhancement`

Replace static header notification badges with live unread message and pending offer counts.

**Acceptance criteria**

- Counts are scoped to the logged-in user.
- Badges are hidden when counts are zero.
- Guest users see no private notification counts.
- Accessible labels describe each notification type.

### 8. Improve messaging security and UX

**Labels:** `security`, `messaging`, `enhancement`

Add conversation grouping, unread states, reply controls, and stronger participant permission checks.

**Acceptance criteria**

- Users can only access conversations they belong to.
- Unread messages are visually clear.
- Forms use nonce and capability validation.

### 9. Add a listing moderation workflow

**Labels:** `admin`, `core`, `moderation`

Create an administrator workflow for approving, rejecting, and requesting changes to listings.

**Acceptance criteria**

- Admins can approve and reject listings.
- Review status is visible to authors.
- Status changes are logged.
- Non-admin users cannot access moderation actions.

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

### 12. Add an admin analytics dashboard

**Labels:** `admin`, `analytics`, `enhancement`

Create an admin dashboard for members, verified members, active listings, pending listings, matches, offers, and registrations.

**Acceptance criteria**

- Access is restricted to administrators.
- Metrics use appropriate WordPress data.
- Sensitive personal data is not exposed unnecessarily.
- Empty states and responsive layouts are included.

### 13. Improve Customizer logo controls

**Labels:** `frontend`, `good first issue`, `customization`

Improve header branding controls with logo preview, recommended dimensions, dark-header preview, and logo sizing options.

**Acceptance criteria**

- Logo preview appears in the Customizer.
- Desktop and mobile headers remain stable.
- Text and accessibility fallbacks work when no logo is uploaded.

### 14. Add email notifications

**Labels:** `messaging`, `notifications`, `enhancement`

Add configurable email notifications for new matches, offers, replies, verification updates, and listing approval.

**Acceptance criteria**

- Members can manage notification preferences.
- Emails contain safe links.
- Duplicate notifications are prevented.
- Delivery failures are logged.

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

**Recommended first issues:** 1, 3, 11, 13, 15, and 16. These have clear boundaries and can be completed without redesigning the marketplace data model.