<?php
/**
 * Plugin Name: Redde Payment Service for WooCommerce
 * Plugin URI: index.php
 * Description: Secure Payment for Mobile Money and Ghana Issued Cards
 * Version: 1.0
 * Author: Wigal Solutions
 * Developed by: Kwame Oteng Appiah-Nti
 * Author URI: https://wigalsolutions.com
 * Author Email: developerkwame@gmail.com
 * License: GPLv2 or later
 * Requires at least: 4.4
 * Tested up to: 5.2
 * 
 * 
 * @package Redde Payment Service
 * @category Plugin
 * @author Kwame Oteng Appiah-Nti
 * @company Wigal Solutions Limited

 Copyright 2019 Wigal Solution Limited
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Admin 'Settings' link on plugin page
 **/
function redde_wc_plugin_admin_action( $actions, $plugin_file ) {
	if ( false == strpos( $plugin_file, basename( __FILE__ ) ) ) {
		return $actions;
	}
	$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=redde-wc-payment' ) . '">Settings</a>';

	array_unshift( $actions, $settings_link );
	return $actions;
}
add_filter( 'plugin_action_links', 'redde_wc_plugin_admin_action', 10, 2 );

function init_redde_wc_payment_gateway() {

	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		class Redde_WC_Payment_Gateway extends WC_Payment_Gateway {
			public function __construct() {
				$this->id                   = 'redde-wc-payment';
				$this->icon                 = plugins_url( 'assets/images/redde-pay.png', __FILE__ );
				$this->has_fields           = true;
				$this->method_title         = __( 'Redde Payment Service', '' );
				$this->method_description = 'WooCommerce Payment Plugin for Redde Payment Service.';
				$this->description = "Secure payment with Mobile Money or Credit Card";
				$this->init_form_fields();
				$this->init_settings();
				$this->title                = $this->get_option( 'title' );
				$this->checkout_url = 'https://api.reddeonline.com/v1/checkout/';

				//$this->default_payment             = $this->get_option( 'default_payment');
				$this->redde_app_id 	           = $this->get_option( 'app_id' );
				$this->redde_api_key               = $this->get_option( 'api_key' );
				$this->redde_merchant_name         = $this->get_option( 'merchant_name' );
				$this->redde_merchant_logo         = $this->get_option( 'merchant_logo' );
				$this->redde_success_callback      = $this->get_option( 'success_callback' );
				$this->redde_failure_callback      = $this->get_option( 'failure_callback' );

				if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
					add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
				} else {
					add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
				}

				/*if ($this->get_option( 'default_payment' ) != "no") {
					add_filter( 'woocommerce_checkout_fields' , 'redde_custom_override_checkout_fields' );
				}*/
			}

			public function init_form_fields() {
				$this->form_fields = array(
					'enabled' => array(
						'title'       => __( 'Enable/Disable', '' ),
						'type'        => 'checkbox',
						'description' => __( 'Check in order to enable ePay WooCommerce Payment Gateway, otherwise, uncheck to disable.', '' ),
						'label'       => __( 'Enable Redde Payment', '' ),
						'default'     => 'no',
						'desc_tip'    => true,
					),
					/*'default_payment' => array(
						'title'       => __( 'Set as Default Payment Gateway', '' ),
						'type'        => 'checkbox',
						'description' => __( 'Check to enable or disable Redde as your default payment gatement. Also this will remove some fields from woocommerce during checkout.', '' ),
						'label'       => __( 'Set as default', '' ),
						'default'     => 'no',
						'desc_tip'    => false,
					),*/
					'title' => array(
						'title'       => __( 'Title', '' ),
						'type'        => 'text',
						'class'       => 'is-read-only',
						'description' => __( 'This controls the title which the user sees during checkout.', '' ),
						'default'     => __( 'Redde Checkout', '' ),
						'desc_tip'    => true,
					),
					'app_id' => array(
						'title'       => __( 'App ID', '' ),
						'type'        => 'text',
						'description' => __( 'App id given to you by Redde Team', '' ),
						'default'     => __( '', '' ),
						'desc_tip'    => true,
					),
					'api_key' => array(
						'title'       => __( 'Apikey', '' ),
						'type'        => 'text',
						'description' => __( 'Apikey given to you by Redde Team', '' ),
						'default'     => __( '', '' ),
						'desc_tip'    => true,
					),
					'description' => array(
						'title'       => __( 'Description', '' ),
						'type'        => 'text',
						'label'       => __( 'Enable to collect onsite payment.', '' ),
						'description' => __( 'Description for merchants payment', '' ),
						'default'     => __( '', '' ),
						'desc_tip'    => false,
					),
					'merchant_name' => array(
						'title'       => __( 'Merchant Name', '' ),
						'type'        => 'text',
						'description' => __( 'This will display merchant name on checkout', '' ),
						'default'     => __( '', '' ),
						'desc_tip'    => false,
					),
					'merchant_logo' => array(
						'title'       => __( 'Merchant Logo', '' ),
						'type'        => 'text',
						'description' => __( 'This will be used to display merchant logo', '' ),
						'default'     => __( '', '' ),
						'desc_tip'    => false,
					),
				);
			}

			/**
			 * Handle payment and process the order.
			 * Also tells WC where to redirect the user, and this is done with a returned array.
			 * Redirect to Redde
			 **/
			function process_payment($order_id)
			{
				global $woocommerce;
				$order = new WC_Order( $order_id );

				// Get an instance of the WC_Order object
				$order = wc_get_order( $order_id );
				$order_data = $order->get_items();

				$payload = array (
					'appid' => $this->redde_app_id,
					'apikey' => $this->redde_api_key,
					'merchantname' => $this->redde_merchant_name,
					'logolink' => esc_url( $this->redde_merchant_logo ),
					'amount' => ( $woocommerce->cart->total ),
					'description' => $this->get_option( 'description' ),
					'clienttransid' => $this->redde_app_id . '-' . time() .'-'. $order_id,
					'failurecallback'=> esc_url( plugin_dir_url(__FILE__).'status/failure.php'),
					'successcallback'=> esc_url( plugin_dir_url(__FILE__).'status/success.php'),
				);

				$response = wp_remote_post($this->checkout_url, array(
					'method'    => 'POST',
					'body'      => json_encode($payload),
					'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
					'timeout'   => 45,
				)
										  );

				//Get response code and body 
				$response_code = wp_remote_retrieve_response_code( $response );
				$response_body = wp_remote_retrieve_body($response);

				$checkout_response = json_decode($response_body);
				$checkout_response = ( ! is_wp_error( $response ) ) ? json_decode( $response_body ) : null;
				//$token = urlencode($checkout_response->responsetoken);
			    //$checkout_url = "http://local.io/redde_checkout/?token=" . $token;
				switch ($response_code) {
					case 200:
						$order->update_status('on-hold', 'Payment in progress');
						//Set session variables
						@session_start();
						$_SESSION['redde_payload'] = $payload;
						$_SESSION['redde_order_id'] = $order_id;
						$_SESSION['redde_checkout_response'] = $checkout_response;
						return array (
							'result'   => 'success',
							'redirect' => $checkout_response->checkouturl //$checkout_url
						);
						break;
					case 400:
						wc_add_notice("HTTP STATUS: $response_code - $checkout_response->reason", "error" );
						break;
					case 500:
						wc_add_notice("HTTP STATUS: $response_code - $checkout_response->reason", "error" );
						break;
					default:
						wc_add_notice("HTTP STATUS CODE here: $response_code Error Connecting to Redde Payment Service, Please try again.", "error" );
						break;
				}
			}
		}//end of class 

	}
}

function wc_add_redde_payment_gateway( $methods ) {
	$methods[] = 'Redde_WC_Payment_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'wc_add_redde_payment_gateway' );
add_action( 'plugins_loaded', 'init_redde_wc_payment_gateway', 0 );
