=== MLS API Studio – AI Real Estate Image Staging & Enhancement ===
Contributors: mlsapidev
Tags: real estate, virtual staging, twilight, declutter, floor plan, photo enhance
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered virtual staging, dusk twilight conversion, decluttering, 3D floor plan renders, and photo enhancement for real estate listings.

== Description ==

**MLS API Studio** integrates the powerful generative AI media engine from [mlsapi.dev](https://mlsapi.dev) directly into your WordPress dashboard.

Transform ordinary real estate photography into listing visuals in seconds:
* **Virtual Room Staging:** Furnish vacant rooms across 29 architectural interior styles (Modern, Scandinavian, Luxury, Farmhouse, etc.).
* **Dusk & Twilight Conversion:** Turn daytime exterior shots into warm twilight imagery with glowing windows and landscape illumination.
* **Room Decluttering:** Erase tenant clutter, cords, moving boxes, and distractions while keeping original architecture intact.
* **De-Staging (Empty Room):** Remove all furniture and restore bare hardwood, tile, or concrete floors.
* **Change Style:** Re-style furnished rooms into a fresh aesthetic without changing layout.
* **Replace Furniture & Materials:** Swap dated sofas, tables, or outdated countertops and backsplashes.
* **2D Floor Plan to 3D Dollhouse:** Turn architectural blueprints and sketches into photorealistic 3D isometric renders.
* **Photo Enhancement:** Touch up lawns, blue skies, and upscale images to 4K super-resolution.

Built with an intuitive in-dashboard modal editor, interactive Before/After comparison slider, and direct Media Library integration.

== Installation ==

1. Upload the `mlsapi-studio` folder to your `/wp-content/plugins/` directory, or install via WordPress Plugin installer.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **MLS Studio > Settings & Billing** in your admin sidebar.
4. Enter your API Key from [mlsapi.dev](https://mlsapi.dev) and click **Test Connection**.
5. Open any image in your Media Library and click **Edit in MLS Studio**!

== Frequently Asked Questions ==

= Do I need an mlsapi.dev account? =
Yes, you will need an active API key from mlsapi.dev to connect to the cloud AI generation pipeline.

= Will this overwrite my original images? =
By default, MLS API Studio saves generated visuals as new attachments in your Media Library. You can also choose to replace the original image in-place if desired.

= Which image formats are supported? =
You can import PNG, JPEG, WebP, AVIF, and export as WebP (optimized), PNG (lossless), or JPEG.

== Changelog ==

= 1.0.0 =
* Initial scaffolding release.
* In-dashboard AI Studio modal with interactive Before/After split slider.
* Full integration with WordPress Media Library and Admin Bar.
* 12 real estate AI operations supported.
* Real-time billing and credits overview dashboard.
