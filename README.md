# MLS API Studio for WordPress

AI real estate photo tools from [mlsapi.dev](https://mlsapi.dev), inside the WordPress Media Library:
virtual staging, day-to-dusk, declutter, empty room, restyle, furniture and material swaps, paint colors,
2D floor plan to 3D, exterior enhancement, 4K upscale and ad creatives.

Pick any image in the Media Library (or paste or drop one), run a Studio operation, compare before and after,
then save the result as a new attachment or replace the original in place.

## Install

1. Copy `mlsapi-studio/` into `wp-content/plugins/` (or zip that folder and upload it under **Plugins → Add New**).
2. Activate **MLS API Studio**.
3. Go to **MLS Studio → Settings & Billing**, paste your API key from [mlsapi.dev](https://mlsapi.dev), and click **Test Connection**.
4. In the Media Library, choose **Edit in MLS Studio** on any image.

Requires WordPress 6.0+ and PHP 7.4+. An mlsapi.dev account is needed; generations use Studio credits.

## How it works

- API keys stay in `wp_options` and never reach the browser; every call is proxied through WordPress AJAX handlers
  with nonce and capability checks (`upload_files` to generate, `edit_post` to replace).
- Jobs are async: the plugin dispatches to `/v1/studio/*`, then polls `/v1/studio/jobs/:id` until the result is ready.
- Results are downloaded server-side, validated as images, and registered with full thumbnail metadata.
- Hooks for developers: `mlsapi_image_created` and `mlsapi_image_replaced`.

## Layout

```
mlsapi-studio/          the plugin (this folder is what ships)
  mlsapi-studio.php     bootstrap
  includes/             API client, admin, AJAX, settings, media hooks, templates
  assets/               modal, slider and settings CSS/JS
  readme.txt            WordPress.org readme
SPEC.md                 design spec and implementation plan
```

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
