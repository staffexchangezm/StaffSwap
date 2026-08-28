# StaffSwap

StaffSwap is an installable WordPress foundation for StaffExchangeHub, a professional workplace exchange marketplace based on the supplied UI samples.

## Included

- `wp-content/themes/staffswap`: responsive marketplace theme with landing page, navigation, footer, forms, listing cards, and a dedicated listing view.
- `wp-content/plugins/staffswap-core`: `swap_listing` custom post type, secure listing metadata, admin editing fields, filterable `[staffswap_listings]` shortcode, and authenticated `[staffswap_create_form]` submission flow.
- `wp-content/plugins/staffswap-resources`: searchable `staff_resource` content type, resource categories, and `[staffswap_resources]` Resources Centre layout.
- `wp-content/plugins/staffswap-profiles`: member profession, location, and employer fields with `[staffswap_profile]` display output.
- `wp-content/plugins/staffswap-messaging`: private `staff_message` content type and `[staffswap_contact listing="123"]` contact form.

The theme is compatible with Elementor and other page builders through normal WordPress templates, menus, widgets, and shortcodes. Elementor is not bundled with this repository: install and activate the Elementor plugin separately before looking for Elementor admin menus. Use an Elementor Shortcode widget with `[staffswap_listings]`, `[staffswap_search]`, `[staffswap_create_form]`, `[staffswap_resources]`, `[staffswap_profile]`, or `[staffswap_contact listing="123"]`. The theme's header, footer, fonts, CSS variables, and scripts remain available to Elementor pages.

## Local setup

1. Copy the `wp-content` directory into a WordPress installation.
2. Activate **StaffSwap Core** in Plugins.
3. Activate **StaffSwap Resources**, **StaffSwap Profiles**, and **StaffSwap Messaging** when those modules are needed.
4. Activate **StaffSwap** in Appearance > Themes.
5. Open Appearance > StaffSwap Setup and click **Run setup**. This creates the essential pages, sets the homepage, and builds the StaffSwap Main Menu. The setup screen can be reopened at any time.
6. Activating the core plugin automatically creates these pages with their shortcodes:
	- `swaps`: `[staffswap_listings]`
	- `create-swap`: `[staffswap_create_form]`
	- `search`: `[staffswap_search]`
	- `register`: `[staffswap_register]`
	- `sign-in`: `[staffswap_login]`
	- `my-profile`: `[staffswap_dashboard]`
7. Add a Resources page with `[staffswap_resources]` and a Profile page with `[staffswap_profile]` if they were not included by the setup screen. Set the home page in Settings > Reading if you want a different page. Saving Permalinks once after activation refreshes the custom listing routes. The `/swap/` archive is also available directly.

## Assets

- `wp-content/themes/staffswap/style.css`: theme and component styles.
- `wp-content/themes/staffswap/assets/js/main.js`: mobile navigation and small form interactions.
- External Google Fonts are enqueued by the theme; replace them with local font files for a fully self-hosted production deployment.

New listings are submitted as `pending` for moderation. Administrators can complete match score, verification, urgency, housing, employer, and location details from the Swap Listings editor.

Theme content is editable from Appearance > Customize > StaffSwap Homepage. Site administrators can update the hero title, description, CTA labels, homepage stats, and primary action color without editing code.

## Elementor cache refresh

After Elementor is installed and active, use **Elementor > Tools > General > CSS & Data > Regenerate Files & Data**. In some Elementor versions this appears as **Elementor > Tools > Regenerate CSS & Data**. If Elementor is not installed or activated, neither menu exists; use the StaffSwap Customizer and normal WordPress cache controls instead. For the homepage, use the **Default** or **Elementor Full Width** page template, not **Elementor Canvas**, because Canvas intentionally removes the theme header and footer.

## Next production integrations

The foundation is ready for payment subscriptions, identity verification, direct messaging, notifications, and a richer member dashboard. Those should be added as separate modules so the core listing data remains portable.