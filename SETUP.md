# StaffSwap Setup Guide

This guide installs StaffSwap as a WordPress marketplace with the native theme, modular plugins, Elementor support, optional WooCommerce, demo data, and database migrations.

## 1. Requirements

- WordPress 6.4 or newer
- PHP 7.4 or newer; PHP 8.1+ is recommended
- MySQL 5.7+ or MariaDB 10.4+
- HTTPS enabled for production
- A WordPress administrator account

Elementor and WooCommerce are optional. The StaffSwap theme and core marketplace work without either dependency.

## 2. Install the files

1. Back up the WordPress database and `wp-content` directory.
2. Copy this repository's `wp-content/themes/staffswap` directory to `wp-content/themes/`.
3. Copy each directory under `wp-content/plugins/` to the WordPress `wp-content/plugins/` directory.
4. Do not rename plugin directories after activation; activation and update paths use their directory names.

For a zip-based install, zip the `staffswap` theme directory and each plugin directory separately. Upload them in **Appearance > Themes > Add New > Upload Theme** and **Plugins > Add New > Upload Plugin**.

## 3. Activate in this order

1. Activate **StaffSwap Core**.
2. Activate **StaffSwap Resources** if the Resources Centre is needed.
3. Activate **StaffSwap Profiles** for professional profile fields.
4. Activate **StaffSwap Messaging** for contact forms and inboxes.
5. Install and activate WooCommerce before activating **StaffSwap WooCommerce Bridge** if premium checkout is needed.
6. Activate the **StaffSwap** theme.

StaffSwap Core creates its database tables on activation. If the database version changes later, the plugin upgrades tables automatically through `dbDelta()`.

## 4. Run the setup wizard

Open **Appearance > StaffSwap Setup** and select **Run setup**.

The wizard creates or repairs:

- Home
- Find Swaps
- Search Swaps
- Create a Swap Post
- Register
- Sign In
- My Profile
- Resources Centre
- How It Works
- Success Stories
- Blog
- Pricing
- Messages, when Messaging is activated

It also:

- Sets Home as the front page
- Creates `StaffSwap Main Menu`
- Assigns it to the theme's Primary Menu location
- Adds missing menu items without duplicating existing links
- Shows plugin and builder health checks

If anything looks wrong, run **Repair pages and menu** from the same screen.

## 5. Configure the site

### StaffSwap settings

Open **Settings > StaffSwap** to edit:

- Site name
- Hero title
- Hero description
- Primary and secondary CTA labels
- Homepage statistics

Open **Appearance > Customize > StaffSwap Homepage** for live visual customization, including the primary action color.

### Navigation

Open **Appearance > Menus**:

1. Select `StaffSwap Main Menu`.
2. Confirm the required pages are present.
3. Confirm **Primary Menu** is selected under Menu Settings.
4. Select **Save Menu**.

### Reading settings

Open **Settings > Reading** and confirm:

- A static page is selected.
- `Home` is selected as the homepage.

## 6. Elementor setup

Install and activate Elementor separately if you want builder editing.

### Page editing

Open any page and select **Edit with Elementor**. The theme supports Elementor page content for all core routes.

### Theme Builder

Use **Templates > Theme Builder** for:

- Header
- Footer
- Single post
- Single swap listing
- Archive
- Search/archive layouts
- 404 layout

Set each published template's display condition to **Entire Site** or the intended content type.

The theme calls Elementor's standard `elementor_theme_do_location()` locations. If an Elementor template produces no output, the native StaffSwap header or footer remains visible.

For the homepage, use **Default** or **Elementor Full Width**. Do not use **Elementor Canvas** if you need the StaffSwap header and footer.

### Starter template

Import:

`wp-content/themes/staffswap/elementor-templates/staffswap-homepage.json`

from **Templates > Saved Templates > Import Templates**, then insert it into the Home page. Replace static featured content with an Elementor Shortcode widget containing:

`[staffswap_listings limit="3"]`

## 7. Available shortcodes

### Marketplace

- `[staffswap_listings]`
- `[staffswap_listings limit="6"]`
- `[staffswap_search]`
- `[staffswap_create_form]`
- `[staffswap_save_button id="123"]`
- `[staffswap_saved_listings]`
- `[staffswap_save_search]`

