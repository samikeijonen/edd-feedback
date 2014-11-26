<?php
/**
 * Additional settings handling email feedbacks
 *
 * @package     EDDFeedback\Settings
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the feedback options
 *
 * @access      public
 * @since       1.0.0
 * @param 		$args array option arguments
 * @return      void
*/
function edd_feedback_settings( $args ) {

	$feedbacks = edd_feedback_get_feedbacks();
	//echo '<pre>'; print_r( $feedbacks ); echo '</pre>';
	ob_start(); ?>
	<table id="edd_feedback_feedbacks" class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th style="width:350%; padding-left: 10px;" scope="col"><?php _e( 'Subject', 'edd-feedback' ); ?></th>
				<th style="width:350%; padding-left: 10px;" scope="col"><?php _e( 'Send Period', 'edd-feedback' ); ?></th>
				<th scope="col" style="padding-left: 10px;"><?php _e( 'Actions', 'edd-feedback' ); ?></th>
			</tr>
		</thead>
		<?php if( ! empty( $feedbacks ) ) : $i = 1; ?>
			<?php foreach( $feedbacks as $key => $feedback ) : $feedback = edd_feedback_get_feedback( $key ); ?>
			<tr <?php if( $i % 2 == 0 ) { echo 'class="alternate"'; } ?>>
				<td><?php echo esc_html( $feedback['subject'] ); ?></td>
				<td><?php echo esc_html( edd_feedback_get_period_label( $key ) ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-feedback&edd_feedback_action=edit-feedback&feedback=' . $key ) ); ?>" class="edd-feedback-edit-feedback" data-key="<?php echo esc_attr( $key ); ?>"><?php _e( 'Edit', 'edd-feedback' ); ?></a>&nbsp;|
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'edit.php?post_type=download&page=edd-feedback&edd_action=delete_feedback&feedback-id=' . $key ) ) ); ?>" class="edd-delete"><?php _e( 'Delete', 'edd-feedback' ); ?></a>
				</td>
			</tr>
			<?php $i++; endforeach; ?>
		<?php endif; ?>
	</table>
	<p>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-feedback&edd_feedback_action=add-feedback' ) ); ?>" class="button-secondary" id="edd_feedback_add_feedback"><?php _e( 'Add Feedback', 'edd-feedback' ); ?></a>
	</p>
	<?php
	echo ob_get_clean();
}
add_action( 'edd_feedback_feedbacks', 'edd_feedback_settings' );

/**
 * Renders the add / edit feedback screen
 *
 * @since  1.0.0
 * @param  array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function edd_feedback_edit() {

	$action = isset( $_GET['edd_feedback_action'] ) ? sanitize_text_field( $_GET['edd_feedback_action'] ) : 'add-feedback';

	if( 'edit-feedback' === $action ) {
		include EDD_FEEDBACK_DIR . 'includes/edit-feedback.php';
	} else {
		include EDD_FEEDBACK_DIR . 'includes/add-feedback.php';
	}

}

/**
 * Processes the creation of a new feedback
 *
 * @since  1.0.0
 * @param  array $data The post data
 * @return void
 */
function edd_feedback_process_add_feedback( $data ) {

	if( ! is_admin() ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to add feedbacks', 'edd-feedback' ) );
	}

	if( ! wp_verify_nonce( $data['edd-feedback-nonce'], 'edd_feedback_nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-feedback' ) );
	}

	$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : __( 'Love to hear feedback', 'edd-feedback' );
	$period  = isset( $data['period'] )  ? sanitize_text_field( $data['period'] )  : '+1week';
	$message = isset( $data['message'] ) ? wp_kses( $data['message'], wp_kses_allowed_html( 'post' ) ) : false;

	if( empty( $message ) ) {
		$message = 'Hello {name},

We would love to hear feedback from you.

You have purchased following products from us: {edd_feedback_downloads}

Are you happy with your purchase? Is there anything we can do for you?';
	}


	$feedbacks = edd_feedback_get_feedbacks();
	$feedbacks[] = array(
		'subject'     => $subject,
		'message'     => $message,
		'send_period' => $period
	);

	update_option( 'edd_feedback_feedbacks', $feedbacks );

	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) ); exit;

}
add_action( 'edd_add_feedback', 'edd_feedback_process_add_feedback' );

/**
 * Processes the update of an existing feedback
 *
 * @since  1.0.0
 * @param  array $data The post data
 * @return void
 */
function edd_feedback_process_update_feedback( $data ) {

	if( ! is_admin() ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to update feedbacks', 'edd-feedback' ) );
	}

	if( ! wp_verify_nonce( $data['edd-feedback-nonce'], 'edd_feedback_nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-feedback' ) );
	}

	if( ! isset( $data['feedback-id'] ) ) {
		wp_die( __( 'No feedback ID was provided', 'edd-feedback' ) );
	}

	$subject = isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : __( 'Love to hear feedback', 'edd-feedback' );
	$period  = isset( $data['period'] )  ? sanitize_text_field( $data['period'] )  : '+1week';
	$message = isset( $data['message'] ) ? wp_kses( $data['message'], wp_kses_allowed_html( 'post' ) ) : false;

	if( empty( $message ) ) {
		$message = 'Hello {name},

We would love to hear feedback from you.

You have purchased following products from us: {edd_feedback_downloads}

Are you happy with your purchase? Is there anything we can do for you?';
	}


	$feedbacks = edd_feedback_get_feedbacks();
	$feedbacks[ absint( $data['feedback-id'] ) ] = array(
		'subject'     => $subject,
		'message'     => $message,
		'send_period' => $period
	);

	update_option( 'edd_feedback_feedbacks', $feedbacks );

	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) ); exit;

}
add_action( 'edd_edit_feedback', 'edd_feedback_process_update_feedback' );

/**
 * Processes the deletion of an existing feedback
 *
 * @since  1.0.0
 * @param  array $data The post data
 * @return void
 */
function edd_feedback_process_delete_feedback( $data ) {

	if( ! is_admin() ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to delete feedbacks', 'edd-feedback' ) );
	}

	if( ! wp_verify_nonce( $data['_wpnonce'] ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-feedback' ) );
	}

	if( empty( $data['feedback-id'] ) ) {
		wp_die( __( 'No feedback ID was provided', 'edd-feedback' ) );
	}

	$feedbacks = edd_feedback_get_feedbacks();
	unset( $feedbacks[ absint( $data['feedback-id'] ) ] );

	update_option( 'edd_feedback_feedbacks', $feedbacks );

	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) ); exit;

}
add_action( 'edd_delete_feedback', 'edd_feedback_process_delete_feedback' );

/**
 * Add feedback admin submenu page
 * *
 * @access      private
 * @since       1.0.0
 * @return      void
*/
function edd_feedback_add_feedback_page() {

	$edd_feedback_page = add_submenu_page( 'edit.php?post_type=download', __( 'Feedback', 'edd-feedback' ), __( 'Feedback', 'edd-feedback' ), 'manage_shop_settings', 'edd-feedback', 'edd_feedback_edit' );

	add_action( 'admin_head', 'edd_feedback_hide_feedback_page' );
}
add_action( 'admin_menu', 'edd_feedback_add_feedback_page', 10 );

/**
 * Removes the feedback menu link
 *
 * @access      private
 * @since       1.0.0
 * @return      void
*/
function edd_feedback_hide_feedback_page() {
	remove_submenu_page( 'edit.php?post_type=download', 'edd-feedback' );
}