<?php
/*
Plugin Name: SlideCraft
Description: A simple slider plugin for WordPress
Version: 1.0
Author: Ertan
*/

// Enqueue styles and scripts
function custom_slider_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('slick-style', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme-style', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    wp_enqueue_script('slick-script', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
    wp_enqueue_style('custom-slider-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('custom-slider-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery','slick-script'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'custom_slider_scripts');

// Add custom control for slider images
function custom_slider_customize_register($wp_customize) {
    $wp_customize->add_section('slider_images_section', array(
        'title' => __('Slider Images', 'slidecraft'),
        'priority' => 30,
    ));

    for ($i = 1; $i <= 5; $i++) { // You can adjust the number of images as needed
        $wp_customize->add_setting('slider_image_' . $i, array(
            'capability' => 'edit_theme_options',
        ));

        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'slider_image_' . $i, array(
            'label' => __('Slider Image ' . $i, 'slidecraft'),
            'section' => 'slider_images_section',
            'settings' => 'slider_image_' . $i,
        )));
    }
}
add_action('customize_register', 'custom_slider_customize_register');

// Shortcode function to display the slider
function custom_slider_shortcode($atts) {
    $images = array();

    // Get slider images from customizer settings
    for ($i = 1; $i <= 5; $i++) { // Adjust the loop range accordingly
        $image_url = get_theme_mod('slider_image_' . $i);
        if (!empty($image_url)) {
            $images[] = $image_url;
        }
    }

    if (!empty($images)) {
        $output = '<div class="custom-slider">';
        $output .= '<div class="slider-container">';
        foreach ($images as $image_url) {
            $output .= '<div class="slide">';
            $output .= '<img src="' . esc_url($image_url) . '" alt="">';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '<div class="slider-dots"></div>';
        $output .= '</div>';

        return $output;
    } else {
        return 'No images found.';
    }
}
add_shortcode('custom_slider', 'custom_slider_shortcode');
