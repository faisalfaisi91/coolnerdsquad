<?php
/* Add custom functions here */

function style_enqueuer()
{
    // enqueue parent styles
    wp_enqueue_style('child-theme', get_stylesheet_directory_uri() . '/assets/css/custom.css');
}

add_action('wp_enqueue_scripts', 'style_enqueuer');

add_filter('nav_menu_link_attributes', 'wpse156165_menu_add_class', 10, 3);

function wpse156165_menu_add_class($atts, $item, $args)
{
    $class = 'nav-link'; // or something based on $item
    $atts['class'] = $class;
    return $atts;
}
function add_classes_on_li($classes, $item, $args)
{
    $classes[] = 'nav-item';
    return $classes;
}
add_filter('nav_menu_css_class', 'add_classes_on_li', 1, 3);

add_filter('nav_menu_css_class', 'special_nav_class', 10, 2);

function special_nav_class($classes, $item)
{
    if (in_array('current-menu-item', $classes)) {
        $classes[] = 'active ';
    }
    return $classes;
}

function my_custom_mime_types($mimes)
{

    // New allowed mime types.
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    $mimes['doc'] = 'application/msword';

    // Optional. Remove a mime type.
    unset($mimes['exe']);

    return $mimes;
}
add_filter('upload_mimes', 'my_custom_mime_types');

// Disable related product on product detail page
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

// Disable tabs on product detail page
add_filter('woocommerce_product_tabs', 'yikes_remove_description_tab', 20, 1);

function yikes_remove_description_tab($tabs)
{

    // Remove the description tab
    if (isset($tabs['description'])) unset($tabs['description']);
    return $tabs;
}
remove_action('woocommerce_before_shop_loop', 'storefront_woocommerce_pagination', 30);
