<?php
/**
 * Plugin hooks implementation
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPDI_Hooks' ) ) {

	class WPDI_Hooks {

		/**
		 * Load overloaded image editors instead of core through Wordpress hook 'wp_image_editors'
		 *
		 * @return array
		 */
		public static function wp_image_editors() {

			return array( 'WPDI_Effects_Imagick', 'WPDI_Effects_GD' );
		}

		/**
		 * Add extra parameters to sizes data array for plugin needs
		 * using Wordpress hook 'intermediate_image_sizes_advanced'
		 *
		 * @param array $new_sizes
		 * @param array $image_meta
		 * @param int $attachment_id
		 *
		 * @return array
		 */
		public static function intermediate_image_sizes_advanced( $new_sizes, $image_meta = array(), $attachment_id = null ) {
			$presets_affected_plugin       = get_option( 'wpdi_presets', array() );
			$presets_affected_plugin_names = array_keys( $presets_affected_plugin );
			$new_sizes_names               = array_keys( $new_sizes );
			$internal                      = WPDI_Common::get_internal_presets();
			$new_sizes_names               = array_merge( $new_sizes_names, array_keys( $internal ) );

			foreach ( $new_sizes_names as $image_type ) {
				$new_sizes[ $image_type ]['wpdi_name']          = $image_type;
				$new_sizes[ $image_type ]['wpdi_attachment_id'] = $attachment_id;

				if ( in_array( $image_type, $presets_affected_plugin_names ) &&
				     isset( $presets_affected_plugin[ $image_type ]['affected'] ) && // only affected got name and attachment id
				     $presets_affected_plugin[ $image_type ]['affected'] && // only affected got name and attachment id
				     ( ! isset( $presets_affected_plugin[ $image_type ]['disabled'] ) or // exclude disabled
				       ! $presets_affected_plugin[ $image_type ]['disabled'] ) ) { // exclude disabled
					$new_sizes[ $image_type ]['wpdi_affected'] = true;
				}
			}

			return $new_sizes;
		}

		/**
		 * Skip disabled in plugin GUI image sizes from subsizes list using Wordpress hook 'intermediate_image_sizes'
		 *
		 * @param array $default_sizes
		 *
		 * @return array
		 */
		public static function intermediate_image_sizes( $default_sizes ) {
			$presets_affected_plugin = get_option( 'wpdi_presets' );

			$internal      = WPDI_Common::get_internal_presets();
			$default_sizes = array_merge( $default_sizes, array_keys( $internal ) );

			if ( ! empty( $presets_affected_plugin ) ) {
				$presets_affected_plugin_names = array_keys( $presets_affected_plugin );
				foreach ( $default_sizes as $image_type ) {
					if ( in_array( $image_type, $presets_affected_plugin_names ) ) {
						if ( isset( $presets_affected_plugin[ $image_type ] ) && $presets_affected_plugin[ $image_type ]['disabled'] ) {
							unset( $default_sizes[ array_search( $image_type, $default_sizes ) ] );
						}
					}
				}
			}

			return $default_sizes;
		}

		/**
		 * Do not allow Woocommerce to re-create images that was handled by plugin with hook 'woocommerce_image_sizes_to_resize'
		 *
		 * @param array $resize_thumbs
		 *
		 * @return array
		 */
		public static function woocommerce_image_sizes_to_resize( $resize_thumbs ) {
			$presets_affected_plugin = get_option( 'wpdi_presets' );

			if ( ! empty( $presets_affected_plugin ) ) {
				$presets_affected_plugin_names = array_keys( $presets_affected_plugin );
				foreach ( $resize_thumbs as $image_type ) {
					if ( in_array( $image_type, $presets_affected_plugin_names ) &&
					     isset( $presets_affected_plugin[ $image_type ]['affected'] ) &&
					     $presets_affected_plugin[ $image_type ]['affected'] ) {
						unset( $resize_thumbs[ array_search( $image_type, $resize_thumbs ) ] );
					}
				}
			}

			return $resize_thumbs;
		}

		/**
		 * Return correct preset size for latter usage
		 *
		 * @param $downsize
		 * @param $attachment_id
		 * @param $size
		 *
		 * @return array|bool
		 */
		public static function image_downsize( $downsize, $attachment_id, $size ) {
			$presets_affected_plugin = get_option( 'wpdi_presets' );

			if ( is_array( $size ) ) {
				return false;
			}

			if ( ! isset( $presets_affected_plugin[ $size ]['affected'] ) or ! $presets_affected_plugin[ $size ]['affected'] ) {
				return false;
			}

			if ( isset( $presets_affected_plugin[ $size ]['disabled'] ) && $presets_affected_plugin[ $size ]['disabled'] ) {
				return false;
			}

			$meta = wp_get_attachment_metadata( $attachment_id );
			if ( ! isset( $meta['sizes'][ $size ] ) ) {
				return false;
			}

			$meta_size = $meta['sizes'][ $size ];
			$uploads   = wp_get_upload_dir();
			$dir       = pathinfo( $meta['file'], PATHINFO_DIRNAME );
			$url       = implode( '/', array( $uploads['baseurl'], $dir, $meta_size['file'] ) );

			return array( $url, $meta_size['width'], $meta_size['height'], $downsize );
		}

		/**
		 * Hook size threshold to avoid scaled suffix
		 *
		 * @param $threshold
		 * @param $imagesize
		 * @param $file
		 * @param $attachment_id
		 *
		 * @return bool
		 */
		public static function big_image_size_threshold( $threshold, $imagesize, $file, $attachment_id ) {
			return false;
		}

		/**
		 * Hook rotation requirement to avoid rotated suffix
		 * Please rotate with plugin effect
		 *
		 * @param $orientation
		 * @param $file
		 *
		 * @return bool
		 */
		public static function wp_image_maybe_exif_rotate( $orientation, $file ) {
			return false;
		}

		/**
		 * Register settings page for manipulating thumbnails
		 */
		public static function register_settings_pages() {
			add_submenu_page(
				'options-general.php',
				__( 'Thumbnails like in Drupal', WPDI_Common::PLUGIN_SYSTEM_NAME ) . ' ' . __( 'Settings', WPDI_Common::PLUGIN_SYSTEM_NAME ),
				__( 'Thumbnails like in Drupal', WPDI_Common::PLUGIN_SYSTEM_NAME ),
				'administrator',
				WPDI_Common::PLUGIN_SYSTEM_NAME,
				__CLASS__ . '::markup_settings_page'
			);
		}

		/**
		 * Markup for all plugin pages
		 */
		public static function markup_settings_page() {
			$action = WPDI_Common::get_action();

			switch ( $action ) {
				case 'add_preset':
					if ( ! empty( $_POST ) ) {
						$post_data = WPDI_Validators::validate_add_preset_fields();
						if ( ! empty( $post_data['wpdi_name'] ) ) {
							$preset = array(
								'wpdi_name' => $post_data['wpdi_name'],
								'effects'   => array(),
								'internal'  => true
							);
							update_option( 'wpdi_preset_' . $post_data['wpdi_name'], $preset );
							WPDI_Common::update_handled( $preset );
							wp_safe_redirect( WPDI_Common::build_plugin_url( array(
								'success' => __( 'Preset successfully added', WPDI_Common::PLUGIN_SYSTEM_NAME )
							) ) );
							exit();
						} else {
							wp_safe_redirect( WPDI_Common::build_plugin_url( array(
								'error' => urlencode( __( 'Please fill new preset name', WPDI_Common::PLUGIN_SYSTEM_NAME ) )
							) ) );
							exit();
						}
					}

					wp_safe_redirect( WPDI_Common::build_plugin_url( array(
						'error' => urlencode( __( 'Forbidden action', WPDI_Common::PLUGIN_SYSTEM_NAME ) )
					) ) );

					break;

				case 'edit': // action where you can add new effect to preset or rename preset in internal
					$thumb = WPDI_Validators::get_thumb();

					$success = false;
					$err     = false;

					if ( ! empty( $_POST ) ) {
						$post_data = WPDI_Validators::validate_edit_fields();
						$preset    = WPDI_Common::get_preset( $thumb );

						// show form if adding new effect with _POST
						if ( ! empty( $post_data['wpdi_effect'] ) && isset( $post_data['wpdi_add_effect_submit'] ) ) {
							if ( 'image_desaturate' == $post_data['wpdi_effect'] ) {
								$preset['effects'][] = $post_data;
								$post_data           = array();
								$success             = __( 'Effect successfully added', WPDI_Common::PLUGIN_SYSTEM_NAME );
							} else {
								echo WPDI_Common::render( 'effect_form/' . $post_data['wpdi_effect'], array(
									'action' => 'add_effect',
									'preset' => $preset,
									'effect' => array(
										'wpdi_effect' => $post_data['wpdi_effect']
									)
								) );
								exit;
							}
						}
						if ( isset( $post_data['wpdi_update_preset_submit'] ) ) { // rename internal
							if ( isset( $preset['internal'] ) && $preset['internal'] ) {
								delete_option( 'wpdi_preset_' . $thumb );
								WPDI_Common::update_handled( $preset, true );
								$thumb   = $post_data['wpdi_name'];
								$success = __( 'Preset successfully updated', WPDI_Common::PLUGIN_SYSTEM_NAME );
							}
						}

						// save changes if not new effect
						$preset = array_merge( $preset, $post_data );
						update_option( 'wpdi_preset_' . $thumb, $preset );
						$err = WPDI_Common::update_preview( $preset );
					}

					$preset = WPDI_Common::get_preset( $thumb );

					$preview = self::calc_preview_array( $preset );
					if ( ! $preview ) {
						$err = __( 'Can`t handle preview, check system errors!', WPDI_Common::PLUGIN_SYSTEM_NAME );
					}
					$effects = apply_filters( 'wpdi_get_available_effects', array(
						'image_crop'           => __( 'Crop', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_desaturate'     => __( 'Desaturate', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_resize'         => __( 'Resize', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_rotate'         => __( 'Rotate', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_scale'          => __( 'Scale', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_scale_and_crop' => __( 'Scale and crop', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_round_corners'  => __( 'Round corners', WPDI_Common::PLUGIN_SYSTEM_NAME ),
						'image_flip_flop'      => __( 'Flip & Flop (reflect image)', WPDI_Common::PLUGIN_SYSTEM_NAME ),
					) );

					$response = array(
						'preset'  => $preset,
						'preview' => $preview,
						'effects' => $effects,
						'success' => $success,
						'error'   => $err
					);
					if ( $err ) {
						$response['error'] = $err;
					} else {
						$response['success'] = $success;
					}
					echo WPDI_Common::render( 'edit-preset', $response );

					break;

				case 'add_effect': // _POST only!
					if ( empty( $_POST ) ) {
						wp_safe_redirect( WPDI_Common::build_plugin_url( array(
							'error' => urlencode( __( 'You can`t do this action', WPDI_Common::PLUGIN_SYSTEM_NAME ) )
						) ) );
						exit();
					}
					$thumb     = WPDI_Validators::get_thumb();
					$post_data = WPDI_Validators::validate_effects_fields();
					$preset    = WPDI_Common::get_preset( $thumb );

					$err = false;

					if ( ! empty( $post_data ) ) {
						$preset['effects'][] = $post_data;
						update_option( 'wpdi_preset_' . $thumb, $preset );
						$err = WPDI_Common::update_preview( $preset );
					}
					$response = array(
						'action' => 'edit',
						'thumb'  => $thumb,
					);
					if ( $err ) {
						$response['error'] = __( $err->get_error_message(), WPDI_Common::PLUGIN_SYSTEM_NAME );
					} else {
						$response['success'] = __( 'Effect successfully added', WPDI_Common::PLUGIN_SYSTEM_NAME );
					}

					wp_safe_redirect( WPDI_Common::build_plugin_url( $response ) );

					break;

				case 'edit_effect':
					$thumb       = WPDI_Validators::get_thumb();
					$effect_name = WPDI_Validators::get_effect();
					$effect_key  = WPDI_Validators::get_effect_key();
					$preset      = WPDI_Common::get_preset( $thumb );
					$effect      = $preset['effects'][ $effect_key ];

					if ( ! empty( $_POST ) ) {
						$post_data = WPDI_Validators::validate_effects_fields();
						if ( $effect_name == $effect['wpdi_effect'] ) {
							$preset['effects'][ $effect_key ] = $post_data;
						}
						update_option( 'wpdi_preset_' . $thumb, $preset );
						$err = WPDI_Common::update_preview( $preset );

						$response = array(
							'action' => 'edit',
							'thumb'  => $thumb,
						);
						if ( $err ) {
							$response['error'] = __( $err->get_error_message(), WPDI_Common::PLUGIN_SYSTEM_NAME );
						} else {
							$response['success'] = __( 'Effect successfully updated', WPDI_Common::PLUGIN_SYSTEM_NAME );
						}

						wp_safe_redirect( WPDI_Common::build_plugin_url( $response ) );

						break;
					}
					echo WPDI_Common::render( 'effect_form/' . $effect_name, array(
						'action'     => 'edit_effect',
						'effect'     => $effect,
						'effect_key' => $effect_key,
						'preset'     => $preset
					) );

					break;

				case 'delete_effect':
					$thumb       = WPDI_Validators::get_thumb();
					$effect_name = WPDI_Validators::get_effect();
					$effect_key  = WPDI_Validators::get_effect_key();
					$preset      = WPDI_Common::get_preset( $thumb );

					if ( $effect_name == $preset['effects'][ $effect_key ]['wpdi_effect'] ) {
						unset( $preset['effects'][ $effect_key ] );
					}

					update_option( 'wpdi_preset_' . $thumb, $preset );
					$err = WPDI_Common::update_preview( $preset );

					$response = array(
						'action' => 'edit',
						'thumb'  => $thumb,
					);
					if ( $err ) {
						$response['error'] = __( $err->get_error_message(), WPDI_Common::PLUGIN_SYSTEM_NAME );
					} else {
						$response['success'] = __( 'Effect successfully deleted', WPDI_Common::PLUGIN_SYSTEM_NAME );
					}

					wp_safe_redirect( WPDI_Common::build_plugin_url( $response ) );

					break;

				case 'disable':
					$thumb  = WPDI_Validators::get_thumb();
					$preset = WPDI_Common::get_preset( $thumb );

					WPDI_Common::update_handled( array_merge( $preset, array( 'disabled' => true ) ) );

					wp_safe_redirect( WPDI_Common::build_plugin_url( array(
						'success' => __( 'Preset successfully disabled', WPDI_Common::PLUGIN_SYSTEM_NAME )
					) ) );

					break;

				case 'enable':
					$thumb  = WPDI_Validators::get_thumb();
					$preset = WPDI_Common::get_preset( $thumb );

					WPDI_Common::update_handled( array_merge( $preset, array( 'disabled' => false ) ) );

					wp_safe_redirect( WPDI_Common::build_plugin_url( array(
						'success' => __( 'Preset successfully enabled', WPDI_Common::PLUGIN_SYSTEM_NAME )
					) ) );

					break;

				case 'delete':
				case 'reset':
					$thumb                  = WPDI_Validators::get_thumb();
					$plugin_handled_presets = get_option( 'wpdi_presets' );
					$success                = false;
					if ( isset( $plugin_handled_presets[ $thumb ] ) ) {
						if ( 'delete' == $action ) {
							$remove_queue = get_option( 'wpdi_delete_preset_queue', array() );
							array_push( $remove_queue, $thumb ); // to remove when delete thumbnail will be invoked
							update_option( 'wpdi_delete_preset_queue', $remove_queue );
							unset( $plugin_handled_presets[ $thumb ] );
							$success = __( 'Preset successfully deleted', WPDI_Common::PLUGIN_SYSTEM_NAME );
						} else {
							$plugin_handled_presets[ $thumb ]['affected'] = false;

							$success = __( 'Preset successfully reseted', WPDI_Common::PLUGIN_SYSTEM_NAME );
						}
						update_option( 'wpdi_presets', $plugin_handled_presets );
						delete_option( 'wpdi_preset_' . $thumb );
					}

					wp_safe_redirect( WPDI_Common::build_plugin_url( array(
						'success' => $success
					) ) );

					break;

				case 'main':
				default:
					global $_wp_additional_image_sizes;

					$default_sizes    = WPDI_Common::get_all_presets_names();
					$affected_presets = get_option( 'wpdi_presets', array() );
					$image_sizes      = array();

					foreach ( $default_sizes as $size ) {
						if ( ! isset( $affected_presets[ $size ]['affected'] ) or ! $affected_presets[ $size ]['affected'] ) {
							$image_sizes[ (string) $size ]['width']  = ! empty( $_wp_additional_image_sizes[ $size ]['width'] ) ?
								$_wp_additional_image_sizes[ $size ]['width'] :
								intval( get_option( "{$size}_size_w" ) );
							$image_sizes[ (string) $size ]['height'] = ! empty( $_wp_additional_image_sizes[ $size ]['height'] ) ?
								$_wp_additional_image_sizes[ $size ]['height'] :
								intval( get_option( "{$size}_size_h" ) );
							$image_sizes[ (string) $size ]['crop']   = ! empty( $_wp_additional_image_sizes[ $size ]['crop'] ) ?
								$_wp_additional_image_sizes[ $size ]['crop'] :
								boolval( get_option( "{$size}_crop", false ) );
						}
					}
					$image_sizes = $image_sizes + $_wp_additional_image_sizes;

					echo WPDI_Common::render( 'settings-page', array(
						'sizes'            => $default_sizes,
						'affected_presets' => $affected_presets,
						'image_sizes'      => $image_sizes,
					) );

					break;
			}
		}

		public static function calc_preview_array( $preset ) {
			$sample_width              = 160;
			$sample_height             = 160;
			$sample_file_attachment_id = get_option( 'wpdi_sample_image_id' );
			$upload                    = wp_upload_dir();

			$original_path = wp_get_attachment_image_url( $sample_file_attachment_id, 'full' );
			if ( empty( $original_path ) ) { // if preview image was accidentally removed - we re-create it
				WPDI_Common::activate();
			}

			$preset_thumb      = wp_get_attachment_image_url( $sample_file_attachment_id, $preset['wpdi_name'] );
			$preset_thumb_path = WPDI_Common::get_relative_file_path( $preset_thumb );
			$preset_thumb_path = implode( '/', array( $upload['basedir'], $preset_thumb_path ) );
			if ( ! file_exists( $preset_thumb_path ) ) {
				WPDI_Common::create_preview_subsize( $preset['wpdi_name'] );
				$preset_thumb = wp_get_attachment_image_url( $sample_file_attachment_id, $preset['wpdi_name'] );
			}

			// Set up original file information.
			$original_image = getimagesize( $original_path );
			if ( $original_image[1] <= 0 || $original_image[0] <= 0 ) {
				return false;
			}

			if ( @$original_image[0] > @$original_image[1] ) {
				$original_width  = min( @$original_image[0], $sample_width );
				$original_height = round( $original_width / @$original_image[0] * @$original_image[1] );
			} else {
				$original_height = min( @$original_image[1], $sample_height );
				$original_width  = round( $original_height / @$original_image[1] * @$original_image[0] );
			}
			$original_size_width  = @$original_image[0] ?? $sample_width;
			$original_size_height = @$original_image[1] ?? $sample_height;
			$original_attributes  = array(
				'width'       => $original_width,
				'height'      => $original_height,
				'size_width'  => $original_size_width,
				'size_height' => $original_size_height,
				'src'         => $original_path . "?TB_iframe=true&width={$original_size_width}&height={$original_size_height}"
			);

			$preview_image = getimagesize( $preset_thumb );
			if ( $preview_image[1] <= 0 || $preview_image[0] <= 0 ) {
				return false;
			}
			if ( @$preview_image[0] > 0 && @$preview_image[1] > 0 ) {
				if ( @$preview_image[0] > @$preview_image[1] ) {
					$preview_width  = min( @$preview_image[0], $sample_width );
					$preview_height = round( $preview_width / @$preview_image[0] * @$preview_image[1] );
				} else {
					$preview_height = min( @$preview_image[1], $sample_height );
					$preview_width  = round( $preview_height / @$preview_image[1] * @$preview_image[0] );
				}
			}
			$preview_size_width  = @$preview_image[0] ?? $sample_width;
			$preview_size_height = @$preview_image[1] ?? $sample_height;
			$preview_attributes  = array(
				'width'       => $preview_width,
				'height'      => $preview_height,
				'size_width'  => $preview_size_width,
				'size_height' => $preview_size_height,
				'src'         => $preset_thumb . "?TB_iframe=true&width={$preview_size_width}&height={$preview_size_height}"
			);

			return array(
				'before' => $original_attributes,
				'after'  => $preview_attributes
			);
		}

		public static function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
			array_unshift( $actions,
				sprintf( '<a href="%s" aria-label="%s">%s</a>',
					menu_page_url( WPDI_Common::PLUGIN_SYSTEM_NAME, false ),
					esc_attr__( 'Images presets settings', WPDI_Common::PLUGIN_SYSTEM_NAME ),
					esc_html__( "Settings", WPDI_Common::PLUGIN_SYSTEM_NAME )
				)
			);

			return $actions;
		}
	}
}