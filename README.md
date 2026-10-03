---
AIGC:
    Label: "1"
    ContentProducer: 001191440300708461136T1XGW3
    ProduceID: cf93d2ba4252e3fc820ac383cb09c649_7edf9a21be7a11f18019525400248c00
    ReservedCode1: sxFKVXbhBiaL87zatX6PQvD3uF/JD8oZVwb2prEVTfS1ujPQLYAwhPv7J7vlfWI1JpIDDTAhGYHXdP6mvv5/jjzVp8pyrXH9+p9pDoep4XW8YF24ckKK0Rt46ZQ8Lj57yCJ/jaTke4rCpZutUL2zNfzcXCxKPN4PrtnSkNNImdfYLGD3RxXn/wD3l20=
    ContentPropagator: 001191440300708461136T1XGW3
    PropagateID: cf93d2ba4252e3fc820ac383cb09c649_7edf9a21be7a11f18019525400248c00
    ReservedCode2: sxFKVXbhBiaL87zatX6PQvD3uF/JD8oZVwb2prEVTfS1ujPQLYAwhPv7J7vlfWI1JpIDDTAhGYHXdP6mvv5/jjzVp8pyrXH9+p9pDoep4XW8YF24ckKK0Rt46ZQ8Lj57yCJ/jaTke4rCpZutUL2zNfzcXCxKPN4PrtnSkNNImdfYLGD3RxXn/wD3l20=
---

# MornRain Simple Sitemap

> A standards compliant XML sitemap at /sitemap.xml - generated on the fly, never cached.

`MornRain Simple Sitemap` is a lightweight, self-contained WordPress plugin by **MornRain**.
It makes **no outbound network requests**, loads **no external CDN assets**,
creates **no custom database tables** and touches **no user data** beyond what
the site owner explicitly configures.

| Item | Value |
| --- | --- |
| License | GPL v2 or later |
| Minimum WordPress | 6.0 |
| Minimum PHP | 8.0 |
| Text domain | `mornrain-simple-sitemap` |
| Function prefix | `mornrain_simple_sitemap_*` |
| Class prefix | `Mornrain_Simple_Sitemap` |

---

## Table of contents

