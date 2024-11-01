<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPDI_Effects_Imagick' ) ) {

	class WPDI_Effects_Imagick extends WP_Image_Editor_Imagick {
		/**
		 * Overload WP rotate function to add background color support
		 * Rotates current image counter-clockwise by $angle.
		 *
		 * @param float $angle
		 * @param null|string $background
		 *
		 * @return true|WP_Error
		 */
		public function rotate( $angle, $background = 'none' ) {
			/**
			 * $angle is 360-$angle because Imagick rotates clockwise
			 * (GD rotates counter-clockwise)
			 */
			try {
				$this->image->rotateImage( new ImagickPixel( $background ), 360 - $angle );

				// Normalise EXIF orientation data so that display is consistent across devices.
				if ( is_callable( array(
						$this->image,
						'setImageOrientation'
					) ) && defined( 'Imagick::ORIENTATION_TOPLEFT' ) ) {
					$this->image->setImageOrientation( Imagick::ORIENTATION_TOPLEFT );
				}

				// Since this changes the dimensions of the image, update the size.
				$result = $this->update_size();
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				$this->image->setImagePage( $this->size['width'], $this->size['height'], 0, 0 );
			} catch ( Exception $e ) {
				return new WP_Error( 'image_rotate_error', $e->getMessage() );
			}

			return true;
		}

		/**
		 * Add desaturate effect
		 *
		 * @return bool
		 */
		public function desaturate() {
			$this->image->transformImageColorspace( Imagick::COLORSPACE_GRAY );

			return true;
		}

		/**
		 * Resize an image without saving the aspect image ratio
		 *
		 * @param $width
		 * @param $height
		 *
		 * @return bool
		 */
		public function true_resize( $width, $height ) {
			return $this->thumbnail_image( $width, $height );
		}

		public function custom_update_size() {
			return parent::update_size();
		}

		/**
		 * Custom scale with upscale
		 *
		 * @param $new_w
		 * @param $new_h
		 *
		 * @return bool|true|WP_Error
		 */
		public function scale( $new_w, $new_h ) {
			$thumb_result = $this->thumbnail_image( $new_w, $new_h );
			if ( is_wp_error( $thumb_result ) ) {
				return $thumb_result;
			}

			return $this->update_size( $new_w, $new_h );
		}

		public function rounded_corners( $action ) {
			return $this->image->roundCorners( $action['radius'], $action['radius'] );
		}

		/**
		 * Add flip(vertical) effect
		 *
		 * @return bool
		 */
		public function true_flip() {
			return $this->image->flipImage();
		}

		/**
		 * Add flop(horizontal) effect
		 *
		 * @return bool
		 */
		public function true_flop() {
			return $this->image->flopImage();
		}

		/**
		 * Overload make_subsize function for plugin effects support
		 *
		 * @param array $size_data
		 *
		 * @return array|WP_Error
		 */
		public function make_subsize( $size_data ) {
			if ( ! isset( $size_data['wpdi_affected'] ) ) {
				$saved = parent::make_subsize( $size_data );
			} else {
				$file  = WPDI_Common::get_relative_file_path( $this->file );
				$saved = WPDI_Common::make_preset( $size_data['wpdi_name'], $file );
			}

			return $saved;
		}
	}
}