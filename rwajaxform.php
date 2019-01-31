<?php 
/*
Plugin Name: RW Ajax Form
Description: Test Ajax form. Tested with BS4 form markup and validation.
Author: Rob Wakeman
Author URI: https://wwww.robwakeman.com
Version: 1.0
License: GPL2
Text Domain: rwajaxform
*/

/**
 * RW Ajax Form class
 */
class RW_Ajax_Form {

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct() {
		
		add_action( 'wp_enqueue_scripts', array( $this, 'rw_enqueue_scripts_styles' ) );

		add_action( 'wp_ajax_nopriv_contact_us', array( $this, 'process_contact_form' ) );
		add_action( 'wp_ajax_contact_us', array( $this, 'process_contact_form' ) );
		
	}



	/**
	 * Enqueue plugin scripts
	 * @return void
	 */
	function rw_enqueue_scripts_styles() {

		// only load js and css if on Ajax form page - hardcoded to work with my own template with Bootstrap form
	  if ( is_page('ajax-form') ):

			wp_enqueue_style( 'rwajaxform', plugin_dir_url( __FILE__ ) . 'css/rw-ajax-form.css' );

			wp_enqueue_script( 'rwajaxform', plugin_dir_url( __FILE__ ) . 'js/rw-ajax-form.js', array( 'jquery' ), null, true );

			// set variables for script
			wp_localize_script( 'rwajaxform', 'settings', array(
				'ajaxurl'	 => admin_url( 'admin-ajax.php' ),
				'error'		 => __( 'RW error sent in rwajaxform.php: Sorry, something went wrong. Please try again', 'rwajaxform' ),
				'nonce' => wp_create_nonce( "rwajaxformsecish" ) // this is a unique token to prevent form hijacking
			) );

		endif; //if ( is_page('ajax-form') )

	}

	/**
	 * Sends bug report to defined email
	 * @return void
	 */	
	function process_contact_form() {

		$data = $_POST;

		// sanitize_text_field is a WP function that performs all the necessary cleaning up functions in one go
		// https://developer.wordpress.org/reference/functions/sanitize_text_field/
		// However, it escapes (backslash) quotes and double quotes - need to investigate

		$name = sanitize_text_field($data["name"]);
		$email = sanitize_email($data["email"]);
		$message = sanitize_text_field($data["message"]);

		$successConfirmation = '<div>';
		$successConfirmation .= '<p>Your enquiry details are:</p>';
		$successConfirmation .= "<ul>";
		$successConfirmation .= "<li class=\"list-unstyled mb-2\"><strong>Name:</strong> $name </li>";
		$successConfirmation .= "<li class=\"list-unstyled mb-2\"><strong>Email:</strong> $email </li>";
		$successConfirmation .= "<li class=\"list-unstyled mb-2\"><strong>Message:</strong> $message </li>";
		$successConfirmation .= "</ul>";
		$successConfirmation .= "</div>";
		

		// check the nonce - nonce_returned is sent in ajaxFormData
		// 3rd arg: $die - option to die if nonce is invalid, default: true
		/*
		If I deliberately misspell action or query_arg (1st and 2nd arguments), it doesn't send this error message to the page. Instead, it triggers the message in $ajax .fail (rw-ajax-form.js).
		*/
		if ( false == check_ajax_referer( 'rwajaxformsecish', 'nonce_returned' ) ) {
			wp_send_json_error( __( '<div class="alert alert-danger" role="alert">JSON error inside check_ajax_referer()</div>', 'rwajaxform' ) );
		}



		// set up the email
		/*line break formatting in plain text email only works if argument for wp_mail fn is in double quotes - as suggested on a forum. Use \r\n to make a new line*/

		$result = wp_mail( "rob@robwakeman.com", "Enquiry from Understrap", $name . "\r\n\r\n" . $email . "\r\n\r\n" . $message);



		// send the email
		if ( true == $result ) {
			wp_send_json_success( __( '<div class="alert alert-success" role="alert">Thanks for sending your enquiry. When we receive the email, we will respond as soon as possible.</div>' . $successConfirmation , 'rwajaxform' ) );
		} else {
			wp_send_json_error( __( '<div class="alert alert-danger" role="alert">JSON error with wp_mail()</div>', 'rwajaxform' ) );
		}

	} // process_contact_form()

} // class RW_Ajax_Form

new RW_Ajax_Form();