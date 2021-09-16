<?php

if (empty($_POST)) {
    header('Location: /');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $_POST["invoice_id"]; ?></title>
    <link rel="stylesheet" href="https://use.typekit.net/azv2zbv.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</head>

<body>
    <div id="primary" class="content-area  col-xs-12">
        <main id="main" class="site-main">

            <article id="post-2648" class="post-2648 page type-page status-publish hentry">
                <div class="entry-content">
                    <div class="woocommerce">
                        <div class="woocommerce-notices-wrapper"></div>
                        <input type="hidden" name="order_id" value="<?php echo $_POST["invoice_id"]; ?>">
                        <input type="hidden" name="address" value="<?php echo $_POST["crypto_address"]; ?>">
                        <input type="hidden" name="amount" value="<?php echo $_POST["crypto_amount"]; ?>">
                        <input type="hidden" name="order_time" value="<?php echo $_POST["order_time"]; ?>">
                        <input type="hidden" name="maxtime" value="<?php echo $_POST["payment_maxtime"]; ?>">
                        <input type="hidden" name="confirmations" value="<?php echo $_POST["payment_confirmations"]; ?>">


                        <div class="cp-order-info">
                            <ul class="cp-order-info-list">
                                <li class="cp-order-info-list-item">
                                    Order number: <strong><?php echo $_POST["invoice_id"]; ?></strong>
                                </li>

                                <li class="cp-order-info-list-item">
                                    Total: <strong><?php echo $_POST["crypto_amount"]; ?> DOGEC (<?php echo $_POST["fiat_amount"]; ?> <?php echo $_POST["currency"]; ?>)</strong>
                                </li>
                            </ul>
                        </div>

                        <div class="cp-box-wrapper">
                            <div class="cp-box-col-1">
                                <h2>DogeCash cryptocurrency payment</h2>
                                <p class="cp-payment-msg">Please send the exact amount in DOGEC to the payment address below.</p>

                                <div>Amount:</div>
                                <div class="cp-input-box">
                                    <input type="text" class="cp-payment-input" value="<?php echo $_POST["crypto_amount"]; ?>" readonly="">
                                    <button type="button" class="cp-copy-btn"><img src="https://cryptogateways.space/wp-content/plugins/woocommerce-dogecash/img/cp-copy-icon.svg"></button>
                                </div>

                                <br>

                                <div>Payment Address:</div>
                                <div class="cp-input-box">
                                    <input type="text" class="cp-payment-input" value="<?php echo $_POST["crypto_address"]; ?>" readonly="">
                                    <button type="button" class="cp-copy-btn"><img src="https://cryptogateways.space/wp-content/plugins/woocommerce-dogecash/img/cp-copy-icon.svg"></button>
                                </div>

                                <br>

                                <div class="cp-payment-info-holder">
                                    <div class="cp-counter">00:00</div>
                                    <div class="cp-payment-info">
                                        <div class="cp-payment-info-status">Waiting for payment...</div>
                                        <div class="cp-payment-info-text">Exchange rate locked 1 DOGEC = <?php echo $_POST["crypto_rate"]; ?> <?php echo $_POST["currency"]; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="cp-box-col-2">
                                <div class="cp-qr-code-holder">
                                    <img src="https://chart.googleapis.com/chart?chs=300x300&amp;cht=qr&amp;chl=<?php echo $_POST["crypto_address"]; ?>&amp;choe=UTF-8">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </article>

            <form method="post" action="<?php echo urldecode($_POST["callback_url"]); ?>" id="callback-form">
                <input type="hidden" name="x_hash" value="<?php echo $_POST["hash"]; ?>">
                <input type="hidden" name="x_status" value="">
                <input type="hidden" name="x_invoice_id" value="<?php echo $_POST["invoice_id"]; ?>">
                <input type="hidden" name="x_address" value="<?php echo $_POST["crypto_address"]; ?>">
                <input type="hidden" name="x_amount" value="<?php echo $_POST["fiat_amount"]; ?>">
                <input type="hidden" name="x_crypto_amount" value="<?php echo $_POST["crypto_amount"]; ?>">
                <input type="hidden" name="x_order_time" value="<?php echo $_POST["order_time"]; ?>">
                <input type="hidden" name="x_maxtime" value="<?php echo $_POST["payment_maxtime"]; ?>">
                <input type="hidden" name="x_confirmations" value="<?php echo $_POST["payment_confirmations"]; ?>">
                <input type="hidden" name="x_txid" value="">
                <input type="hidden" name="x_redirect_link" value="<?php echo $_POST["redirect_link"]; ?>">
            </form>

        </main>
    </div>
</body>

</html>