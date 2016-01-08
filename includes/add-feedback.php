<?php
/**
 * Add Feedback
 *
 * Add feedback functions are from Pippin Williamson and his Software License Plugin.
 *
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDFeedback\Feedbacks
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php _e( 'Add Feedback', 'edd-feedback' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd-feedback-settings-section' ); ?>" class="add-new-h2"><?php _e( 'Go Back', 'edd-feedback' ); ?></a></h2>
<form id="edd-add-feedback" action="" method="post">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-feedback-subject"><?php _e( 'Email Subject', 'edd-feedback' ); ?></label>
				</th>
				<td>
					<input name="subject" id="edd-feedback-subject" type="text" value="" style="width: 300px;"/>
					<p class="description"><?php _e( 'The subject line of the feedback email', 'edd-feedback' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-feedback-period"><?php _e( 'Email Period', 'edd-feedback' ); ?></label>
				</th>
				<td>
					<select name="period" id="edd-feedback-period">
						<?php foreach( edd_feedback_get_periods() as $period => $label ) : ?>
							<option value="<?php echo esc_attr( $period ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'When should this email be sent?', 'edd-feedback' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-feedback-message"><?php _e( 'Email Subject', 'edd-feedback' ); ?></label>
				</th>
				<td>
					<?php wp_editor( '', 'message', array( 'textarea_name' => 'message' ) ); ?>
					<p class="description"><?php _e( 'The email message to be sent with the feedback. The following template tags can be used in the message:', 'edd-feedback' ); ?></p>
					<ul>
						<li>{downloads} <?php _e( 'The download list', 'edd-feedback' ); ?></li>
					</ul>
				</td>
			</tr>

		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="edd-action" value="feedback_add_feedback"/>
		<input type="hidden" name="edd-feedback-nonce" value="<?php echo wp_create_nonce( 'edd_feedback_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Add Feedback', 'edd-feedback' ); ?>" class="button-primary"/>
	</p>
</form>
