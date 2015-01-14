<?php
/**
 * Helper Functions
 *
 * @package     EDDFeedback\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if feedback emails is disabled
 *
 * @since  1.0.0
 * @return boolean Feedback disabled
 */
function edd_feedback_disabled() {
	if( edd_get_option( 'edd_feedback_disable_emails' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if allow sending feedback emails in checkout page is disabled
 *
 * @since  1.0.1
 * @return boolean Checkbox disabled
 */
function edd_feedback_allow_checkout_hidden() {
	if( edd_get_option( 'edd_feedback_allow_checkout_hidden' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Ask customers in the checkout do they want feedback email
 *
 * @since  1.0.0
 * @return void
 */
function edd_feedback_custom_checkout_fields() {
	
	// Get cart items
	$cart_items = edd_get_cart_contents();
	
	// Proceed if we have cart items
	if ( $cart_items ) {
		
		foreach ( $cart_items as $key => $item ) {
			$feedback_disabled = get_post_meta( $item['id'], '_edd_feedback_disable_feedback_emails', true );
			// If we found at least one value that is not true, break and proceed 
			if (  true != $feedback_disabled ) {
				$feedback_disabled = false;
				break;
			}
		}
		
		/**
		 * If feedback is not disabled at least for one download go ahead and proceed.
		 *
		 * Also check do we want to hide 'Allow sending feedback emails' on checkout page.
		 */
		if( !$feedback_disabled ) {
			$allow_feedback_hidden = edd_feedback_allow_checkout_hidden();
			if( !$allow_feedback_hidden ) {
			?>
				<fieldset id="edd_feedback_send_email">
					<p>
						<input name="edd_feedback_agree_send_email" type="checkbox" id="edd_feedback_agree_send_email" value="1" checked="checked" />
						<label for="edd_feedback_agree_send_email"><?php echo __( 'Allow sending feedback email.', 'edd-feedback' ); ?></label>
					</p>
				</fieldset>
			<?php
			} else {
			?>
				<input name="edd_feedback_agree_send_email" type="hidden" value="1" />
			<?php
			}
		}
	}
	
}
add_action( 'edd_purchase_form_before_submit', 'edd_feedback_custom_checkout_fields' );

/**
 * Save custom field for agreement of send email
 *
 * @since  1.0.0
 * @return array Payment meta
 */
function edd_feedback_save_custom_fields( $payment_meta ) {

    $payment_meta['edd_feedback_agree_send_email'] = isset( $_POST['edd_feedback_agree_send_email'] ) ? sanitize_text_field( $_POST['edd_feedback_agree_send_email'] ) : '';
    
    return $payment_meta;
}
add_filter( 'edd_payment_meta', 'edd_feedback_save_custom_fields' );
 
/**
 * Add feedback email checkbox to "View Order Details" page
 *
 * @since  1.0.0
 * @return void
 */
function edd_feedback_view_order_details( $payment_id ) {

	$payment_meta     = edd_get_payment_meta( $payment_id );
	$agree_send_email = isset( $payment_meta['edd_feedback_agree_send_email'] ) ? $payment_meta['edd_feedback_agree_send_email'] : __( 'none', 'edd-feedback' );
?>
	<div class="edd-admin-box-inside edd-feedback-agree-send-email">
		<p>
			<span class="label" title="<?php _e( 'Send feedback email', 'edd-feedback' ); ?>"><i data-code="f465" class="dashicons dashicons-email"></i></span>&nbsp;
			<input name="edd_feedback_agree_send_email" type="checkbox" id="edd_feedback_agree_send_email" value="1" <?php checked( $agree_send_email, true ); ?> />
			<label class="description" for="edd_feedback_agree_send_email"><?php echo __( 'Send feedback email', 'edd-feedback' ); ?></label>
		</p>
	</div>
<?php
}
add_action( 'edd_view_order_details_payment_meta_after', 'edd_feedback_view_order_details' );

/**
 * Save check feedback email field when it's modified via view order details
 *
 * @since  1.0.0
 * @return void
 */
function edd_feedback_updated_edited_purchase( $payment_id ) {
 
	// get the payment meta
	$payment_meta = edd_get_payment_meta( $payment_id );
 
	// update our agreement to send feedback emails
	$payment_meta['edd_feedback_agree_send_email'] = isset( $_POST['edd_feedback_agree_send_email'] ) ? $_POST['edd_feedback_agree_send_email'] : false;
 
	// update the payment meta with the new array 
	update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'edd_feedback_updated_edited_purchase' );