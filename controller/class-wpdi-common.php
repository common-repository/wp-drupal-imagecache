<?php
/**
 * The core plugin class.
 *
 * It is used to define startup settings and requirements
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPDI_Common' ) ) {

	class WPDI_Common {

		/**
		 * @var int Use this priority for all Wordpress hooks in plugin
		 */
		const PLUGIN_HOOK_PRIORITY = 11;

		/**
		 * @var string Plugin common system name
		 */
		const PLUGIN_SYSTEM_NAME = 'wp-drupal-imagecache';

		/**
		 * @var string Human readable plugin name for front end
		 */
		const PLUGIN_HUMAN_NAME = 'Thumbnails like in Drupal';

		/**
		 * @var string Path to plugin root directory
		 */
		public $plugin_base_path = '';

		public function __construct() {
			$this->plugin_base_path = self::get_plugin_root_path();

			$this->load_dependencies();
			$this->set_locale();
		}

		/**
		 * Print error messages
		 *
		 * @param null|WP_Error $error
		 *
		 * @return false|string
		 */
		public static function print_error( $error = null ) {
			$get_error = WPDI_Validators::get_errors();
			if ( $error ) {
				if ( is_wp_error( $error ) ) {
					$error = $error->get_error_message();
					$error = __( $error, WPDI_Common::PLUGIN_SYSTEM_NAME );
				}
				array_push( $get_error, $error );
			}

			ob_start();
			if ( ! empty( $get_error ) ) {
				echo '<div class="notice notice-error">';
				foreach ( $get_error as $msg ) {
					echo '<p>' . $msg . '</p>';
				}
				echo '</div>';
			}

			return ob_get_clean();
		}

		/**
		 * Print success messages
		 *
		 * @param null|string $success
		 *
		 * @return false|string
		 */
		public static function print_success( $success = null ) {
			$get_success = WPDI_Validators::get_success();

			if ( $success ) {
				array_push( $get_success, $success );
			}

			ob_start();
			if ( ! empty( $get_success ) ) {
				echo '<div class="notice notice-success">';
				foreach ( $get_success as $msg ) {
					echo '<p>' . $msg . '</p>';
				}
				echo '</div>';
			}

			return ob_get_clean();
		}

		/**
		 * Compose preset effects description
		 *
		 * @param array $effects
		 *
		 * @return string
		 */
		public static function build_preset_effects_echo( $effects ) {
			$result = array();
			if ( empty( $effects ) ) {
				return '';
			}

			foreach ( $effects as $effect ) {
				switch ( $effect['wpdi_effect'] ) {
					case 'image_crop':
						$result[] = sprintf( __( 'Cropped %sx%s %s', self::PLUGIN_SYSTEM_NAME ),
							@$effect['wpdi_effect_width'],
							@$effect['wpdi_effect_height'],
							@$effect['wpdi_effect_arrangement']
						);
						break;

					case 'image_desaturate':
						$result[] = __( 'Desaturated', self::PLUGIN_SYSTEM_NAME );
						break;

					case 'image_resize':
						$result[] = sprintf( __( 'Resized %sx%s', self::PLUGIN_SYSTEM_NAME ),
							@$effect['wpdi_effect_width'],
							@$effect['wpdi_effect_height']
						);
						break;

					case 'image_rotate':
						$bg       = ! empty( $effect['wpdi_effect_bg_color'] ) ?
							$effect['wpdi_effect_bg_color'] :
							__( 'transparent', WPDI_Common::PLUGIN_SYSTEM_NAME );
						$result[] = sprintf( __( 'Rotated to %s deg., background %s', self::PLUGIN_SYSTEM_NAME ),
							@$effect['wpdi_effect_angle'],
							$bg
						);
						break;

					case 'image_scale':
						$s        = isset( $effect['wpdi_effect_upscale'] ) && $effect['wpdi_effect_upscale'] ?
							__( 'Scaled %sx%s with upscale', self::PLUGIN_SYSTEM_NAME ) :
							__( 'Scaled %sx%s without upscale', self::PLUGIN_SYSTEM_NAME );
						$result[] = sprintf( $s, @$effect['wpdi_effect_width'], @$effect['wpdi_effect_height'] );
						break;

					case 'image_scale_and_crop':
						$result[] = sprintf( __( 'Scaled&cropped %sx%s %s', self::PLUGIN_SYSTEM_NAME ),
							@$effect['wpdi_effect_width'],
							@$effect['wpdi_effect_height'],
							@$effect['wpdi_effect_arrangement']
						);
						break;

					case 'image_round_corners':
						$result[] = __( 'Rounded corners', self::PLUGIN_SYSTEM_NAME );
						break;

					case 'image_flip_flop':
						$result[] = $effect['wpdi_effect_direction'] == 'flip' ?
							__( 'Flipped(reflected vertically)', self::PLUGIN_SYSTEM_NAME ) :
							__( 'Flopped(reflected horizontally)', self::PLUGIN_SYSTEM_NAME );
						break;

					default:
						$result[] = apply_filters( 'wpdi_build_preset_effect', $effect['wpdi_effect'] );
						break;
				}
			}

			return implode( ', ', $result );
		}

		/**
		 * Compose all available preset types
		 *
		 * @return array
		 */
		public static function get_all_presets_names() {
			$default_sizes    = array( 'thumbnail', 'medium', 'medium_large', 'large' );
			$additional_sizes = wp_get_additional_image_sizes();

			$affected_presets = get_option( 'wpdi_presets', array() );

			$default_sizes = array_merge( $default_sizes, array_keys( $additional_sizes ) );

			foreach ( $affected_presets as $name => $data ) {
				if ( ! in_array( $name, $default_sizes ) ) {
					if ( ! isset( $data['internal'] ) OR ! $data['internal'] ) {
						unset( $affected_presets[ $name ] );
					}
				}
			}
			update_option( 'wpdi_presets', $affected_presets );

			$default_sizes = array_merge( $default_sizes, array_keys( $affected_presets ) );
			$default_sizes = array_unique( $default_sizes );

			sort( $default_sizes );

			return $default_sizes;
		}

		/**
		 * Get all plugin created presets
		 *
		 * @return array
		 */
		public static function get_internal_presets() {
			$presets_affected_plugin = get_option( 'wpdi_presets' );
			$internal                = array();

			if ( empty( $presets_affected_plugin ) ) {
				return $internal;
			}

			foreach ( $presets_affected_plugin as $name => $th ) {
				if ( isset( $th['internal'] ) && $th['internal'] ) {
					$internal[ $name ] = $th;
				}
			}

			return $internal;
		}

		/**
		 * Plugin entry point
		 */
		public function run() {
			$this->define_admin_hooks();
			$this->define_common_hooks();
			$this->define_internal_sizes();
		}

		/**
		 * Add actions and work for admin part of plugin
		 */
		private function define_admin_hooks() {
			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
				add_action( 'admin_menu', array( WPDI_Hooks::class, 'register_settings_pages' ) );
				add_filter( "plugin_action_links_" . plugin_basename( dirname( __DIR__ ) . '/bootstrap.php' ), array(
					WPDI_Hooks::class,
					'plugin_action_links'
				), self::PLUGIN_HOOK_PRIORITY, 4 );
			}
		}

		/**
		 * Add actions and work for common part of plugin
		 */
		private function define_common_hooks() {

			add_action( 'wp_ajax_wpdi_delete_disabled_thumbs', array(
				WPDI_Common::class,
				'delete_disabled_thumbs'
			) );
			add_action( 'wp_ajax_wpdi_regenerate_thumb', array(
				WPDI_Common::class,
				'regenerate_thumbs'
			) );
			add_filter( 'wp_image_editors', array(
				WPDI_Hooks::class,
				'wp_image_editors'
			), self::PLUGIN_HOOK_PRIORITY );
			add_filter( 'intermediate_image_sizes_advanced', array(
				WPDI_Hooks::class,
				'intermediate_image_sizes_advanced'
			), self::PLUGIN_HOOK_PRIORITY, 3 );
			add_filter( 'intermediate_image_sizes', array(
				WPDI_Hooks::class,
				'intermediate_image_sizes'
			), self::PLUGIN_HOOK_PRIORITY );
			add_filter( 'woocommerce_image_sizes_to_resize', array(
				WPDI_Hooks::class,
				'woocommerce_image_sizes_to_resize'
			), self::PLUGIN_HOOK_PRIORITY );
			add_filter( 'image_downsize', array(
				WPDI_Hooks::class,
				'image_downsize'
			), self::PLUGIN_HOOK_PRIORITY, 3 );
			add_filter( 'big_image_size_threshold', array(
				WPDI_Hooks::class,
				'big_image_size_threshold'
			), self::PLUGIN_HOOK_PRIORITY, 4 );
			add_filter( 'wp_image_maybe_exif_rotate', array(
				WPDI_Hooks::class,
				'wp_image_maybe_exif_rotate'
			), self::PLUGIN_HOOK_PRIORITY, 2 );

			add_filter( 'woocommerce_resize_images', '__return_false', self::PLUGIN_HOOK_PRIORITY );

		}

		/**
		 * Load plugin files
		 */
		private function load_dependencies() {
			require_once $this->plugin_base_path . 'controller/class-wpdi-hooks.php';
			require_once $this->plugin_base_path . 'controller/class-wpdi-validators.php';
			require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
			require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
			require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';
			require_once $this->plugin_base_path . 'model/class-effects.php';
			require_once $this->plugin_base_path . 'model/class-effects-gd.php';
			require_once $this->plugin_base_path . 'model/class-effects-imagick.php';
		}

		/**
		 * Add localization support
		 */
		private function set_locale() {
			load_plugin_textdomain(
				self::PLUGIN_SYSTEM_NAME,
				false,
				self::PLUGIN_SYSTEM_NAME . '/languages/'
			);
		}

		/**
		 * Register all plugin admin part styles and JS
		 */
		public function register_admin_scripts() {
			wp_enqueue_style( 'wpdi-styles', self::get_plugin_root_path( 'url' ) . 'static/css/styles.css', array(), '1.0.3' );
		}

		/**
		 * Use for register plugin options
		 */
		public static function activate() {
			$file = self::get_plugin_root_path() . 'static/images/sample.png';

			$temp = session_save_path() . '/sample.png';
			if ( ! $temp ) {
				$temp = sys_get_temp_dir() . '/sample.png';
			}

			copy( $file, $temp );
			$sample_attachment_id = self::upload_media( $temp, 0, __( 'Sample image for preview', self::PLUGIN_SYSTEM_NAME ) );

			@unlink( $temp );

			update_option( 'wpdi_sample_image_id', $sample_attachment_id );
		}

		/**
		 * Do all jobs when plugin deactivated
		 */
		public static function deactivate() {
			$sample_attachment_id = get_option( 'wpdi_sample_image_id' );
			wp_delete_attachment( $sample_attachment_id, true );
			delete_option( 'wpdi_sample_image_id' );
		}

		/**
		 * Use for unregister plugin options registered before in self::activate()
		 */
		public static function uninstall() {
			$thumbs = self::get_all_presets_names();
			delete_option( 'wpdi_presets' );
			foreach ( $thumbs as $thumb ) {
				delete_option( 'wpdi_preset_' . $thumb );
			}
		}

		/**
		 * Get path or uri for plugin based folder
		 *
		 * @param string $type switch path or url for result
		 *
		 * @return string
		 */
		public static function get_plugin_root_path( $type = 'path' ) {
			if ( 'url' == $type ) {
				return plugin_dir_url( dirname( __FILE__ ) );
			}

			return plugin_dir_path( dirname( __FILE__ ) );
		}

		/**
		 * Upload media handler
		 *
		 * @param string $file Path to media file
		 * @param int $post_id Post ID to assign media
		 * @param string|null $desc Media description
		 *
		 * @return int|WP_Error Attachment ID or error
		 */
		private static function upload_media( $file, $post_id = 0, $desc = null ) {
			$filename = explode( '/', $file );

			$file_array = array(
				'name'     => array_pop( $filename ),
				'tmp_name' => $file,
			);

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, $post_id, $desc );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
			}

			return $id;
		}

		/**
		 * Update or remove preset from plugin handled list
		 *
		 * @param array $preset
		 * @param bool $remove
		 */
		public static function update_handled( $preset, $remove = false ) {

			$plugin_handled_presets = get_option( 'wpdi_presets' );

			if ( $remove ) {
				unset( $plugin_handled_presets[ $preset['wpdi_name'] ] );
			} else {
				$preset_data = array(
					'name'     => $preset['wpdi_name'],
					'disabled' => isset( $preset['disabled'] ) ?
						$preset['disabled'] :
						( isset( $plugin_handled_presets[ $preset['wpdi_name'] ]['disabled'] ) ?
							$plugin_handled_presets[ $preset['wpdi_name'] ]['disabled'] :
							false
						),
					'affected' => ! empty( $preset['effects'] ),
					'internal' => isset( $preset['internal'] ) ?
						$preset['internal'] :
						( isset( $plugin_handled_presets[ $preset['wpdi_name'] ]['internal'] ) ?
							$plugin_handled_presets[ $preset['wpdi_name'] ]['internal'] :
							false
						),
				);

				$plugin_handled_presets[ $preset['wpdi_name'] ] = $preset_data;
			}

			update_option( 'wpdi_presets', $plugin_handled_presets );
		}

		/**
		 * Just get url param for router
		 *
		 * @return string
		 */
		public static function get_action() {
			if ( ! empty( $_REQUEST['action'] ) ) {
				return $_REQUEST['action'];
			}

			return 'main';
		}

		/**
		 * Get preset data from preset name
		 *
		 * @param string $thumb
		 *
		 * @return array
		 */
		public static function get_preset( $thumb ) {
			$preset = get_option( 'wpdi_preset_' . $thumb );

			if ( ! isset( $preset['wpdi_name'] ) ) {
				$preset['wpdi_name'] = $thumb;
			}

			return $preset;
		}

		/**
		 * Render plugin views function
		 *
		 * @param $name
		 * @param null|array $vars
		 *
		 * @return false|string
		 */
		public static function render( $name, $vars = null ) {
			if ( is_array( $vars ) ) {
				extract( $vars );
			}
			ob_start();
			$name = str_replace( '.php', '', $name ) . '.php';
			$path = self::get_plugin_root_path() . 'views/' . $name;
			if ( file_exists( $path ) ) {
				require( $path );
			}

			return ob_get_clean();
		}

		/**
		 * Make changed thumbs for preview on any changes in image presets
		 *
		 * @param $preset
		 *
		 * @return array|bool|WP_Error
		 */
		public static function update_preview( $preset ) {
			$saved = WPDI_Common::create_preview_subsize( $preset['wpdi_name'] );
			if ( is_wp_error( $saved ) ) {
				return $saved;
			}

			$preset['width']  = $saved['width'];
			$preset['height'] = $saved['height'];

			self::update_handled( $preset );

			return false; // err = false
		}

		/**
		 * Create preview sample image sub-size thumbnail and update image meta
		 *
		 * @param $size
		 *
		 * @return array|WP_Error
		 */
		public static function create_preview_subsize( $size ) {
			$sample_file_attachment_id = get_option( 'wpdi_sample_image_id' );
			$sample_image_path         = wp_get_attachment_metadata( $sample_file_attachment_id );
			$sample_image_path         = $sample_image_path['file'];
			$preset                    = WPDI_Common::get_preset( $size );
			if ( empty( $preset['effects'] ) ) {
				$upload            = wp_upload_dir();
				$sample_image_path = implode( '/', array( $upload['basedir'], $sample_image_path ) );

				return wp_create_image_subsizes( $sample_image_path, $sample_file_attachment_id );
			}
			$saved = self::make_preset( $size, $sample_image_path );

			if ( ! is_wp_error( $saved ) ) {
				WPDI_Common::update_meta( $sample_file_attachment_id, $size, $saved );
			}

			return $saved;
		}

		/**
		 * Do all magic with source image
		 *
		 * @param string $size
		 * @param string $image_path Set path to image starting from upload dir. Example 2010/01/qweqwe.jpg
		 *
		 * @return array|WP_Error
		 */
		public static function make_preset( $size, $image_path ) {
			$preset        = WPDI_Common::get_preset( $size );
			$uploads       = wp_get_upload_dir();
			$image_handler = new WPDI_Effects( $uploads['basedir'] . '/' . $image_path );

			if ( ! $image_handler->is_correct_handler() ) {
				return new WP_Error( 'image_effects_class_error', __( 'Someone overload image effect class by priority.' ) );
			}

			if ( empty( $preset['effects'] ) ) {
				return new WP_Error( 'image_effects_error', __( 'Effects or preset not found' ) );
			}

			foreach ( $preset['effects'] as $effect ) {
				if ( empty( $effect['wpdi_effect'] ) ) {
					continue;
				}
				if ( ! method_exists( $image_handler, $effect['wpdi_effect'] ) ) {
					$image_handler->add_error( 'Effect handler missing: ' . $effect['wpdi_effect'] );
					continue;
				}

				switch ( $effect['wpdi_effect'] ) {
					case 'image_crop':
						$image_handler->image_crop( $effect['wpdi_effect_arrangement'], $effect['wpdi_effect_width'], $effect['wpdi_effect_height'] );
						break;

					case 'image_desaturate':
						$image_handler->image_desaturate();
						break;

					case 'image_resize':
						$image_handler->image_resize( $effect['wpdi_effect_width'], $effect['wpdi_effect_height'] );
						break;

					case 'image_rotate':
						if ( isset( $effect['wpdi_effect_randomize'] ) && $effect['wpdi_effect_randomize'] ) {
							$max = 180;
							if ( ! empty( $effect['wpdi_effect_angle'] ) && $effect['wpdi_effect_angle'] ) {
								$max = $effect['wpdi_effect_angle'];
							}
							$effect['wpdi_effect_angle'] = mt_rand( - $max, $max );
						}
						$image_handler->image_rotate( $effect['wpdi_effect_angle'], $effect['wpdi_effect_bg_color'] );
						break;

					case 'image_scale':
						$image_handler->image_scale( $effect['wpdi_effect_width'], $effect['wpdi_effect_height'], @$effect['wpdi_effect_upscale'] );
						break;

					case 'image_scale_and_crop':
						$image_handler->image_scale_and_crop( $effect['wpdi_effect_arrangement'], $effect['wpdi_effect_width'], $effect['wpdi_effect_height'] );
						break;

					case 'image_round_corners':
						$corners = ! empty( $effect['wpdi_effect_independent_corners'] ) ? $effect['wpdi_effect_independent_corners'] : [];
						$image_handler->image_round_corners( $effect['wpdi_effect_radius'], $corners );
						break;

					case 'image_flip_flop':
						$image_handler->image_flip_flop( $effect['wpdi_effect_direction'] );
						break;

					default:
						try {
							$function_name = apply_filters( 'wpdi_make_preset_effect', $effect['wpdi_effect'] );
							$image_handler->$function_name( $effect );
						} catch ( Exception $e ) {
							wp_safe_redirect( WPDI_Common::build_plugin_url( array(
								'error' => urlencode( __( 'Incorrect effect:', WPDI_Common::PLUGIN_SYSTEM_NAME ) . $e->getMessage() )
							) ) );
							exit();
						}
						break;
				}

				$image_handler->custom_update_size();
			}

			return $image_handler->image_save();
		}

		public static function update_meta( $attachment_id, $size, $saved ) {

			if ( ! is_wp_error( $saved ) ) {
				// update attachment metadata with new filename and sizes to get correct in future
				$meta_data                   = wp_get_attachment_metadata( $attachment_id );
				$meta_data['sizes'][ $size ] = $saved;

				wp_update_attachment_metadata( $attachment_id, $meta_data );
			}
		}

		/**
		 * Build url for plugin pages
		 *
		 * @param array $params
		 *
		 * @return string
		 */
		public static function build_plugin_url( $params = array() ) {
			$params['page'] = WPDI_Common::PLUGIN_SYSTEM_NAME;

			return admin_url( 'options-general.php?' . http_build_query( $params ) );
		}

		/**
		 * Create relative path to image with image name
		 *
		 * @param $file
		 *
		 * @return string
		 */
		public static function get_relative_file_path( $file ) {
			return _wp_get_attachment_relative_path( $file ) . '/' . wp_basename( $file );
		}

		/**
		 * Register internal plugin image sizes
		 */
		private function define_internal_sizes() {
			$internal = WPDI_Common::get_internal_presets();
			foreach ( $internal as $i => $value ) {
				if ( ( ! isset( $value['disabled'] ) OR ! $value['disabled'] ) ) {
					if ( isset( $value['affected'] ) && $value['affected'] ) {
						add_image_size( $value['name'] );
					}
				}
			}
		}

		/**
		 * Get all attachments from media library
		 *
		 * @return WP_Post[]
		 */
		public static function get_all_attachments() {
			$args = array(
				'post_status'    => 'any',
				'post_type'      => 'attachment',
				'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png' ),
				'posts_per_page' => - 1,
				'offset'         => 0,
			);
			$q    = new WP_Query();

			return $q->query( $args );
		}

		/**
		 * Get all image thumbnails, exclude enabled thumbnails and remove left.
		 * Work with all attachments
		 */
		public static function delete_disabled_thumbs() {
			$attachments      = self::get_all_attachments();
			$upload           = wp_upload_dir();
			$affected_presets = get_option( 'wpdi_presets', array() );
			$remove_queue     = get_option( 'wpdi_delete_preset_queue', array() );

			// parse all posts/pages content to find direct usage thumbnails
			$pages_content = ( new WP_Query() )->query( array(
				'post_status'    => 'publish',
				'post_type'      => array( 'page', 'post' ),
				'posts_per_page' => - 1,
				'offset'         => 0,
			) );

			array_walk( $pages_content, function ( &$item ) {
				$content = $item->post_content;
				$item    = '';
				$matches = array();
				preg_match_all( '@<img([^>]+)>@siU', $content, $matches );
				if ( ! empty( $matches[0] ) ) {
					$item = implode( '', $matches[0] );
				}
			} );
			$content = implode( '', $pages_content );

			foreach ( $attachments as $attachment ) {
				$meta      = wp_get_attachment_metadata( $attachment->ID );
				$file_info = pathinfo( $meta['file'] );

				// find files which in enabled presets
				$exclude = array();
				foreach ( $meta['sizes'] as $thumb_name => $thumb ) {
					if ( isset( $affected_presets[ $thumb_name ]['disabled'] ) && $affected_presets[ $thumb_name ]['disabled'] ) {
						continue;
					}
					if ( in_array( $thumb_name, $remove_queue ) ) {
						continue;
					}

					if ( empty( $thumb['path'] ) ) {
						$thumb['path'] = implode( '/', array(
							$upload['basedir'],
							$file_info['dirname'],
							$thumb['file']
						) );
					}

					array_push( $exclude, $thumb['path'] );
				}

				// found all file thumbnails
				$base_file = implode( '/', array( $upload['basedir'], $file_info['dirname'], $file_info['filename'] ) );
				$ext       = $file_info['extension'];
				$files     = glob( "{$base_file}-[0123456789]*x[0123456789]*.{$ext}" );
				$files     = array_diff( $files, $exclude ); // exclude enabled preset thumbnails

				//find usage in post content and exclude that files too
				$in_content = array();
				foreach ( $files as $file ) {
					$relative_file = self::get_relative_file_path( $file );
					if ( false !== mb_stripos( $content, $relative_file ) ) {
						array_push( $in_content, $file );
					}
				}
				$files = array_diff( $files, $in_content ); // exclude thumbnails in post content

				if ( ! empty( $files ) ) {
					array_map( 'unlink', $files );
				}
			}

			self::clear_cache();
		}

		/**
		 * Regenerate preset thumbnails
		 *
		 * @return bool
		 */
		public static function regenerate_thumbs() {
			global $_wp_additional_image_sizes;

			set_time_limit( 0 );
			$size        = WPDI_Validators::get_thumb();
			$attachments = self::get_all_attachments();

			if ( empty( $size ) or empty( $attachments ) ) {
				return false;
			}

			$upload                  = wp_upload_dir();
			$presets_affected_plugin = get_option( 'wpdi_presets' );

			foreach ( $attachments as $attachment ) {
				$meta = wp_get_attachment_metadata( $attachment->ID );
				if ( empty( $meta ) ) {
					continue;
				}

				$image_path = implode( '/', array( $upload['basedir'], $meta['file'] ) );
				$new_sizes  = array();

				$new_sizes[ $size ] = array(
					'crop'               => boolval( get_option( "{$size}_crop", false ) ),
					'wpdi_name'          => $size,
					'wpdi_attachment_id' => $attachment->ID
				);
				if ( isset( $presets_affected_plugin[ $size ]['affected'] ) && $presets_affected_plugin[ $size ]['affected'] ) {
					$new_sizes[ $size ]['wpdi_affected'] = true;
				}

				$w = $_wp_additional_image_sizes[ $size ]['width'] ?? intval( get_option( "{$size}_size_w", 0 ) );
				if ( $w > 0 ) {
					$new_sizes[ $size ]['width'] = $w;
				}

				$h = $_wp_additional_image_sizes[ $size ]['height'] ?? intval( get_option( "{$size}_size_h", 0 ) );
				if ( $h > 0 ) {
					$new_sizes[ $size ]['height'] = $h;
				}

				unset( $meta['sizes'][ $size ] );

				_wp_make_subsizes( $new_sizes, $image_path, $meta, $attachment->ID );
			}

			self::clear_cache();

			return true;
		}

		/**
		 * Clear caches if need
		 */
		public static function clear_cache() {
			// clear wp cache
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
			}
		}

	}
}
