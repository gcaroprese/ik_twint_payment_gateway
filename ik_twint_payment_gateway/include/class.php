<?php
/*

Class Ik_Twint_PaymentGateway
Update: 29/08/2021
Author: Gabriel Caroprese

*/

add_action( 'plugins_loaded', 'ik_twintpg_gateway_init', 11 );

function ik_twintpg_gateway_init() {

	class Ik_Twint_PaymentGateway extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'ik_twintpg';
			$this->icon               = apply_filters('ik_twintpg_gateway_filter_icon', IK_TWINTPG_PLUGIN_DIR_PUBLIC.'\img\twint-icon.png' );
			$this->has_fields         = false;
			$this->method_title       = __( 'Twint Payments', 'ik_twintpg' );
			$this->method_description = __( 'Zahlungen über Twint. Bestellungen werden beim Eingang als "in der Warteschleife" gekennzeichnet.', 'ik_twintpg' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}
	
	
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'ik_twintpg_gateway_filter_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Aktivieren/Deaktivieren', 'ik_twintpg' ), // enable/Disable
					'type'    => 'checkbox',
					'label'   => __( 'Aktivieren TWINT Zahlung', 'ik_twintpg' ),//enable Twint Payment
					'default' => 'yes'
				),
				
				'title' => array(
					'title'       => __( 'Titel', 'ik_twintpg' ),//Title
					'type'        => 'text',
					'description' => __( 'Dies steuert den Titel für die Zahlungsmethode, die der Kunde während des Checkouts sieht.', 'ik_twintpg' ),
					'default'     => __( 'Twint Payments', 'ik_twintpg' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Beschreibung', 'ik_twintpg' ), //Description
					'type'        => 'textarea',
					'description' => __( 'Beschreibung der Zahlungsmethode, die der Kunde an Ihrer Kasse sieht.', 'ik_twintpg' ),
					'default'     => __( 'Für eine TWINT Zahlung, senden Sie uns den Rechnungsbetrag an folgende Handynummer: 012 345 67 89', 'ik_twintpg' ),
					'desc_tip'    => true,
				),
				
				'instructions' => array(
					'title'       => __( 'Anleitung', 'ik_twintpg' ),
					'type'        => 'textarea',
					'description' => __( 'Anweisungen, die der Dankesseite und den E-Mails hinzugefügt werden.', 'ik_twintpg' ),
					'default'     => '',
					'desc_tip'    => true,
				),
			) );
		}
	
	
		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}
	
	
		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
	
	
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Warten auf Zahlungsbestätigung durch Twint.', 'ik_twintpg' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
	
  }
}

?>