### Accounts and profiles

- `[staffswap_register]`
- `[staffswap_login]`
- `[staffswap_dashboard]`
- `[staffswap_profile]`

### Resources and messaging

- `[staffswap_resources]`
- `[staffswap_resource_download id="123"]`
- `[staffswap_contact listing="123"]`
- `[staffswap_inbox]`

### Commerce

- `[staffswap_upgrade]`

All shortcodes can be placed in Elementor Shortcode widgets, WordPress Shortcode blocks, or normal page content.

## 8. Database tables

StaffSwap Core uses the WordPress database prefix, so a site with prefix `wp_` receives:

- `wp_staffswap_matches`: listing-to-listing candidate matches, score, and workflow status.
- `wp_staffswap_saved_searches`: member search filters and alert frequency.
- `wp_staffswap_events`: product activity events such as saved searches and future match/contact actions.

Never hard-code the `wp_` prefix in custom code. Use the plugin's `staffswap_db_table()` helper or `$wpdb->prefix`.

The schema version is stored in the `staffswap_db_version` option. Database changes must be additive and run through `dbDelta()` so existing installations are upgraded safely.

## 9. WooCommerce

1. Install and configure WooCommerce currency, tax, and payment settings.
2. Activate **StaffSwap WooCommerce Bridge**.
3. The bridge creates a virtual `StaffSwap Plus` product if one does not already exist.
4. Add `[staffswap_upgrade]` to a pricing or account page.
5. Test checkout in a sandbox/payment-test mode.
6. A completed order marks the purchaser with `staffswap_plus_active = 1`.

The bridge does not replace WooCommerce checkout or payment processing. Configure those in WooCommerce itself.

## 10. Demo content

Import [staffswap-demo.xml](staffswap-demo.xml) through **Tools > Import > WordPress**.

Activate the relevant StaffSwap plugins before importing custom post types. The import contains pages, authors, swap listings, resources, categories, and blog posts.

Demo XML is for development/staging only. Change demo emails, passwords, and content before production use.

## 11. Cache and CSS refresh

After theme/plugin deployment:

1. Clear the browser cache.
2. Clear any WordPress caching plugin.
3. Clear LiteSpeed/Hostinger cache if applicable.
4. In Elementor, use **Tools > General > CSS & Data > Regenerate Files & Data**.
5. Save **Settings > Permalinks** once to refresh custom post type routes.

## 12. Troubleshooting

### Header is missing

- Check that the page template is not Elementor Canvas.
- Check **Templates > Theme Builder** for an empty Header template.
- Check ElementsKit Header Footer for an empty active header.
- Run **Appearance > StaffSwap Setup > Repair pages and menu**.
- Confirm `StaffSwap Main Menu` is assigned to Primary Menu.

### Elementor says content area was not found

- Ensure Elementor is active.
- Ensure the page is using Default or Elementor Full Width.
- Update the theme files, especially `front-page.php` and `page.php`.
- Regenerate Elementor Files & Data.
- Remove duplicate/empty builder templates.

### Listings do not show

- Confirm StaffSwap Core is active.
- Confirm the listing status is Published.
- Save Permalinks.
- Confirm listing metadata is populated in the Swap Listing editor.

### Resources do not show

- Confirm StaffSwap Resources is active.
- Confirm the page contains `[staffswap_resources]`.
- Confirm the resource status is Published.

### Premium checkout does not show

- Confirm WooCommerce is active before the bridge.
- Confirm StaffSwap WooCommerce Bridge is active.
- Check that the StaffSwap Plus product exists under Products.
- Confirm WooCommerce checkout is configured.

## 13. Production checklist

- Use HTTPS.
- Replace demo content and emails.
- Configure WordPress mail delivery through a transactional provider.
- Configure backups and test database restoration.
- Configure WooCommerce payment gateways only after sandbox testing.
- Restrict administrator accounts and use strong passwords.
- Keep WordPress, PHP, WooCommerce, Elementor, theme, and plugins updated.
- Test registration, listing moderation, messaging, downloads, saved searches, and checkout on staging first.
- Monitor PHP error logs and failed cron jobs.
