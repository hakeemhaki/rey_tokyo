<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( ! class_exists('ReyCore_Webfonts_Downloader') ):
/**
 * Fonts-downloading manager.
 *
 * @since 2.0.0
 */
class ReyCore_Webfonts_Downloader {

	// TODO
	// posibility to clear option

	const OPTION = 'reycore_downloaded_font_files';

	/**
	 * Get styles from URL.
	 *
	 * @access public
	 * @since 2.0.0
	 * @param string $url The URL.
	 * @return string
	 */
	public function get_styles( $css, $args = [] ) {

		$files = $this->get_local_files_from_css( $css, $args );

		// Convert paths to URLs.
		foreach ( $files as $remote => $local ) {
			$files[ $remote ] = str_replace( WP_CONTENT_DIR, content_url(), $local );
		}

		return str_replace(
			array_keys( $files ),
			array_values( $files ),
			$css
		);

	}

	/**
	 * Download files mentioned in our CSS locally.
	 *
	 * @access protected
	 * @since 2.0.0
	 * @param string $css The CSS we want to parse.
	 * @return array      Returns an array of remote URLs and their local counterparts.
	 */
	protected function get_local_files_from_css( $css, $args ) {

		$font_files = $this->get_files_from_css( $css, $args );

		// $stored = get_option( self::OPTION, [] );

		$change = false; // If in the end this is true, we need to update the cache option.

		// If the fonts folder don't exist, create it.
		if ( ! file_exists( WP_CONTENT_DIR . '/fonts' ) ) {
			wp_mkdir_p( WP_CONTENT_DIR . '/fonts' );
		}

		foreach ( $font_files as $font_family => $files ) {

			// The folder path for this font-family.
			$folder_path = WP_CONTENT_DIR . '/fonts/' . $font_family;

			// If the folder doesn't exist, create it.
			if ( ! file_exists( $folder_path ) ) {
				wp_mkdir_p( $folder_path );
			}

			foreach ( $files as $url ) {

				// Get the filename.
				$filename  = basename( wp_parse_url( $url, PHP_URL_PATH ) );
				$font_path = $folder_path . '/' . $filename;

				if ( file_exists( $font_path ) ) {

					// Skip if already cached.
					if ( isset( $stored[ $url ] ) ) {
						continue;
					}

					$stored[ $url ] = $font_path;

					$change         = true;
				}

				if ( ! function_exists( 'download_url' ) ) {
					require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
				}

				// Download file to temporary location.
				$tmp_path = download_url( $url );

				// Make sure there were no errors.
				if ( is_wp_error( $tmp_path ) ) {
					continue;
				}

				// Move temp file to final destination.
				if ( $this->get_filesystem()->move( $tmp_path, $font_path, true ) ) {

					$stored[ $url ] = $font_path;
					$change         = true;

					self::log('-- Downloaded font.');
				}
			}
		}

		if ( $change ) {
			update_option( self::OPTION, $stored );
		}

		return $stored;
	}


	/**
	 * Get font files from the CSS.
	 *
	 * @access public
	 * @since 2.0.0
	 * @param string $css The CSS we want to parse.
	 * @return array      Returns an array of font-families and the font-files used.
	 */
	public function get_files_from_css( $css, $args ) {

		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!' , '' , $css );;

		$font_faces = explode( '@font-face', $css );

		$result = [];

		// Loop all our font-face declarations.
		foreach ( $font_faces as $font_face ) {

			// Make sure we only process styles inside this declaration.
			$style = explode( '}', $font_face )[0];

			// Sanity check.
			if ( false === strpos( $style, 'font-family' ) ) {
				continue;
			}

			// Get an array of our font-families.
			preg_match_all( '/font-family.*?\;/', $style, $matched_font_families );

			// Get an array of our font-files.
			preg_match_all( '/url\(.*?\)/i', $style, $matched_font_files );

			// Get the font-family name.
			$font_family = 'unknown';
			if ( isset( $matched_font_families[0] ) && isset( $matched_font_families[0][0] ) ) {
				$font_family = rtrim( ltrim( $matched_font_families[0][0], 'font-family:' ), ';' );
				$font_family = trim( str_replace( array( "'", ';' ), '', $font_family ) );
				$font_family = sanitize_key( strtolower( str_replace( ' ', '-', $font_family ) ) );
			}

			/*
			Due to licensing, it's best not to self host Adobe fonts.

			if( isset($args['type']) && $args['type'] === 'adobe' ){

				// Get an array of our font-styles.
				preg_match_all( '/font-style.*?\;/', $style, $matched_font_styles );

				$font_style = 'normal';
				if ( isset( $matched_font_styles[0] ) && isset( $matched_font_styles[0][0] ) ) {
					$font_style = rtrim( ltrim( $matched_font_styles[0][0], 'font-style:' ), ';' );
					$font_style = sanitize_key( strtolower( str_replace( ' ', '-', $font_style ) ) );
				}

				// Get an array of our font-weights.
				preg_match_all( '/font-weight.*?\;/', $style, $matched_font_weights );

				$font_weight = 'unknown';
				if ( isset( $matched_font_weights[0] ) && isset( $matched_font_weights[0][0] ) ) {
					$font_weight = rtrim( ltrim( $matched_font_weights[0][0], 'font-weight:' ), ';' );
					$font_weight = sanitize_key( strtolower( str_replace( ' ', '-', $font_weight ) ) );
				}
			}
			*/

			// Make sure the font-family is set in our array.
			if ( ! isset( $result[ $font_family ] ) ) {
				$result[ $font_family ] = [];
			}

			// Get files for this font-family and add them to the array.

			foreach ( $matched_font_files as $match ) {

				// Sanity check.
				if ( ! isset( $match[0] ) ) {
					continue;
				}

				$match_no_q = str_replace('"', '', $match[0]);

				// Add the file URL.
				$result[ $font_family ][] = rtrim( ltrim( $match_no_q, 'url(' ), ')' );
			}

			// Make sure we have unique items.
			// We're using array_flip here instead of array_unique for improved performance.
			$result[ $font_family ] = array_flip( array_flip( $result[ $font_family ] ) );
		}

		return $result;
	}

	/**
	 * Get the filesystem.
	 *
	 * @access protected
	 * @since 2.0.0
	 * @return WP_Filesystem
	 */
	protected function get_filesystem() {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			}
			WP_Filesystem();
		}
		return $wp_filesystem;
	}

	public static function log($message){
		if( defined('WP_DEBUG') && WP_DEBUG ){
			error_log(var_export( $message,1));
		}
	}
}
endif;
