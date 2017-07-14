<?php
/*
* WordPress Plugin: WP Media Player
* 
* File Written By:
* - Ruslan Yakushev
* - http://ruslany.net
* 
* File Information:
* - Utility functions
* 
*/
if ( !class_exists('wp_mp_utils') ) {
	class wp_mp_utils {
		/**
		* Converts string representation to int
		* @return 
		* @param object $size
		*/
		function convert_string_to_bytes( $size ) {
			$size = strtolower( $size );
			$bytes = (int) $size;
			if ( strpos($size, 'k') !== false )
				$bytes = intval( $size ) * 1024;
			elseif ( strpos( $size, 'm' ) !== false )
				$bytes = intval( $size ) * 1024 * 1024;
			elseif ( strpos( $size, 'g' ) !== false )
				$bytes = intval( $size ) * 1024 * 1024 * 1024;
			return $bytes;
		}
		/**
		* Converts int representation to string
		* @return 
		* @param object $bytes
		*/
		function convert_bytes_to_string( $bytes ) {
			$units = array( 0 => __('B'), 1 => __('kB'), 2 => __('MB'), 3 => __('GB') );
			$log = log( $bytes, 1024 );
			$power = (int) $log;
			$size = pow(1024, $log - $power);
			return round($size, 2) . ' ' . $units[$power];
		}
		/**
		* Finds maximum allowed upload size
		* @return 
		*/
		function max_upload_size_string() {
			$u_bytes = wp_mp_utils::convert_string_to_bytes( ini_get( 'upload_max_filesize' ) );
			$p_bytes = wp_mp_utils::convert_string_to_bytes( ini_get( 'post_max_size' ) );
			$bytes = min( $u_bytes, $p_bytes );
			return wp_mp_utils::convert_bytes_to_string( $bytes );
		}
		
		/**
		* Compares two filenames alphabetically
		* @return 
		* @param object $a
		* @param object $b
		*/
		function compare_filename( $a, $b ) {
			return strnatcmp( $a['filename'], $b['filename'] );
		}
		/**
		* Compares file sizes
		* @return 
		* @param object $a
		* @param object $b
		*/
		function compare_filesize( $a, $b ) {
			if ( $a['filezie'] == $b['filesize'] ) {
				return 0;
			}
			return ( $a['filesize'] < $b['filesize'] ) ? -1 : 1;
		}
		/**
		* Gets file name without an extension
		* @return 
		* @param object $filename
		*/
		function get_filename_without_ext( $filename ) {
			$pos = strrpos( $filename, '.' );
			if ( $pos === false ){
				return $filename;
			} else {
				return substr( $filename, 0, $pos );
			}
		}
		/**
		* Do not remember what this function is for
		* @return 
		* @param object $search
		* @param object $string
		* @param object $offset
		*/
		function strposOffset( $search, $string, $offset ) {
			/*** explode the string ***/
			$arr = explode( $search, $string );
			/*** check the search is not out of bounds ***/
			switch ( $offset ) {
				case $offset == 0 :
				return false;
				break;
				
				case $offset > max( array_keys( $arr ) ) :
				return false;
				break;
				
				default:
				return strlen( implode( $search, array_slice( $arr, 0, $offset ) ) );
			}
		}
		/**
		* Given a full URI address, returns the URL path.
		* @return 
		* @param object $url
		*/
		function get_url_path( $url ) {
			return substr( $url, wp_mp_utils::strposOffset( '/', $url, 3 ) );
		}
	}
}
?>