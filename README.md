# ADStn Auto Poster for WordPress

<div align="center">

![ADStn Logo](https://www.adstn.ovh/themes/default/staticthemecache/header-logo.png)

### Auto-publish and synchronize WordPress articles to the ADStn social platform seamlessly via Developer REST API & OAuth 2.0.

[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](LICENSE)
[![Platform](https://img.shields.io/badge/Platform-ADStn-615dfa.svg)](https://www.adstn.ovh)

</div>

---

## 📖 Overview

**ADStn Auto Poster** is an enterprise-grade WordPress plugin developed to connect WordPress sites directly with the **ADStn Developer Platform** (`https://www.adstn.ovh`). It enables automatic broadcasting of newly published articles and pages to ADStn feeds in real-time, boosting social distribution and referral traffic.

---

## ✨ Features

- **OAuth 2.0 Authorization Code Flow**: Secure, one-click connection with automated token refresh (`refresh_token`).
- **Direct Access Token Support**: Optional manual bearer token entry for developer workflows.
- **Smart Template Builder**: Customize shared message templates using dynamic placeholders:
  - `{title}` - Article title
  - `{url}` - Post direct permalink
  - `{excerpt}` - Post excerpt / summary
  - `{hashtags}` - Automatically formatted `#hashtags`
  - `{author}` - Post author name
  - `{categories}` - Comma-separated post categories
  - `{tags}` - Comma-separated post tags
  - `{site_name}` - WordPress site title
- **Live Feed Simulation**: Real-time simulated feed preview of how your post will render on ADStn.
- **Granular Publishing Rules**:
  - Filter by Post Types (Posts, Pages, Custom Post Types).
  - Include or exclude specific categories.
  - Set default privacy levels (`public`, `followers`, `private`).
- **Per-Post Editor Sidebar (Metabox)**:
  - Toggle auto-publishing on/off for specific posts.
  - Write custom per-post messages overriding default templates.
  - Instant "Share to ADStn Now" button via AJAX.
- **Full Activity & Sync Logs**: Searchable, filterable audit trail of all API requests, responses, and errors with one-click retry.
- **Multilingual (i18n & L10n)**: 100% WordPress translation standard compliant with complete English source and ready-to-use Arabic translations (`.pot`, `.po`, `.mo`).

---

## 🚀 Installation & Setup

1. Clone or download this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/ADStn-Network/adstn-auto-poster.git
   ```
2. Activate the plugin from the **WordPress Admin > Plugins** menu.
3. Navigate to **ADStn Poster > Connection**.
4. Create an application on the **ADStn Developer Portal**:
   - URL: [https://www.adstn.ovh/developer/apps/create](https://www.adstn.ovh/developer/apps/create)
   - Copy the **Authorized Redirect URI** from plugin settings into your ADStn App settings.
   - Request scopes: `user.identity.read`, `user.profile.read`, `user.content.write`.
5. Enter your `Client ID` and `Client Secret`, then click **Connect & Authorize with ADStn**.

---

## 🔒 Security & Standards

- All API communications use HTTPS.
- Full protection against CSRF with WordPress Nonces across all AJAX endpoints.
- User capability checks on all administrative actions (`manage_options`).
- Strict data sanitization and escaping for all user inputs and database queries.

---

## 📄 License

This project is licensed under the GNU General Public License v2.0 or later - see the [LICENSE](LICENSE) file for details.
