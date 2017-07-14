=== WP Silverlight Media Player ===
Contributors: ruslany
Tags: silverlight, mediaplayer
Requires at least: 2.5
Tested up to: 2.8.4
Stable tag: 0.8
A Silverlight-based Media Player for WordPress.

== Description ==
This plugin allows addition of Silverlight-based media players to WordPress blog posts and pages. The players can be used to play Windows Media Video (WMV) encoded video content.

The plugin has the following features:

* 6 player styles
* Watermark image
* Tracking and reporting on how many times the videos have been watched
* Default player configuration settings, such as size, thumbnail, auto load and auto play.
* Per-instance player configuration settings that can be used to customize each individual player within or across blog posts.
* Unlimited number of players within the same blog post or page.
* UI for uploading of video files and for inserting media players into blog posts and pages

Follow the instructions at [WP Media Player - Video Encoding](http://ruslany.net/wp-media-player/video-encoding/) to encode the video content for the player.

The version 0.8 contains several bug fixes and a new feature for adding watermark image in the player. Refer to the [changelog](http://ruslany.net/wp-media-player/changelog/) for more details.

For more information, demos and usage instructions refer to [the plugin home page](http://ruslany.net/wp-media-player/).

== Installation ==
1. Upload wp-media-player directory (including all files and directories within) to the /wp-content/plugins/ directory. Make sure that the path to the main plugin php file is /wp-content/plugins/wp-media-player/wp-media-player.php. If path does not look like this then plugin may not show up in the WordPress plugins page.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the plugin settings at Settings, WP Media Player and read the usage instructions

== Frequently Asked Questions ==
= How do I encode video to use with this plugin ? =
Because the player is silverlight-based, it requires that the video file is encoded by using silverlight-friendly parameters. It is recommended to encode the WMV by following instructions [here](http://ruslany.net/wp-media-player/video-encoding/)

= Can I use this plugin with Windows Media Services 9? =
Yes. Just specify the url to the video file: [mediaplayer src=mms://somesite.com/somefile.wmv]

== Screenshots ==
1. Default player style
2. Plugin Settings page
3. Add Silverlight Video button
4. Insert media player into a blog post
5. Player statistics

== Changelog ==

= 0.8 =
* Fixed a bug in the Uploader dialog that caused it to fail on Linux file systems
* Fixed the bug in player which caused the video to be shifted to the left
* Added new feature to support watermark image in the player

= 0.7.1 =
* Fixed a nasty bug in Media Player JavaScript that caused the player to fail in Internet Explorer if HTML contained a tag with id="title"

= 0.7 =
* Cleaned up and compressed the player's JavaScript files
* Fixed the bug that caused video playback to fail when NextGen Gallery plugin was used.
* Added functionality to track the video playback and to report the player statistics
* Added localization files
* Added Russian (ru_RU) translation

= 0.6.1 =
* Fixed notice about undefined offset when parsing parameters
* Fixed notice about undefined index when sort parameter is not available on query string
* Fixed invalid HTML markup in the uploader.php file
* Fixed notice about undefined variable $filename
* Fixed notice about Undefined variable $errors

= 0.6 =
* Added configuration option to mute the audio upon player start up
* Localizability fixes

= 0.5 =
* PHP 4 compatibility fixes

= 0.4 =
* UI for uploading video files
* UI for inserting media player tag into posts and pages
* Changed all JS script names to lowercase

= 0.3 =
* Plugin upgrade support
* Fixed handling of double and single quotes in tag parameters

= 0.2 =
* Fix for a bug in calculating player’s dimensions

= 0.1 =
* First version of the plugin