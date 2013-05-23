=== Plugin Name ===
Contributors: username1, username2 (this should be a list of wordpress.org userid's)
Donate link: http://mneilsworld.com/donate
Tags: css, javascript, js, cascade, style, sheet, combine, compress, uglify, minify, closure, admin, aggregate, cache 
Requires at least: 3.3.1
Tested up to: 3.3.1
Stable tag: 1.0.0

Easily manage the merging and compression of js and css files from plugins and themes

== Description ==

The plugin scans your WP isntallation and finds all .js and .css files in the current list of
activated plugins and theme. The administrative panel allows you to then determine which pages those
files should be available on and if they should be combined and compressed. This will only combine/compress
files that are properly included in WP using wp_enqueue_script or wp_enqueue_style. We will also adhere to the files
in_footer include boolean for js files. 

CSS will be combined and (optionally) compressed using a basic regex and cached.

JS will be combined and (optionally) compressed using either uglify or closure

== Installation ==


1. Upload `mncombine` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the admin panel and choose your options

== Frequently Asked Questions ==

none yet

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==

= 1.0.0 =
* The very first version in all it's glory
