# SolanaWP WordPress Theme

**Version:** 1.0.0
**Author:** WORLDGPL
**Author URI:** https://www.worldgpl.com/
**Theme URI:** https://www.worldgpl.com/solanawp-theme/
**License:** GNU General Public License v2 or later
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html
**Text Domain:** solanawp
**Domain Path:** /languages

A WordPress theme for the Hannisol Solana Address Checker, designed to replicate the provided HTML design. Features comprehensive validation and analysis for Solana addresses. "Hannisol's Insight, Navigating Crypto Like Hannibal Crossed the Alps."

## Description

SolanaWP is a specialized WordPress theme built to power the Hannisol Solana Address Checker website. It provides a comprehensive interface for users to validate and analyze Solana addresses, offering insights into balances, transactions, security, and potential risks. The theme is designed to be fast, responsive, and SEO-friendly.

The design and core feature set are based on the "hannisolsvelte.html" static prototype and the "solanacheckerplan.txt" project document.

## Features

* **Solana Address Checker Interface:** Main feature, allowing users to input a Solana address and receive detailed analysis. (Functionality primarily driven by JavaScript and backend API calls).
* **Address Validation:** Checks format, length, and type.
* **Balance & Holdings:** Displays SOL balance and token/NFT counts.
* **Transaction Analysis:** Shows total transactions, first/last activity, and recent transactions.
* **Account Details & Security Analysis:** Provides owner info, executable status, risk levels.
* **Rug Pull Risk Analysis:** Assesses potential risks associated with a token/address.
* **Community Interaction Metrics:** Displays community size, engagement, sentiment.
* **Responsive Design:** Adapts to various screen sizes, from mobile to desktop.
* **Customizable:** Key theme elements (logo, some colors, ad placements via widgets) can be managed through the WordPress Customizer.
* **Widgetized Sidebar:** For displaying ad banners and other content, as seen in `hannisolsvelte.html`. This supports the monetization strategy outlined in `solanacheckerplan.txt`.
* **Monetization Ready:** Includes styling for ad banners and affiliate sections.
* **Branding:** Incorporates the Hannisol logo and typography guidelines from `solanacheckerplan.txt`.

## Installation

1.  Download the `SolanaWP.zip` file (once the theme is packaged).
2.  In your WordPress admin panel, go to **Appearance > Themes**.
3.  Click **Add New**, then **Upload Theme**.
4.  Choose the `SolanaWP.zip` file and click **Install Now**.
5.  Once installed, click **Activate**.

## Setup

1.  **Homepage Setup:**
    * Create a new Page (e.g., "Address Checker").
    * In the Page Attributes, select the "Hannisol Address Checker" template.
    * Go to **Settings > Reading**. Set "Your homepage displays" to "A static page".
    * Select your newly created page as the "Homepage".
    * Save changes.

2.  **Custom Logo:**
    * Go to **Appearance > Customize > Site Identity**.
    * Upload your logo. The recommended size is 120x120 pixels.

3.  **Navigation Menu:**
    * Go to **Appearance > Menus**.
    * Create a new menu, assign it to the "Primary Menu" location.

4.  **Widgets (Ads & Sidebar Content):**
    * Go to **Appearance > Widgets**.
    * Add widgets to the "Main Sidebar" or "Ad Banner Area (Sidebar)".
    * For ads, use the "Custom HTML" widget and paste your ad code. You can apply classes like `ad-banner` or `ad-banner small` to the wrapping div within the Custom HTML widget if the widget area itself doesn't enforce these classes.

5.  **Affiliate Links (Example):**
    * If Customizer options are added for affiliate links (see `inc/customizer.php` examples), configure them under **Appearance > Customize > Affiliate Links & Ads**.
    * The affiliate section on the checker page uses these links.

## JavaScript Functionality (Solana Checker)

The core Solana address checking functionality is powered by JavaScript (`assets/js/main.js`). This script currently contains **simulated data**. For a live site, this JavaScript needs to be modified to:

1.  Make AJAX calls to a WordPress backend endpoint (which you'll need to create, handling security with nonces).
2.  The WordPress backend endpoint will then securely interact with Solana RPC endpoints or other necessary third-party APIs to fetch real-time data for the given address.
3.  The backend will process this data and send a structured JSON response back to the JavaScript.
4.  The JavaScript will then parse this JSON and populate the results cards on the page.

Refer to the `solanacheckerplan.txt` for details on the core features and data points expected.

## Customization

Further customization options (colors, fonts, layout settings) can be added to `inc/customizer.php`.

## CSS Styling

The primary styles are in `assets/css/main.css`, adapted from `hannisolsvelte.html`. You can modify this file for visual changes. For minor CSS tweaks, using the "Additional CSS" section in the WordPress Customizer is also an option.

## Translation

This theme is translation-ready. The text domain is `solanawp`. A `solanawp.pot` file is included in the `languages/` directory to assist with creating translations.

## Child Theming

For advanced customizations and to ensure your changes are not overwritten during theme updates (if this theme were to be updated), it's recommended to use a child theme.

---

This `README.md` provides essential information about the theme, its setup, and how its core functionality is intended to work.
