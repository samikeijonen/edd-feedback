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
		$message  = ! empty( $feedback['message'] ) ? $feedback['message'] : __( "Hello {name},\n\nWe would love to hear feedback from you.\n\nYou have purchased following products from us:\n\n{downloads}\n\nAre you happy with your purchase? Is there anything we can do for you?", "edd-feedback" );
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
		
		// Get downloads in a list without links
		$cart_items    = edd_get_payment_meta_cart_details( $payment_id );
		$download_list = '<ul>';

		if ( $cart_items ) {
			foreach ( $cart_items as $item ) {

				if ( edd_use_skus() ) {
					$sku = edd_get_download_sku( $item['id'] );
				}
				
				if ( edd_item_quantities_enabled() ) {
					$quantity = $item['quantity'];
				}

				$price_id = edd_get_cart_item_price_id( $item );

				$title = get_the_title( $item['id'] );
				
				// Add quantity to title
				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'edd-feedback' ) . ': ' . $quantity;
				}

				// Add sku to title
				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'edd-feedback' ) . ': ' . $sku;
				}

				// Add variable pricing option to title
				if ( $price_id !== null ) {
					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$download_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . '</li>';
				
			}
		}

		$download_list .= '</ul>';
		
		$text = str_replace( '{downloads}', $download_list, $text );

		return $text;
	}


}
$edd_feedback_emails = new EDD_Feedback_Emails;
