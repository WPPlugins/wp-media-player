<?php
/*
*  WordPress Plugin: WP Media Player
* 
* File Written By:
* - Ruslan Yakushev
* - http://ruslany.net
* - Implementation idea was taken from Mike Jolley (http://blue-anvil.com)
* 
* File Information:
* - Uploads the video file to WordPress and inserts 
*   the media player tag into posts and pages
* 
*/

require_once( '../../../wp-load.php' );
require_once( 'classes/upload.class.php' );
require_once( 'classes/utils.class.php' );
wp_admin_css_color( 'classic', __('Blue'), admin_url( "css/colors-classic.css" ), array( '#073447', '#21759B', '#EAF3FA', '#BBD8E7' ) );
wp_admin_css_color( 'fresh', __('Gray'), admin_url( "css/colors-fresh.css" ), array( '#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF' ) );

wp_enqueue_script( 'common' );
wp_enqueue_script( 'jquery-color' );

@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );

if ( !current_user_can( 'upload_files' ) )
	wp_die( __('You do not have permission to upload files.') );

load_plugin_textdomain( 'wp-media-player', '/wp-content/plugins/wp-media-player/languages' );

$videourl = null;
$postid = $_GET['post_id'];
$upload_path = get_option( 'upload_path' );
$errors = null;

// Workaround for PHP 4
if ( strpos( $upload_path, ABSPATH ) === 0 ) {
	$upload_path = substr( $upload_path, strlen( ABSPATH ) );
}
$upload_path = trim( $upload_path, '/' );

$default_parameters = array(	'autoLoad' => get_option( 'wp_media_player_autoLoad' ),
								'autoPlay' => get_option( 'wp_media_player_autoPlay' ),
								'muted' => get_option( 'wp_media_player_muted' ),
								'thumb' => get_option( 'wp_media_player_thumb' ),
								'width' => get_option( 'wp_media_player_width' ),
								'height' => get_option( 'wp_media_player_height' ) );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action( 'admin_xml_ns' ); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>" />
	<title><?php bloginfo( 'name' ) ?> &rsaquo; <?php _e( 'Uploads' ); ?> &#8212; <?php _e( 'WordPress' ); ?></title>
	<?php
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
	?>
	<script type="text/javascript">
	//<![CDATA[
		function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
	//]]>
	</script>
	<?php
	do_action( 'admin_print_styles' );
	do_action( 'admin_print_scripts' );
	if ( isset( $content_func ) && is_string( $content_func ) )
		do_action( "admin_head_{$content_func}" );
	?>
