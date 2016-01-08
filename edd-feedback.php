<?php
/**
 * Plugin Name:     EDD Feedback
 * Plugin URI:      https://foxland.fi/downloads/edd-feedback
 * Description:     Send feedback emails automatically after purchase. 
 * Version:         1.0.4
 * Author:          Sami Keijonen
 * Author URI:      https://foxland.fi
 * Text Domain:     edd-feedback
 * Domain Path:     /languages
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package         EDD\EDDFeedback
 * @author          Sami Keijonen <sami.keijonen@foxnet.fi>
 * @copyright       Copyright (c) Sami Keijonen
 * @license         http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'EDD_Feedback' ) ) {

	/**
	 * Main EDD_Feedback class
	 *
	 * @since          1.0.0
	 */
	class EDD_Feedback {

		/**
		* @var         EDD_Feedback $instance The one true EDD_Feedback
		* @since       1.0.0
		*/
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true EDD_Feedback
		 */
		public static function instance() {
			if( !self::$instance ) {
				self::$instance = new EDD_Feedback();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			
			// Plugin version
			define( 'EDD_FEEDBACK_VER', '1.0.4' );

			// Plugin path
			define( 'EDD_FEEDBACK_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_FEEDBACK_URL', plugin_dir_url( __FILE__ ) );

		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
		
			// Get out if EDD is not active
			if( ! function_exists( 'EDD' ) ) {
				return;
			}
			
			// Include files and scripts
			if ( is_admin() ) {
				require_once EDD_FEEDBACK_DIR . 'includes/meta-boxes.php';
				require_once EDD_FEEDBACK_DIR . 'includes/settings.php';
			}
			
			require_once EDD_FEEDBACK_DIR . 'includes/functions.php';
			require_once EDD_FEEDBACK_DIR . 'includes/feedbacks.php';
			require_once EDD_FEEDBACK_DIR . 'includes/EDD_Feedback_Emails.php';
		}
		
		
		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
		
			// Load the default language files
			load_plugin_textdomain( 'edd-feedback', false, 'edd-feedback/languages' );
			
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
         */
		private function hooks() {
            
			// Register section for settings
			add_filter( 'edd_settings_sections_extensions', array( $this, 'settings_sections' ) );
			
			// Register settings
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );

			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'EDD Feedback', EDD_FEEDBACK_VER, 'Sami Keijonen', null, 'http://foxland.fi/' );
			}
		}
		
		/**
		 * Add section for settings
		 *
		 * @access      public
		 * @since       1.0.4
		 * @param       array $sections The existing EDD sections array
		 * @return      array The modified EDD sections array
		 */
		public function settings_sections( $sections ) {
				
			$sections['edd-feedback-settings-section'] = esc_html_x( 'EDD Feedback', 'section name in settings', 'edd-feedback' );
			return $sections;
				
		}


		/**
		 * Add settings
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       array $settings The existing EDD settings array
		 * @return      array The modified EDD settings array
		 */
		public function settings( $settings ) {
            
			$edd_feedback_settings = array(
				array(
					'id'          => 'edd_feedback_settings',
					'name'        => '<strong>' . __( 'EDD Feedback Settings', 'edd-feedback' ) . '</strong>',
					'desc'        => __( 'Configure EDD Feedback Settings', 'edd-feedback' ),
					'type'        => 'header',
				),
				array(
					'id'          => 'edd_feedback_disable_emails',
					'name'        => __( 'Disable Feedback Emails', 'edd-feedback' ),
					'desc'        => __( 'Check this box if you do not want to send feedback emails.', 'edd-feedback' ),
					'type'        => 'checkbox'
				),
				array(
					'id'          => 'edd_feedback_allow_checkout_hidden',
					'name'        => __( 'Hide on Checkout', 'edd-feedback' ),
					'desc'        => __( 'Check this box if you do not want to show "Allow sending feedback email" checkbox on the checkout page. This is useful when you are already asking users to join email list.', 'edd-feedback' ),
					'type'        => 'checkbox'
				),
				array(
					'id'          => 'feedback_feedbacks', // EDD adds prefix 'edd_' in hook type
					'name'        => __( 'Feedbacks', 'edd-feedback' ),
					'desc'        => __( 'Configure the feedback notice emails.', 'edd-feedback' ),
					'type'        => 'hook'
				)
			);
			
			// If EDD is at version 2.5 or later use section for settings.
			if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
				// Use the previously noted array key as an array key again and next your settings
				$edd_feedback_settings = array( 'edd-feedback-settings-section' => $edd_feedback_settings );
			}

			return array_merge( $settings, $edd_feedback_settings );
		}
		
	}


	/**
	 * The main function responsible for returning the one true EDD_Feedback
	 * instance to functions everywhere
	 *
	 * @since       1.0.0
	 * @return      \EDD_Feedback The one true EDD_Feedback
	 */
	function EDD_Feedback_load() {
	
		if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			if( ! class_exists( 'EDD_Extension_Activation' ) ) {
				require_once 'includes/class.extension-activation.php';
			}

			$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$activation = $activation->run();
		} else {
			return EDD_Feedback::instance();
		}

	}

	add_action( 'plugins_loaded', 'EDD_Feedback_load' );

} // End if class_exists check
