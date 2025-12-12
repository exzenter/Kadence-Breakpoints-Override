=== Kadence Breakpoints Override ===
Contributors: Exzent.de - mitchWP
Tags: kadence, breakpoints, responsive, mobile, tablet
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Override frontend breakpoint pixel widths for Kadence without builds. Provides an admin page to set breakpoints and injects the CSS on the frontend.

== Description ==

**Kadence Breakpoints Override** allows you to customize the responsive breakpoints used by Kadence Theme and Kadence Blocks on the frontend of your WordPress site.

= Features =

* **Custom Tablet Breakpoint** - Set a custom pixel width for the tablet breakpoint (default: 1024px)
* **Custom Mobile Breakpoint** - Set a custom pixel width for the mobile breakpoint (default: 767px)
* **No Build Tools Required** - 100% PHP-based, no npm, webpack, or any build process needed
* **Update Safe** - Works independently of Kadence, survives theme and plugin updates
* **Frontend Only** - Only affects the public site, Gutenberg editor preview remains unchanged
* **Clean & Lightweight** - Minimal code, no bloat, follows WordPress coding standards

= How It Works =

The plugin injects custom CSS into the `<head>` of your frontend pages that overrides the default Kadence breakpoint values. This is done using CSS custom properties and media queries.

= Important Note =

This plugin only affects the **frontend** (public-facing site). The Gutenberg editor preview will continue to use the default Kadence breakpoints. This is by design, as WordPress does not provide a clean way to modify editor breakpoints without build tools.

== Installation ==

1. Upload the `kadence-breakpoints-override` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings → Kadence Breakpoints** to configure your custom breakpoints
4. Save your settings - the new breakpoints will immediately apply to your frontend

== Frequently Asked Questions ==

= Will this break my Kadence setup? =

No. The plugin only adds CSS overrides and does not modify any Kadence files. If you deactivate the plugin, your site will return to using the default Kadence breakpoints.

= Why doesn't this affect the Gutenberg editor? =

WordPress loads the editor in an iframe with its own styles. Modifying those would require JavaScript builds and editor-specific hooks. This plugin intentionally keeps things simple by only targeting the frontend.

= Will my settings survive Kadence updates? =

Yes! The plugin stores its settings independently in the WordPress database and does not modify any Kadence files.

= What are the default Kadence breakpoints? =

* Tablet: 1024px (screens 1024px and below use tablet styles)
* Mobile: 767px (screens 767px and below use mobile styles)

= Can I reset to default values? =

Simply enter 1024 for Tablet and 767 for Mobile to restore Kadence defaults, or deactivate the plugin.

== Screenshots ==

1. Admin settings page for configuring breakpoints
2. CSS output preview showing the generated styles

== Changelog ==

= 1.0.0 =
* Initial release
* Admin page for setting tablet and mobile breakpoints
* Frontend CSS injection for breakpoint overrides
* Settings link on plugins page
* CSS preview on admin page

== Upgrade Notice ==

= 1.0.0 =
Initial release of Kadence Breakpoints Override.

