# StaffSwap

StaffSwap is an installable WordPress foundation for StaffExchangeHub, a professional workplace exchange marketplace based on the supplied UI samples.

## Included

- `wp-content/themes/staffswap`: responsive marketplace theme with landing page, navigation, footer, forms, listing cards, and a dedicated listing view.
- `wp-content/plugins/staffswap-core`: `swap_listing` custom post type, secure listing metadata, admin editing fields, filterable `[staffswap_listings]` shortcode, and authenticated `[staffswap_create_form]` submission flow.

## Local setup

1. Copy the `wp-content` directory into a WordPress installation.
2. Activate **StaffSwap Core** in Plugins.
3. Activate **StaffSwap** in Appearance > Themes.
4. Activating the plugin automatically creates these pages with their shortcodes:
	- `swaps`: `[staffswap_listings]`
	- `create-swap`: `[staffswap_create_form]`
5. Set the home page in Settings > Reading. Saving Permalinks once after activation refreshes the custom listing routes. The `/swap/` archive is also available directly.

New listings are submitted as `pending` for moderation. Administrators can complete match score, verification, urgency, housing, employer, and location details from the Swap Listings editor.

## Next production integrations

The foundation is ready for payment subscriptions, identity verification, direct messaging, notifications, and a richer member dashboard. Those should be added as separate modules so the core listing data remains portable.