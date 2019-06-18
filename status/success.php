<?php
/**
* Redde Checkout Status
*
*/
require_once 'functions.php';
init_session();
@error_reporting(0);

if(isset($_SESSION['redde_payload']) && isset($_SESSION['redde_order_id']) && isset($_SESSION['redde_checkout_response'])){

	//Checkout Status URL
	$checkout_url = 'https://api.reddeonline.com/v1/checkoutstatus/'.$_SESSION['redde_checkout_response']->checkouttransid;
	
	//Set http headers
	$headers = array();
	$headers[] = "Content-Type: application/json";
	$headers[] = "apikey: ".$_SESSION['redde_payload']['apikey'];
	$headers[] = "appid: ".$_SESSION['redde_payload']['appid'];

	//Curl Implementation
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$checkout_url);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 

	//Get response and process 
	$result=curl_exec($ch);
	$response_error= curl_error($ch);
	curl_close($ch);

	//Response from Payment Service
	if(empty($response_error)){
		$response = json_decode($result, true);
		if(isset($response['status']) && $response['status'] == 'PAID'){
			require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/wp-config.php");
			$wp->init();
			$wp->register_globals(); 
			global $woocommerce;

			$order = wc_get_order( $_SESSION['redde_order_id'] );
			wc_reduce_stock_levels($_SESSION['redde_order_id']);
			$order->payment_complete();
			$order->add_order_note('Payment successfully processed, Checkout ID : '.$_SESSION['redde_checkout_response']->checkouttransid);
			$order->update_status('completed');
			$woocommerce->cart->empty_cart();

			echo'<!doctype html>
				<html lang="en">
					<head>
						<meta charset="utf-8">
						<title>'.get_bloginfo('name').' Checkout Receipt</title>
						<meta name="viewport" content="width=device-width, initial-scale=1.0">
						<meta charset="utf-8"/>
						<meta name="robots" content="noindex,nofollow" />
						<meta name="robots" content="nofollow"/>
						<meta http-equiv="cache-control" content="no-cache"/>
						<link rel="icon" type="image/x-icon" href="../assets/images/redde-ico.png">
						<link href="../assets/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
						<link href="../assets/css/theme.css" rel="stylesheet" type="text/css" media="all" />
						<link href="../assets/css/custom.css" rel="stylesheet" type="text/css" media="all" />
						<link href="https://fonts.googleapis.com/css?family=Open+Sans:200,300,400,400i,500,600,700%7CMerriweather:300,300i" rel="stylesheet">
						<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
						<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
						<link rel="stylesheet" media="print" href="../assets/css/print.css" />

					</head>

					<body class="bg--secondary">
						<!-- -->
						<div class="r-o">
							<div class="s-o n-f-s">
								<img src="../assets/images/redde-pay.png" style="margin-top: 25px;" width="20%" />
								<div id="n-f-s">
									<div>
										<i class="material-icons">attach_money</i>
										<i class="fa fa-lock"></i>
										<i class="fa fa-money"></i>
										<div></div>
									</div>
								</div>
							</div>
							<div class="dmmi">
								<p id="mm-i" class="typing-white"></p>
							</div>
						</div>
						<!-- -->
						<div id="status">
							<section class="mgt--60">
								<div class="container">
									<div class="row" id="mb">

										<div  class="col-md-9 center-block">
											<div class="boxed boxed--lg boxed--border">
												<div class="row">
													<div class="col-md-12">
														<div class="pricing pricing-3 text-center">
															<div class="pricing__head bg--secondary boxed">
																<p class="center-block">'.get_bloginfo('name').' - '.get_site_url().'</p>
																<img class="img-80" alt="Image" class="logo" src="'.$_SESSION['redde_payload']['logolink'].'" width="20%" />
																<strong><h1 id="sm" class="sm"></h1></strong>
																<strong><h1 id="emd" class="em" style="color: #000;font-weight: bolder;">Payment Confirmation </h1></strong>
																<table class="table">
																	<thead>
																		<tr>
																			<th scope="col">Transaction Status:</th>
																			<th scope="col">&nbsp;</th>
																			<th scope="col">&nbsp;</th>
																			<th scope="col">'.$response['status'].'</th>
																		</tr>
																	</thead>
																	<tbody><tr>
																		<th scope="row">#</th>
																		<td>Item(s)</td>
																		<td>Qty</td>
																		<td>Total</td>
																		</tr>';
																		// Getting the items in the order
																		$order_items = $order->get_items();
																		// Iterating through each item in the order
																		$num = 1;
																		foreach ($order_items as $item_id => $item_data) {
																			// Get the product name
																			$product_name = $item_data['name'];
																			// Get the item quantity
																			$item_quantity = wc_get_order_item_meta($item_id, '_qty', true);
																			// Get the item line total
																			$item_total = wc_get_order_item_meta($item_id, '_line_total', true);

																		echo '<tr>
																		<th scope="row">'.$num.'</th>
																		<td>'.$product_name.'</td>
																		<td>'.$item_quantity.'</td>
																		<td>'.get_option('woocommerce_currency').' '.number_format($item_total,2).'</td></tr>';
																			$num++;
																		}
																		echo'
																		<tr>
																			<th scope="row">&nbsp;</th>
																			<td>&nbsp;</td>
																			<td>Grand Total:</td>
																			<td>'.get_option('woocommerce_currency').' '.$order->get_total().'</td>
																		</tr>
																	</tbody>
																</table>
																<a id="print-button" href="javascript:void(0)" onclick="window.print(); return false;" style="margin-top: 30px;" class="btn btn--primary btn--icon">
																	<span class="btn__text"><i class="fa fa-file"></i>Print Receipt</span>
																</a>
																<a id="close-button" href="'.get_site_url().'" style="margin-top: 30px;" class="btn bg--dark btn--icon" >
																	<span class="btn__text"><i class="fa fa-arrow-right"></i>Return to Main Page</span>
																</a>
															</div>
														</div><!--end message -->
													</div>
												</div>
											</div><!-- row here -->
										</div>
									</div><!--end of message row-->
								</div>
								<!--end of container-->
							</section>

							<footer class="space--sm footer-1 text-center-xs mgt--80">
								<div class="container">
									<div class="row">
										<div class="col-md-6 center-block text-center text-center-xs">
											<img alt="Image" class="logo" src="../assets/images/redde-pay.png" />
											<span class="type--fine-print">&copy;
												<span class="update-year"></span> Redde Payment Service.</span>
											<a class="type--fine-print receipt-hide" target="_blank" href="https://www.reddeonline.com/assets/policies/redde-privacy-policy.pdf">Privacy</a>
											<a class="type--fine-print receipt-hide" target="_blank" href="https://www.reddeonline.com/assets/policies/terms-of-use-policy-updated.pdf">Terms</a>
										</div>
									</div>
									<!--end of row-->
								</div>
								<!--end of container-->
							</footer>
						</div>
						<script src="../assets/js/jquery-3.1.1.min.js"></script>
						<script src="../assets/js/redde-checkout-status.js"></script>
					</body>
				</html>';			

		} else {
			header("location: failure.php");
		}

	}
}
