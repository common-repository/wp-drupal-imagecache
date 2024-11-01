<?php
defined( 'ABSPATH' ) || exit;

/* @var array $sizes */
/* @var array $affected_presets */
/* @var array $image_sizes */
/* @var array $success */
/* @var array $error */

$default_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );
?>
<?php echo WPDI_Common::print_error( @$error ) ?>
<?php echo WPDI_Common::print_success( @$success ) ?>
<div class="wrap">
    <h1 class="wp-heading-inline">
		<?php esc_html_e( WPDI_Common::PLUGIN_HUMAN_NAME, WPDI_Common::PLUGIN_SYSTEM_NAME ); ?>
        &nbsp;<?php _e( 'Settings', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
        <a href="#TB_inline=true&width=600&height=200&inlineId=modal-add-preset" class="thickbox page-title-action"
           title="<?php _e( 'Create new preset', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>">
			<?php _e( 'Create new preset', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>

    </h1>
    <div id="delete-disabled-btn">
        <span class="spinner"></span>
        <span class="result dashicons"></span>
        <button class="button" disabled="disabled">
			<?php _e( 'delete thumbs', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
            <strong>
                (<?php _e( 'PLEASE READ AND AGREE', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>)
                <span class="dashicons dashicons-arrow-right-alt" style="display: inline;vertical-align: sub;"></span>
            </strong>
        </button>
        <a href="#TB_inline=true&width=600&height=300&inlineId=modal-delete-thumb-hint" class="hint thickbox"
           title="<?php _e( 'Delete thumbnails warning!', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>">
            <span class="dashicons dashicons-editor-help"></span>
        </a>

    </div>
    <table class="wp-list-table widefat fixed striped pages">
        <thead>
        <tr>
            <th class="manage-column"><?php _e( 'Preset name', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
            <th class="manage-column"><?php _e( 'Result', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
            <th class="manage-column"><?php _e( 'Type', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
            <th class="manage-column"><?php _e( 'Status', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
            <th class="manage-column"><?php _e( 'Actions', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></th>
        </tr>
        </thead>
		<?php
		if ( ! empty( $sizes ) ) {
			foreach ( $sizes as $size ): ?>
                <tr>
                    <td><?php echo $size ?></td>
                    <td><?php
						if ( isset( $affected_presets[ $size ]['affected'] ) && $affected_presets[ $size ]['affected'] ) {
							$preset = WPDI_Common::get_preset( $size );
							echo WPDI_Common::build_preset_effects_echo( $preset['effects'] );
						} else {
							$w = isset( $image_sizes[ $size ]['width'] ) ? $image_sizes[ $size ]['width'] : 0;
							$h = isset( $image_sizes[ $size ]['height'] ) ? $image_sizes[ $size ]['height'] : 0;
							$c = isset( $image_sizes[ $size ]['crop'] ) ? ( $image_sizes[ $size ]['crop'] ?
								__( 'Scale %sx%s with crop', WPDI_Common::PLUGIN_SYSTEM_NAME ) :
								__( 'Scale %sx%s without crop', WPDI_Common::PLUGIN_SYSTEM_NAME )
							) : '';
							if ( $w > 0 || $h > 0 ) {
								echo sprintf( $c, $w, $h );
							}
						} ?></td>
                    <td><?php if ( isset( $affected_presets[ $size ]['internal'] ) && $affected_presets[ $size ]['internal'] ): ?>
							<?php _e( 'Internal', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
						<?php else: ?>
							<?php _e( 'External', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
							<?php echo in_array( $size, $default_sizes ) ? ', ' . __( 'Core', WPDI_Common::PLUGIN_SYSTEM_NAME ) : '' ?>
						<?php endif; ?>
                    </td>
                    <td><?php if ( isset( $affected_presets[ $size ]['disabled'] ) && $affected_presets[ $size ]['disabled'] ): ?>
                            <span class="red"><?php _e( 'Disabled', WPDI_Common::PLUGIN_SYSTEM_NAME ); ?></span>&nbsp;(
                            <a
                                    href="<?php echo WPDI_Common::build_plugin_url(
										array(
											'action' => 'enable',
											'thumb'  => $size
										) ) ?>"><?php _e( 'enable', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>)
						<?php else: ?>
                            <span class="green"><?php _e( 'Enabled', WPDI_Common::PLUGIN_SYSTEM_NAME ); ?></span>&nbsp;(
                            <a
                                    href="<?php echo WPDI_Common::build_plugin_url(
										array(
											'action' => 'disable',
											'thumb'  => $size
										) ) ?>"><?php _e( 'disable', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a>)
						<?php endif; ?>
                    <td>
                        <ul class="list-unstyled list-flex">
                            <li><a class="button button-small" href="<?php echo WPDI_Common::build_plugin_url( array(
									'action' => 'edit',
									'thumb'  => $size
								) ) ?>"><?php _e( 'edit', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></a></li>

							<?php if ( ! isset( $affected_presets[ $size ]['disabled'] ) OR ! $affected_presets[ $size ]['disabled'] ): ?>
                                <li>
                                    <button data-thumb="<?php echo $size ?>"
                                            class="regenerate-thumb button button-small button-green">
										<?php _e( 'regenerate', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                                    </button>
                                    <span class="spinner"></span>
                                    <span class="result dashicons"></span>
                                </li>
							<?php endif; ?>

							<?php if ( isset( $affected_presets[ $size ]['internal'] ) && $affected_presets[ $size ]['internal'] ): ?>
                                <li><a class="button button-small button-red"
                                       href="<?php echo WPDI_Common::build_plugin_url(
									       array(
										       'action' => 'delete',
										       'thumb'  => $size
									       ) ) ?>"
                                       onclick="return confirm('<?php _e( 'Are you sure?', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>');">
										<?php _e( 'remove', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                                    </a></li>
							<?php else: ?>
								<?php if ( isset( $affected_presets[ $size ]['affected'] ) && $affected_presets[ $size ]['affected'] ): ?>
                                    <li><a class="button button-small button-red"
                                           href="<?php echo WPDI_Common::build_plugin_url(
										       array(
											       'action' => 'reset',
											       'thumb'  => $size
										       ) ) ?>"
                                           title="<?php _e( 'remove all plugin changes from preset', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>">
											<?php _e( 'reset', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                                        </a></li>
								<?php endif; ?>
							<?php endif; ?>
                        </ul>
                    </td>
                </tr>
			<?php endforeach;
		} ?>
    </table>
</div> <!-- .wrap -->
<?php add_thickbox(); ?>


<div id="modal-add-preset" style="display:none;">
    <form method="post"
          action="<?php echo WPDI_Common::build_plugin_url( array(
		      'action' => 'add_preset'
	      ) ) ?>">
        <table class="form-table">
            <tr class="field-name">
                <th><label for="field-name"><?php _e( 'Name', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></label></th>
                <td>
                    <input type="text" name="wpdi_name" id="field-name" class="regular-text" value="">
                    <span class="description"><?php _e( 'Thumbnail unique name which used everywhere', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-right">
                    <button type="submit" class="button button-primary" name="wpdi_add_preset_submit">
						<?php _e( 'Add preset', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="modal-delete-thumb-hint" style="display:none;">
    <div class="flex-stretch">
        <h2><?php _e( 'Please pay attention about this functionality!', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></h2>
        <h3 class="centered-block">
            <strong class="red"><?php _e( 'MAKE BACKUP BEFORE DELETE THUMBNAILS!!!!', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></strong>
        </h3>
        <p>
			<?php _e( 'This action will find and remove all disabled preset thumbnails and thumbnails created with nonexistent or deleted presets.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
        </p>
        <p><?php _e( 'And it keep thumbnails which used direct in post or page.', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?></p>
        <div class="centered-block">
            <button id="read-and-agree" class="button button-red">
				<?php _e( 'I read and understand', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        jQuery('#read-and-agree').on('click', function () {
            tb_remove();
            jQuery('#delete-disabled-btn button').html('<?php _e( 'delete thumbs', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>').removeProp('disabled');
        });
        jQuery('#delete-disabled-btn button').on('click', function () {
            var spinner = jQuery(this).parent().find('.spinner'),
                result = jQuery(this).parent().find('.result');
            if (confirm('<?php _e( 'Are you sure?', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>')) {
                jQuery.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ) ?>?action=wpdi_delete_disabled_thumbs',
                    timeout: 0,
                    beforeSend: function () {
                        if (!spinner.hasClass('is-active')) {
                            spinner.addClass('is-active');
                        }
                        result.removeClass('dashicons-yes');
                        result.removeClass('dashicons-no-alt');
                        result.hide();
                    },
                    success: function () {
                        if (spinner.hasClass('is-active')) {
                            spinner.removeClass('is-active');
                            result.addClass('dashicons-yes').show();
                        }
                    },
                    error: function () {
                        if (spinner.hasClass('is-active')) {
                            spinner.removeClass('is-active');
                            result.addClass('dashicons-no-alt').show();
                        }
                    }
                });
            }
        });
        jQuery('.regenerate-thumb').on('click', function () {
            var spinner = jQuery(this).parent().find('.spinner'),
                result = jQuery(this).parent().find('.result'),
                thumb = jQuery(this).data('thumb');
            if (confirm('<?php _e( 'Are you sure?', WPDI_Common::PLUGIN_SYSTEM_NAME ) ?>')) {
                jQuery.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ) ?>?action=wpdi_regenerate_thumb&thumb=' + thumb,
                    timeout: 0,
                    beforeSend: function () {
                        if (!spinner.hasClass('is-active')) {
                            spinner.addClass('is-active');
                        }
                        result.removeClass('dashicons-yes');
                        result.removeClass('dashicons-no-alt');
                        result.hide();
                    },
                    success: function () {
                        if (spinner.hasClass('is-active')) {
                            spinner.removeClass('is-active');
                        }
                        result.addClass('dashicons-yes').show();
                    },
                    error: function () {
                        if (spinner.hasClass('is-active')) {
                            spinner.removeClass('is-active');
                        }
                        result.addClass('dashicons-no-alt').show();
                    }
                });
            }
        });
    });
</script>