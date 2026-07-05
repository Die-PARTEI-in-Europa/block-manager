=== parteieuropa.eu - Block Manager ===
Contributors: parteieuropa
Tags: blocks, gutenberg, block editor, editor, admin
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable or disable individual Gutenberg blocks in the editor from one simple settings screen. Works with any registered block.

== Description ==

Block Manager gives site administrators a single screen to control which blocks appear in the block editor. Blocks are grouped by their own category, and disabling a block hides it everywhere the editor is used — without touching any content that already exists.

It is deliberately small: no dependencies, no tracking, no database bloat. A single option stores the list of disabled blocks.

Features:

* One checklist of every registered block, grouped by category.
* "Enable all" / "Disable all" helpers.
* Works with core, third-party and custom blocks alike.
* Uses the native `allowed_block_types_all` filter — no editor hacks.
* Fully translatable (text domain `wp-block-manager`).
* Cleans up after itself on uninstall.

== Installation ==

1. Upload the `wp-block-manager` folder to `/wp-content/plugins/`, or install the plugin through the Plugins screen in WordPress.
2. Activate the plugin through the "Plugins" screen.
3. Go to **Settings → Block Manager** and choose which blocks to enable.

== Frequently Asked Questions ==

= Does disabling a block delete existing content? =

No. Disabling a block only removes it from the inserter and the list of allowed block types. Content that already uses the block is left untouched.

= Which blocks can it manage? =

Any block registered on the server, including core blocks, blocks from other plugins, and your own custom blocks.

= Does it add its own block category? =

No. The plugin only reads the categories that blocks already declare; it never registers a category of its own.

== Screenshots ==

1. The Block Manager settings screen with blocks grouped by category.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
