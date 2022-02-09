<?php

/**
 * Author: Kwame Oteng Appiah-Nti
 * Author URI: http://twitter.com/developerkwame
 * Author Email: koteng@wigal.com.gh
 * 
 * Redde Functions:
 * Functions to assist plugin
 */

/**
 * Redde Checkout Status
 */
require_once 'functions.php';
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
$wp->init();
$wp->register_globals();
init_session();
@error_reporting(0);
global $woocommerce;
$order = wc_get_order($_SESSION['redde_order_id']);
wc_reduce_stock_levels($_SESSION['redde_order_id']);
$order->add_order_note('Payment failed ');
$order->update_status('failed');
if (isset($_SESSION['redde_payload']) && isset($_SESSION['redde_order_id']) && isset($_SESSION['redde_checkout_response'])) {
?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <title><?= get_bloginfo('name'); ?> Checkout Status</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8" />
        <meta name="robots" content="noindex,nofollow" />
        <meta name="robots" content="nofollow" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta name="description" content="Redde Payment Status">
        <meta name="copyright" content="Wigal Solutions" />
        <link rel="icon" type="image/x-icon" href="../assets/images/redde-ico.png">
        <link href="../assets/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
        <link href="../assets/css/theme.css" rel="stylesheet" type="text/css" media="all" />
        <link href="../assets/css/custom.css" rel="stylesheet" type="text/css" media="all" />
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:200,300,400,400i,500,600,700%7CMerriweather:300,300i" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        <div class="">
            <section class="mgt--60">
                <div class="container">
                    <div class="row" id="mb">
                        <div class="col-md-9 center-block">
                            <div class="boxed boxed--lg boxed--border">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="pricing pricing-3 text-center">
                                            <div class="pricing__head bg--secondary boxed">
                                                <img class="img-80" alt="Image" class="logo" src="<?php echo $_SESSION['redde_payload']['logolink']; ?>" width="20%" />
                                                <strong>
                                                    <h1 id="emd" class="em">Error !! Payment Not Processed</h1>
                                                </strong>
                                                <a style="margin-top: 30px;" class="btn bg--googleplus btn--icon" href="<?= get_site_url(); ?>">
                                                    <span class="btn__text"><i class="fa fa-arrow-right"></i>Return to Main Page</span>
                                                </a>
                                            </div>
                                        </div>
                                        <!--end message -->
                                    </div>
                                </div>
                            </div><!-- row here -->
                        </div>
                    </div>
                    <!--end of message row-->
                </div>
                <!--end of container-->
            </section>

            <footer class="space--sm footer-1 text-center-xs mgt--80">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 center-block text-center text-center-xs">
                            <img alt="Image" class="logo" src="../assets/images/redde-pay.png" />
                            <span class="type--fine-print">&copy;
                                <span class="update-year"></span> Redde Checkout.</span>
                            <a class="type--fine-print" target="_blank" href="https://www.reddeonline.com/assets/policies/redde-privacy-policy.pdf">Privacy</a>
                            <a class="type--fine-print" target="_blank" href="https://www.reddeonline.com/assets/policies/terms-of-use-policy-updated.pdf">Terms</a>
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

    </html>
<?php
    remove_session(array('redde_payload', 'redde_order_id', 'redde_checkout_response'));
} else {
    header('HTTP/1.0 404 Not Found');
    exit;
}

?>