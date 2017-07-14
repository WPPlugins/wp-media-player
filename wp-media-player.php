<?php
/*
Plugin Name: WP Media Player
Plugin URI: http://ruslany.net/wp-media-player/
Description: A Silverlight-based media player.
Version: 0.8
Author: Ruslan Yakushev
Author URI: http://ruslany.net
*/

/*
* This tool is provided by Ruslan Yakushev (http://ruslany.net) under the Microsoft Public License 
* (http://www.microsoft.com/opensource/licenses.mspx).
* 
* This license governs use of the accompanying software. If you use the software, you accept this license. 
* If you do not accept the license, do not use the software.
* 
* Definitions
* The terms "reproduce," "reproduction," "derivative works," and "distribution" have the same meaning here 
* as under U.S. copyright law. A "contribution" is the original software, or any additions or changes to the
*  software. A "contributor" is any person that distributes its contribution under this license. 
*  "Licensed patents" are a contributor's patent claims that read directly on its contribution.
*  
*  Grant of Rights
*  (A) Copyright Grant- Subject to the terms of this license, including the license conditions and limitations 
*  in section 3, each contributor grants you a non-exclusive, worldwide, royalty-free copyright license to 
*  reproduce its contribution, prepare derivative works of its contribution, and distribute its contribution 
*  or any derivative works that you create.
*  (B) Patent Grant- Subject to the terms of this license, including the license conditions and limitations 
*  in section 3, each contributor grants you a non-exclusive, worldwide, royalty-free license under its 
*  licensed patents to make, have made, use, sell, offer for sale, import, and/or otherwise dispose of its 
*  contribution in the software or derivative works of the contribution in the software.
*  
*  Conditions and Limitations
*  (A) No Trademark License- This license does not grant you rights to use any contributors' name, logo, 
*  or trademarks. 
*  (B) If you bring a patent claim against any contributor over patents that you claim are infringed by 
*  the software, your patent license from such contributor to the software ends automatically. 
*  (C) If you distribute any portion of the software, you must retain all copyright, patent, trademark, 
*  and attribution notices that are present in the software. 
*  (D) If you distribute any portion of the software in source code form, you may do so only under this 
*  license by including a complete copy of this license with your distribution. If you distribute any 
*  portion of the software in compiled or object code form, you may only do so under a license that 
*  complies with this license. 
*  (E) The software is licensed "as-is." You bear the risk of using it. The contributors give no express 
*  warranties, guarantees, or conditions. You may have additional consumer rights under your local laws 
*  which this license cannot change. To the extent permitted under your local laws, the contributors 
*  exclude the implied warranties of merchantability, fitness for a particular purpose and non-infringement.
*/


// Load WP-Config File If This File Is Called Directly
if ( !function_exists( 'add_action' ) ){
	$wp_root = '../../..';
	if ( file_exists( $wp_root.'/wp-load.php' ) ) {
		require_once( $wp_root.'/wp-load.php' );
	} else {
		require_once( $wp_root.'/wp-config.php' );
	}
}

