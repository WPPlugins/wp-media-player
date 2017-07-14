<?php
/*
* WordPress Plugin: WP Media Player
* 
* File Written By:
* - Ruslan Yakushev
* - http://ruslany.net
* 
* File Information:
* - Displays and updates the media player settings
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
	<h2><?php _e('WP Media Player Settings', 'wp-media-player'); ?></h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field( 'update-options' ); ?>
		<h3><?php _e('Default Settings', 'wp-media-player'); ?></h3>
		<p><?php _e('These setting for media player will be used by default unless overwritten by corresponding settings for each individual player instance.', 'wp-media-player'); ?></p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Load and Play Options', 'wp-media-player'); ?></th>
				<td><fieldset><legend class="hidden">Load and Play Options</legend>
					<label for="default_auto_load">
						<input name="wp_media_player_autoLoad" type="checkbox" id="default_auto_load" value="1" <?php checked( '1', get_option( 'wp_media_player_autoLoad' ) ); ?> />
						<?php _e('Cue Video on Page Load', 'wp-media-player'); ?>
					</label>
					<br />
					<label for="default_auto_play">
						<input name="wp_media_player_autoPlay" type="checkbox" id="default_auto_play" value="1" <?php checked( '1', get_option( 'wp_media_player_autoPlay' ) ); ?> />
						<?php _e('Automatically Start Video when Cued', 'wp-media-player'); ?>
					</label>
					<br />
					<label for="default_muted">
						<input name="wp_media_player_muted" type="checkbox" id="default_muted" value="1" <?php checked( '1', get_option( 'wp_media_player_muted' ) ); ?> />
						<?php _e('Mute the Audio', 'wp-media-player'); ?>
					</label>
					<br />
					<small><em><?php _e('(These settings may be overridden for individual players)', 'wp-media-player'); ?></em></small>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Player Size', 'wp-media-player'); ?></th>
					<td><fieldset><legend class="hidden">Player Size</legend>
						<label for="wp_media_player_width"><?php _e('Width', 'wp-media-player'); ?></label>
						<input name="wp_media_player_width" type="text" id="wp_media_player_width" value="<?php echo get_option( 'wp_media_player_width' ); ?>" size="6" />
						<label for="wp_media_player_height"><?php _e('Height', 'wp-media-player'); ?></label>
						<input name="wp_media_player_height" type="text" id="wp_media_player_height" value="<?php echo get_option( 'wp_media_player_height' ); ?>" size="6" /><br />
					</fieldset></td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wp_media_player_thumb"><?php _e('Thumbnail path (optional)', 'wp-media-player'); ?></label>
				</th>
				<td>
					<input name="wp_media_player_thumb" type="text" id="wp_media_player_thumb" class="code" value="<?php echo get_option( 'wp_media_player_thumb' ); ?>" size="60" />
					<br />
					<small><em><?php _e('(If left empty then default thumbnail will be used)', 'wp-media-player'); ?></em></small>
				</td>
			</tr>
		</table>
		<h3><?php _e('Player styles', 'wp-media-player'); ?></h3>
		<p><?php _e('Select how the media player will look like.', 'wp-media-player'); ?></p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="wp_media_player_style"><?php _e('Style', 'wp-media-player'); ?></label></th>
				<td>
					<select name="wp_media_player_style" id="wp_media_player_style"><?php echo $mp_plugin -> dropdown_styles( get_option( 'wp_media_player_style' ) ); ?></select>
				</td>
			</tr>
		</table>
		<h3><?php _e('Watermark Image', 'wp-media-player'); ?></h3>
		<p><?php _e('The watermak can be used to display the logo image on every video that is played by the player.', 'wp-media-player'); ?></p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wp_media_player_wmsrc"><?php _e('Image Path <br/> (JPG, PNG only)', 'wp-media-player'); ?></label>
				</th>
				<td>
					<input name="wp_media_player_wmsrc" type="text" id="wp_media_player_wmsrc" class="code" value="<?php echo get_option( 'wp_media_player_wmsrc' ); ?>" size="60" />
					<br />
					<small><em><?php _e('(For example: http://myblog.com/wp-content/uploads/logo.png)', 'wp-media-player'); ?></em></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_media_player_wmposition"><?php _e('Position', 'wp-media-player'); ?></label></th>
				<td>
					<select name="wp_media_player_wmposition" id="wp_media_player_wmposition"><?php echo $mp_plugin -> dropdown_positions( get_option( 'wp_media_player_wmposition' ) ); ?></select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_media_player_wmopacity"><?php _e('Opacity', 'wp-media-player'); ?></label></th>
				<td>
					<select name="wp_media_player_wmopacity" id="wp_media_player_wmopacity"><?php echo $mp_plugin -> dropdown_opacities( get_option( 'wp_media_player_wmopacity' ) ); ?></select>
				</td>
			</tr>
		</table>
		<h3><?php _e('Displaying Player on Home Page', 'wp-media-player'); ?></h3>
		<p><?php _e('By default players in the blog posts are not displayed on a home page of the blog. Instead links to the players are displayed. This way blog visitors will not be reqired to download media player scripts if they do not want to see the video. Also, this prevents the possibility of multiple players in different blog posts from starting playing at the same time. You can change this behavior by using the option below.', 'wp-media-player'); ?></p>
		<table class="form-table">
			<tr>
				<th scope="row" class="th-full">
					<label for="show_on_home_page">
						<input name="wp_media_player_show_on_home_page" type="checkbox" id="show_on_home_page" value="1" <?php checked( '1', get_option( 'wp_media_player_show_on_home_page' ) ); ?> />
						<?php _e('Show Player on Home Page', 'wp-media-player'); ?>
					</label>
				</th>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="wp_media_player_show_on_home_page,wp_media_player_autoLoad,wp_media_player_autoPlay,wp_media_player_muted,wp_media_player_width,wp_media_player_height,wp_media_player_thumb,wp_media_player_style,wp_media_player_wmsrc,wp_media_player_wmposition,wp_media_player_wmopacity" />
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>