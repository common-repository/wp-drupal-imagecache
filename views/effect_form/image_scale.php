<?php
defined( 'ABSPATH' ) || exit;

/* @var $effect array */
/* @var $preset array */
/* @var $action string */
/* @var $effect_key string */
?>
<h2><?php _e( 'Image scale settings', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></h2>
<p><?php _e( 'Scaling will maintain the aspect-ratio of the original image. If only a single dimension is specified, the other dimension will be calculated.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></p>
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
        <tr class="field-effect-upscale">
            <th></th>
            <td>
                <label for="field-effect-upscale">
                    <input type="checkbox" name="wpdi_effect_upscale" id="field-effect-upscale" class="regular-text"
                           value="1" <?php echo checked( 1, esc_attr( @$effect['wpdi_effect_upscale'] ) ) ?>>
					<?php _e( 'Allow Upscaling', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                </label>
                <span class="description"><?php _e( 'Let scale make images larger than their original size', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
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