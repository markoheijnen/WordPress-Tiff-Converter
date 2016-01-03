<?php

WP_CLI::addCommand( 'tiff-converter', 'Tiff_Converter_Command' );

/**
 * Ability to convert all images throught CLI
 *
 * @package tiff-converter
 */
class Tiff_Converter_Command extends WP_CLI_Command {

	/**
	 * Run the converter now
	 *
	 * @since 1.0
	 *
	 * @param array $args can be extension
	 * @param array $vars
	 */
	function update_attachments( $args = array(), $vars = array() ) {
		$images    = Tiff_Converter::get_images();
		$mime_type = 'image/jpg'; // Maybe $args[0] for changing it

		if( $images ) {
			$succeed = $failed = 0;

			foreach( $images as $image ) {
				$file = get_attached_file( $image->ID );

				$result = Tiff_Converter_Handle::convert_image( $file, $mime_type );

				if( ! is_wp_error( $result ) ) {
					$update_args = array(
						'ID'             => $image->ID,
						'post_mime_type' => $result['mime-type']
					);
					$result2 = wp_update_post( $update_args, true );

					if( $result2 && ! is_wp_error( $result2 ) ) {
						unlink( $file );

						update_attached_file( $image->ID, $result['path'] );
						wp_update_attachment_metadata( $image->ID, wp_generate_attachment_metadata( $image->ID, $result['path'] ) );

						$succeed++;
					}
					else {
						unlink( $result['path'] );
						$failed++;
					}
				}
				else {
					$failed++;
				}
			}

			WP_CLI::success( sprintf( '%d images are converted and %s failed', $succeed, $failed ) );
		}
		else {
			WP_CLI::success( 'No images to convert' );
		}
	}

	/**
	 * Help function for this command
	 *
	 * @since 1.0
	 */
	public static function help() {
		WP_CLI::line( <<<EOB
usage: wp tiff-converter [update_attachments]

	update_attachments    run a backup now
EOB
	);
	}
}