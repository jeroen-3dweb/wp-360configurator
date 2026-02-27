---
apply: always
---

# Copilot Instructions

## Docker Compose for local development

- Do not mount `wp-content/themes` in `docker-compose.yml`.
- This project is currently plugin-focused for local development.
- Do not mount the full `wp-content/plugins` directory in `docker-compose.yml`.
- Mount only the active plugin during development:
  `./wp-content/plugins/3dweb-print-studio:/var/www/html/wp-content/plugins/3dweb-print-studio:delegated`.

## WordPress coding standards

- Write all plugin code according to official WordPress coding standards.
- Follow WordPress PHP, JavaScript, and CSS coding style conventions.
- Use WordPress best practices for security and data handling:
  sanitize input, validate data, and escape output.
- Use WordPress APIs and naming conventions where applicable.

## Agent execution behavior

- Do not get stuck in repetitive loops. If a step fails or repeats without progress, stop repeating it, state the blocker briefly, and either try a different approach or ask the user for clarification.

## Configurator Workflow And Multi-theme Architecture

- Product-to-configurator link:
  When a WooCommerce product is linked to a configurator product SKU, the Add to Cart button becomes the start button for a configurator session ("Start configuration").
  Example files:
  - `wp-content/plugins/3dweb-print-studio/includes/woo/class-3dweb-ps-woo-metabox.php`
  - `wp-content/plugins/3dweb-print-studio/public/themes/default/woo/woo_default.js`
  - `wp-content/plugins/3dweb-print-studio/public/themes/class-3dweb-ps-public-woo-base.php`

- Returning from configurator:
  After the customer returns from the configurator, the URL contains the session reference parameter `teamSessionReference`.
  Use this parameter to load session assets and show generated images on the product page.
  Example files:
  - `wp-content/plugins/3dweb-print-studio/public/themes/class-3dweb-ps-public-woo-base.php`
  - `wp-content/plugins/3dweb-print-studio/public/js/3dweb-ps-public.js`
  - `wp-content/plugins/3dweb-print-studio/public/themes/default/woo/woo_default.js`

- Cart and checkout behavior:
  Generated session references and images must be preserved in cart item data and used in cart rendering.
  The same reference must be added to order line item metadata so the selected design remains attached to the final order.
  Example files:
  - `wp-content/plugins/3dweb-print-studio/includes/woo/class-3dweb-ps-woo.php`
  - `wp-content/plugins/3dweb-print-studio/public/themes/class-3dweb-ps-public-woo-base.php`

- Back-office order visibility:
  In admin/order context, the design reference and generated output should be visible so store owners can review or download the design assets easily.
  Reference behavior:
  - `wp-content/plugins/3dweb-print-studio/public/themes/class-3dweb-ps-public-woo-base.php` (order line item metadata via `handleCreateOrderLineItem`)

- Theme extensibility:
  The project is intentionally structured to support multiple themes quickly, with theme-specific front-end components (for example different slider/gallery integrations).
  Keep shared flow in base classes and put theme-specific behavior in theme implementations/hooks.
  Example files:
  - `wp-content/plugins/3dweb-print-studio/public/themes/class-3Dweb-ps-public-theme-factory.php`
  - `wp-content/plugins/3dweb-print-studio/public/themes/class-3dweb-ps-public-woo-base.php`
  - `wp-content/plugins/3dweb-print-studio/public/themes/default/woo/woo_default.js`
  - `wp-content/plugins/3dweb-print-studio/public/js/sliders/3dweb-ps-flexslider.js`
