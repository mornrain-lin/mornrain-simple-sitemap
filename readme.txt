=== MornRain Simple Sitemap ===
Contributors: mornrain
Donate link: https://github.com/mornrain-lin
Tags: sitemap, seo, xml, robots, google
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Serves a plain XML sitemap at /sitemap.xml, with automatic paging for large sites.

== Description ==

MornRain Simple Sitemap publishes a standards compliant XML sitemap without
writing a single file to disk. The document is generated on the fly from your
published posts and pages and served directly by WordPress.

Available entry points:

* `/sitemap.xml` - the first page of URLs.
* `/sitemap-2.xml`, `/sitemap-3.xml`, ... - further pages on large sites.
* `/sitemap-index.xml` - a sitemap index listing every page.
* `/?mornrain_simple_sitemap=1` - the same output on sites that use plain
  permalinks.

The plugin also appends a `Sitemap:` line to your virtual robots.txt so that
crawlers discover the sitemap automatically.

Nothing is cached to disk, no custom table is created and no outbound request
is made. Only published content is listed, and every URL is escaped before it
reaches the XML.

This plugin stores nothing you did not explicitly configure, sends
no data to any remote service, and adds no custom database tables.

== Installation ==

1. Upload the `mornrain-simple-sitemap` folder to the `/wp-content/plugins/` directory, or
   install the ZIP through *Plugins > Add New > Upload Plugin*.
2. Activate the plugin through the *Plugins* screen in WordPress.
3. Visit `/sitemap.xml` (or `/?mornrain_simple_sitemap=1` on sites without
   pretty permalinks) to confirm the sitemap renders.

== Frequently Asked Questions ==

= The sitemap returns a 404. What should I check? =

The pretty routes need the rewrite rules to be registered. Deactivate and
reactivate the plugin, or open *Settings > Permalinks* and save once, which
flushes the rules. On sites using plain permalinks use
`/?mornrain_simple_sitemap=1` instead.

= Can I change which post types are listed? =

Yes:

    add_filter( 'mornrain_simple_sitemap_settings', function ( $settings ) {
        $settings['post_types'] = array( 'post', 'page', 'product' );
        return $settings;
    } );

= Does this conflict with the WordPress core sitemap? =

No. Core serves `/wp-sitemap.xml`, this plugin serves `/sitemap.xml`. Both can
coexist, and you may reference either one in Search Console.

= How does it behave on a site with 50,000 posts? =

Results are paged, 1000 URLs per page by default. The sitemap index at
`/sitemap-index.xml` lists every page, so no single document grows unbounded.

= Does it write any files or create tables? =

No. The XML is built per request and never stored, and the plugin creates a
single option row for its settings.

= Can I exclude one post? =

Yes:

    add_filter( 'mornrain_simple_sitemap_include_post', function ( $include, $post ) {
        return 42 === $post->ID ? false : $include;
    }, 10, 2 );

== Screenshots ==

1. The plugin working on the front end.
2. The relevant WordPress admin screen.

== Changelog ==

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0.0 =
Initial public release.
（内容由AI生成，仅供参考）
