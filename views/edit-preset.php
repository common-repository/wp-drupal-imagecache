<?php
defined( 'ABSPATH' ) || exit;

/* @var $preset array of preset data */
/* @var $preview array of preview parameters */
/* @var $effects array of available effects */
/* @var array $success */
/* @var array $error */
?>
    <h2><?php _e( 'Preview', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></h2>
<?php echo WPDI_Common::print_error(@$error) ?>
<?php echo WPDI_Common::print_success(@$success) ?>
    <div class="preset-preview">
        <div class="before">
        <span>
            <?php _e( 'before', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
            (<a class="thickbox"
                href="<?php echo $preview['before']['src'] ?>"><?php _e( 'view actual size', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>)
        </span>
            <div class="preview-image-wrapper">
                <a class="thickbox" href="<?php echo $preview['before']['src'] ?>"><img
                            src="<?php echo $preview['before']['src'] ?>"
                            style="width:<?php echo $preview['before']['width'] ?>px;height:<?php echo $preview['before']['height'] ?>px"
                            alt="<?php _e( 'preview before changes', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"></a>
                <div class="height" style="height:<?php echo $preview['before']['height'] ?>px">
                    <span><?php echo $preview['before']['size_height'] ?>px</span>
                </div>
                <div class="width" style="width:<?php echo $preview['before']['width'] ?>px">
                    <span><?php echo $preview['before']['size_width'] ?>px</span>
                </div>
            </div>
        </div>
        <div class="after">
        <span>
            <?php _e( 'after', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
            (<a class="thickbox"
                href="<?php echo $preview['after']['src'] ?>"><?php _e( 'view actual size', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>)
        </span>
            <div class="preview-image-wrapper">
                <a class="thickbox" href="<?php echo $preview['after']['src'] ?>"><img
                            src="<?php echo $preview['after']['src'] ?>"
                            style="width:<?php echo $preview['after']['width'] ?>px;height:<?php echo $preview['after']['height'] ?>px"
                            alt="<?php _e( 'preview after change', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"></a>
                <div class="height" style="height:<?php echo $preview['after']['height'] ?>px">
                    <span><?php echo $preview['after']['size_height'] ?>px</span>
                </div>
                <div class="width" style="width:<?php echo $preview['after']['width'] ?>px">
                    <span><?php echo $preview['after']['size_width'] ?>px</span>
                </div>
            </div>
        </div>
    </div>

    <div class="preset-form">
        <form method="post"
              action="<?php echo WPDI_Common::build_plugin_url( array(
			      'action' => 'edit',
			      'thumb'  => esc_attr( $preset['wpdi_name'] )
		      ) ) ?>">
            <table class="form-table" role="presentation">
                <tr class="field-name">
                    <th><label for="field-name"><?php _e( 'Name', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label></th>
                    <td>
                        <input type="text" name="wpdi_name" id="field-name" class="regular-text"
							<?php echo ( isset( $preset['internal'] ) && $preset['internal'] ) ? '' : 'readonly'; ?>
                               value="<?php esc_attr_e( $preset['wpdi_name'] ); ?>">
                        <span class="description"><?php _e( 'Thumbnail unique name which used everywhere', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label for="field-effects"><?php _e( 'Effects', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label></th>
                    <td>
                        <table class="table effects">
                            <thead>
                            <tr>
                                <th><?php _e( 'Effect', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
                                <th><?php _e( 'Actions', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php if ( ! empty( $preset['effects'] ) ):
								foreach ( $preset['effects'] as $key => $effect ):
									if ( empty( $effect['wpdi_effect'] ) ) {
										continue;
									} ?>
                                    <tr>
                                        <td><?php echo $effects[$effect['wpdi_effect']] ?></td>
                                        <td>
											<?php if ( 'image_desaturate' != $effect['wpdi_effect'] ): ?>
                                                <a href="<?php echo WPDI_Common::build_plugin_url( array(
													'action'     => 'edit_effect',
													'thumb'      => esc_attr( $preset['wpdi_name'] ),
													'effect_key' => (string) $key,
													'effect'     => $effect['wpdi_effect']
												) ) ?>"><?php _e( 'edit', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>
											<?php endif; ?>
                                            <a href="<?php echo WPDI_Common::build_plugin_url( array(
												'action'     => 'delete_effect',
												'thumb'      => esc_attr( $preset['wpdi_name'] ),
												'effect_key' => (string) $key,
												'effect'     => $effect['wpdi_effect']
											) ) ?>"
                                               onclick="return confirm('<?php _e( 'Are you sure?', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>');">
												<?php _e( 'delete', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
							<?php endif; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2">
                                    <div class="wrap">
                                        <select name="wpdi_effect" id="field-effect">
                                            <option value=""
                                                    selected="selected"><?php _e( 'Select a new effect', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></option>
											<?php foreach ( $effects as $effect_name => $effect_text ): ?>
                                                <option value="<?php echo $effect_name ?>"><?php echo $effect_text ?></option>
											<?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="button button-primary"
                                                name="wpdi_add_effect_submit">
											<?php _e( 'Add effect', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                                        </button>
                                    </div>
                                    <span class="description"><?php _e( 'Select effect to add to image', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
									<?php if ( isset( $preset['internal'] ) && $preset['internal'] ): ?>
                                        <input type="submit" id="wpdi_update_preset_submit" class="button-primary"
                                               name="wpdi_update_preset_submit"
                                               value="<?php _e( 'Save preset', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"/>
									<?php endif; ?>
                                    <a class="button" href="<?php echo WPDI_Common::build_plugin_url() ?>">
										<?php _e( 'back', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                                    </a>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php add_thickbox(); ?>