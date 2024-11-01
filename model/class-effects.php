<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPDI_Effects' ) ) {

	class WPDI_Effects {
		private $handler;
		private $image;

		public function __construct( $image_path ) {
			if ( ! $this->image_load( $image_path ) ) {
				$this->add_error( 'Can`t load image' );
			}

			$this->handler = wp_get_image_editor( $image_path );

			return $this->handler;
		}

		public function is_correct_handler() {
			$class = get_class( $this->handler );

			return in_array( $class, array( 'WPDI_Effects_Imagick', 'WPDI_Effects_GD' ) );
		}

		public function get_image() {
			return $this->image;
		}

		public function image_load( $image_path ) {

			if ( ! is_file( $image_path ) ) {
				$this->add_error( 'Image file does not exists' );
			}

			$image = array(
				'source'    => $image_path,
				'file_size' => filesize( $image_path )
			);

			$data = @getimagesize( $image['source'] );

			if ( ! isset( $data ) || ! is_array( $data ) ) {
				$this->add_error( 'Bad image' );
			}

			$extensions         = array( '1' => 'gif', '2' => 'jpg', '3' => 'png' );
			$extension          = isset( $extensions[ $data[2] ] ) ? $extensions[ $data[2] ] : '';
			$image['width']     = $data[0];
			$image['height']    = $data[1];
			$image['extension'] = $extension;
			$image['mime_type'] = $data['mime'];

			$this->image = $image;

			return true;
		}

		/**
		 * @return array|WP_Error
		 */
		public function image_save() {
			return $this->handler->save();
		}

		/**
		 * Crop image
		 *
		 * @param $arrangement
		 * @param $width
		 * @param $height
		 *
		 * @return bool|WP_Error
		 */
		public function image_crop( $arrangement, $width, $height ) {
			list( $x, $y ) = explode( '-', $arrangement );
			$size = $this->handler->get_size();
			$x    = $this->image_filter_keyword( $x, $size['width'], $width );
			$y    = $this->image_filter_keyword( $y, $size['height'], $height );

			return $this->handler->crop( $x, $y, $width, $height );
		}

		/**
		 * Desaturate image
		 *
		 * @return bool
		 */
		public function image_desaturate() {
			return $this->handler->desaturate();
		}

		/**
		 * Resize image
		 *
		 * @param $width
		 * @param $height
		 *
		 * @return bool|WP_Error
		 */
		public function image_resize( $width, $height ) {
			return $this->handler->true_resize( $width, $height );
		}

		public function custom_update_size() {
			return $this->handler->custom_update_size();
		}

		/**
		 * Rotate image
		 *
		 * @param $degrees
		 * @param null|string $background
		 *
		 * @return bool|WP_Error
		 */
		public function image_rotate( $degrees, $background = null ) {
			if ( ! empty( $background ) ) {
				return $this->handler->rotate( $degrees, $background );
			}

			return $this->handler->rotate( $degrees );
		}

		/**
		 * Scale image
		 *
		 * @param null $width
		 * @param null $height
		 * @param bool $upscale
		 *
		 * @return bool|WP_Error
		 */
		public function image_scale( $width = null, $height = null, $upscale = false ) {
			// Scale the dimensions - if they don't change then just return success.
			if ( ! $dimensions = $this->image_dimensions_scale( $width, $height, $upscale ) ) {
				return true;
			}

			return $this->handler->scale( $dimensions['width'], $dimensions['height'] );
		}

		/**
		 * Scale image and crop
		 *
		 * @param $arrangement
		 * @param $width
		 * @param $height
		 *
		 * @return mixed
		 */
		public function image_scale_and_crop( $arrangement, $width, $height ) {
			$size = $this->handler->get_size();

			$scale = max( $width / $size['width'], $height / $size['height'] );

			if ( $this->handler->resize( $size['width'] * $scale, $size['height'] * $scale ) ) {
				list( $x, $y ) = explode( '-', $arrangement );
				$x = $this->image_filter_keyword( $x, $size['width'] * $scale, $width );
				$y = $this->image_filter_keyword( $y, $size['height'] * $scale, $height );

				return $this->handler->crop( $x, $y, $width, $height );
			}

			return true;
		}

		/**
		 * Create rounded colors on image
		 *
		 * @param $radius
		 * @param array|bool $independent_corners
		 *
		 * @return mixed
		 */
		public function image_round_corners( $radius, $independent_corners = [] ) {
			// set the independent corners to all be the same.
			$corners = array( 'tl', 'tr', 'bl', 'br' );
			if ( empty( $radius ) ) {
				$radius = 0;
			}

			$action = [
				'radius' => $radius
			];

			foreach ( $corners as $key ) {
				// Use the all-the-same radius setting.
				$action[ $key ] = ! empty( $independent_corners[ $key ] ) ? $independent_corners[ $key ] : $radius;
			}

			return $this->handler->rounded_corners( $action );
		}

		/**
		 * Create flip o or flop on image
		 *
		 * @param string $direction
		 *
		 * @return mixed
		 */
		public function image_flip_flop( $direction ) {
			if ( $direction == 'flip' ) {
				return $this->handler->true_flip();
			}

			return $this->handler->true_flop();
		}

		/**
		 * Calculate sizes from arrangement
		 *
		 * @param $value
		 * @param $current_pixels
		 * @param $new_pixels
		 *
		 * @return float|int
		 */
		public function image_filter_keyword( $value, $current_pixels, $new_pixels ) {
			switch ( $value ) {
				case 'top':
				case 'left':
					return 0;

				case 'bottom':
				case 'right':
					return $current_pixels - $new_pixels;

				case 'center':
					return $current_pixels / 2 - $new_pixels / 2;
			}

			return $value;
		}

		/**
		 * Scales image dimensions while maintaining aspect ratio.
		 *
		 * The resulting dimensions can be smaller for one or both target dimensions.
		 *
		 * @param $width
		 *   The target width, in pixels. If this value is NULL then the scaling will be
		 *   based only on the height value.
		 * @param $height
		 *   The target height, in pixels. If this value is NULL then the scaling will
		 *   be based only on the width value.
		 * @param $upscale
		 *   Boolean indicating that images smaller than the target dimensions will be
		 *   scaled up. This generally results in a low quality image.
		 *
		 * @return array|bool
		 *   TRUE if $dimensions was modified, FALSE otherwise.
		 *
		 * @see image_scale()
		 */
		public function image_dimensions_scale( $width = null, $height = null, $upscale = false ) {
			$dimensions = $this->handler->get_size();
			$aspect     = $dimensions['height'] / $dimensions['width'];

			// Calculate one of the dimensions from the other target dimension,
			// ensuring the same aspect ratio as the source dimensions. If one of the
			// target dimensions is missing, that is the one that is calculated. If both
			// are specified then the dimension calculated is the one that would not be
			// calculated to be bigger than its target.
			if ( ( $width && ! $height ) || ( $width && $height && $aspect < $height / $width ) ) {
				$height = (int) round( $width * $aspect );
			} else {
				$width = (int) round( $height / $aspect );
			}

			// Don't upscale if the option isn't enabled.
			if ( ! $upscale && ( $width >= $dimensions['width'] || $height >= $dimensions['height'] ) ) {
				return false;
			}

			$dimensions['width']  = $width;
			$dimensions['height'] = $height;

			return $dimensions;
		}

		public function add_error( $msg ) {
			wp_safe_redirect( WPDI_Common::build_plugin_url( array(
				'error' => urlencode( __( $msg, WPDI_Common::PLUGIN_SYSTEM_NAME ) )
			) ) );
			exit();
		}

	}
}