<?php
/**
 * All plugin validators here
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPDI_Validators' ) ) {

	class WPDI_Validators {

		/**
		 * Validator for preset edit fields
		 *
		 * @return array
		 */
		public static function validate_edit_fields() {
			if ( empty( $_POST ) ) {
				return array();
			}

			$available = array(
				'wpdi_add_effect_submit',
				'wpdi_update_preset_submit',
				'wpdi_effect',
				'wpdi_name'
			);

			$result = array();

			foreach ( $_POST as $name => $value ) {
				$name = sanitize_text_field( $name );
				if ( ! in_array( $name, $available ) ) {
					continue;
				}

				if ( ! is_string( $value ) ) {
					continue;
				}

				$result[ $name ] = sanitize_text_field( $value );
			}

			return $result;
		}

		/**
		 * Validator for add/edit effect form fields
		 *
		 * @return array
		 */
		public static function validate_effects_fields() {
			if ( empty( $_POST ) ) {
				return array();
			}

			$available = array(
				'wpdi_name',
				'wpdi_effect',
				'wpdi_add_effect_submit',
				'wpdi_effect_width',
				'wpdi_effect_height',
				'wpdi_effect_upscale',
				'wpdi_effect_arrangement',
				'wpdi_effect_randomize',
				'wpdi_effect_bg_color',
				'wpdi_effect_angle',
				'wpdi_effect_arrangement',
				'wpdi_effect_independent_corners_tl',
				'wpdi_effect_independent_corners_tr',
				'wpdi_effect_independent_corners_bl',
				'wpdi_effect_independent_corners_br',
				'wpdi_effect_radius',
				'wpdi_effect_direction',
			);

			$arrangements = array(
				'left-top',
				'center-top',
				'right-top',
				'left-center',
				'center-center',
				'right-center',
				'left-bottom',
				'center-bottom',
				'right-bottom'
			);

			$result = array();

			foreach ( $_POST as $name => $value ) {
				$name = sanitize_text_field( $name );
				if ( ! in_array( $name, $available ) ) {
					continue;
				}

				if ( ! is_string( $value ) ) {
					continue;
				}

				if ( isset( $_POST['wpdi_effect'] ) &&
				     in_array( $_POST['wpdi_effect'], array( 'image_crop', 'image_scale_and_crop' ) ) &&
				     ! isset( $_POST['wpdi_effect_arrangement'] )
				) {
					$_POST['wpdi_effect_arrangement'] = 'center-center';
				}

				switch ( $name ) {
					case 'wpdi_effect_arrangement':
						$value = sanitize_text_field( $value );
						if ( ! in_array( $value, $arrangements ) ) {
							$result['wpdi_effect_arrangement'] = 'center-center';
						} else {
							$result[ $name ] = $value;
						}
						break;

					case 'wpdi_effect_direction':
						if ( ! in_array( $value, [ 'flip', 'flop' ] ) ) {
							$value = 'flip';
						}
						$result[ $name ] = $value;
						break;

					case 'wpdi_effect_randomize':
					case 'wpdi_effect_upscale':
						$value = self::clear_digits( $value );
						if ( 1 !== $value ) {
							$value = 0;
						}
						$result[ $name ] = $value;
						break;

					case 'wp-drupal-imagecache':
						$result['wp-drupal-imagecache'] = 1;
						break;

					case 'wpdi_effect_bg_color':
						$value                          = preg_replace( '@[^#0-9a-fA-F]+@', '', $value );
						$result['wpdi_effect_bg_color'] = $value;
						break;

					case 'wpdi_effect_angle':
						$value                       = preg_replace( '@[^\.,0-9\-]+@', '', $value );
						$value                       = number_format( $value, 2, '.', '' );
						$result['wpdi_effect_angle'] = $value;
						break;

					case 'wpdi_effect_width':
					case 'wpdi_effect_height':
					case 'wpdi_effect_independent_corners_tl':
					case 'wpdi_effect_independent_corners_tr':
					case 'wpdi_effect_independent_corners_bl':
					case 'wpdi_effect_independent_corners_br':
					case 'wpdi_effect_radius':
						$result[ $name ] = self::clear_digits( $value );
						break;

					default:
						$result[ $name ] = sanitize_text_field( $value );
						break;
				}
			}

			return $result;
		}

		/**
		 * validator for add preset
		 *
		 * @return array
		 */
		public static function validate_add_preset_fields() {
			if ( ! isset( $_POST['wpdi_name'] ) ) {
				return array();
			}

			return array(
				'wpdi_name' => sanitize_text_field( $_POST['wpdi_name'] )
			);
		}

		/**
		 * Get and validate errors messages from $_REQUEST
		 *
		 * @return array
		 */
		public static function get_errors() {
			if ( empty( $_REQUEST['error'] ) ) {
				return array();
			}

			$mgs = array( urldecode( $_REQUEST['error'] ) );
			$mgs = array_map( 'sanitize_text_field', $mgs );

			return $mgs;
		}

		/**
		 * Get and validate success messages from $_REQUEST
		 *
		 * @return array
		 */
		public static function get_success() {
			if ( empty( $_REQUEST['success'] ) ) {
				return array();
			}

			$mgs = array( urldecode( $_REQUEST['success'] ) );
			$mgs = array_map( 'sanitize_text_field', $mgs );

			return $mgs;
		}

		/**
		 * Get and validate thumb name from $_REQUEST
		 *
		 * @return string
		 */
		public static function get_thumb() {
			return sanitize_text_field( $_REQUEST['thumb'] );
		}

		/**
		 * Get and validate effect name from $_REQUEST
		 *
		 * @return string
		 */
		public static function get_effect() {
			return sanitize_text_field( $_REQUEST['effect'] );
		}

		/**
		 * Get and validate effect_key name from $_REQUEST
		 *
		 * @return string
		 */
		public static function get_effect_key() {
			return sanitize_text_field( $_REQUEST['effect_key'] );
		}

		/**
		 * Sanitize string to integer value
		 *
		 * @param $text
		 *
		 * @return int
		 */
		public static function clear_digits( $text ) {
			return intval( preg_replace( '@[^\d]+@si', '', $text ) );
		}
	}
}