if ( !class_exists( 'wp_media_player' ) ) {
	define( 'MAX_PLAYER_WIDTH', 800 );
	define( 'MAX_PLAYER_HEIGHT', 600 );
	define( 'DEFAULT_PLAYER_WIDTH', 448 );
	define( 'DEFAULT_PLAYER_HEIGHT', 336 );
	define( 'MEDIA_PLAYER_META_TAG', 'wp_media_player' );
	define( 'MEDIA_FILENAME_LENGTH', 256 );
	
	class wp_media_player {
		var $pluginurl;
		var $default_parameters;
		var $plugin_parameters;
		var $player_styles;
		var $watermark_positions;
		var $watermark_opacities;
		var $post_needs_meta = false;
		var $content_save_pre_count = 0;
		
		/**
		* Constructor where all the WP hook handlers are assigned
		* @return nothing
		*/
		function wp_media_player()
		{
			global $wpdb;
			$wpdb -> playerstats = $wpdb -> prefix.'media_player_stats';
			$this -> player_styles = array( '0' => 'Blitz',
											'1' => 'Executive',
											'2' => 'HiddenDark',
											'3' => 'HiddenLight',
											'4' => 'PopUp',
											'5' => 'Silverlight');

			register_activation_hook( __FILE__, array(&$this, 'create_playerstats_table') );
			add_action( 'init', array(&$this, 'initialize'), 12 );
			add_action( 'wp_enqueue_scripts', array(&$this, 'load_javascripts'), 12 );
			add_action( 'admin_enqueue_scripts', array(&$this, 'load_admin_style'), 12);
			add_action( 'admin_menu', array(&$this, 'player_menu'), 12 );
			add_action( 'wp_insert_post', array(&$this, 'post_meta_tag'), 12);
			add_action( 'media_buttons', array(&$this, 'add_media_button'), 12);
			add_filter( 'the_content', array(&$this, 'replace_tag_to_html'), 12 );
			add_filter( 'content_save_pre', array(&$this, 'check_for_tag'), 12 );

			// Localization
			load_plugin_textdomain( 'wp-media-player', '/wp-content/plugins/wp-media-player/languages' );
			
			$this -> watermark_positions = array(	'1' => __('Top and Left', 'wp-media-player'),
													'2' => __('Top and Right', 'wp-media-player'),
													'3' => __('Bottom and Left', 'wp-media-player'),
													'4' => __('Bottom and Right', 'wp-media-player') );
			$this -> watermark_opacities = array(	'.2' => __('20%', 'wp-media-player'),
													'.4' => __('40%', 'wp-media-player'),
													'.6' => __('60%', 'wp-media-player'),
													'.8' => __('80%', 'wp-media-player'),
													'1' => __('100%', 'wp-media-player') );
		}

		/**
		* Initialization of the plugin's configuration parameters
		* @return nothing
		*/
		function initialize() {
			$this -> pluginurl = apply_filters( 'wp_media_player_url', get_bloginfo( 'wpurl' ) . '/wp-content/plugins/wp-media-player/' );

			// Load default player settings
			$this -> default_parameters = array(	'autoLoad' => get_option( 'wp_media_player_autoLoad' ),
													'autoPlay' => get_option( 'wp_media_player_autoPlay' ),
													'muted' => get_option( 'wp_media_player_muted' ),
													'src' => '',
													'thumb' => get_option( 'wp_media_player_thumb' ),
													'width' => get_option( 'wp_media_player_width' ),
													'height' => get_option( 'wp_media_player_height' ) );
			if ( $this -> default_parameters['thumb'] == '' ) {
				$this -> default_parameters['thumb'] = $this -> pluginurl.'images/sl.jpg';
			}
			// Load default plugin settings
			$this -> plugin_parameters = array( 	'style' => get_option( 'wp_media_player_style' ),
													'showOnHomePage' => get_option( 'wp_media_player_show_on_home_page' ),
													'watermarkSource' => get_option( 'wp_media_player_wmsrc' ),
													'watermarkOpacity' => get_option( 'wp_media_player_wmopacity' ),
													'watermarkPosition' => get_option( 'wp_media_player_wmposition' ) );
		}

		/**
		* Creates the database table for storing video views data.
		* Also adds plugin options
		* 
		* @return nothing
		*/
		function create_playerstats_table() {
			global $wpdb;

			if ( @is_file( ABSPATH.'/wp-admin/includes/upgrade.php' ) ) {
				include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );
			}
			elseif ( @is_file( ABSPATH.'/wp-admin/upgrade-functions.php' ) ) {
				include_once( ABSPATH.'/wp-admin/upgrade-functions.php' );
			}
			else {
				die( 'We have problem finding your \'/wp-admin/upgrade.php\'' );
			}

			$charset_collate = '';
			if ( $wpdb -> supports_collation() ) {
				if ( !empty( $wpdb->charset ) ) {
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( !empty( $wpdb->collate ) ) {
					$charset_collate .= " COLLATE $wpdb->collate";
				}
			}

			$create_playerstats_sql = "CREATE TABLE $wpdb->playerstats (".
													"play_guid CHAR(32) NOT NULL,".
													"play_postid INT(11) NOT NULL ,".
													"play_posttitle TEXT NOT NULL,".
													"play_videofile VARCHAR(".MEDIA_FILENAME_LENGTH.") NOT NULL,".
													"play_opened INT(11) NOT NULL ,".
													"play_ended INT(11) NOT NULL ,".
													"PRIMARY KEY (play_guid)) $charset_collate;";

			maybe_create_table( $wpdb -> playerstats, $create_playerstats_sql );

			add_option( 'wp_media_player_autoLoad', '0' );
			add_option( 'wp_media_player_autoPlay', '0' );
			add_option( 'wp_media_player_muted', '0' );
			add_option( 'wp_media_player_width', DEFAULT_PLAYER_WIDTH );
			add_option( 'wp_media_player_height', DEFAULT_PLAYER_HEIGHT );
			add_option( 'wp_media_player_thumb', '' );
			add_option( 'wp_media_player_style', '1' );
			add_option( 'wp_media_player_wmsrc', '' );
			add_option( 'wp_media_player_wmopacity', '1' );
			add_option( 'wp_media_player_wmposition', '4' );
			add_option( 'wp_media_player_show_on_home_page', 0 );
		}
		
		/**
		* Create a 2D array from a CSV string
		*
		* @param mixed $data 2D array
		* @param string $delimiter Field delimiter
		* @param string $enclosure Field enclosure
		* @param string $newline Line seperator
		* @return 2D array
		*/
		function str_parse_csv( $data, $delimiter = ',', $enclosure = '"', $newline = '\n' ) {
			$pos = $last_pos = -1;
			$end = strlen( $data );
			$row = 0;
			$quote_open = false;
			$trim_quote = false;
			$return = array();

			// Create a continuous loop
			for ( $i = -1; ; ++$i ) {
				++$pos;
				// Get the positions
				$comma_pos = strpos( $data, $delimiter, $pos );
				$quote_pos = strpos( $data, $enclosure, $pos );
				$newline_pos = strpos( $data, $newline, $pos );
				// Which one comes first?
				$pos = min( ($comma_pos === false) ? $end : $comma_pos, ($quote_pos === false) ? $end : $quote_pos, ($newline_pos === false) ? $end : $newline_pos);
				// Cache it
				$char = ( isset( $data[$pos] ) ) ? $data[$pos] : null;
				$done = ($pos == $end);
				// It it a special character?
				if ( $done || $char == $delimiter || $char == $newline ) {
				// Ignore it as we're still in a quote
					if ( $quote_open && !$done ) {
						continue;
					}
					$length = $pos - ++$last_pos;
					// Is the last thing a quote?
					if ( $trim_quote ) {
						// Well then get rid of it
						--$length;
					}
					// Get all the contents of this column
					$return[$row][] = ($length > 0) ? str_replace( $enclosure . $enclosure, $enclosure, substr( $data, $last_pos, $length ) ) : '';
					// And we're done
					if ( $done )
						break;
					// Save the last position
					$last_pos = $pos;
					// Next row?
					if ( $char == $newline ) 
						++$row;
					$trim_quote = false;
				}
				// Our quote?
				else if ( $char == $enclosure ) {
					// Toggle it
					if ( $quote_open == false ) {
						// It's an opening quote
						$quote_open = true;
						$trim_quote = false;
						// Trim this opening quote?
						if ( $last_pos + 1 == $pos ) {
							++$last_pos;
						}
					}
					else {
						// It's a closing quote
						$quote_open = false;
						// Trim the last quote?
						$trim_quote = true;
					}
				}
			}
			return $return;
		}

		/**
		* Replace the first occurence of the element in the string
		* 
		* @return Replaced string
		* @param string $search String to search for
		* @param object $replace String to replace with
		* @param object $subject String to search in
		*/
		function str_replace_once( $search, $replace, $subject ) {
			$firstChar = strpos( $subject, $search );
			if ( $firstChar !== false ) {
				$beforeStr = substr( $subject, 0, $firstChar );
				$replaced_pos = $firstChar + strlen( $search );
				$afterStr = substr( $subject, $replaced_pos );
				return $beforeStr.$replace.$afterStr;
			} else {
				return $subject;
			}
		}

		/**
		* Finds All occurances of media player tag
		* @return Array of all occurances
		* @param object $content The haystack to search in
		*/
		function get_player_tag( $content ) {
			$regex = '/(?:SKIP:)?\[mediaplayer([^\]]+?)]/si';
			preg_match_all( $regex, $content, $matches, PREG_SET_ORDER );
			return $matches;
		}

		/**
		* Quick check if the string contains a media player tag
		* @return 
		* @param object $content
		*/
		function player_tag_exists( $content ) {
			if ( stristr( $content, '[mediaplayer') )
				return true;
			else
				return false;
		}

		/**
		* Check if media player should be added to the post or page
		* @return 
		*/
		function show_player_check() {
			global $wp_query;

			if ( is_feed() == 1 ){
				return false;
			}
			if ( (is_page() == 1) || (is_single() == 1) ){
				if ( get_post_meta( $wp_query->get_queried_object_id(), MEDIA_PLAYER_META_TAG, true ) == '1' ) {
					return true;
				}
			}
			if( $this -> plugin_parameters['showOnHomePage'] == 1 ){
				return true;
			}
			return false;
		}

		/**
		* Updates player's dimensions to ensure aspect ratio is maintained
		* @return updated dimensions
		* @param object $width
		* @param object $height
		*/
		function process_dimensions( $width, $height ) {	
			$tmp_width = 0;
			$tmp_height = 0;

			if ( is_numeric( $width ) ) {
				if ( $width <= 0 || $width > MAX_PLAYER_WIDTH )
					$tmp_width = DEFAULT_PLAYER_WIDTH;
				else 
					$tmp_width = $width;
			}
			else
				$tmp_width = DEFAULT_PLAYER_WIDTH;

			if ( is_numeric( $height ) ){
				if ( $height <= 0 || $height > MAX_PLAYER_HEIGHT ) 
					$tmp_height = DEFAULT_PLAYER_HEIGHT;
				else 
					$tmp_height = $height;
			}
			else
				$tmp_height = DEFAULT_PLAYER_HEIGHT;

			if ( $tmp_width >= $tmp_height )
				$tmp_height = round( $tmp_width * 0.75 );
			else
				$tmp_width = round( $tmp_height * 1.33 );

			return array( $tmp_width.'px', $tmp_height.'px' );
		}

		/**
		* Repaces all the [mediaplayer] tags within a post or a page with
		* the JavaScript code for rendering media player
		* @return updated content
		* @param object $content
		*/
		function replace_tag_to_html( $content ) {
			if ( !$this -> player_tag_exists( $content ) ) 
				return $content;
			$matches = $this -> get_player_tag( $content );
			if ( empty( $matches ) ) 
				return $content;
			global $post;
			$post_id = $post -> ID;
			$show_player = $this -> show_player_check();

			$i = 0;	
			foreach ( (array)$matches as $match ) {
				$scriptcode = '';
				if ( strpos( $match[0], 'SKIP:' ) !== false ) {
					$scriptcode = substr( $match[0], strpos( $match[0], '[' ) );
				}
				elseif ( $show_player == true ) {
					$results = $this -> str_parse_csv( $match[1], ' ', '\"' );
					$parameters = array();
					foreach ( $results as $result ) {
						foreach ( $result as $result2 ) {
							if ( strpos( $result2, '=' ) !== false )
								list( $paramName, $paramValue ) = explode( '=', $result2, 2 );
							else
								continue;

							$paramValue = trim( $paramValue, '"\'' );
							if ( array_key_exists( $paramName, $this -> default_parameters ) ) {
								$parameters[$paramName] = $paramValue;
							}
						}
					}

					$scriptcode = "<span id=\"mediaPlayer_".$post_id."_$i\"><script type='text/javascript'>\n";
					$scriptcode .= "<!--\n";
					$scriptcode .= "function getCustomPlaylist_".$post_id."_$i()\n";
					$scriptcode .= "{ return [{ \n";

					$tmp = '';
					$tmp1 = '';
					if ( array_key_exists( 'src', $parameters ) )
						$tmp = $parameters['src'];
					else
						$tmp = $this -> default_parameters['src'];
					$scriptcode .= "\"mediaSource\":\"$tmp\", \n";

					if ( array_key_exists( 'thumb', $parameters ) )
						$tmp = $parameters['thumb'];
					else
						$tmp = $this -> default_parameters['thumb'];
					$scriptcode .= "\"placeholderSource\":\"$tmp\", \n";

					$scriptcode .= "\"chapters\":[]}]; }\n" ;
					
					$scriptcode .= "var player_".$post_id."_$i = new StartMediaPlayer(\"mediaPlayer_".$post_id."_$i\", ";
					
					$scriptcode .= "'".$this -> pluginurl."styles/".strtolower($this -> player_styles[$this -> plugin_parameters['style']])."/player.xaml', ";

					if ( array_key_exists( 'width', $parameters ) )
						$tmp = $parameters['width'];
					else
						$tmp = $this -> default_parameters['width'];

					if ( array_key_exists('height', $parameters ) )
						$tmp1 = $parameters['height'];
					else
						$tmp1 = $this -> default_parameters['height'];
						
					list( $tmp, $tmp1 ) = $this -> process_dimensions( $tmp, $tmp1 );
					$scriptcode .= "\"$tmp\", \"$tmp1\");\n";

					if ( array_key_exists( 'autoPlay', $parameters ) )
						$tmp = $parameters['autoPlay'];
					else
						$tmp = $this -> default_parameters['autoPlay'];
					$scriptcode .= "player_".$post_id."_$i.autoPlayParam=".($tmp == '1'?'true':'false').";\n";

					if ( array_key_exists( 'autoLoad', $parameters ) )
						$tmp = $parameters['autoLoad'];
					else
						$tmp = $this -> default_parameters['autoLoad'];
					$scriptcode .= "player_".$post_id."_$i.autoLoadParam=".($tmp == '1'?'true':'false').";\n";

					if ( array_key_exists( 'muted', $parameters ) )
						$tmp = $parameters['muted'];
					else
						$tmp = $this -> default_parameters['muted'];
					$scriptcode .= "player_".$post_id."_$i.mutedParam=".($tmp == '1'?'true':'false').";\n";

					$tmp = $this -> plugin_parameters['watermarkSource'];
					if ( strlen( $tmp ) > 0 ) {
						$scriptcode .= "player_".$post_id."_$i.watermarkSource=\"$tmp\";\n";
						$tmp = $this -> plugin_parameters['watermarkOpacity'];
						$scriptcode .= "player_".$post_id."_$i.watermarkOpacity=$tmp;\n";
						$tmp = $this -> plugin_parameters['watermarkPosition'];
						$scriptcode .= "player_".$post_id."_$i.watermarkPosition=$tmp;\n";
					}

					$scriptcode .= "player_".$post_id."_$i.postId=".$post_id.";\n";
					$scriptcode .= "player_".$post_id."_$i.ajaxUrl='".$this -> pluginurl."wp-media-player.php';\n";

					$scriptcode .= "player_".$post_id."_$i.getPlaylist=getCustomPlaylist_".$post_id."_$i;\n";
					$scriptcode .= "-->\n";
					$scriptcode .= "</script></span>";
				}
				else {
					$scriptcode = "<a href=\"".get_permalink()."#mediaPlayer_".$post_id."_$i\">".__('Play Video', 'wp-media-player')."</a>";
				}
				$content = $this -> str_replace_once( $match[0], $scriptcode, $content );
				$i++;
			}
			return $content;
		}

		/**
		* Checks if content contains the [mediaplayer] tag
		* @return unmodified content
		* @param object $content
		*/
		function check_for_tag( $content ) {
			// This is a workaround for the weird behavior in content_save_pre.
			// The filter is called multiple tims and the second time it contains the previous
			// revision, which screws up the meta logic.
			if ( $this -> content_save_pre_count > 0 ) 
				return $content;

			if (  $this -> player_tag_exists( $content) ) {
				$this -> post_needs_meta = true;
			}
			else {
				$this -> post_needs_meta = false;
			}

			$this -> content_save_pre_count++;
			return $content;
		}

		/**
		* Adds a meta tag to a post or a page to indicate that the post contains 
		* [mediaplayer] tags.
		* @return nothing
		* @param object $id
		*/
		function post_meta_tag( $id ) {
			$meta = get_post_meta( $id, MEDIA_PLAYER_META_TAG, true );
			if ( $this -> post_needs_meta == true ) {	
				if ( empty( $meta ) ) {
					add_post_meta( $id, MEDIA_PLAYER_META_TAG, '1', true );
				}
				elseif ( $meta != '1' ) {
					delete_post_meta( $id, MEDIA_PLAYER_META_TAG, $meta );
					add_post_meta( $id, MEDIA_PLAYER_META_TAG, '1', true );
				}
			}
			else {
				delete_post_meta( $id, MEDIA_PLAYER_META_TAG, $meta );
			}
		}

		/**
		* Enqueues all the JavaScript references necessary for the player to work
		* @return nothing
		*/
		function load_javascripts() {
			if ( $this -> show_player_check() == true ) {	
				global $post;
			
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'microsoftajax', $this -> pluginurl.'scripts/microsoftajax.js' );
				wp_enqueue_script( 'silverlight', $this -> pluginurl.'scripts/silverlight.js' );
				wp_enqueue_script( 'silverlightcontrol', $this -> pluginurl.'scripts/silverlightcontrol.js' );
				wp_enqueue_script( 'silverlightmedia', $this -> pluginurl.'scripts/silverlightmedia.js' );
				wp_enqueue_script( 'expressionplayer', $this -> pluginurl.'scripts/expressionplayer.js' );
				wp_enqueue_script( 'player', $this -> pluginurl.'styles/'.strtolower( $this -> player_styles[$this -> plugin_parameters['style']] ).'/player.js' );
				wp_enqueue_script( 'startmediaplayer', $this -> pluginurl.'scripts/startplayer.js', array( 'jquery' ) );
			}
		}

		/**
		* Enqueues the CSS for the adming page
		* @return nothing
		* @param object $hook_suffix
		*/
		function load_admin_style( $hook_suffix ) {
			$player_admin_pages = array( 'wp-media-player/player-stats.php', 'wp-media-player/player-settings.php', 'wp-media-player/player-about.php' );
			if ( in_array( $hook_suffix, $player_admin_pages ) ) {
				wp_enqueue_style( 'wp-media-player-admin', plugins_url( 'wp-media-player/player-admin-css.css' ) );
			}
		}

		/**
		* Creates the HTML markup for the styles drop down box
		* @return HTML markup string
		* @param object $default[optional]
		*/
		function dropdown_html( $dropdown_data, $default ) {
			$p = '';
			$r = '';
			foreach ( $dropdown_data as $key => $name ) {
				if ( $default == $key ) // Make default first in list
					$p = "\n\t<option selected='selected' value='$key'>$name</option>";
				else
					$r .= "\n\t<option value='$key'>$name</option>";
			}
			return $p . $r;
		}

		function dropdown_styles( $default = '1' ) {
			return $this -> dropdown_html( $this -> player_styles, $default );
		}	
		function dropdown_positions( $default = '4' ) {
			return $this -> dropdown_html( $this -> watermark_positions, $default );
		}
		function dropdown_opacities( $default = '1' ) {
			return $this -> dropdown_html( $this -> watermark_opacities, $default );
		}

		/**
		* Registers the player's admin menus
		* @return nothing
		*/
		function player_menu() {
			if ( function_exists( 'add_menu_page' ) ) {
				add_menu_page( __('Media Player', 'wp-media-player'), __('Media Player', 'wp-media-player'), 8, 'wp-media-player/player-stats.php', '', plugins_url( 'wp-media-player/images/silverlight-button-download.gif' ) );
			}
			if ( function_exists( 'add_submenu_page' ) ) {
				add_submenu_page( 'wp-media-player/player-stats.php', __('WP Media Player Statistics', 'wp-media-player'), __('Statistics', 'wp-media-player'), 8, 'wp-media-player/player-stats.php' );
				add_submenu_page( 'wp-media-player/player-stats.php', __('WP Media Player Settings', 'wp-media-player'), __('Settings', 'wp-media-player'),  8, 'wp-media-player/player-settings.php' );
				add_submenu_page( 'wp-media-player/player-stats.php', __('About WP Media Player Plugin', 'wp-media-player'), __('About', 'wp-media-player'),  8, 'wp-media-player/player-about.php' );
			}
		}

		/**
		* Adds a button to a post or page edit UI that is used to insert
		* the media player tag into the content
		* @return 
		*/
		function add_media_button() {
			global $post;
			$postid = 0;
			if ( isset( $post ) )
				$postid = $post -> ID;
			echo '<a href="../wp-content/plugins/wp-media-player/uploader.php?post_id='.$postid.'&tab=choose&TB_iframe=true&amp;height=500&amp;width=640" class="thickbox" title="'.__('Silverlight Video','wp-media-player').'"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/wp-media-player/images/silverlight-button-download.gif" alt="'.__('Silverlight Video','wp-media-player').'"></a>';
		}

		/**
		* Logs the tracking data about player playback.
		* @return nothing
		*/
		function process_ajax_stats_request() {
			global $wpdb;

			if ( isset( $_GET['pid'] ) && isset( $_GET['stat'] ) && isset( $_GET['file'] ) ) {
				$post_id = intval( $_GET['pid'] );
				// Check Whether Is A Valid Post
				$post = get_post( $post_id );
				if ( $post ) {
					$filename = urldecode( $_GET['file'] );
					$status = intval( $_GET['stat'] );
					// Generate the key value for the record in the database
					$guid = md5( $filename.'_'.$post_id );
					/**
					* Build an update query based on what status was reported.
					* If no status was reported then bail out.
					*/
					$query = "UPDATE $wpdb->playerstats SET ";
					if ( $status === 1 )
						$query .= 'play_opened=play_opened+1';
					elseif ( $status === 2 )
						$query .= 'play_ended=play_ended+1';
					else 
						return;
					$query .= " WHERE play_guid='$guid'";
					// Run the query
					$query_result = $wpdb -> query( $query );
					/**
					* If the row update failed then check if the video url passed on the query string matches the one used by
					* the [mediaplayer src] tag inside of the post. Only after that attempt to insert a new recored. 
					*/
					if ( $query_result == 0 && stristr( $post->post_content, $filename ) ) {
						$filename = addslashes( $filename );
						// Ensure that the string length does not exceed allowed size
						if ( strlen( $filename ) > MEDIA_FILENAME_LENGTH )
							$filename = substr( $filename, 0, MEDIA_FILENAME_LENGTH );
						$query = "INSERT INTO $wpdb->playerstats (play_guid, play_postid, play_posttitle, play_videofile, play_opened, play_ended) ".
								"VALUES ('$guid', $post_id, '".addslashes( $post -> post_title )."', '$filename', 1, 0)";
						$query_result = $wpdb -> query( $query );
					}
				}
			}
		}
	} // End Class wp_media_player
}

// Initialize the plugin class
if ( class_exists( 'wp_media_player' ) ) {
	if ( !isset( $mp_plugin ) )
		$mp_plugin = new wp_media_player();
	// Run the function to process the AJAX call.
	$mp_plugin -> process_ajax_stats_request();
}
?>