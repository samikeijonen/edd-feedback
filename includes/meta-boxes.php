<?php
/**
 * Add metaboxes
 *
 * @package     EDDFeedback\Metaboxes
 * @since       1.0.0
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom meta box for 'download' metabox.
 *
 * @since 1.0.0
 * @return void
 */

 function edd_feedback_create_meta_boxes() {
 
	add_meta_box( 'edd_feedback_disable_feedbacks', esc_html__( 'Disable Feedback Emails', 'edd-feedback' ), 'edd_feedback_render_disable_feedback_emails_meta_box', 'download' , 'side', 'core' );
	
}
add_action( 'add_meta_boxes', 'edd_feedback_create_meta_boxes' );

/**
 * Displays the metabox for disabling feedback emails.
 *
 * @since  1.0.0
 * @access public
 * @param  object  $post
 * @param  array   $metabox
 * @return void
 */
function edd_feedback_render_disable_feedback_emails_meta_box( $post, $metabox ) {

	wp_nonce_field( basename( __FILE__ ), 'edd-feedback-disable-feedback-emails-nonce' );
	
	// Retrieve metadata values if they already exist
	$edd_feedback_disable_feedback_emails = get_post_meta( $post->ID, '_edd_feedback_disable_feedback_emails', true );
	
	?>
	
	<p>
		<input type="checkbox" name="edd_feedback_disable_feedback_emails" id="edd_feedback_disable_feedback_emails" value="1" <?php checked( $edd_feedback_disable_feedback_emails, true ); ?> />
		<label for="edd_feedback_disable_feedback_emails"><?php _e( 'Check this if you want to disable feedback emails for this item.', 'edd-feedback' ); ?></label>
	</p>
	
	<?php
}

/**
 * Saves the metadata for set as private meta box.
 *
 * @since  1.0.0
 * @access public
 * @param  int     $post_id
 * @param  object  $post
 * @return void
 */
function edd_feedback_save_meta_box( $post_id, $post ) {

	// Check nonce
	if ( !isset( $_POST['edd-feedback-disable-feedback-emails-nonce'] ) || !wp_verify_nonce( $_POST['edd-feedback-disable-feedback-emails-nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	
	// Check for auto save / bulk edit
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	$meta = array(
		'_edd_feedback_disable_feedback_emails' => strip_tags( $_POST['edd_feedback_disable_feedback_emails'] )
	);

        foreach ( $meta as $meta_key => $new_meta_value ) {
			
			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* If there is no new meta value but an old value exists, delete it. */
			if ( current_user_can( 'edit_post', $post_id ) && '' == $new_meta_value && $meta_value ) {
				delete_post_meta( $post_id, $meta_key, $meta_value );
			}	

			/* If a new meta value was added and there was no previous value, add it. */
			elseif ( current_user_can( 'edit_post', $post_id ) && $new_meta_value && '' == $meta_value ) {
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
			}

			/* If the new meta value does not match the old value, update it. */
			elseif ( current_user_can( 'edit_post', $post_id ) && $new_meta_value && $new_meta_value != $meta_value ) {
				update_post_meta( $post_id, $meta_key, $new_meta_value );
			}
				
        }
}
add_action( 'save_post', 'edd_feedback_save_meta_box', 10, 2 );