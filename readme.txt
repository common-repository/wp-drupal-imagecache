=== Thumbnails like in Drupal ===
Contributors: stasionok
Donate link: https://web-marshal.ru/wordpress-thumbnails-like-in-drupal/
Tags: image, thumbnail, drupal, regenerate, image effect, image preset, imagecache
Requires at least: 5.3
Tested up to: 6.2
Stable tag: 1.1.2
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Wordpress implementation/fork of Drupal imagecache module.

== Description ==

Take some thumbnail type (called here as preset) and cast available image effects on it to get expected size or view.

With this plugin you can:

* change exists preset or create new one.
* on upload image create only enabled preset thumbnails
* disable unused presets
* remove disabled or extra/trash thumbnails images
* regenerate any preset thumbnail images (after change preset as ex.)

For each preset you can cast any of available effects to change thumbnail sizes or just desaturate it or something else..

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/wp-drupal-imagecache` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to plugin settings page and tune your presets and thumbnails

After enabling plugin, it create image in image library. It image critical needed for plugin for preview changes. Please do not remove it.

== Frequently Asked Questions ==

= Why I got problem after using plugin =

Please note that main system preset *large* used as default when include in Gutenberg editor.
So if you disable *large* and delete all unused thumbnail your page can got broken image!
It that case just enable *large* and regenerate it thumbnails.

= If I remove plugin image for preview by mistake =

Just deactivate and activate plugin again. It create that image again.

= How to add custom effects to plugin effects list =

1. Use filter *wpdi_get_available_effects* to add or remove effect from effects list
2. Extend *WPDI_Effects_Imagick* and/or *WPDI_Effects_GD* plugin classes if you want to add custom effect
3. Use *wp_image_editors* to replace plugin handlers with created in #2
4. Use *wpdi_make_preset_effect* for set handler function name (optional), handle function with effect name by default
5. Use *wpdi_build_preset_effect* for describe effect in presets linst (optional)

ps: You should use priority greater then in plugin (WPDI_Common::PLUGIN_HOOK_PRIORITY);

== Screenshots ==

1. Main plugin page with all presets list, it effects and actions
2. Preset edit page. You can see thumbnail preview, all effects casted to preset and preset actions
3. Scale and crop effect parameters window

== Changelog ==

= 1.0 =
* Basic functionality released.

= 1.0.1 =
* Fix check for gd/imagick php extension. Now you can use plugin with only imagick or gd extension enabled

= 1.1 =
* Add round corners image effect
* Add flip & flop (reflect) image effect

= 1.1.1 =
* Fix errors
* Check 6.2 support

= 1.1.2 =
* Fix errors
