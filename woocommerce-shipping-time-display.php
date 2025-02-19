<?php
/*
Plugin Name: WooCommerce Shipping Time Display
Plugin URI: https://wcwiz.com/woocommerce-shipping-time-display
Description: Adds a custom shipping time field to WooCommerce products and displays it on the product page.
Version: 1.0
Author: Shameem Reza
Author URI: https://shameem.dev
License: GPL2
*/

// Add shipping time field to WooCommerce product settings
add_action('woocommerce_product_options_general_product_data', function () {
    woocommerce_wp_text_input([
        'id'          => '_shipping_time',
        'label'       => __('Shipping Time', 'woocommerce'),
        'description' => __('Enter shipping time, e.g., 24H, 48H, 3 days', 'woocommerce'),
        'desc_tip'    => true,
    ]);
});

// Save the shipping time field data
add_action('woocommerce_process_product_meta', function ($post_id) {
    if (isset($_POST['_shipping_time'])) {
        update_post_meta($post_id, '_shipping_time', sanitize_text_field($_POST['_shipping_time']));
    }
});

// Display the shipping time on the product page
add_action('woocommerce_single_product_summary', function () {
    global $product;
    $shipping_time = get_post_meta($product->get_id(), '_shipping_time', true);
    if ($shipping_time) {
        echo '<p><strong>' . __('Shipping Time:', 'woocommerce') . '</strong> ' . esc_html($shipping_time) . '</p>';
    }
}, 20);
