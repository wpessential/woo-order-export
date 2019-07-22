<?php

class Woo_Order_Export {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'woe_add_page_link' ) );
		add_action( 'wp_ajax_woe_order_file_download', array( $this, 'woe_order_file_download' ) );
		add_action( 'wp_ajax_nopriv_woe_order_file_download', array( $this, 'woe_order_file_download' ) );
		add_action( 'admin_print_scripts', array( $this, 'woe_enqueue_scripts' ) );

	}

	public function woe_add_page_link() {
		$page = add_submenu_page( 'woocommerce', esc_html__( 'Orders Export', 'woo-order-export' ), esc_html__( 'Orders Export', 'woo-order-export' ), 'edit_posts', 'order_exports', array(
			$this,
			'woe_add_form'
		) );
	}

	public function woe_add_form() {
		?>
		<div class="container">
			<form method="post" class="order_export_form">
				<h1><?php echo esc_html( 'Orders Export' ); ?></h1>
				<div class="row">
					<div class="col-25">
						<label for="fdate"><?php esc_html_e( 'Start Date', 'woo-order-export' ) ?>:</label>
					</div>
					<div class="col-75">
						<input type="text" id="form_date" name="from_date" required placeholder="MM/DD/YY">
					</div>
				</div>
				<div class="row">
					<div class="col-25">
						<label for="tdate"><?php esc_html_e( 'End Date', 'woo-order-export' ) ?>:</label>
					</div>
					<div class="col-75">
						<input type="text" id="to_date" name="to_date" required placeholder="MM/DD/YY">
					</div>
				</div>
				<div class="row">
					<div class="col-25">
						<label for="order_status"><?php esc_html_e( 'Order Status', 'woo-order-export' ) ?>:</label>
					</div>
					<div class="col-75">
						<select name="order_status">
							<option value="wc-on-hold" selected="selected"><?php esc_html_e( 'On hold', 'woo-order-export' ) ?></option>
							<option value="wc-failed"><?php esc_html_e( 'Failed', 'woo-order-export' ) ?></option>
							<option value="wc-refunded"><?php esc_html_e( 'Refunded', 'woo-order-export' ) ?></option>
							<option value="wc-completed"><?php esc_html_e( 'Completed', 'woo-order-export' ) ?></option>
							<option value="wc-cancelled"><?php esc_html_e( 'Cancelled', 'woo-order-export' ) ?></option>
							<option value="wc-pending"><?php esc_html_e( 'Pending payment', 'woo-order-export' ) ?></option>
							<option value="wc-processing"><?php esc_html_e( 'Processing', 'woo-order-export' ) ?></option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-25">
						<label for="post_order"><?php esc_html_e( 'Post Order', 'woo-order-export' ) ?>:</label>
					</div>
					<div class="col-75">
						<select name="order">
							<option value="DESC" selected="selected"><?php esc_html_e( 'Desending', 'woo-order-export' ) ?></option>
							<option value="ASC"><?php esc_html_e( 'Asending', 'woo-order-export' ) ?></option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-25">
						<label for="post_order_by"><?php esc_html_e( 'Post Order By', 'woo-order-export' ) ?>:</label>
					</div>
					<div class="col-75">
						<select name="orderby">
							<option value="none" selected="selected"><?php esc_html_e( 'None', 'woo-order-export' ) ?></option>
							<option value="ID"><?php esc_html_e( 'IDs', 'woo-order-export' ) ?></option>
							<option value="name"><?php esc_html_e( 'Name', 'woo-order-export' ) ?></option>
							<option value="type"><?php esc_html_e( 'Type', 'woo-order-export' ) ?></option>
							<option value="rand"><?php esc_html_e( 'Random', 'woo-order-export' ) ?></option>
							<option value="date"><?php esc_html_e( 'Date', 'woo-order-export' ) ?></option>
							<option value="modified"><?php esc_html_e( 'Modified', 'woo-order-export' ) ?></option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-25">
						<label for="custom_address"><?php esc_html_e( 'Customer Email', 'woo-order-export' ) ?>:</label>
					</div>
					<div class="col-75">
						<input type="email" id="custom_address" name="custom_address" placeholder="admin@example.com">
					</div>
				</div>
				<div class="row">
					<?php echo wp_nonce_field( 'woe_order_file_download', 'export_orders_ref' ); ?>
					<input type="submit" value="<?php esc_attr_e( 'Export Orders', 'woo-order-export' ); ?>">
				</div>
			</form>
		</div>
		<?php
	}

	public function woe_get_order_ids( $data_q = array() ) {
		$ids  = [];
		$args = [
			'date_created' => $data_q[ 0 ] . '...' . $data_q[ 1 ],
			'status'       => $data_q[ 2 ],
			'order'        => $data_q[ 3 ],
			'orderby'      => $data_q[ 4 ],
		];
		if ( ! empty( $data_q[ 5 ] ) ) {
			$args[ 'customer' ] = $data_q[ 5 ];
		}
		$order_get = wc_get_orders( $args );
		if ( ! empty( $order_get ) ) {
			foreach ( $order_get as $result ) {
				$ids[] = $result->get_id();
			}
		}

		return $ids;
	}

	public function woe_get_orders_detail( $ids ) {
		$data_array = [];
		if ( ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				$order    = new WC_Order( $id );
				$qty      = 0;
				$products = [];
				foreach ( $order->get_items() as $item_id => $item ) {
					$qty        += $item->get_quantity();
					$products[] = $item->get_id();
				}
				$oder_date    = $order->get_date_created();
				$data_array[] = array(
					'order_id'            => $order->get_id(),
					'date'                => $oder_date->date( 'm/d/Y' ),
					'order_status'        => $order->get_status(),
					'product_id'          => implode( '|', $products ),
					'qty'                 => $qty,
					'billing_first_name'  => $order->get_billing_first_name(),
					'billing_Last_name'   => $order->get_billing_last_name(),
					'billing_company'     => $order->get_billing_company(),
					'billing_address_1'   => $order->get_billing_address_1(),
					'billing_address_2'   => $order->get_billing_address_2(),
					'billing_city'        => $order->get_billing_city(),
					'billing_state'       => $order->get_billing_state(),
					'billing_zip_code'    => $order->get_billing_postcode(),
					'billing_country'     => $order->get_billing_country(),
					'billing_email'       => $order->get_billing_email(),
					'billing_phone'       => $order->get_billing_phone(),
					'shipping_first_name' => $order->get_shipping_first_name(),
					'shipping_Last_name'  => $order->get_shipping_last_name(),
					'shipping_company'    => $order->get_shipping_company(),
					'shipping_address_1'  => $order->get_shipping_address_1(),
					'shipping_address_2'  => $order->get_shipping_address_2(),
					'shipping_city'       => $order->get_shipping_city(),
					'shipping_state'      => $order->get_shipping_state(),
					'shipping_zip_code'   => $order->get_shipping_postcode(),
					'shipping_country'    => $order->get_shipping_country(),
					'order_note'          => $order->get_customer_note(),
					'custom_order_note'   => $order->get_meta( 'custom_order_note' ),
					'currency'            => html_entity_decode( $order->get_currency() ),
					'total_price'         => $order->get_total(),
				);
			}
		}

		return $data_array;
	}

	public function woe_order_file_download() {
		check_ajax_referer( 'woe_order_file_download', 'export_orders_ref' );
		$from_data    = sanitize_text_field( $_POST[ 'from_date' ] );
		$to_date      = sanitize_text_field( $_POST[ 'to_date' ] );
		$order_status = sanitize_text_field( $_POST[ 'order_status' ] );
		$order        = sanitize_text_field( $_POST[ 'order' ] );
		$orderby      = sanitize_text_field( $_POST[ 'orderby' ] );
		$email        = sanitize_text_field( $_POST[ 'custom_address' ] );
		if ( empty( $from_data ) && empty( $to_date ) && empty( $order_status ) ) {
			exit( esc_html__( 'Data is not verified...', 'woo-order-export' ) );
		} else {
			$ids    = $this->woe_get_order_ids( [ $from_data, $to_date, $order_status, $order, $orderby, $email ] );
			$orders = $this->woe_get_orders_detail( $ids );
			if ( empty( $orders ) ) {
				return;
			} else {
				$url = $this->woe_get_csv( $orders );
				exit( $url );
			}
		}

	}

	public function woe_get_csv( $report_data ) {
		$csv_file_name = 'order-report-' . time() . '.csv';
		$dir           = wp_get_upload_dir()[ 'basedir' ] . '/woo-roder-export';
		$file_dir_set  = $dir . '/' . $csv_file_name;
		$fop           = @fopen( $file_dir_set, 'w' );

		$header_displayed = false;
		if ( ! empty( $report_data ) ) {
			foreach ( $report_data as $data ) {
				if ( ! $header_displayed ) {
					fputcsv( $fop, array_keys( $data ) );
					$header_displayed = true;
				}
				fputcsv( $fop, $data );

			}
		}
		fclose( $fop );

		if ( ! file_exists( wp_get_upload_dir()[ 'basedir' ] . '/woo-roder-export/' . $csv_file_name ) ) {
			return;
		}

		return wp_get_upload_dir()[ 'baseurl' ] . '/woo-roder-export/' . $csv_file_name;
	}

	public function woe_enqueue_scripts() {
		$screen = get_current_screen();
		if ( strpos( $screen->id, 'woocommerce_page_order_exports' ) === false ) {
			return;
		}

		wp_enqueue_style( 'jquery-ui-datepicker-css', WOE_PLUGIN_FILE . 'assets/css/jquery-ui.min.css', '', WOE_PLUGIN_DETAIL[ 'Version' ], 'all' );
		wp_enqueue_style( 'woo-order-export', WOE_PLUGIN_FILE . 'assets/css/woo-order-export.css', '', WOE_PLUGIN_DETAIL[ 'Version' ], 'all' );

		$script = 'jQuery(document).ready(function($){
			$("#form_date, #to_date").datepicker();
			$(".order_export_form").submit(function(e){
				e.preventDefault();
				var form_data = "action=woe_order_file_download&" + $(this).serialize();
				$.ajax({url:"' . admin_url( 'admin-ajax.php' ) . '",type: "POST",data: form_data,success: function(res){if(res != 0){window.open(res);}else{alert("' . esc_js( 'Data Not Exist.' ) . '");}}});
			});
		})';
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_add_inline_script( 'jquery-ui-datepicker', $script );

	}
}
