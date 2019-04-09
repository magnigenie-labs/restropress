<?php
/**
 * Emails
 *
 * This class handles all emails sent through RPRESS
 *
 * @package     RPRESS
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.1.php GNU Public License
 * @since       2.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Emails Class
 *
 * @since  1.0.0
 */
class RPRESS_Emails {

	/**
	 * Holds the from address
	 *
	 * @since  1.0.0
	 */
	private $from_address;

	/**
	 * Holds the from name
	 *
	 * @since  1.0.0
	 */
	private $from_name;

	/**
	 * Holds the email content type
	 *
	 * @since  1.0.0
	 */
	private $content_type;

	/**
	 * Holds the email headers
	 *
	 * @since  1.0.0
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since  1.0.0
	 */
	private $html = true;

	/**
	 * The email template to use
	 *
	 * @since  1.0.0
	 */
	private $template;

	/**
	 * The header text for the email
	 *
	 * @since  2.1
	 */
	private $heading = '';

	/**
	 * Get things going
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'rpress_email_send_before', array( $this, 'send_before' ) );
		add_action( 'rpress_email_send_after', array( $this, 'send_after' ) );

	}

	/**
	 * Set a property
	 *
	 * @since  1.0.0
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Get a property
	 *
	 * @since 1.0.0.9
	 */
	public function __get( $key ) {
		return $this->$key;
	}

	/**
	 * Get the email from name
	 *
	 * @since  1.0.0
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = rpress_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'rpress_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Get the email from address
	 *
	 * @since  1.0.0
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = rpress_get_option( 'from_email' );
		}

		if( empty( $this->from_address ) || ! is_email( $this->from_address ) ) {
			$this->from_address = get_option( 'admin_email' );
		}

		return apply_filters( 'rpress_email_from_address', $this->from_address, $this );
	}

	/**
	 * Get the email content type
	 *
	 * @since  1.0.0
	 */
	public function get_content_type() {
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'rpress_email_default_content_type', 'text/html', $this );
		} else if ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'rpress_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get the email headers
	 *
	 * @since  1.0.0
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'rpress_email_headers', $this->headers, $this );
	}

	/**
	 * Retrieve email templates
	 *
	 * @since  1.0.0
	 */
	public function get_templates() {
		$templates = array(
			'default' => __( 'Default Template', 'restropress' ),
			'none'    => __( 'No template, plain text only', 'restropress' )
		);

		return apply_filters( 'rpress_email_templates', $templates );
	}

	/**
	 * Get the enabled email template
	 *
	 * @since  1.0.0
	 *
	 * @return string|null
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = rpress_get_option( 'email_template', 'default' );
		}

		return apply_filters( 'rpress_email_template', $this->template );
	}

	/**
	 * Get the header text for the email
	 *
	 * @since  1.0.0
	 */
	public function get_heading() {
		return apply_filters( 'rpress_email_heading', $this->heading );
	}

	/**
	 * Parse email template tags
	 *
	 * @since  1.0.0
	 * @param string $content
	 */
	public function parse_tags( $content ) {

		// The email tags are parsed during setup for purchase receipts and sale notifications
		// Onoce tags are not restricted to payments, we'll expand this. 

		return $content;
	}

	/**
	 * Build the final email
	 *
	 * @since  1.0.0
	 * @param string $message
	 *
	 * @return string
	 */
	public function build_email( $message ) {

		if ( false === $this->html ) {
			return apply_filters( 'rpress_email_message', wp_strip_all_tags( $message ), $this );
		}

		$message = $this->text_to_html( $message );

		ob_start();

		rpress_get_template_part( 'emails/header', $this->get_template(), true );

		/**
		 * Hooks into the email header
		 *
		 * @since  1.0.0
		 */
		do_action( 'rpress_email_header', $this );

		if ( has_action( 'rpress_email_template_' . $this->get_template() ) ) {
			/**
			 * Hooks into the template of the email
			 *
			 * @param string $this->template Gets the enabled email template
			 * @since  1.0.0
			 */
			do_action( 'rpress_email_template_' . $this->get_template() );
		} else {
			rpress_get_template_part( 'emails/body', $this->get_template(), true );
		}

		/**
		 * Hooks into the body of the email
		 *
		 * @since  1.0.0
		 */
		do_action( 'rpress_email_body', $this );

		rpress_get_template_part( 'emails/footer', $this->get_template(), true );

		/**
		 * Hooks into the footer of the email
		 *
		 * @since  1.0.0
		 */
		do_action( 'rpress_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return apply_filters( 'rpress_email_message', $message, $this );
	}

	/**
	 * Send the email
	 * @param  string  $to               The To address to send to.
	 * @param  string  $subject          The subject line of the email to send.
	 * @param  string  $message          The body of the email to send.
	 * @param  string|array $attachments Attachments to the email in a format supported by wp_mail()
	 * @since  1.0.0
	 */
	public function send( $to, $subject, $message, $attachments = '' ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send email with RPRESS_Emails until init/admin_init has been reached', 'restropress' ), null );
			return false;
		}

		/**
		 * Hooks before the email is sent
		 *
		 * @since  1.0.0
		 */
		do_action( 'rpress_email_send_before', $this );

		$subject = $this->parse_tags( $subject );
		$message = $this->parse_tags( $message );

		$message = $this->build_email( $message );

		$attachments = apply_filters( 'rpress_email_attachments', $attachments, $this );

		$sent       = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );
		$log_errors = apply_filters( 'rpress_log_email_errors', true, $to, $subject, $message );

		if( ! $sent && true === $log_errors ) {
			if ( is_array( $to ) ) {
				$to = implode( ',', $to );
			}

			$log_message = sprintf(
				__( "Email from RestroPress failed to send.\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'restropress' ),
				date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
				$to,
				$subject
			);

			error_log( $log_message );
		}

		/**
		 * Hooks after the email is sent
		 *
		 * @since  1.0.0
		 */
		do_action( 'rpress_email_send_after', $this );

		return $sent;

	}

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since  1.0.0
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since  1.0.0
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Reset heading to an empty string
		$this->heading = '';
	}

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since  1.0.0
	 */
	public function text_to_html( $message ) {

		if ( 'text/html' == $this->content_type || true === $this->html ) {
			$message = apply_filters( 'rpress_email_template_wpautop', true ) ? wpautop( $message ) : $message;
			$message = apply_filters( 'rpress_email_template_make_clickable', true ) ? make_clickable( $message ) : $message;
			$message = str_replace( '&#038;', '&amp;', $message );
		}

		return $message;
	}

}
