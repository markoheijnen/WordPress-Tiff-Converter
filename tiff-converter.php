<?php
/*
Plugin Name: Tiff Converter
Plugin URI: 
Description: Uploaded tiff images will be convertered to JPGs
Version: 1.0
License: GPLv2 or later
Author: humanmade, markoheijnen
Author URI: http://www.hmn.md/
Text Domain: tiffconverter
Domain Path: /languages
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Online: http://www.gnu.org/licenses/gpl.txt
*/

/*
 Fix function TODO:
 - wp_upload_bits()

 NICE HAVE
 - Switch when use of Alpha -> Imagick -> $this->image->getimagealphachannel()
*/

if ( ! defined('ABSPATH') )
    die();

include dirname( __FILE__ ) . '/inc/converter.php';

if ( defined('WP_CLI') && WP_CLI ) {
	include( dirname( __FILE__ ) . '/inc/wp-cli.php' );
}

class Tiff_Converter {

	/**
	 * Load all the code
	 *
	 * @since 1.0
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'check_if_usable' ) );

		add_filter( 'mime_types', array( $this, 'fix_tif_extension' ) );

		new Tiff_Converter_Handle;
	}

	/**
	 * Checks on activation if this plugin can be used.
	 * Will deactivate and die if not possible.
	 *
	 * @since 1.0
	 */
	public function check_if_usable() {
		if ( ! $this->is_usable() ) {
			deactivate_plugins( __FILE__ );
			wp_die(
				'<p>' . __( "This plugin requires Imagick/Gmagick to be installed.", 'tiffconverter' ) . '</p>',
				'Plugin Activation Error',
				array(
					'back_link' => true
				)
			);
		}
	}

	/**
	 * Checks if the image editor supports tiff images
	 *
	 * @since 1.0
	 *
	 * @return bool true is possible
	 */
	public function is_usable() {
		return wp_image_editor_supports( array( 'mime_type' => 'image/tiff' ) );
	}


	/**
	 * Little hack due the fact that Imagick doesn't reconize TIF as supported
	 * Uses the filer 'mime_types' to do this fix
	 *
	 * @since 1.0
	 *
	 * @param array $mime_types
	 * @return array
	 */
	public function fix_tif_extension( $mime_types ) {
		$mime_types['tiff|tif'] = $mime_types['tif|tiff'];
		unset( $mime_types['tif|tiff'] );

		return $mime_types;
	}

	/**
	 * Request all tiff images in the media library
	 *
	 * @since 1.0
	 *
	 * @return array Return array with WP_Post objects
	 */
	public static function get_images() {
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image/tiff'
		);

		return get_posts( $args );
	}

}

$GLOBALS['tiff_converter'] = new Tiff_Converter;
