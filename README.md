# StaffSwap

StaffSwap is an installable WordPress foundation for StaffExchangeHub, a professional workplace exchange marketplace based on the supplied UI samples.

## Included

- `wp-content/themes/staffswap`: responsive marketplace theme with landing page, navigation, footer, forms, listing cards, and a dedicated listing view.
- `wp-content/plugins/staffswap-core`: `swap_listing` custom post type, secure listing metadata, admin editing fields, filterable `[staffswap_listings]` shortcode, and authenticated `[staffswap_create_form]` submission flow.
- `wp-content/plugins/staffswap-resources`: searchable `staff_resource` content type, resource categories, and `[staffswap_resources]` Resources Centre layout.
- `wp-content/plugins/staffswap-profiles`: member profession, location, and employer fields with `[staffswap_profile]` display output.
- `wp-content/plugins/staffswap-messaging`: private `staff_message` content type and `[staffswap_contact listing="123"]` contact form.
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

## Elementor cache refresh

After Elementor is installed and active, use **Elementor > Tools > General > CSS & Data > Regenerate Files & Data**. In some Elementor versions this appears as **Elementor > Tools > Regenerate CSS & Data**. If Elementor is not installed or activated, neither menu exists; use the StaffSwap Customizer and normal WordPress cache controls instead. For the homepage, use the **Default** or **Elementor Full Width** page template, not **Elementor Canvas**, because Canvas intentionally removes the theme header and footer.

## Add the menu

After running StaffSwap Setup, go to **Appearance > Menus**, select **StaffSwap Main Menu**, and confirm **Primary Menu** is checked under Menu Settings. Click **Save Menu**. To edit the header with Elementor, install Elementor Pro or a header/footer extension such as ElementsKit, create a header template, add a WordPress **Nav Menu** widget, select **StaffSwap Main Menu**, and set its display condition to **Entire Site**. Do not leave an empty header template assigned, because an empty ElementsKit/Elementor header replaces the theme header. For a normal theme header, use **Default** or **Elementor Full Width**, not **Elementor Canvas**.

## Next production integrations

The foundation is ready for payment subscriptions, identity verification, direct messaging, notifications, and a richer member dashboard. Those should be added as separate modules so the core listing data remains portable.