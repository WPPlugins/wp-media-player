<?php
/*
* WordPress Plugin: WP Media Player
* 
* File Written By:
* - Ruslan Yakushev
* - http://ruslany.net
* 
* File Information:
* - Displays the usage instructions
* 
*/

$base_name = plugin_basename( 'wp-media-player/player-stats.php' );
$base_page = 'admin.php?page=' . $base_name;

if ( function_exists( 'current_user_can' ) && !current_user_can( 'manage_options' ) )
	die( __('Cheatin&#8217; uh?', 'wp-media-player') );
if ( !user_can_access_admin_page() )
	wp_die( __('You do not have sufficient permissions to access this page.', 'wp-media-player') );
?>
<div class="wrap">
	<div id="icon-wp-media-player" class="icon32"><br /></div>
	<h2><?php _e('About WP Media Player Plugin', 'wp-media-player'); ?></h2>
	<p><?php _e('To add a player to a blog post or a page, follow these steps:', 'wp-media-player'); ?></p>
	<p><?php _e('<strong>Step 1</strong>: Encode the video to a Windows Media Video (WMV) format by using <a href="http://www.microsoft.com/Expression/try-it/default.aspx">Microsoft Expression Encoder</a> or <a href="http://www.microsoft.com/windows/windowsmedia/forpros/encoder/default.mspx">Windows Media Encoder</a>. If you use Windows Media Encoder then follow <a href="http://ruslany.net/wp-media-player/video-encoding/" target="_blank">these instructions</a> to properly encode the video content for Silverlight.', 'wp-media-player'); ?></p>
	<p><?php _e('<strong>Step 2</strong>: Upload the video to WordPress either via FTP or by clicking on “Silverlight Video” in the post editing page. When uploading the video, take a note of the absolute URL path where the video file has been placed to.', 'wp-media-player'); ?></p>
	<p><?php _e('<strong>Step 3</strong>: Place the following tag inside of the blog post content where you want the video player to appear:', 'wp-media-player'); ?></p>
	<pre>
[mediaplayer src=”/absolute/url/path/to/video/file.wmv”]
	</pre>
	<h3><?php _e('Usage examples', 'wp-media-player'); ?></h3>
	<p><?php _e('To use a custom placeholder image instead of the default one use the "thumb" parameter, e.g.:', 'wp-media-player'); ?></p>
	<pre>
[mediaplayer src=”/absolute/url/path/to/video/file.wmv” thumb=”/absolute/url/path/to/thumb/file.jpg”]
	</pre>
	<p><?php _e('To specify player’s width and height, use the "width" and "height" parameters, e.g.:', 'wp-media-player'); ?></p>
	<pre>
[mediaplayer src=”/absolute/url/path/to/video/file.wmv” width=320 height=240]
	</pre>
	<p><?php _e('To change auto load and auto play settings use "autoLoad" and "autoPlay" parameters, e.g.:', 'wp-media-player'); ?></p>
	<pre>
[mediaplayer src=”/absolute/url/path/to/video/file.wmv” autoLoad=1 autoPlay=0]
	</pre>
	<p><?php _e('To mute the audio, use the "muted" parameter, e.g.:', 'wp-media-player'); ?></p>
	<pre>
[mediaplayer src=”/absolute/url/path/to/video/file.wmv” muted=1]
	</pre>
</div>