<?php
/* Add custom functions here */

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
