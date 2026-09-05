=== ADStn Auto Poster ===
Contributors: mrghozzi
Donate link: https://www.adstn.ovh
Tags: adstn, auto publish, social share, auto post, crosspost
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Auto-publish WordPress articles and content directly to the ADStn social platform via Developer REST API & OAuth 2.0.

== Description ==

**ADStn Auto Poster** is a professional WordPress plugin designed to seamlessly auto-publish and synchronize your WordPress posts, articles, and custom post types with the **ADStn** social platform ([https://www.adstn.ovh](https://www.adstn.ovh)).

Increase your reach, boost social engagement, and drive traffic back to your website automatically every time you publish new content.

### ✨ Key Features:

* **Seamless OAuth 2.0 Authentication**: One-click authorization flow with secure token exchange and automated token refresh (`refresh_token`).
* **Direct Access Token Support**: Optional manual bearer token input for developers and custom setups.
* **Smart Content Template Builder**: Fully customizable post templates supporting dynamic tags:
  * `{title}` - Post title
  * `{url}` - Direct permalink to the post
  * `{excerpt}` - Post excerpt or trimmed summary (configurable character length)
  * `{hashtags}` - Automatically generated hashtags
  * `{author}` - Post author display name
  * `{site_name}` - WordPress site title
  * `{categories}` - Comma-separated post categories
  * `{tags}` - Comma-separated post tags
* **Live Feed Simulation Preview**: Interactive real-time preview showing exactly how your post will render on the ADStn platform before publishing.
* **Intelligent Hashtag Generator**: Automatically converts WordPress tags and categories into clean, social-friendly `#hashtags`.
* **Granular Filtering & Publishing Rules**:
  * Select supported post types (Posts, Pages, Custom Post Types).
  * Filter by category inclusion and exclusion.
  * Choose trigger events (on first publish or on post updates).
  * Set default ADStn post privacy (`public`, `followers`, or `private`).
* **Post Editor Metabox (Sidebar)**:
  * Enable/disable auto-publishing on a per-post basis.
  * Write a custom message overriding the default template for specific posts.
  * Instant "Share to ADStn Now" AJAX button to share immediately without reloading.
  * View previous publishing date, status, and error logs directly inside the editor.
* **Detailed Activity & Sync Logs**:
  * Full audit trail of every publishing attempt with status badges (`Success`, `Failed`, `Pending`).
  * Inspect full request payloads and API responses for easy troubleshooting.
  * One-click "Retry" action for failed attempts.
* **Modern & Multilingual Dashboard**:
  * Clean, responsive UI with Glassmorphism styling and dark/light accents.
  * 100% WordPress i18n/l10n standard compliant with `.pot` template for seamless community translations via translate.wordpress.org.

== Third-Party Services ==

This plugin integrates with and connects to the **ADStn Developer Platform** (https://www.adstn.ovh) in order to publish articles and manage social account connectivity.

* **Service Name**: ADStn (https://www.adstn.ovh)
* **Purpose**: User OAuth 2.0 authentication, profile validation, and publishing post content to the authenticated user's ADStn feed.
* **Data Transmitted**: Post title, URL, post excerpt, generated hashtags, and user OAuth tokens. No private site data or visitor information is sent.
* **Terms of Service**: https://www.adstn.ovh/terms
* **Privacy Policy**: https://www.adstn.ovh/privacy

== Installation ==

1. Upload the `adstn-auto-poster` directory to your WordPress plugins directory (`/wp-content/plugins/`).
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to **ADStn Poster** &rarr; **Connection** in your WordPress admin menu.
4. Create an application in the ADStn Developer Platform at `https://www.adstn.ovh/developer/apps/create`:
   * Set your App Name and Website Domain.
   * Copy the **Authorized Redirect URI** displayed in the plugin settings and paste it into the **Redirect URIs** field on ADStn.
   * Enable the required scopes: `user.identity.read`, `user.content.write`.
5. Copy your **Client ID** and **Client Secret** into the plugin settings and click **Connect & Authorize with ADStn**.
6. That's it! Your published posts will now automatically sync to your ADStn account.

== Frequently Asked Questions ==

= Does this plugin require an ADStn account? =
Yes, you need an active account on [https://www.adstn.ovh](https://www.adstn.ovh) and must register an app in the Developer Platform.

= How does the token refresh work? =
The plugin handles OAuth 2.0 token expiration automatically. If your access token is near expiration, the plugin uses the refresh token behind the scenes without interrupting your publishing workflow.

= Can I exclude specific categories from being published? =
Yes. In **ADStn Poster** &rarr; **Publish Rules**, you can select specific categories to include or exclude.

= Can I share a post that was published earlier? =
Yes. Open the post in the WordPress editor, scroll down to the **ADStn Auto Poster** sidebar metabox, and click **Share to ADStn Now**.

= Is this plugin translation-ready? =
Yes. The plugin is 100% internationalized with a template `.pot` file included in `/languages` and is fully ready for translate.wordpress.org.

== Screenshots ==

1. Overview of connected account profile, follower counters, and publishing metrics.
2. OAuth 2.0 one-click connect and manual bearer token fallback options.
3. Post types, category filters, and default privacy settings.
4. Dynamic template editor with live simulation feed card.
5. Detailed log table with inspectable request/response payloads and retry action.
6. Sidebar controls in Gutenberg and Classic editor for per-post overrides and instant sharing.

== Changelog ==

= 1.0.1 =
* Fix: Streamlined OAuth 2.0 scopes to `user.identity.read` and `user.content.write`, eliminating Web Application Firewall (WAF) and ModSecurity false-positive blocks on system dotfile rules (`.profile`).
* Fix: Added defensive query parameter recovery in the OAuth callback handler (`handle_oauth_callback`) to support authorization servers that concatenate callback parameters with `?` instead of `&`.
* Fix: Improved backward compatibility with WordPress 5.8+ by replacing `str_contains()` with `strpos()` in callback processing.
* Security: Hardened input sanitization and unslashing for incoming query parameters with full WordPress Coding Standards (WPCS) compliance.
* Tweak: Updated admin connection and setup guide views to reflect required OAuth scopes.

= 1.0.0 =
* Initial release.
* Full integration with ADStn Developer REST API v1 and OAuth 2.0 Authorization Code Flow.
* Smart template engine with dynamic tags and hashtag generator.
* Real-time simulated feed preview.
* Post editor sidebar metabox with AJAX instant share.
* Comprehensive activity logging and error inspection with one-click retry.
* Full WordPress i18n/L10n multilingual support (English & Arabic).

== Upgrade Notice ==

= 1.0.1 =
Recommended update: Fixes OAuth 2.0 authorization callback processing, enhances WAF firewall compatibility, and improves WordPress 5.8+ compatibility.

= 1.0.0 =
Initial official release on WordPress.org.
