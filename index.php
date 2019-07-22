<?php
/**
 * Plugin Name: Woo Order Export
 * Plugin URI: https://demo.themecat.org/woo-order-export
 * Description: Woo Order Export is used to export the WooCommerce orders with all details. It has the option to export the order from a selectable date range and order status base.
 * Version: 1.0.1
 * Author: Themecat_Info
 * Author URI: https://themecat.org/
 * Text Domain: woo-order-export
 * Requires at least: 4.5
 * Tested up to: 5.2
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define the plugin directory url in http:// or https:// base
 * @since 1.0.0
 */
if ( ! defined( 'WOE_PLUGIN_FILE' ) ) {
	define( 'WOE_PLUGIN_FILE', plugin_dir_url( __FILE__ ) );
}

/**
 * Define the plugin directory path
 * @since 1.0.0
 */
if ( ! defined( 'WOE_PLUGIN_DIR' ) ) {
	define( 'WOE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Define the plugin data info
 * @since 1.0.0
 */
if ( ! defined( 'WOE_PLUGIN_DETAIL' ) ) {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	define( 'WOE_PLUGIN_DETAIL', get_plugin_data( __FILE__ ) );
}

/**
 * The notification html
 * @since 1.0.0
 * @return string return the html of notifaction.
 */
if ( ! function_exists( 'woo_order_export_swatches_wc_notice' ) ) {
	function woo_order_export_swatches_wc_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Woo Order Export is enabled but not effective. It requires WooCommerce in order to work.', 'woo-order-export' ); ?></p>
		</div>
		<?php
	}
}

/**
 * Hook the notification
 * @since 1.0.0
 * @return void.
 */
if ( ! function_exists( 'woo_order_export_swatches_constructor' ) ) {
	function woo_order_export_swatches_constructor() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'woo_order_export_swatches_wc_notice' );
		} else {
			require_once WOE_PLUGIN_DIR . 'includes/front-end/classes/class-woo-order-export.php';
			new Woo_Order_Export();
		}
	}
}

/**
 * Hook the plugin language translation file
 * @since 1.0.0
 * @return void.
 */
if ( ! function_exists( 'woo_order_export_language' ) ) {
	function woo_order_export_language() {
		load_plugin_textdomain( 'woo-order-export', false, WOE_PLUGIN_DIR . 'languages' );
	}
}

/**
 * Create the plugin download files directory in the upload directory
 * @since 1.0.0
 * @return void.
 */
if ( ! function_exists( 'create_dir' ) ) {
	function create_dir() {
		$dir_check = wp_get_upload_dir()[ 'basedir' ] . '/woo-roder-export';
		if ( ! is_dir( $dir_check ) ) {
			wp_mkdir_p( $dir_check );
		}
	}
}

add_action( 'init', 'woo_order_export_language' );
add_action( 'plugins_loaded', 'woo_order_export_swatches_constructor', 20 );
register_activation_hook( __FILE__, 'create_dir' );
