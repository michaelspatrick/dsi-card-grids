# DSI Card Grids & Sliders

Card-based grids and sliders for **WooCommerce products** and **WordPress posts**, using mostly your theme's styling.

## Shortcodes

### Products: `[dsi_products]`

Attributes (subset of WooCommerce `[products]`):

- `ids` – Comma-separated product IDs.
- `category` – Comma-separated product category slugs.
- `tag` – Comma-separated product tag slugs.
- `limit` – Number of products. Default `12`.
- `orderby` – `date`, `title`, `menu_order`, `rand`. Default `date`.
- `order` – `DESC` or `ASC`. Default `DESC`.
- `on_sale` – `true` / `false`. Default `false`.
- `featured` – `true` / `false`. Default `false`.
- `slider` – `true` / `false` for slider mode. Default `false`.
- `items` – Number of visible cards (slider). Default `3`.
- `interval` – Autoplay interval in ms. Default `5000`.
- `button` – `view` or `add_to_cart`. Default `view`.
- `class` – Extra CSS class on wrapper.

Examples:

```text
[dsi_products]
[dsi_products limit="8" category="seminars" on_sale="true"]
[dsi_products slider="true" items="4" interval="4000" button="add_to_cart"]
```

### Posts: `[dsi_posts]`

Attributes:

- `post_type` – Defaults to `post`.
- `category` – Category slugs (for `post`).
- `tag` – Tag slugs (for `post`).
- `ids` – Comma-separated post IDs.
- `exclude` – Comma-separated post IDs to exclude.
- `limit` – Default `6`.
- `orderby` – `date`, `title`, `rand`. Default `date`.
- `order` – `DESC` / `ASC`. Default `DESC`.
- `slider` – `true` / `false`. Default `false`.
- `items` – Visible cards (slider). Default `3`.
- `interval` – Autoplay interval in ms. Default `5000`.
- `class` – Extra CSS class.

Examples:

```text
[dsi_posts]
[dsi_posts limit="3" category="news"]
[dsi_posts slider="true" items="3" interval="6000"]
```

## Markup & Theme Styling

- Products output `<article>` with classes: `dsi-card dsi-card-product` plus normal `post_class()` for product.
- Posts output `<article>` with classes: `dsi-card dsi-card-post` plus normal `post_class()`.

Each card contains:

- `.dsi-card-media` with featured/product image.
- `.dsi-card-body.entry-summary` with:
  - `.dsi-card-title.entry-title`
  - `.dsi-card-excerpt`
  - `.dsi-card-meta` (date or price).
- `.dsi-card-footer` with `.dsi-card-button.button`.

Your theme can style these classes or rely on existing `entry-*` and `.button` styles.

The plugin’s CSS is intentionally minimal and only handles:

- Slider track flex layout.
- Navigation button positioning.
- Placeholder image aspect ratio.
- Badge container positioning.

## Slider Behaviour

- Uses vanilla JS, no jQuery.
- Respects `data-dsi-items` and `data-dsi-interval` attributes from the shortcode.
- Autoplays by default; pauses on hover.

## Installation

1. Upload the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin.
3. Use `[dsi_products]` and `[dsi_posts]` in pages, posts, or templates.
