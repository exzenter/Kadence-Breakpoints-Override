# Kadence Breakpoints Override

A WordPress plugin that allows you to customize the responsive breakpoints used by Kadence Theme and Kadence Blocks on the frontend without requiring build tools.

## Features

- 🎯 **Custom Tablet Breakpoint** - Set a custom pixel width for the tablet breakpoint (default: 1024px)
- 📱 **Custom Mobile Breakpoint** - Set a custom pixel width for the mobile breakpoint (default: 767px)
- 🚀 **No Build Tools Required** - 100% PHP-based, no npm, webpack, or any build process needed
- ✅ **Update Safe** - Works independently of Kadence, survives theme and plugin updates
- 🎨 **Frontend Only** - Only affects the public site, Gutenberg editor preview remains unchanged
- 💡 **Clean & Lightweight** - Minimal code, no bloat, follows WordPress coding standards

## Installation

### Manual Installation

1. Download or clone this repository
2. Upload the `kadence-breakpoints-override` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **Settings → Kadence Breakpoints** to configure your custom breakpoints
5. Save your settings - the new breakpoints will immediately apply to your frontend

### Via Git

```bash
cd wp-content/plugins/
git clone https://github.com/yourusername/kadence-breakpoints-override.git
```

Then activate the plugin through WordPress admin.

## Usage

1. Navigate to **Settings → Kadence Breakpoints** in your WordPress admin
2. Enter your desired breakpoint values:
   - **Tablet Breakpoint**: Width at which tablet styles are applied (default: 1024px)
   - **Mobile Breakpoint**: Width at which mobile styles are applied (default: 767px)
3. Click "Save Breakpoints"
4. Clear your cache (browser, plugin caches, server caches)
5. Visit your frontend to see the changes

**Note**: The mobile breakpoint must be less than the tablet breakpoint. The plugin will automatically adjust this if needed.

## How It Works

The plugin uses multiple methods to ensure breakpoints are overridden:

1. **WordPress Filters** - Hooks into Kadence filter hooks at the source:
   - `kadence_blocks_tablet_breakpoint`
   - `kadence_blocks_mobile_breakpoint`
   - `kadence_blocks_default_breakpoints`
   - `kadence_screen_sizes` (Kadence Theme)

2. **CSS Custom Properties** - Injects CSS variables that override default values:
   - `--global-kb-tablet-width`
   - `--global-kb-mobile-width`
   - `--kb-tablet-break`
   - `--kb-mobile-break`

3. **Media Query Overrides** - Adds CSS rules to ensure visibility classes work correctly with custom breakpoints

## Default Breakpoints

Kadence uses the following default breakpoints:
- **Tablet**: 1024px (screens 1024px and below use tablet styles)
- **Mobile**: 767px (screens 767px and below use mobile styles)

## Important Notes

### Gutenberg Editor

This plugin **only affects the frontend** (public-facing site). The Gutenberg editor preview will continue to use the default Kadence breakpoints. This is by design, as WordPress does not provide a clean way to modify editor breakpoints without build tools.

### Caching

After changing breakpoint values, make sure to:
- Clear browser cache
- Clear any caching plugin caches (WP Rocket, W3 Total Cache, etc.)
- Clear server-side caches if applicable
- Purge CDN cache if using a CDN

### Browser Compatibility

Works in all modern browsers. Uses standard CSS features with no JavaScript dependencies for the main functionality.

## File Structure

```
kadence-breakpoints-override/
├── kadence-breakpoints-override.php  # Main plugin file
├── breakpoint-override.js            # Alternative JS solution (optional)
├── readme.txt                        # WordPress.org readme
├── README.md                         # This file
└── .gitignore                        # Git ignore file
```

## Alternative JavaScript Solution

The repository includes `breakpoint-override.js` as an alternative standalone JavaScript solution. This can be used independently if you prefer not to use the plugin, or if you need to override breakpoints in a different way.

To use the JS solution:
1. Edit the breakpoint values in the `CONFIG` object
2. Add the script to your site via:
   - Theme's `functions.php` with `wp_enqueue_script`
   - A code snippets plugin
   - Customizer → Additional JavaScript (if available)

## Development

### Requirements

- WordPress 5.0+
- PHP 7.2+
- Kadence Theme or Kadence Blocks plugin

### Hooks & Filters

The plugin provides the following hooks:

**Filters:**
- `kadence_blocks_tablet_breakpoint` - Overrides tablet breakpoint (priority: 999)
- `kadence_blocks_mobile_breakpoint` - Overrides mobile breakpoint (priority: 999)
- `kadence_blocks_default_breakpoints` - Overrides default breakpoints array (priority: 999)
- `kadence_screen_sizes` - Overrides Kadence Theme screen sizes (priority: 999)

**Actions:**
- `wp_head` (priority: 1) - Injects early CSS variables
- `wp_head` (priority: 9999) - Injects late CSS overrides
- `wp_footer` (priority: 9999) - Injects footer CSS for late-loaded styles

## FAQ

**Q: Will this break my Kadence setup?**  
A: No. The plugin only adds CSS overrides and uses WordPress filters. It does not modify any Kadence files. If you deactivate the plugin, your site will return to using the default Kadence breakpoints.

**Q: Why doesn't this affect the Gutenberg editor?**  
A: WordPress loads the editor in an iframe with its own styles. Modifying those would require JavaScript builds and editor-specific hooks. This plugin intentionally keeps things simple by only targeting the frontend.

**Q: Will my settings survive Kadence updates?**  
A: Yes! The plugin stores its settings independently in the WordPress database and does not modify any Kadence files.

**Q: Can I reset to default values?**  
A: Simply enter 1024 for Tablet and 767 for Mobile to restore Kadence defaults, or deactivate the plugin.

## Troubleshooting

1. **Breakpoints not working?**
   - Clear all caches (browser, plugin caches, server caches)
   - If using a CDN, purge the CDN cache
   - Check if another plugin or theme is also modifying breakpoints
   - Inspect the page source to verify the CSS is being injected
   - Check the browser console for any errors

2. **Settings not saving?**
   - Ensure you have `manage_options` capability
   - Check WordPress debug log for errors
   - Verify the database is writable

3. **Gutenberg editor still shows old breakpoints?**
   - This is expected behavior. The plugin only affects the frontend.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

GPL v2 or later

## Credits

Developed for the WordPress community. Compatible with Kadence Theme and Kadence Blocks.

## Changelog

### 1.1.0
- Added WordPress filter hooks for deeper integration
- Enhanced CSS injection with multiple priority levels
- Added footer CSS injection for late-loaded styles
- Improved admin page with troubleshooting section
- Better sanitization and validation

### 1.0.0
- Initial release
- Admin page for setting tablet and mobile breakpoints
- Frontend CSS injection for breakpoint overrides
- Settings link on plugins page
- CSS preview on admin page

