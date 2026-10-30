# StaffSwap Elementor Starter Templates

Import `staffswap-homepage.json` from the Elementor template library:

1. Open **Templates > Saved Templates > Import Templates**.
2. Select `staffswap-homepage.json`.
3. Create or edit the Home page with Elementor.
4. Insert the imported StaffSwap Homepage template.
5. Replace the static featured content with an Elementor Shortcode widget containing `[staffswap_listings limit="3"]` when the StaffSwap Core plugin is active.

The template uses StaffSwap theme classes and design tokens, so it inherits the theme typography, colors, spacing, responsive behavior, and button styling. Header and footer remain controlled by the theme unless you create Elementor Theme Builder Header/Footer templates.

## Editing pages with Elementor

The theme supports Elementor content on regular pages, including pages that use StaffSwap's custom page templates. Edit a page with Elementor and its saved Elementor layout replaces the theme's default page layout.

For the blog, either:

- Assign a page as the WordPress **Posts page** and edit its layout with Elementor. The theme renders that saved layout on the blog route. Add a Posts widget (Elementor Pro or a compatible add-on) if you also want a dynamic posts list.
- With Elementor Pro, create an Elementor **Archive** template in **Templates > Theme Builder** to design the blog archive. An Elementor Archive template takes precedence over the Posts page layout.

With Elementor Pro, single post layouts can be designed with an Elementor **Single** Theme Builder template. Otherwise, each post's body remains editable through Elementor where the post type is enabled in Elementor settings.
