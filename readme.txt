=== Plugin Name ===
Contributors: timwhitlock
Donate link: http://timwhitlock.info/donate-to-a-project/
Tags: twitter, tweets, oauth, api, rest, api, widget, sidebar
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Latest Tweets widget, compatible with the new Twitter API 1.1

== Description ==

Connect your Twitter account to this plugin and the widget will display your latest tweets on your site.

This plugin is compatible with the new Twitter API 1.1 and provides full authentication via the Wordpress admin area.


Built by [timwhitlock](https://twitter.com/timwhitlock)


== Installation ==

1. Unzip all files to the `/wp-content/plugins/` directory
2. Log into Wordpress admin and activate the 'Latest Tweets' plugin through the 'Plugins' menu

Once the plugin is installed and enabled you can bind it to a Twitter account as follows:

3. Register a Twitter application at https://dev.twitter.com/apps
4. Note the Consumer key and Consumer secret under OAuth settings
5. Log into Wordpress admin and go to Settings > Twitter API
6. Enter the consumer key and secret and click 'Save settings'
7. Click the 'Connect to Twitter' button and follow the prompts.

Once your site is authenticated you can configure the widget as follows:

8. Log into Wordpress admin and go to Appearance > Widgets
9. Drag 'Latest Tweets' from 'Available widgets' to where you want it. e.g. Main Sidebar
10. Optionally configure the widget title and number of tweets to display.


== Changelog ==

= 1.0.1 =
* First public release

= 1.0.2 =
* Fixed hook for PHP < 5.3

== Credits ==

Screenshot taken with permission from http://stayingalivefoundation.org/blog

== Notes ==

Be aware of [Twitter's display requirements](https://dev.twitter.com/terms/display-requirements) when rendering tweets on your website.