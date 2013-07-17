<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   CommunityWatch
 * @author    Josh Eaton <josh@josheaton.org>
 * @license   GPL-2.0+
 * @link      http://www.josheaton.org/
 * @copyright 2013 Josh Eaton
 */
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php
	settings_errors( 'cw_display' );
	?>

	<div class="rkvr-form-options">
		<form method="post" action="options.php">
		<?php
		settings_fields( 'cw_display' );
		$prefix			= 'cw_display';
		$cw_display		= get_option( 'cw_display' );

		// get individual display options
		$cw_types		= isset( $cw_display['types'] ) 	? $cw_display['types'] 				: '';
		?>

		<table class="form-table cw-table">
		<tbody>
			<tr>
				<th><label><?php _e('Select the post types to include', $this->plugin_slug); ?></label></th>
				<td>
					<span class="types-wrap">
					<?php echo $this->post_type_boxes($cw_types, $prefix); ?>
					</span>
				</td>
			</tr>

<!-- 			<tr>
				<th><label><?php _e('Link text', $this->plugin_slug) ?></label></th>
				<td>
					<input type="text" id="link-text" name="cw_display[link_text]" value="<?php if (isset($cw_display['link_text'])) esc_attr($cw_display['link_text']); ?>">
				</td>
			</tr> -->

			<tr class="type-required">
				<th><?php _e('Link placement', $this->plugin_slug); ?></th>
				<td>
					<input type="radio" id="link-top" name="cw_display[position]" value="top" <?php if (isset($cw_display['position'])) checked( $cw_display['position'], 'top' ); ?>>
					<label for="link-top"><?php _e('Before post content', $this->plugin_slug); ?></label>

					<br />

					<input type="radio" id="link-bottom" name="cw_display[position]" value="bottom" <?php if (isset($cw_display['position'])) checked( $cw_display['position'], 'bottom' ); ?>>
					<label for="link-bottom"><?php _e('After post content', $this->plugin_slug); ?></label>

					<br />

					<input type="radio" id="link-both" name="cw_display[position]" value="both" <?php if (isset($cw_display['position'])) checked( $cw_display['position'], 'both' ); ?>>
					<label for="link-both"><?php _e('Above and below content', $this->plugin_slug); ?></label>

<!-- 					<br />

					<input type="radio" id="link-manual" name="cw_display[position]" value="manual" <?php if (isset($cw_display['position'])) checked( $cw_display['position'], 'manual' ); ?>>
					<label for="link-manual"><?php _e('Place manually via <code>cw_report_link();</code> template tag', $this->plugin_slug); ?></label> -->

				</td>
			</tr>

			<tr>
				<th><?php _e('Icon display', $this->plugin_slug); ?></th>
				<td>
					<input type="checkbox" name="cw_display[show_icons]" id="show_icons" value="1" <?php checked($cw_display['show_icons'], 1);?>>
					<label for="show_icons"><?php _e('Show icons before links', $this->plugin_slug);?></label>
				</td>
			</tr>

		</tbody>
		</table>

		<p><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form>

	</div>

</div>
