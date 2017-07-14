<?php
/*
* WordPress Plugin: WP Media Player
* 
* File Written By:
* - Ruslan Yakushev
* - http://ruslany.net
* - Implementation idea was taken from Lester "GaMerZ" Chan (http://lesterchan.net)
* 
* File Information:
* - Display the player statistics
* 
*/

$base_name = plugin_basename( 'wp-media-player/player-stats.php' );
$base_page = 'admin.php?page=' . $base_name;

if ( function_exists( 'current_user_can' ) && !current_user_can( 'manage_options' ) ) 
	die( __('Cheatin&#8217; uh?', 'wp-media-player') );
if ( !user_can_access_admin_page() ) 
	wp_die( __('You do not have sufficient permissions to access this page.', 'wp-media-player') );

// Reset play counter if necessary
if ( isset( $_GET['reset'] ) ) {
	$reset_play_guid = addslashes( $_GET['reset'] );
	$wpdb -> query( "DELETE FROM $wpdb->playerstats WHERE play_guid = '$reset_play_guid'" );
}

$playerstats_page = isset( $_GET['statspage'] ) ? intval( $_GET['statspage'] ) : 0;
$playerstats_sortby = isset( $_GET['by'] ) ? trim( $_GET['by'] ) : '';
$playerstats_sortby_text = '';
$playerstats_sortorder = isset( $_GET['order'] ) ? trim( $_GET['order'] ) : '';
$playerstats_sortorder_text = '';
$playerstats_log_perpage = isset( $_GET['perpage'] ) ? intval( $_GET['perpage'] ) : 0;
$playerstats_sort_url = '';

// Form sorting URL
if ( !empty( $playerstats_sortby ) ) {
	$playerstats_sort_url .= '&amp;by=' . $playerstats_sortby;
}
if ( !empty( $playerstats_sortorder ) ) {
	$playerstats_sort_url .= '&amp;order=' . $playerstats_sortorder;
}
if ( $playerstats_log_perpage > 0 ) {
	$playerstats_sort_url .= '&amp;perpage=' . $playerstats_log_perpage;
}

// Get Order By
switch ( $playerstats_sortby ) {
	case 'postid' :
		$playerstats_sortby = 'play_postid';
		$playerstats_sortby_text = __('Post ID', 'wp-media-player');
		break;
	case 'posttitle' :
		$playerstats_sortby = 'play_posttitle';
		$playerstats_sortby_text = __('Post Title', 'wp-media-player');
		break;
	case 'videofile' :
		$playerstats_sortby = 'play_videofile';
		$playerstats_sortby_text = __('Video File', 'wp-media-player');
		break;
	case 'opened' :
		$playerstats_sortby = 'play_opened';
		$playerstats_sortby_text = __('Started', 'wp-media-player');
		break;
	case 'ended' :
	default:
		$playerstats_sortby = 'play_ended';
		$playerstats_sortby_text = __('Finished', 'wp-media-player');
}

### Get Sort Order
switch ( $playerstats_sortorder ) {
	case 'asc' :
		$playerstats_sortorder = 'ASC';
		$playerstats_sortorder_text = __('Ascending', 'wp-media-player');
		break;
	case 'desc' :
	default:
		$playerstats_sortorder = 'DESC';
		$playerstats_sortorder_text = __('Descending', 'wp-media-player');
}

// Checking $playerstats_page and $offset
$total_playerstats = $wpdb -> get_var( "SELECT COUNT(play_guid) FROM $wpdb->playerstats" );
if ( empty( $playerstats_page ) || $playerstats_page == 0 ) { 
	$playerstats_page = 1; 
}
if ( empty ( $playerstats_log_perpage ) || $playerstats_log_perpage == 0 ) { 
	$playerstats_log_perpage = 10;
}
// Determin $offset
$offset = ( $playerstats_page - 1 ) * $playerstats_log_perpage;
// Determine Max Number Of Stats To Display On Page
if ( ( $offset + $playerstats_log_perpage ) > $total_playerstats ) { 
	$max_on_page = $total_playerstats; 
} else { 
	$max_on_page = ( $offset + $playerstats_log_perpage ); 
}
// Determine Number Of Stats To Display On Page
if ( ( $offset + 1 ) > $total_playerstats ) { 
	$display_on_page = $total_playerstats;
} else { 
	$display_on_page = $offset + 1; 
}
// Determing Total Amount Of Pages
$total_pages = ceil( $total_playerstats / $playerstats_log_perpage );

