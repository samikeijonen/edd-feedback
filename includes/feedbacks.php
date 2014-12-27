<?php
/**
 * Email feedback functions
 *
 * Some of the feedback functions are from Pippin Williamson and his Software License Plugin.
 * I have modified some of them and added new ones.
 *
 * @author      Sami Keijonen
 * @author      Pippin Williamson
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @link        https://easydigitaldownloads.com/extensions/software-licensing/
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @package     EDDFeedback\Feedbacks
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve feedback periods
 *
 * @since  1.0.0
 * @return array Feedback periods
 */
function edd_feedback_get_periods() {
	$periods = array(
		'-1day'    => __( 'One day after purchase', 'edd-feedback' ),
		'-2days'   => __( 'Two days after purchase', 'edd-feedback' ),
		'-3days'   => __( 'Three days after purchase', 'edd-feedback' ),
		'-1week'   => __( 'One week after purchase', 'edd-feedback' ),
		'-2weeks'  => __( 'Two weeks after purchase', 'edd-feedback' ),
		'-3weeks'  => __( 'Two weeks after purchase', 'edd-feedback' ),
		'-1month'  => __( 'One month after purchase', 'edd-feedback' ),
		'-2months' => __( 'Two months after purchase', 'edd-feedback' ),
		'-3months' => __( 'Three months after purchase', 'edd-feedback' ),
	);
	return apply_filters( 'edd_feedback_get_periods', $periods );
}

/**
 * Retrieve the feedback label
 *
 * @since  1.0.0
 * @return String
 */
function edd_feedback_get_period_label( $feedback_id = 0 ) {
	
	$feedback  = edd_feedback_get_feedback( $feedback_id );
	$periods   = edd_feedback_get_periods();
	$label     = $periods[ $feedback['send_period'] ];

	return apply_filters( 'edd_feedback_get_period_label', $label, $feedback_id );
}

/**
 * Retrieve a feedback
 *
 * @since  1.0.0
 * @return array Renewal feedback details
 */
function edd_feedback_get_feedback( $feedback_id = 0 ) {

	$feedbacks  = edd_feedback_get_feedbacks();

	$defaults = array(
		'subject'      => __( 'Love to hear feedback', 'edd-feedback' ),
		'send_period'  => '-1week',
		'message'      => 'Hello {name},

We would love to hear feedback from you.

You have purchased following products from us:

{downloads}

Are you happy with your purchase? Is there anything we can do for you?'
	);

	$feedback = isset( $feedbacks[ $feedback_id ] ) ? $feedbacks[ $feedback_id ] : $feedbacks[0];

	$feedback = wp_parse_args( $feedback, $defaults );

	return apply_filters( 'edd_feedback_get_feedback', $feedback, $feedback_id );

}

/**
 * Retrieve feedbacks
 *
 * @since  1.0.0
 * @return array Feedbacks defined in settings
 */
function edd_feedback_get_feedbacks() {

	// Get feedbacks from options
	$feedbacks = get_option( 'edd_feedback_feedbacks', array() );

	if( empty( $feedbacks ) ) {

		$message = 'Hello {name},

We would love to hear feedback from you.

You have purchased following products from us:

{downloads}

Are you happy with your purchase? Is there anything we can do for you?';

		$feedbacks[0] = array(
			'subject'     => __( 'Love to hear feedback', 'edd-feedback' ),
			'send_period' => '-1week',
			'message'     => $message
		);

	}

	return apply_filters( 'edd_feedback_get_feedbacks', $feedbacks );
}

/**
 * Check email feedback once a day.
 *
 * Uses EDD daily cron job. Send email feedback for users.
 *
 * @since  1.0.0
 * @return void
 */
function edd_feedback_scheduled_reminders() {

	if( edd_feedback_disabled() ) {
		return;
	}

	$edd_feedback_emails = new EDD_Feedback_Emails;

	$feedbacks = edd_feedback_get_feedbacks();

	foreach( $feedbacks as $feedback_id => $feedback ) {
		
		// Get payments for date that matches send period
		$date_unix = strtotime( $feedback['send_period'], strtotime( date( 'Y-m-d' ) ) );
		$year  = date( 'Y', $date_unix );
		$month = date( 'm', $date_unix );
		$day   = date( 'd', $date_unix );

		$payments_args = array(
			'year'   => $year,
			'month'  => $month,
			'day'    => $day,
			'status' => 'complete'
		);
		
		// Add filter for developers
		$payments_args = apply_filters( 'edd_feedback_payments_args', $payments_args );
		
		// Get payments
		$get_payments = edd_get_payments( $payments_args );
		
		// Continue if there is now payments
		if( ! $get_payments ) {
			continue;
		}

		foreach( $get_payments as $get_payment ) {
			
			// Get payment ID
			$payment_id = $get_payment->ID;
			
			// Get email from payment ID
			$email = edd_get_payment_user_email( $payment_id );
			
			// Get payment meta details based on payment id
			$cart_items = edd_get_payment_meta_cart_details( $payment_id );
			
			// Get payment meta based on payment id
			$payment_meta     = edd_get_payment_meta( $payment_id );
			$agree_send_email = $payment_meta['edd_feedback_agree_send_email'];
			
			// Bail if user have unchecked email feedback at checkout
			if( isset( $agree_send_email ) && true != $agree_send_email ) {
				continue;
			}
			
			// Send email for all that have purchased something that matches feedback period
			$edd_feedback_emails->send_renewal_reminder( sanitize_email( $email ), $feedback_id, $payment_id );

		}

	}

}
add_action( 'edd_daily_scheduled_events', 'edd_feedback_scheduled_reminders' );
