<?php
/**
 * Send email class
 *
 * This is modified version of email class from Pippin Williamson and his Software License Plugin.
 *
 * @author      Sami Keijonen
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDFeedback\Renewals
 * @since       1.0.0
 */

class EDD_Feedback_Emails {

	public function send_renewal_reminder( $user_email = null, $feedback_id = 0, $payment_id = 0 ) {
		
		// Bail if there is no email
		if( empty( $user_email ) ) {
			return;
		}

		$send = true;

		$send = apply_filters( 'edd_feedback_send_feedback', $send, $user_email, $feedback_id, $payment_id );

		if( ! $send ) {
			return;
		}
		
		// Payment data
		$payment_data = edd_get_payment_meta( $payment_id );

		// Email to
		$email_to = $user_email;

		$feedback = edd_feedback_get_feedback( $feedback_id );
		$message  = ! empty( $feedback['message'] ) ? $feedback['message'] : __( "Hello {name},\n\nWe would love to hear feedback from you.\n\nYou have purchased following products from us: {edd_feedback_downloads}\n\nAre you happy with your purchase? Is there anything we can do for you?", "edd-feedback" );
		$message  = $this->filter_feedback_template_tags( $message, $user_email, $payment_id );
		
		// run our message through the standard EDD email tag function
		$message  = apply_filters( 'edd_purchase_receipt', edd_do_email_tags( $message, $payment_id ), $payment_id, $payment_data );
		
		$subject  = ! empty( $feedback['subject'] ) ? $feedback['subject'] : __( 'Love to hear feedback', 'edd-feedback' );
		$subject  = $this->filter_feedback_template_tags( $subject, $user_email, $payment_id );
		
		// run subject through the standard EDD email tag function
		$subject  = edd_do_email_tags( $subject, $payment_id );
		
		if( class_exists( 'EDD_Emails' ) ) {
			
			EDD()->emails->__set( 'heading', __( 'Feedback', 'edd-feedback' ) );
			EDD()->emails->send( $email_to, $subject, $message );

		} else {

			$from_name  = get_bloginfo( 'name' );
			$from_email = get_bloginfo( 'admin_email' );
			$headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
			$headers   .= "Reply-To: ". $from_email . "\r\n";

			wp_mail( $email_to, $subject, $message, $headers );

		}
		
		// Add info in post meta just in case
		add_post_meta( $payment_id, sanitize_key( '_edd_feedback_sent_' . $feedback['send_period'] ), time() ); // Prevent renewal feedbacks from being sent more than once
		
	}

	public function filter_feedback_template_tags( $text = '', $user_email = null, $payment_id = 0 ) {
		
		$user_id   = edd_get_payment_user_id( $payment_id );
		$user_info = edd_get_payment_meta_user_info( $payment_id );
		
		// Retrieve the customer name
		if ( $user_id ) {
			$user_data     = get_userdata( $user_id );
			$customer_name = $user_data->display_name;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$customer_name = $user_info['first_name'];
		} else {
			$customer_name = $user_info['email'];
		}
		
		// Get downloads
		$downloads = edd_get_payment_meta_downloads( $payment_id );
	
		if ( is_array( $downloads ) ) {
			// Create a list of downloads
			$download_list = '';
			$download_list = '<ul>';
			foreach ( $downloads as $download ) {
				$download_list = '<li>' . $download['name'] . '</li>';
			}
			$download_list = '</ul>';
		}
		
		//$text = str_replace( '{name}', $customer_name, $text );
		//$text = str_replace( '{downloads}', $downloads, $text );

		return $text;
	}


}
$edd_feedback_emails = new EDD_Feedback_Emails;