// Get The Stats
$playerstats = $wpdb -> get_results( "SELECT play_guid, play_postid, play_posttitle, play_videofile, play_opened, play_ended FROM $wpdb->playerstats ORDER BY $playerstats_sortby $playerstats_sortorder LIMIT $offset, $playerstats_log_perpage" );

?>
<div class="wrap">
	<div id="icon-wp-media-player" class="icon32"><br /></div>
	<h2><?php _e('WP Media Player Statistics', 'wp-media-player'); ?></h2>
	<p><?php printf( __('Displaying records <strong>%s</strong> to <strong>%s</strong> of total <strong>%s</strong> records.', 'wp-media-player'), number_format_i18n( $display_on_page ), number_format_i18n( $max_on_page ), number_format_i18n( $total_playerstats ) ); ?></p>
	<p><?php printf( __('Sorted by <strong>%s</strong> in <strong>%s</strong> order.', 'wp-media-player'), $playerstats_sortby_text, $playerstats_sortorder_text ); ?></p>
	<table  class="widefat">
		<thead>
			<tr>
				<th width="10%"><?php _e('Post ID', 'wp-media-player'); ?></th>
				<th width="25%"><?php _e('Post Title', 'wp-media-player'); ?></th>
				<th width="40%"><?php _e('Video File Path or URL', 'wp-media-player'); ?></th>
				<th width="10%" style="text-align:center"><?php _e('Started', 'wp-media-player'); ?></th>
				<th width="10%" style="text-align:center"><?php _e('Finished', 'wp-media-player'); ?></th>
				<th width="5%"><?php _e('Reset', 'wp-media-player'); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php
		if ( $playerstats ) {
			$i = 0;
			foreach ( $playerstats as $playerstat ) {
				$style = ( $i%2 == 0 ) ? 'class="alternate"' : '';
				$play_guid = stripslashes( $playerstat -> play_guid );
				$play_postid = intval( $playerstat -> play_postid );
				$play_posttitle = stripslashes( $playerstat -> play_posttitle );
				$play_videofile = htmlspecialchars( stripslashes( $playerstat -> play_videofile ) );
				$play_opened = intval( $playerstat -> play_opened );
				$play_ended = intval( $playerstat -> play_ended );
				echo "<tr $style>\n";
				echo '<td>' . $play_postid . '</td>'."\n";
				echo "<td>$play_posttitle</td>\n";
				echo "<td>$play_videofile</td>\n";
				echo "<td style=\"text-align:center\"><strong>$play_opened</strong></td>\n";
				echo "<td style=\"text-align:center\"><strong>$play_ended</strong></td>\n";
				echo '<td style="text-align:center"><a href="' . $base_page . '&amp;reset=' . $play_guid . '&amp;statspage=' . $playerstats_page . $playerstats_sort_url . '"><img src="' . plugins_url( 'wp-media-player/images/cross.png' ).'" alt="Reset" title="Reset Counters" /></a></td>'."\n";
				echo '</tr>';
				$i++;
			}
		} else {
			echo '<tr><td colspan="6" align="center"><strong>' . __('No Statistics Found', 'wp-media-player').'</strong></td></tr>';
		}
	?>
		</tbody>
	</table>
	<!-- <Paging> -->
