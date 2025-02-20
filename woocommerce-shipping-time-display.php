<?php
/*
* Plugin Name: WooCommerce Shipping Time Display
* Plugin URI: https://wcwiz.com/how-to-show-shipping-time-on-woocommerce-product-page/
* Description: Adds a custom shipping time field to WooCommerce products and displays it on the product page.
* Version: 1.9
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
 * Add shipping time field to WooCommerce simple products.
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
 * Save the shipping time field data for simple products.
 */
function wcst_save_shipping_time_field($post_id) {
    if (isset($_POST['_shipping_time'])) {
        update_post_meta($post_id, '_shipping_time', sanitize_text_field($_POST['_shipping_time']));
    }
}
add_action('woocommerce_process_product_meta', 'wcst_save_shipping_time_field');

/**
 * Add shipping time field to variations.
 */
function wcst_add_shipping_time_to_variations($loop, $variation_data, $variation) {
    woocommerce_wp_text_input([
        'id'          => "variation_shipping_time_$variation->ID",
        'label'       => __('Shipping Time', 'wc-shipping-time'),
        'description' => __('Enter shipping time for this variation, e.g., 24H, 48H, 3 days', 'wc-shipping-time'),
        'desc_tip'    => true,
        'type'        => 'text',
        'value'       => get_post_meta($variation->ID, '_shipping_time', true),
    ]);
}
add_action('woocommerce_variation_options_pricing', 'wcst_add_shipping_time_to_variations', 10, 3);

/**
 * Save shipping time for variations.
 */
function wcst_save_shipping_time_variation($variation_id) {
    if (isset($_POST["variation_shipping_time_$variation_id"])) {
        update_post_meta($variation_id, '_shipping_time', sanitize_text_field($_POST["variation_shipping_time_$variation_id"]));
    }
}
add_action('woocommerce_save_product_variation', 'wcst_save_shipping_time_variation', 10, 2);

/**
 * Pass variation shipping time data to WooCommerce variation system.
 */
function wcst_add_variation_shipping_time_data($data, $product, $variation) {
    if (!is_array($data)) {
        $data = [];
    }
    $shipping_time = get_post_meta($variation->get_id(), '_shipping_time', true);
    $data['shipping_time'] = !empty($shipping_time) ? $shipping_time : '';
    
    return $data;
}
add_filter('woocommerce_available_variation', 'wcst_add_variation_shipping_time_data', 10, 3);

/**
 * Display the shipping time on the product page and support default variations.
 */
function wcst_display_shipping_time() {
    global $product;

    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    $shipping_time = '';

    // Handle default variation selection
    if ($product->is_type('variable')) {
        $default_attributes = $product->get_default_attributes();
        foreach ($product->get_available_variations() as $variation) {
            $match = true;
            foreach ($default_attributes as $attribute => $value) {
                if ($variation['attributes']['attribute_' . $attribute] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $variation_id = $variation['variation_id'];
                $shipping_time = get_post_meta($variation_id, '_shipping_time', true);
                break;
            }
        }
    }

    // Fallback to parent product shipping time if no variation has shipping time
    if (empty($shipping_time)) {
        $shipping_time = get_post_meta($product->get_id(), '_shipping_time', true);
    }

    ?>
    <p id="wcst-shipping-time" style="display: <?php echo empty($shipping_time) ? 'none' : 'block'; ?>;">
        <strong><?php echo esc_html__('Shipping Time:', 'wc-shipping-time'); ?></strong>
        <span><?php echo esc_html($shipping_time); ?></span>
    </p>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('form.variations_form').on('found_variation', function(event, variation) {
                if (variation.shipping_time) {
                    $('#wcst-shipping-time').show();
                    $('#wcst-shipping-time span').text(variation.shipping_time);
                } else {
                    $('#wcst-shipping-time').hide();
                }
            });

            $('form.variations_form').on('reset_data', function() {
                $('#wcst-shipping-time').hide();
            });
        });
    </script>
    <?php
}
add_action('woocommerce_single_product_summary', 'wcst_display_shipping_time', 20);