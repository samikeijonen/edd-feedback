<?php
/**
 * Edit Feedback
 *
 * Edit feedback functions are from Pippin Williamson and his Software License Plugin.
 *
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDFeedback\Renewals
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['feedback'] ) || ! is_numeric( $_GET['feedback'] ) ) {
	//wp_die( __( 'Something went wrong.', 'edd-feedback' ), __( 'Error', 'edd-feedback' ) );
}

$feedback_id = absint( $_GET['feedback'] );
$feedback    = edd_feedback_get_feedback( $feedback_id );
?>
<h2><?php _e( 'Edit Feedback', 'edd-feedback' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ); ?>" class="add-new-h2"><?php _e( 'Go Back', 'edd-feedback' ); ?></a></h2>
<form id="edd-feedback" action="" method="post">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-feedback-subject"><?php _e( 'Email Subject', 'edd-feedback' ); ?></label>
				</th>
				<td>
					<input name="subject" id="edd-feedback-subject" type="text" value="<?php echo esc_attr( stripslashes( $feedback['subject'] ) ); ?>" style="width: 300px;"/>
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
							<option value="<?php echo esc_attr( $period ); ?>"<?php selected( $period, $feedback['send_period'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'When should this email be sent?', 'edd-feedback' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-feedback-message"><?php _e( 'Email Message', 'edd-feedback' ); ?></label>
				</th>
				<td>
					<?php wp_editor( wpautop( wp_kses_post( wptexturize( $feedback['message'] ) ) ), 'message', array( 'textarea_name' => 'message' ) ); ?>
					<p class="description"><?php _e( 'The email message to be sent with the feedback. The following template tags can be used in the message:', 'edd-feedback' ); ?></p>
					<ul>
						<li>{downloads} <?php _e( 'The download list', 'edd-feedback' ); ?></li>
					</ul>
				</td>
			</tr>

		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="edd-action" value="edit_feedback"/>
		<input type="hidden" name="feedback-id" value="<?php echo esc_attr( $feedback_id ); ?>"/>
		<input type="hidden" name="edd-feedback-nonce" value="<?php echo wp_create_nonce( 'edd_feedback_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Update Feedback', 'edd-feedback' ); ?>" class="button-primary"/>
	</p>
</form>
