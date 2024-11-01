<?php
defined( 'ABSPATH' ) || exit;

/* @var $effect array */
/* @var $preset array */
/* @var $action string */
/* @var $effect_key string */
?>
<h2><?php _e( 'Image rotate settings', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></h2>
<p><?php _e( 'Rotating an image may cause the dimensions of an image to increase to fit the diagonal.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></p>
<form method="post"
      action="<?php echo WPDI_Common::build_plugin_url( array(
	      'action'     => @$action,
	      'thumb'      => @$preset['wpdi_name'],
	      'effect'     => @$effect['wpdi_effect'],
	      'effect_key' => (string) @$effect_key
      ) ) ?>">
    <table class="form-table" role="presentation">
        <tbody>
        <tr class="field-effect-angle">
            <th><label for="field-effect-angle"><?php _e( 'Rotation angle', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label>
            </th>
            <td>
                <input type="number" name="wpdi_effect_angle" id="field-effect-angle" class="regular-text" required
                       value="<?php esc_attr_e( @$effect['wpdi_effect_angle'] ); ?>">
                <span class="description"><?php _e( 'The number of degrees the image should be rotated. Positive numbers are clockwise, negative are counter-clockwise.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
            </td>
        </tr>
        <tr class="field-effect-bg-color">
            <th>
                <label for="field-effect-bg-color"><?php _e( 'Background color', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label>
            </th>
            <td>
                <input type="text" name="wpdi_effect_bg_color" id="field-effect-bg-color" class="regular-text"
                       value="<?php esc_attr_e( @$effect['wpdi_effect_bg_color'] ); ?>">
                <span class="description"><?php _e( 'The background color to use for exposed areas of the image. Use web-style hex colors (#FFFFFF for white, #000000 for black). Leave blank for transparency on image types that support it.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
            </td>
        </tr>
        <tr class="field-effect-randomize">
            <th></th>
            <td>
                <label for="field-effect-randomize">
                    <input type="checkbox" name="wpdi_effect_randomize" id="field-effect-randomize" class="regular-text"
                           value="1" <?php echo checked( 1, esc_attr( @$effect['wpdi_effect_randomize'] ) ) ?>>
					<?php _e( 'Randomize', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                </label>
                <span class="description"><?php _e( 'Randomize the rotation angle for each image. The angle specified above is used as a maximum.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
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