1. [Features](#features)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Hooks reference](#hooks-reference)
5. [File structure](#file-structure)
6. [Development and quality checks](#development-and-quality-checks)
7. [Frequently asked questions](#frequently-asked-questions)
8. [Changelog](#changelog)
9. [License](#license)

---

## Features

- Serves `/sitemap.xml` directly from WordPress through a rewrite rule.
- Automatically pages large sites: `/sitemap-2.xml`, `/sitemap-3.xml`, and a
  `/sitemap-index.xml` that lists them all.
- Falls back to `/?mornrain_simple_sitemap=1` on sites using plain permalinks.
- Appends a `Sitemap:` line to the virtual robots.txt.
- Lists only published posts and pages, ordered by last modification.
- Writes nothing to disk, creates no custom tables, makes no outbound requests.
- Every URL is escaped with `esc_xml()` (or an XML-safe fallback) before output.

---

## Installation

### Option A - Install from the WordPress admin (recommended)

1. Download or clone this repository.
2. Compress the `mornrain-simple-sitemap` folder itself into `mornrain-simple-sitemap.zip`. The archive must
   contain the plugin folder, not the repository root.
3. Go to **Plugins > Add New > Upload Plugin**, choose the ZIP, click
   **Install Now**, then **Activate**.

### Option B - Copy the folder over FTP / SSH

1. Copy the whole `mornrain-simple-sitemap` folder into `wp-content/plugins/`.
2. Go to **Plugins** and activate `MornRain Simple Sitemap`.

### Option C - Git clone (developer workflow)

```bash
cd wp-content/plugins
git clone https://github.com/mornrain-lin/mornrain-simple-sitemap.git
```

---

## Configuration

No settings screen is required; behaviour is controlled with filters so the
plugin stays configuration free.

| Filter | Default | Purpose |
| --- | --- | --- |
| `mornrain_simple_sitemap_settings` | post types `post`, `page`; 1000 URLs per page | Change the whole configuration array. |
| `mornrain_simple_sitemap_include_post` | `true` | Exclude an individual post. |
| `mornrain_simple_sitemap_include_home` | `true` | Drop the home page entry. |
| `mornrain_simple_sitemap_robots_line` | `Sitemap: <url>` | Change or blank out the robots.txt line. |

Example - list a custom post type and shrink the page size:

```php
add_filter(
    'mornrain_simple_sitemap_settings',
    function ( $settings ) {
        $settings['post_types'] = array( 'post', 'page', 'portfolio' );
        $settings['per_page']   = 500;
        return $settings;
    }
);
```

The raw settings array is stored in the single option
`mornrain_simple_sitemap_settings`:

| Key | Type | Default |
| --- | --- | --- |
| `post_types` | array | `array( 'post', 'page' )` |
| `per_page` | int | `1000` (clamped to 1 - 5000) |
| `robots` | bool | `1` |
| `home_entry` | bool | `1` |

---

## Hooks reference

| Hook | Type | Purpose |
| --- | --- | --- |
| `mornrain_simple_sitemap_settings` | filter | `array $settings` - the effective configuration. |
| `mornrain_simple_sitemap_allow_render` | filter | `bool $allowed, string $target` - block rendering. |
| `mornrain_simple_sitemap_include_post` | filter | `bool $include, WP_Post $post`. |
| `mornrain_simple_sitemap_include_home` | filter | `bool $include` - drop the home entry. |
| `mornrain_simple_sitemap_robots_line` | filter | `string $line, string $output`. |

Routes registered by the plugin:

| Route | Query variable | Output |
| --- | --- | --- |
| `/sitemap.xml` | `mornrain_simple_sitemap=1` | First page of URLs |
| `/sitemap-N.xml` | `mornrain_simple_sitemap=N` | Nth page of URLs |
| `/sitemap-index.xml` | `mornrain_simple_sitemap=index` | Sitemap index |

Public helper functions:

| Function | Returns |
| --- | --- |
| `mornrain_simple_sitemap_get_settings()` | `array<string, mixed>` |
| `mornrain_simple_sitemap_sanitize( $input )` | Sanitised settings array |
| `mornrain_simple_sitemap_escape( $value )` | XML-safe string |
| `mornrain_simple_sitemap_lastmod( $post )` | W3C datetime string |
| `Mornrain_Simple_Sitemap_Renderer::page_url( $page )` | URL of one sitemap page |
| `Mornrain_Simple_Sitemap_Renderer::index_url()` | URL of the sitemap index |

---

## File structure

```text
mornrain-simple-sitemap/
|-- .github/
|   `-- workflows/
|       `-- build.yml
|-- includes/
|   |-- class-mornrain-simple-sitemap-renderer.php
|   |-- class-mornrain-simple-sitemap.php
|   `-- functions-simple-sitemap.php
|-- tests/
|   |-- ScaffoldTest.php
|   `-- bootstrap.php
|-- mornrain-simple-sitemap.php
|-- composer.json
|-- LICENSE
|-- phpunit.xml.dist
|-- README.md
|-- readme.txt
`-- uninstall.php
```

---

## Development and quality checks

```bash
composer install
composer validate
composer lint   # runs php -l over every PHP file
composer test   # runs PHPUnit
```

Coding style follows the
[WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/):
tab indentation, Yoda conditions, prefixed global functions, nonce and
capability checks on every write path, and escaped output everywhere.

Continuous integration lives in `.github/workflows/build.yml`. It runs on every
push and pull request across PHP 8.1, 8.2 and 8.3: `composer install`,
`php -l` linting, PHPUnit, and finally packages a release ZIP as a build
artifact.

---

## Frequently asked questions

### The sitemap returns 404. What should I do?

Deactivate and reactivate the plugin, or open **Settings > Permalinks** and save
once. Both actions flush the rewrite rules. On sites using plain permalinks use
`/?mornrain_simple_sitemap=1`.

### How large can the sitemap get?

1000 URLs per page by default, up to 5000. Beyond one page,
`/sitemap-index.xml` lists every page so a crawler can follow them in order.

### Can I change which post types are listed?

Yes, through the `mornrain_simple_sitemap_settings` filter.

### Does it replace the core sitemap?

No. Core keeps `/wp-sitemap.xml`; this plugin adds `/sitemap.xml`. Both may be
referenced in a search console.

### Does it create files or database tables?

No. The XML is generated per request, and the plugin stores only one option row
for its settings, which `uninstall.php` removes on uninstall.

### Can I exclude an individual post?

Yes, with the `mornrain_simple_sitemap_include_post` filter, which receives the
`WP_Post` object.

---

## Changelog

### 1.0.0

- Initial public release.

---

## License

Released under the **GNU General Public License v2 or later**. See
[LICENSE](LICENSE) for the full text.
*（内容由AI生成，仅供参考）*
