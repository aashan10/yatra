<?php

class Yatra_Payment_Gateway_PayPal extends Yatra_Payment_Gateways
{
    protected $id = 'paypal';

    public function __construct()
    {

        include_once 'paypal-functions.php';

        $configuration = array(

            'settings' => array(
                'title' => __('PayPal Standard', 'yatra'),
                'default' => 'no',
                'id' => $this->id,
                'frontend_title' => __('PayPal Standard', 'yatra'),

            ),
        );


        add_action('init', array($this, 'yatra_listen_paypal_ipn'));
        add_action('yatra_payment_checkout_payment_gateway_paypal', array($this, 'process_payment'));
        add_action('yatra_verify_paypal_ipn', array($this, 'yatra_paypal_ipn_process'));


        parent::__construct($configuration);


    }

    public function admin_setting_tab()
    {
        $settings =

            array(
                array(
                    'title' => __('PayPal Settings', 'yatra'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'yatra_payment_gateways_paypal_options',
                ),
                array(
                    'title' => __('PayPal Email Address', 'yatra'),
                    'desc' => __(' Enter your PayPal account\'s email', 'yatra'),
                    'id' => 'yatra_payment_gateway_paypal_email',
                    'type' => 'text',
                ),

                array(
                    'type' => 'sectionend',
                    'id' => 'yatra_payment_gateways_paypal_options',
                ),

            );


        return $settings;
    }

    public function process_payment($booking_id)
    {

        /*if (!isset($_GET['do_payment'])) {
            return;
        }

        $booking_id = 70;*/

        $txn_id = get_post_meta($booking_id, 'txn_id', true);

        $payment_id = get_post_meta($booking_id, 'yatra_payment_id', true);

        $booking = get_post($booking_id);

        $booking_status = isset($booking->post_status) ? $booking->post_status : '';

        if ($booking_status == 'yatra-completed' || empty($booking_status) || !empty($txn_id) || !empty($payment_id)) {

            return;
        }

        include_once dirname(__FILE__) . '/class-yatra-gateway-paypal-request.php';


        do_action('yatra_before_payment_process', $booking_id);

        $paypal_request = new Yatra_Gateway_Paypal_Request();

        $redirect_url = $paypal_request->get_request_url($booking_id);

        wp_redirect($redirect_url);

        exit;
    }


    /**
     * Listen for a $_GET request from our PayPal IPN.
     * This would also do the "set-up" for an "alternate purchase verification"
     */
    public function yatra_listen_paypal_ipn()
    {

        if (isset($_GET['yatra_listener']) && $_GET['yatra_listener'] == 'IPN' && isset($_POST['custom'])) {

            do_action('yatra_verify_paypal_ipn');
        }
        // echo WP_CONTENT_DIR;die;
    }


    /**
     * When a payment is made PayPal will send us a response and this function is
     * called. From here we will confirm arguments that we sent to PayPal which
     * the ones PayPal is sending back to us.
     * This is the Pink Lilly of the whole operation.
     */
    public function yatra_paypal_ipn_process()
    {


        /*1. Check that $_POST['payment_status'] is "Completed"
        2. Check that $_POST['txn_id'] has not been previously processed
        3. Check that $_POST['receiver_email'] is your Primary PayPal email
        4. Check that $_POST['payment_amount'] and $_POST['payment_currency'] are correct
        /**
         * Instantiate the IPNListener class
         */
        include dirname(__FILE__) . '/php-paypal-ipn/IPNListener.php';

        $listener = new IPNListener();

        $booking_id = isset($_POST['custom']) ? absint($_POST['custom']) : 0;


        if ($booking_id < 1) {

            return;
        }

        $message = '';


        /**
         * Set to PayPal sandbox or live mode
         */
        $listener->use_sandbox = yatra_payment_gateway_test_mode();

        /**
         * Check if IPN was successfully processed
         */
        if ($verified = $listener->processIpn()) {


            /**
             * Log successful purchases
             */
            $transactionData = $listener->getPostData(); // POST data array

            file_put_contents('yatra-ipn_success.log', print_r($transactionData, true) . PHP_EOL, LOCK_EX | FILE_APPEND);

            $message = json_encode($transactionData);
            /**
             * Verify seller PayPal email with PayPal email in settings
             *
             * Check if the seller email that was processed by the IPN matches what is saved as
             * the seller email in our DB
             */
            if ($_POST['receiver_email'] != get_option('yatra_payment_gateway_paypal_email')) {
                $message .= "\nEmail seller email does not match email in settings\n";
            }

            /**
             * Verify currency
             *
             * Check if the currency that was processed by the IPN matches what is saved as
             * the currency setting
             */
            if (trim($_POST['mc_currency']) != trim(get_option('yatra_currency'))) {
                $message .= "\nCurrency does not match those assigned in settings\n";
            }

            /**
             * Check if this payment was already processed
             *
             * PayPal transaction id (txn_id) is stored in the database, we check
             * that against the txn_id returned.
             */
            $txn_id = get_post_meta($booking_id, 'txn_id', true);
            if (empty($txn_id)) {
                update_post_meta($booking_id, 'txn_id', $_POST['txn_id']);
            } else {
                $message .= "\nThis payment was already processed\n";
            }

            /**
             * Verify the payment is set to "Completed".
             *
             * Create a new payment, send customer an email and empty the cart
             */

            if (!empty($_POST['payment_status']) && $_POST['payment_status'] == 'Completed') {
                // Update booking status and Payment args.

                yatra_update_booking_status($booking_id, 'yatra-completed');

                yatra_update_payment_status($booking_id);

                $payment_id = get_post_meta($booking_id, 'yatra_payment_id', true);

                update_post_meta($payment_id, '_paypal_args', $_POST);

                update_post_meta($payment_id, 'yatra_total_paid_amount', $_POST['mc_gross']);

                update_post_meta($payment_id, 'yatra_total_paid_currency', $_POST['mc_currency']);

                update_post_meta($payment_id, 'yatra_payment_gateway', $this->id);

                do_action('yatra_after_successful_payment', $booking_id, $message, $payment_id, $this->id);


            } else {

                $message .= "\nPayment status not set to Completed\n";

            }

        } else {

            /**
             * Log errors
             */
            $errors = $listener->getErrors();

            file_put_contents('yatra-ipn_errors.log', print_r($errors, true) . PHP_EOL, LOCK_EX | FILE_APPEND);

            do_action('yatra_after_failed_payment', $booking_id, $message, $this->id);
            
        }
        file_put_contents('yatra-ipn_message.log', print_r($message, true) . PHP_EOL, LOCK_EX | FILE_APPEND);


        update_post_meta($booking_id, 'yatra_payment_message', $message);
    }

}