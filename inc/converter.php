<?php

class Tiff_Converter_Handle {
	private static $new_type = 'image/jpg';

	/**
	 * Register all needed filters for handeling user upload
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'wp_handle_upload_prefilter' ) );
		add_filter( 'wp_handle_upload', array( $this, 'wp_handle_upload' ), 10, 2 );
	}

	/**
	 * Convert image on wp_handle_upload.
	 *
	 * @since 1.0
	 *
	 * @param array An associative array of file attributes
	 * @return array returns an associative array of file attributes
	 */
	public function wp_handle_upload_prefilter( $file ) {
		if( 'image/tiff' == $file['type'] ) {
			$result = $this->convert_image( $file['tmp_name'] );

			if( ! is_wp_error( $result ) ) {
				$file['type'] = $result['mime-type'];  // image/tiff
				rename( $result['path'], $file['tmp_name'] );
			}
		}

		return $file;
	}

	/**
	 * Convert image on wp_handle_sideload.
	 *
	 * @since 1.0
	 *
	 * @param array An associative array of file attributes.
	 * @param string The location where the filter has been used.
	 * @return array returns an associative array of file attributes
	 */
	public function wp_handle_upload( $data, $function ) {
		if( 'upload' == $function )
			return $data; //Handles by convert_image()

		if( 'image/tiff' != $type )
			return $data;

		$result = $this->convert_image( $data['file'] );

		if( ! is_wp_error( $result ) ) {
			unlink( $data['file'] );

			$data['file'] = $result['path'];
			$data['url']  = dirname( $data['url'] ) . '/' . $result['file'];
			$data['type'] = $result['mime-type'];
		}

		return $data;
	}

	/**
	 * Convert image to a new mime type.
	 *
	 * @since 1.0
	 *
	 * @param string The filepath of an image
	 * @param string The new mime type of the image. By default jpg
	 * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
	 */
	public static function convert_image( $filepath, $new_mime_type = null ) {
		if( ! $new_mime_type )
			$new_mime_type = self::$new_type;

		$editor = wp_get_image_editor( $filepath );
		return $editor->save( $filepath, $new_mime_type );
	}
}