</head>
<body id="media-upload">
	<div id="media-upload-header">
		<ul id='sidemenu'>
			<li id='tab-add'><a href='uploader.php?post_id=<?php echo $postid; ?>&tab=choose' <?php if ( $_GET['tab']=='choose' ) echo "class='current'"; ?>><?php _e('Choose Video', 'wp-media-player'); ?></a></li>
			<li id='tab-downloads'><a href='uploader.php?post_id=<?php echo $postid; ?>&tab=library' <?php if ( $_GET['tab']=='library' ) echo "class='current'"; ?>><?php _e('Video Library', 'wp-media-player'); ?></a></li>
		</ul>
	</div>
	<?php
	// Get the Tab
	$tab = $_GET['tab'];
	switch( $tab ) {
		case 'choose' :
			$videourl_post = '';
			if( $_POST ) {
				//get postdata
				$videourl_post = htmlspecialchars( trim( $_POST['videourl'] ) );
				if ( empty( $videourl_post ) ) {
					if ( $_FILES['upload'] && empty( $errors ) ) {
						// attempt to upload a file
						$my_upload = new wp_mp_file_upload();
						$my_upload -> upload_dir = ABSPATH.$upload_path.'/';
						$my_upload -> extensions = array( '.wmv' );
						$my_upload -> max_length_filename = 100;
						$my_upload -> rename_file = false;
						//upload it
						$my_upload -> the_temp_file = $_FILES['upload']['tmp_name'];
						$my_upload -> the_file = $_FILES['upload']['name'];
						$my_upload -> http_error = $_FILES['upload']['error'];
						$my_upload -> replace = ( isset( $_POST['replace'] ) ) ? $_POST['replace'] : "n";
						$my_upload -> do_filename_check = "n";
						if ( $my_upload -> upload() ) {
							$full_path = ABSPATH.$upload_path.'/'.$my_upload->file_copy;
							$videourl = get_bloginfo( 'wpurl' ).'/'.$upload_path.'/'.$my_upload -> file_copy;
							$attachment = array(
								'post_title' => wp_mp_utils::get_filename_without_ext( $my_upload->the_file ),
								'post_content' => '',
								'post_type' => 'attachment',
								'post_parent' => $postid,
								'post_mime_type' => 'video/asf',
								'guid' => $videourl
							);
							// Add the attachment
							$id = wp_insert_attachment( $attachment, $full_path, $postid );
							$videourl = wp_mp_utils::get_url_path( $videourl );
						} 
						else {
							$errors = '<div id="media-upload-error">'.$my_upload -> show_error_string().'</div>';
						}
					}
					elseif ( empty( $errors ) ) {
						if ( empty( $videourl_post ) ) $errors = __('<div id="media-upload-error">No file selected</div>',"wp-media-player");
					}
				}
				else {
					$videourl = $videourl_post;
				}
			}
			if ( $errors || !$_POST ) {
			?>
			<form enctype="multipart/form-data" method="post" action="uploader.php?tab=choose&post_id=<?php echo $postid ?>" id="media-upload-form type-form validate">
				<h3><?php _e('Choose video source',"wp-media-player"); ?></h3>
				<?php if ( !empty( $errors ) ) echo $errors; ?>
				<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_option( 'max_upload_size' ); ?>" />
				<input type="hidden" name="postDate" value="<?php echo date( __('Y-m-d H:i:s','wp-media-player') ) ;?>" />
				<?php 
					global $userdata;
					get_currentuserinfo();
					echo '<input type="hidden" name="user" value="'.$userdata -> user_login.'" />';
				?>	
				<table class="describe"><tbody>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="uploadfile"><?php _e('Upload Video File','wp-media-player'); ?></label></span>
						</th> 
						<td class="field"><input type="file" name="upload" style="width:320px;" id="file" /></td>
					</tr>
					<tr><td></td><td class="help" style="font-size:11px;"><?php _e('Max. filesize = ','wp-media-player'); ?><?php echo wp_mp_utils::max_upload_size_string(); ?>.</td></tr>
					<tr valign="top">
						<td colspan="2"><p style="text-align:center"><?php _e('&mdash; OR &mdash;', 'wp-media-player'); ?></p></td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="videourl"><?php _e('Video File URL', 'wp-media-player'); ?></label></span>
						</th>
						<td class="field"><input id="videourl" name="videourl" value="<?php echo $videourl_post; ?>" type="text" /></td>
					</tr>
					<tr><td></td><td class="help" style="font-size:11px;"><?php _e('e.g. http://www.someserver.com/videos/somevideo.wmv ','wp-media-player'); ?></td></tr>
				</tbody></table>
				<p class="submit"><input type="submit" class="button button-primary" name="insertonlybutton" value="<?php _e('Add Video File', 'wp-media-player'); ?>" /></p>
			</form>
			<?php } 
		break;
		case 'library' :
			if( isset( $_REQUEST['videourl'] ) ){
				$videourl = urldecode( $_REQUEST['videourl'] );
			}
			else {
			// Show table of video files
			?>
			<form enctype="multipart/form-data" method="post" action="uploader.php?tab=library&post_id=<?php echo $postid ?>" class="media-upload-form" id="gallery-form">
			<h3><?php _e('Video Library', 'wp-media-player'); ?></h3>
			<table class="widefat" style="width:100%;" cellpadding="0" cellspacing="0"> 
				<thead>
					<tr>
						<th scope="col" style="vertical-align:middle"><a href="?post_id=<?php echo $postid; ?>&tab=library&amp;sort=filename"><?php _e('File','wp-media-player'); ?></a></th>
						<th scope="col" style="vertical-align:middle;"><a href="?post_id=<?php echo $postid; ?>&tab=library&amp;sort=filesize"><?php _e('Size','wp-media-player'); ?></a></th>
						<th scope="col" style="text-align:center;vertical-align:middle"><?php _e('Action','wp-media-player'); ?></th>
					</tr>
				</thead>
			<?php
					// If current page number, use it 
					if ( !isset( $_REQUEST['p'] ) ) { 
						$page = 1; 
					} else { 
						$page = $_REQUEST['p']; 
					}

					// Iterate through all the directories in the Uploads and collect all the wmv files
					$files = array();
					$dirs = array( ABSPATH.$upload_path );
					while ( NULL !== ( $dir = array_pop( $dirs ) ) ) {
						if ( $dh = opendir( $dir ) ) {
							$relative_path = substr( $dir, strlen( ABSPATH ) - 1 );
							while ( false !== ( $file = readdir( $dh ) ) ) {
								if ( $file == '.' || $file == '..' )
									continue;
								$path = $dir . '/' . $file;
								if( is_dir( $path ) )
									$dirs[] = $path;
								else{
									if ( strpos( $file, '.' ) === false )
										continue;
									$fileChunks = explode( '.', $file ); 
									if ( $fileChunks[1] == 'wmv' ) { //interested in second chunk only
										$files[] = array(	'filename' => $file, 
															'filesize' => filesize( $path ),
															'fileurl' => wp_mp_utils::get_url_path( get_bloginfo( 'wpurl' ).$relative_path.'/'.$file ) );
									}
								}
							}
							closedir( $dh );
						}
					}

					$total_files = sizeof( $files );
					$total_pages = ceil( $total_files / 10 );
					$sort = 'filename';

					if ( $total_files > 0 ) {
						// Sort column
						if ( isset( $_REQUEST['sort'] ) && ( $_REQUEST['sort'] == 'filesize' || $_REQUEST['sort'] == 'filename' ) ) 
							$sort = $_REQUEST['sort'];

						if ( $sort == 'filesize' )
							usort( $files, array( "wp_mp_utils", "compare_filesize" ) );
						else
							usort( $files, array( "wp_mp_utils", "compare_filename" ) );

						$from = ( $page * 10 ) - 10;
						$to = $page * 10 - 1;
						$to = ( $to < $total_files - 1 ) ? $to : $total_files - 1;

						echo '<tbody id="the-list">';
						for ( $i = $from; $i <= $to; $i++ ) {
							echo '<tr class="alternate">';
							echo '<td style="vertical-align:middle">'.$files[$i]['filename'].'</td>';
							echo '<td style="text-align:left;vertical-align:middle">'.wp_mp_utils::convert_bytes_to_string( $files[$i]['filesize'] ).'</td>';
							echo '<td style="text-align:center;vertical-align:middle"><a href="uploader.php?post_id='.$postid.'&tab=library&videourl='.urlencode( $files[$i]['fileurl'] ).'" style="display:block" class="button insertvideo" id="video-'.$i.'">'.__('Insert', 'wp-media-player').'</a></td>';
						}
						echo '</tbody>';
					}
			?>
			</table>
			<div class="tablenav">
				<div style="float:left" class="tablenav-pages">
					<?php
						if ( $total_pages>1 )  {
							// Build Page Number Hyperlinks 
							if ( $page > 1 ) { 
								$prev = $page - 1; 
								echo "<a href=\"?post_id=$postid&tab=library&amp;p=$prev&amp;sort=$sort\">&laquo; ".__('Previous','wp-media-player').'</a>' ; 
							} else 
								echo '<span class=\'current page-numbers\'>&laquo; '.__('Previous','wp-media-player').'</span>';

							for ( $i = 1; $i <= $total_pages; $i++ ) { 
								if ( $page == $i ) 
										echo " <span class='page-numbers current'>$i</span> "; 
									else  
										echo " <a href=\"?tab=library&amp;p=$i&amp;sort=$sort\">$i</a> "; 
							} 
							// Build Next Link 
							if ( $page < $total_pages ) { 
								$next = $page + 1; 
								echo "<a href=\"?post_id=$postid&tab=library&amp;p=$next&amp;sort=$sort\">".__('Next','wp-media-player').' &raquo;</a>'; 
							} else 
								echo '<span class=\'current page-numbers\'>'.__('Next','wp-media-player').' &raquo;</span>';
						}
					?>
				</div>
			</div>
			<br style="clear: both; margin-bottom:1px; height:2px; line-height:2px;" />
		</form>
		<?php
		}
	}

	if ( isset( $videourl ) && $videourl != null && !$errors ) {
		?>
		<div style="margin:1em;">
			<h3><?php _e('Insert media player into post', 'wp-media-player'); ?></h3>
				<p><strong><?php _e('Video Source URL: ', 'wp-media-player'); ?></strong><span id="source_url"><?php echo $videourl; ?></span></p>
				<table class="describe"><tbody>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><?php _e('Load and Play <br />options', 'wp-media-player'); ?></span>
						</th>
						<td class="field">
							<fieldset>
								<label for="default_auto_load">
									<input name="wp_media_player_autoLoad" type="checkbox" id="player_auto_load" name="player_auto_load" value="1" <?php echo ( $default_parameters['autoLoad'] == 1 ? 'checked' : '' ); ?> />
									<?php _e('Cue Video on Page Load', 'wp-media-player'); ?>
								</label>
								<br />
								<label for="default_auto_play">
									<input name="wp_media_player_autoPlay" type="checkbox" id="player_auto_play" name="player_auto_play" value="1" <?php echo ( $default_parameters['autoPlay'] == 1 ? 'checked' : '' ); ?> />
									<?php _e('Automatically Start Video when Cued', 'wp-media-player')?>
								</label>
								<br />
								<label for="default_muted">
									<input name="wp_media_player_muted" type="checkbox" id="player_muted" name="player_muted" value="1" <?php echo ( $default_parameters['muted'] == 1 ? 'checked' : '' ); ?> />
									<?php _e('Mute the Audio', 'wp-media-player')?>
								</label>
								<br />
							</fieldset>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="playerwidth"><?php _e('Player Width', 'wp-media-player'); ?></label></span>
						</th>
						<td class="field"><input id="playerwidth" name="playerwidth" style="width:60px;" value="<?php echo $default_parameters['width']; ?>" type="text" /></td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="playerheight"><?php _e('Player Height', 'wp-media-player'); ?></label></span>
						</th>
						<td class="field"><input id="playerheight" name="playerheight" style="width:60px;" value="<?php echo $default_parameters['height']; ?>" type="text" /></td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="file"><?php _e('Thumbnail Path (optional)','wp-media-player'); ?></label></span>
						</th> 
						<td class="field"><input id="thumbpath" name="thumbpath" value="<?php echo $default_parameters['thumb']; ?>" type="text"></td>
					</tr>
				</tbody></table>
			<p class="submit">
				<input type="submit" id="insertdownload" class="button button-primary" name="insertintopost" value="<?php _e('Insert into Post', 'wp-media-player'); ?>" />
			</p>
			<script type="text/javascript">
					/* <![CDATA[ */
					jQuery('#insertdownload').click(function(){
					var win = window.dialogArguments || opener || parent || top;
					var autoLoad = jQuery('input#player_auto_load').attr('checked') ? 1 : 0;
					var autoPlay = jQuery('input#player_auto_play').attr('checked') ? 1 : 0;
					var muted = jQuery('input#player_muted').attr('checked') ? 1 : 0;
					var result = '[mediaplayer src=\'' + jQuery('#source_url').text() + '\''
					if (autoLoad != <?php echo ($default_parameters['autoLoad'] == 1) ? 1 : 0; ?>) result += ' autoLoad=' + autoLoad
					if (autoPlay != <?php echo ($default_parameters['autoPlay'] == 1) ? 1 : 0; ?>) result += ' autoPlay=' + autoPlay
					if (muted != <?php echo ($default_parameters['muted'] == 1) ? 1 : 0; ?>) result += ' muted=' + muted
					if (jQuery('input#playerwidth').val() != <?php echo $default_parameters['width']; ?>) result += ' width=' + jQuery('input#playerwidth').val()
					if (jQuery('input#playerheight').val() != <?php echo $default_parameters['height']; ?>) result += ' height=' + jQuery('input#playerheight').val()
					if (jQuery('input#thumbpath').val() != '') result += ' thumb=\'' + jQuery('input#thumbpath').val() + '\''
					result += ' ]'
					win.send_to_editor(result);
					});
					/* ]]> */
			</script>
		</div>
	<?php 
	}
	?>
</body>
</html>