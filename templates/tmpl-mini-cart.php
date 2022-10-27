<?php
/**
 * The Template for displaying cart
 * @package     Yatra\Templates
 * @version     2.1.2
 */
defined('ABSPATH') || exit;

?>
<div class="yatra-mini-cart"><?php

    if (count($cart_items) < 1) {

        echo '<p>Your tour cart is empty. Please select any of the booking first.</p>';
        return;
    }
    do_action('before_yatra_cart');

    echo '<div class="yatra-mini-cart-table-wrapper">';

    yatra()->cart->get_mini_cart_table();

    echo '</div>';

    $checkout_page_url = yatra_get_checkout_page(true);
    $cart_page_url = yatra_get_cart_page(true);

    $proceed_to_checkout_button_text = get_option('yatra_proceed_to_checkout_text', 'Proceed to checkout');

    $view_cart_text = __('View Cart', 'yatra');
    ?>
    <div class="yatra-button-group">
        <a href="<?php echo esc_url_raw($cart_page_url) ?>" class="yatra-button button yatra-view-cart-button">
            <?php echo esc_html($view_cart_text); ?></a>
        <a href="<?php echo esc_url_raw($checkout_page_url) ?>" class="yatra-button button yatra-proceed-to-checkout">
            <?php echo esc_html($proceed_to_checkout_button_text); ?></a>

    </div>
</div>
