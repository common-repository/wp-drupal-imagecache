<?php
defined( 'ABSPATH' ) || exit;

/* @var $effect array */
/* @var $preset array */
/* @var $action string */
/* @var $effect_key string */
?>
<h2><?php _e( 'Image scale and crop settings', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></h2>
<p><?php _e( 'Scale and crop will maintain the aspect-ratio of the original image, then crop the larger dimension. This is most useful for creating perfectly square thumbnails without stretching the image.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></p>
<form method="post"
      action="<?php echo WPDI_Common::build_plugin_url( array(
	      'action'     => @$action,
	      'thumb'      => @$preset['wpdi_name'],
	      'effect'     => @$effect['wpdi_effect'],
	      'effect_key' => (string) @$effect_key
      ) ) ?>">
    <table class="form-table" role="presentation">
        <tbody>
        <tr class="field-effect-width">
            <th><label for="field-effect-width"><?php _e( 'Width', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label></th>
            <td>
                <input type="text" name="wpdi_effect_width" id="field-effect-width" class="regular-text" required
                       value="<?php esc_attr_e( @$effect['wpdi_effect_width'] ); ?>">
                <span class="description"><?php _e( 'in pixels', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
            </td>
        </tr>
        <tr class="field-effect-height">
            <th><label for="field-effect-height"><?php _e( 'Height', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label></th>
            <td>
                <input type="text" name="wpdi_effect_height" id="field-effect-height" class="regular-text" required
                       value="<?php esc_attr_e( @$effect['wpdi_effect_height'] ); ?>">
                <span class="description"><?php _e( 'in pixels', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
            </td>
        </tr>
        <tr class="field-effect-arrangement">
            <th>
                <label for="field-effect-arrangement"><?php _e( 'Arrangement', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label>
            </th>
            <td>
                <table class="effect-arrangement-table">
                    <tbody>
                    <tr class="odd">
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Top left', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>" type="radio"
                                       id="edit-data-arrangement-left-top" name="wpdi_effect_arrangement"
                                       value="left-top" class="form-radio"
							        <?php echo checked( 'left-top', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Top center', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>" type="radio"
                                       id="edit-data-arrangement-center-top" name="wpdi_effect_arrangement"
                                       value="center-top" class="form-radio"
							        <?php echo checked( 'center-top', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Top right', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>" type="radio"
                                       id="edit-data-arrangement-right-top" name="wpdi_effect_arrangement"
                                       value="right-top" class="form-radio"
							        <?php echo checked( 'right-top', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                    </tr>
                    <tr class="even">
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Center left', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"
                                       type="radio" id="edit-data-arrangement-left-center"
                                       name="wpdi_effect_arrangement" value="left-center"
                                       class="form-radio" <?php echo checked( 'left-center', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Center', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>" type="radio"
                                       id="edit-data-arrangement-center-center" name="wpdi_effect_arrangement"
                                       value="center-center" class="form-radio"
							        <?php echo checked( 'center-center', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Center right', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"
                                       type="radio" id="edit-data-arrangement-right-center"
                                       name="wpdi_effect_arrangement" value="right-center" class="form-radio"
							        <?php echo checked( 'right-center', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                    </tr>
                    <tr class="odd">
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Bottom left', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"
                                       type="radio" id="edit-data-arrangement-left-bottom"
                                       name="wpdi_effect_arrangement" value="left-bottom" class="form-radio"
							        <?php echo checked( 'left-bottom', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Bottom center', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"
                                       type="radio" id="edit-data-arrangement-center-bottom"
                                       name="wpdi_effect_arrangement" value="center-bottom" class="form-radio"
							        <?php echo checked( 'center-bottom', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                        <td>
                            <div class="form-item form-type-radio form-item-data-arrangement">
                                <input title="<?php _e( 'Bottom right', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"
                                       type="radio" id="edit-data-arrangement-right-bottom"
                                       name="wpdi_effect_arrangement" value="right-bottom" class="form-radio"
							        <?php echo checked( 'right-bottom', @$effect['wpdi_effect_arrangement'] ) ?>>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <span class="description"><?php _e( 'The part of the image that will be retained during the crop.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
            </td>
        </tr>
        </tbody>
    </table>
    <p class="submit">
        <input type="hidden" name="wpdi_effect" value="<?php echo $effect['wpdi_effect'] ?>">
        <input type="submit" id="submit" class="button-primary"
               value="<?php _e( 'Save Effect', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>"/>
        <a class="button" onclick="window.history.back();">
			<?php _e( 'back', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
        </a>
    </p>
</form>