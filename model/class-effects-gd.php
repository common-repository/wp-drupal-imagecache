<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPDI_Effects_GD' ) ) {

	class WPDI_Effects_GD extends WP_Image_Editor_GD {
		/**
		 * Overload WP rotate function to add background color support
		 * Rotates current image counter-clockwise by $angle.
		 *
		 * @param float $angle
		 * @param null|string $background
		 *
		 * @return true|WP_Error
		 */
		public function rotate( $angle, $background = null ) {
			if ( function_exists( 'imagerotate' ) ) {
				// Convert the hexadecimal background value to a RGBA array.
				if ( isset( $background ) ) {
					$background = strtolower( $background );
					list($r, $g, $b) = sscanf($background, "#%02x%02x%02x");
					$background = array(
						'red'   => $r,
						'green' => $g,
						'blue'  => $b,
						'alpha' => 0,
					);
				} else {
					// Background color is not specified: use transparent white as background.
					$background = array(
						'red'   => 255,
						'green' => 255,
						'blue'  => 255,
						'alpha' => 127
					);
				}

				$transparency = imagecolorallocatealpha( $this->image, $background['red'], $background['green'], $background['blue'], $background['alpha'] );
				$rotated      = imagerotate( $this->image, $angle, $transparency );

				if ( is_resource( $rotated ) ) {
					imagealphablending( $rotated, true );
					imagesavealpha( $rotated, true );
					imagedestroy( $this->image );
					$this->image = $rotated;
					$this->update_size();

					return true;
				}
			}

			return new WP_Error( 'image_rotate_error', __( 'Image rotate failed.' ), $this->file );
		}

		/**
		 * Add desaturate effect
		 *
		 * @return bool
		 */
		public function desaturate() {
			return imagefilter( $this->image, IMG_FILTER_GRAYSCALE );
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
			$size = @getimagesize( $this->file );
			$ext  = pathinfo( $this->file, PATHINFO_EXTENSION );
			$res  = $this->image_gd_create_tmp( $width, $height, $ext );

			if ( ! imagecopyresampled( $res, $this->image, 0, 0, 0, 0, $width, $height, $size[0], $size[1] ) ) {
				return false;
			}

			imagedestroy( $this->image );
			// Update image object.
			$this->image = $res;

			return true;
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
			$resized = wp_imagecreatetruecolor( $new_w, $new_h );
			imagecopyresampled( $resized, $this->image, 0, 0, 0, 0, $new_w, $new_h, $this->size['width'], $this->size['height'] );

			if ( is_resource( $resized ) ) {
				imagedestroy( $this->image );
				$this->image = $resized;

				return true;

			} elseif ( is_wp_error( $resized ) ) {
				/* @var $resized WP_Error */
				return $resized;
			}

			return new WP_Error( 'image_resize_error', __( 'Image resize failed.' ), $this->file );
		}

		public function custom_update_size() {
			return parent::update_size();
		}

		/**
		 * Trim rounded corners off an image, using an anti-aliasing algorithm.
		 *
		 * Implementation of hook_image()
		 *
		 * Note, this is not image toolkit-agnostic yet! It just assumes GD.
		 * We can abstract it out once we have something else to abstract to.
		 * In the meantime just don't.
		 *
		 * @param $action
		 *
		 * @return bool
		 */
		public function rounded_corners( $action ) {
			$size = @getimagesize( $this->file );

			// Read settings.
			$width   = $size[0];
			$height  = $size[1];
			$corners = array( 'tl', 'tr', 'bl', 'br' );

			$im = &$this->image;
			// Prepare drawing on the alpha channel.
			imagesavealpha( $im, true );
			imagealphablending( $im, false );

			foreach ( $corners as $key ) {
				$r = $action[ $key ];

				// key can be 'tl', 'tr', 'bl', 'br'.
				$is_bottom = ( substr( $key, 0, 1 ) == 'b' );
				$is_right  = ( substr( $key, 0, 1 ) == 'r' );

				// dx and dy are in "continuous coordinates",
				// and mark the distance of the pixel middle to the image border.
				for ( $dx = .5; $dx < $r; ++ $dx ) {
					for ( $dy = .5; $dy < $r; ++ $dy ) {

						// ix and iy are in discrete pixel indices,
						// counting from the top left
						$ix = floor( $is_right ? $width - $dx : $dx );
						$iy = floor( $is_bottom ? $height - $dy : $dy );

						// Color lookup at ($ix, $iy).
						$color_ix = imagecolorat( $im, $ix, $iy );
						$color    = imagecolorsforindex( $im, $color_ix );


						// Do not process opacity if transparency is 100%. Just jump...
						// Opacity is always 0 on a transparent source pixel.
						if ( $color['alpha'] != 127 ) {
							$opacity = $this->canvasactions_roundedcorners_pixel_opacity( $dx, $dy, $r );
							if ( $opacity >= 1 ) {
								// we can finish this row,
								// all following pixels will be fully opaque.
								break;
							}


							if ( isset( $color['alpha'] ) ) {
								$color['alpha'] = 127 - round( $opacity * ( 127 - $color['alpha'] ) );
							} else {
								$color['alpha'] = 127 - round( $opacity * 127 );
							}
							// Value should not be more than 127, and not less than 0.
							$color['alpha'] = ( $color['alpha'] > 127 ) ? 127 : ( ( $color['alpha'] < 0 ) ? 0 : $color['alpha'] );
						}

						$color_ix = imagecolorallocatealpha( $im, $color['red'], $color['green'], $color['blue'], $color['alpha'] );
						imagesetpixel( $im, $ix, $iy, $color_ix );
					}
				}
			}

			return true;
		}

		/**
		 * Add flip(vertical) effect
		 *
		 * @return bool
		 */
		public function true_flip() {
			return (bool) imageflip( $this->image, IMG_FLIP_HORIZONTAL );
		}

		/**
		 * Add flop(horizontal) effect
		 *
		 * @return bool
		 */
		public function true_flop() {
			return (bool) imageflip( $this->image, IMG_FLIP_VERTICAL );
		}

		/**
		 * Create a truecolor image preserving transparency from a provided image.
		 *
		 * @param int $width The new width of the new image, in pixels.
		 * @param int $height The new height of the new image, in pixels.
		 * @param string $ext Image extention
		 *
		 * @return false|resource A GD image handle.
		 *   A GD image handle.
		 */
		public function image_gd_create_tmp( $width, $height, $ext ) {
			$res = imagecreatetruecolor( $width, $height );

			if ( $ext == 'gif' ) {
				// Find out if a transparent color is set, will return -1 if no
				// transparent color has been defined in the image.
				$transparent = imagecolortransparent( $this->image );

				if ( $transparent >= 0 ) {
					// Find out the number of colors in the image palette. It will be 0 for
					// truecolor images.
					$palette_size = imagecolorstotal( $this->image );
					if ( $palette_size == 0 || $transparent < $palette_size ) {
						// Set the transparent color in the new resource, either if it is a
						// truecolor image or if the transparent color is part of the palette.
						// Since the index of the transparency color is a property of the
						// image rather than of the palette, it is possible that an image
						// could be created with this index set outside the palette size (see
						// http://stackoverflow.com/a/3898007).
						$transparent_color = imagecolorsforindex( $this->image, $transparent );
						$transparent       = imagecolorallocate( $res, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'] );

						// Flood with our new transparent color.
						imagefill( $res, 0, 0, $transparent );
						imagecolortransparent( $res, $transparent );
					} else {
						imagefill( $res, 0, 0, imagecolorallocate( $res, 255, 255, 255 ) );
					}
				}
			} elseif ( $ext == 'png' ) {
				imagealphablending( $res, false );
				$transparency = imagecolorallocatealpha( $res, 0, 0, 0, 127 );
				imagefill( $res, 0, 0, $transparency );
				imagealphablending( $res, true );
				imagesavealpha( $res, true );
			} else {
				imagefill( $res, 0, 0, imagecolorallocate( $res, 255, 255, 255 ) );
			}

			return $res;
		}

		/**
		 * Calculate the transparency value for a rounded corner pixel
		 *
		 * @param $x
		 *   distance from pixel center to image border (left or right)
		 *   should be an integer + 0.5
		 *
		 * @param $y
		 *   distance from pixel center to image border (top or bottom)
		 *   should be an integer + 0.5
		 *
		 * @param $r
		 *   radius of the rounded corner
		 *   should be an integer
		 *
		 * @return float
		 *   opacity value between 0 (fully transparent) and 1 (fully opaque).
		 *
		 * OPTIMIZE HERE! This is a really tight loop, potentially getting called
		 * thousands of times
		 */
		public function canvasactions_roundedcorners_pixel_opacity( $x, $y, $r ) {
			if ( $x < 0 || $y < 0 ) {
				return 0;
			} else if ( $x > $r || $y > $r ) {
				return 1;
			}
			$dist_2 = ( $r - $x ) * ( $r - $x ) + ( $r - $y ) * ( $r - $y );
			$r_2    = $r * $r;
			if ( $dist_2 > ( $r + 0.8 ) * ( $r + 0.8 ) ) {
				return 0;
			} else if ( $dist_2 < ( $r - 0.8 ) * ( $r - 0.8 ) ) {
				return 1;
			} else {
				// this pixel needs special analysis.
				// thanks to a quite efficient algorithm, we can afford 10x antialiasing :)
				$opacity = 0.5;
				if ( $x > $y ) {
					// cut the pixel into 10 vertical "stripes"
					for ( $dx = - 0.45; $dx < 0.5; $dx += 0.1 ) {
						// find out where the rounded corner edge intersects with the stripe
						// this is plain triangle geometry.
						$dy = $r - $y - sqrt( $r_2 - ( $r - $x - $dx ) * ( $r - $x - $dx ) );
						$dy = ( $dy > 0.5 ) ? 0.5 : ( ( $dy < - 0.5 ) ? - 0.5 : $dy );
						// count the opaque part of the stripe.
						$opacity -= 0.1 * $dy;
					}
				} else {
					// cut the pixel into 10 horizontal "stripes"
					for ( $dy = - 0.45; $dy < 0.5; $dy += 0.1 ) {
						// this is the math:
						//   ($r-$x-$dx)^2 + ($r-$y-$dy)^2 = $r^2
						//   $dx = $r - $x - sqrt($r^2 - ($r-$y-$dy)^2)
						$dx      = $r - $x - sqrt( $r_2 - ( $r - $y - $dy ) * ( $r - $y - $dy ) );
						$dx      = ( $dx > 0.5 ) ? 0.5 : ( ( $dx < - 0.5 ) ? - 0.5 : $dx );
						$opacity -= 0.1 * $dx;
					}
				}

				return ( $opacity < 0 ) ? 0 : ( ( $opacity > 1 ) ? 1 : $opacity );
			}
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