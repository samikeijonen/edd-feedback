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