<?php
	if ( $total_pages > 1 ) {
?>
	<br />
	<table class="widefat">
		<tr>
			<td align="left" width="50%">
					<?php
						if ( $playerstats_page > 1 && ( ( ( $playerstats_page * $playerstats_log_perpage ) - ( $playerstats_log_perpage - 1 ) ) <= $total_playerstats ) ) {
							echo '<strong>&laquo;</strong> <a href="' . $base_page . '&amp;statspage=' . ( $playerstats_page - 1 ) . $playerstats_sort_url . '" title="&laquo; ' . __('Previous Page', 'wp-media-player').'">' . __('Previous Page', 'wp-media-player') . '</a>';
						} else {
							echo '&nbsp;';
						}
					?>
				</td>
				<td align="right" width="50%">
					<?php
						if ( $playerstats_page >= 1 && ( ( ( $playerstats_page * $playerstats_log_perpage ) + 1) <=  $total_playerstats ) ) {
							echo '<a href="' . $base_page . '&amp;statspage=' . ( $playerstats_page + 1 ) . $playerstats_sort_url . '" title="' . __('Next Page', 'wp-media-player') . ' &raquo;">' . __('Next Page', 'wp-media-player') . '</a> <strong>&raquo;</strong>';
						} else {
							echo '&nbsp;';
						}
					?>
				</td>
			</tr>
			<tr class="alternate">
				<td colspan="2" align="center">
					<?php printf( __('Pages (%s): ', 'wp-media-player'), number_format_i18n( $total_pages ) ); ?>
					<?php
						if ( $playerstats_page >= 4 ) {
							echo '<strong><a href="' . $base_page . '&amp;statspage=1' . $playerstats_sort_url . '" title="' . __('Go to First Page', 'wp-media-player') . '">&laquo; ' . __('First', 'wp-media-player') . '</a></strong> ... ';
						}
						if ( $playerstats_page > 1 ) {
							echo ' <strong><a href="' . $base_page . '&amp;statspage=' . ( $playerstats_page - 1 ) . $playerstats_sort_url . '" title="&laquo; ' . __('Go to Page', 'wp-media-player') . ' ' . number_format_i18n( $playerstats_page - 1 ) . '">&laquo;</a></strong> ';
						}
						for ( $i = $playerstats_page - 2 ; $i  <= $playerstats_page + 2; $i++ ) {
							if ( $i >= 1 && $i <= $total_pages ) {
								if ( $i == $playerstats_page ) {
									echo '<strong>[' . number_format_i18n( $i ) . ']</strong> ';
								} else {
									echo '<a href="' . $base_page . '&amp;statspage=' . $i . $playerstats_sort_url . '" title="' . __('Page', 'wp-media-player') . ' ' . number_format_i18n( $i ) . '">' . number_format_i18n( $i ) . '</a> ';
								}
							}
						}
						if ( $playerstats_page < $total_pages ) {
							echo ' <strong><a href="' . $base_page . '&amp;statspage=' . ( $playerstats_page + 1 ) . $playerstats_sort_url . '" title="' . __('Go to Page', 'wp-media-player') . ' ' . number_format_i18n( $playerstats_page + 1 ) . ' &raquo;">&raquo;</a></strong> ';
						}
						if( ($playerstats_page+2) < $total_pages ) {
							echo ' ... <strong><a href="' . $base_page . '&amp;statspage=' . $total_pages . $playerstats_sort_url . '" title="' . __('Go to Last Page', 'wp-media-player') . '">' . __('Last', 'wp-media-player') . ' &raquo;</a></strong>';
						}
					?>
				</td>
			</tr>
		</table>
	<!-- </Paging> -->
<?php
	}
?>
	<br />
	<form action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] ); ?>" method="get">
		<input type="hidden" name="page" value="<?php echo $base_name; ?>" />
		<table class="widefat">
			<tr class="alternate">
				<th><?php _e('Sort Options:', 'wp-media-player'); ?></th>
				<td>
					<select name="by" size="1">
						<option value="postid"<?php if( $playerstats_sortby == 'play_postid' ) { echo ' selected="selected"'; }?>><?php _e('Post ID', 'wp-media-player'); ?></option>
						<option value="posttitle"<?php if( $playerstats_sortby == 'play_posttitle' ) { echo ' selected="selected"'; }?>><?php _e('Post Title', 'wp-media-player'); ?></option>
						<option value="videofile"<?php if( $playerstats_sortby == 'play_videofile' ) { echo ' selected="selected"'; }?>><?php _e('Video File', 'wp-media-player'); ?></option>
						<option value="opened"<?php if( $playerstats_sortby == 'play_opened' ) { echo ' selected="selected"'; }?>><?php _e('Started', 'wp-media-player'); ?></option>
						<option value="ended"<?php if( $playerstats_sortby == 'play_ended' ) { echo ' selected="selected"'; }?>><?php _e('Finished', 'wp-media-player'); ?></option>
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="order" size="1">
						<option value="asc"<?php if( $playerstats_sortorder == 'ASC' ) { echo ' selected="selected"'; }?>><?php _e('Ascending', 'wp-media-player'); ?></option>
						<option value="desc"<?php if( $playerstats_sortorder == 'DESC' ) { echo ' selected="selected"'; } ?>><?php _e('Descending', 'wp-media-player'); ?></option>
					</select>
					&nbsp;&nbsp;&nbsp;
					<select name="perpage" size="1">
					<?php
						for ( $i = 10; $i <= 50; $i += 10 ) {
							if ( $playerstats_log_perpage == $i ) {
								echo "<option value=\"$i\" selected=\"selected\">" . __('Per Page', 'wp-media-player').": " . number_format_i18n( $i ) . "</option>\n";
							} else {
								echo "<option value=\"$i\">" . __('Per Page', 'wp-media-player').": " . number_format_i18n( $i ) . "</option>\n";
							}
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" value="<?php _e('Go', 'wp-media-player'); ?>" class="button" /></td>
			</tr>
		</table>
	</form>
</div>