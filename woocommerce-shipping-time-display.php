<?php
/*
* Plugin Name: WooCommerce Shipping Time Display
* Plugin URI: https://wcwiz.com/how-to-show-shipping-time-on-woocommerce-product-page/
* Description: Adds a custom shipping time field to WooCommerce products and displays it on the product page.
* Version: 1.1
* Author: Shameem Reza
* Author URI: https://shameem.dev
* License: GPL2
* Text Domain: wc-shipping-time
* Domain Path: /languages
* WC requires at least: 5.0
* WC tested up to: 9.6.2
* Requires PHP: 7.3
* Requires Plugins: woocommerce
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Declare HPOS compatibility
add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Add shipping time field to WooCommerce product settings.
 */
function wcst_add_shipping_time_field() {
    woocommerce_wp_text_input([
        'id'          => '_shipping_time',
        'label'       => __('Shipping Time', 'wc-shipping-time'),
        'description' => __('Enter shipping time, e.g., 24H, 48H, 3 days', 'wc-shipping-time'),
        'desc_tip'    => true,
    ]);
}
add_action('woocommerce_product_options_general_product_data', 'wcst_add_shipping_time_field');

/**
 * Save the shipping time field data.
 */
function wcst_save_shipping_time_field($post_id) {
    if (isset($_POST['_shipping_time'])) {
        update_post_meta($post_id, '_shipping_time', sanitize_text_field($_POST['_shipping_time']));
    }
}
add_action('woocommerce_process_product_meta', 'wcst_save_shipping_time_field');

/**
 * Display the shipping time on the product page.
 */
function wcst_display_shipping_time() {
    global $product;

    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    $shipping_time = get_post_meta($product->get_id(), '_shipping_time', true);
    if (!empty($shipping_time)) {
        echo '<p><strong>' . esc_html__('Shipping Time:', 'wc-shipping-time') . '</strong> ' . esc_html($shipping_time) . '</p>';
    }
}
add_action('woocommerce_single_product_summary', 'wcst_display_shipping_time', 20);
