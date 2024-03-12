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
    // wp_enqueue_script('custom-slider-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery','slick-script'), '1.0', true);
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

    // Slider Settings Section
    $wp_customize->add_section('slider_images_section', array(
        'title' => __('Slider Settings', 'slidecraft'),
        'priority' => 31,
    ));

    //Slider Autoplay
    $wp_customize->add_setting('slider_autoplay', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('slider_autoplay', array(
        'label' => __('Autoplay', 'slidecraft'),
        'section' => 'slider_images_section',
        'type' => 'checkbox',
    ));


    // Slider Speed
    $wp_customize->add_setting('slider_speed', array(
        'default' => 3000, // Default speed in milliseconds
        'sanitize_callback' => 'absint', // Ensure the value is an integer
    ));
    $wp_customize->add_control('slider_speed', array(
        'label' => __('Slider Speed (ms)', 'slidecraft'),
        'section' => 'slider_images_section',
        'type' => 'number',
    ));

    // Navigation Dots
    $wp_customize->add_setting('slider_arrows', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('slider_arrows', array(
        'label' => __('Navigation Dots', 'slidecraft'),
        'section' => 'slider_images_section',
        'type' => 'checkbox',
    ));

    // Slider Animation
    $wp_customize->add_setting('slider_animation', array(
        'default' => 'slide', // Default animation type
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('slider_animation', array(
        'label' => __('Slider Animation', 'slidecraft'),
        'section' => 'slider_images_section',
        'type' => 'select',
        'choices' => array(
            'slide' => __('Slide', 'slidecraft'),
            'fade' => __('Fade', 'slidecraft'),
        ),
    ));




    // Slider Overlay Color
    $wp_customize->add_setting('slider_overlay_color', array(
        'default' => '#000000', // Default overlay color
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'slider_overlay_color', array(
        'label' => __('Overlay Color', 'slidecraft'),
        'section' => 'slider_images_section',
    )));

    // Slider Overlay Opacity
    $wp_customize->add_setting('slider_overlay_opacity', array(
        'default' => 0.5, // Default overlay opacity
        'sanitize_callback' => function($value) {
            // Ensure the value is a decimal between 0 and 1
            $value = floatval($value);
            return min(1, max(0, $value));
        },
    ));
    $wp_customize->add_control('slider_overlay_opacity', array(
        'label' => __('Overlay Opacity', 'slidecraft'),
        'section' => 'slider_images_section',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 0,
            'max' => 1,
            'step' => 0.1,
        ),
    ));

    // Slider Content Settings
    $wp_customize->add_setting('slider_content_text', array(
        'default' => 'Your Content Here', // Default content text
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('slider_content_text', array(
        'label' => __('Content Text', 'slidecraft'),
        'section' => 'slider_images_section',
        'type' => 'text',
    ));

    $wp_customize->add_setting('slider_content_color', array(
        'default' => '#ffffff', // Default content color
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'slider_content_color', array(
        'label' => __('Content Color', 'slidecraft'),
        'section' => 'slider_images_section',
    )));
    
}
add_action('customize_register', 'custom_slider_customize_register');

// Shortcode function to display the slider
function custom_slider_shortcode($atts) {
    $autoplay = get_theme_mod('slider_autoplay', true);
    $speed = get_theme_mod('slider_speed', 3000);
    $dots = get_theme_mod('slider_arrows', true);
    $animation = get_theme_mod('slider_animation', 'slide');
    $overlay_color = get_theme_mod('slider_overlay_color', '#000000');
    $overlay_opacity = get_theme_mod('slider_overlay_opacity', 0.5);

    
    $images = array();

    // Get slider images from customizer settings
    for ($i = 1; $i <= 5; $i++) { // Adjust the loop range accordingly
        $image_url = get_theme_mod('slider_image_' . $i);
        if (!empty($image_url)) {
            $images[] = $image_url;
        }
    }

    if (!empty($images)) {
        $output = '<div class="custom-slider" style="position: relative;">';
        $output .= '<div class="slider-container">';
        foreach ($images as $image_url) {
            $output .= '<div class="slide">';
            $output .= '<img src="' . esc_url($image_url) . '" alt="">';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '</div>';
        // Apply settings
        $output .= '<style>';
        $output .= '.custom-slider { position: relative; }';
        $output .= '.slider-container { width: 100%; height: 100%; }';
        
        if ($overlay_opacity > 0) {
            $output .= '.slider-container:before { content: ""; background-color: ' . $overlay_color . '; opacity: '. $overlay_opacity . '; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }';
            $output .= '.slider-container:after { content: "' . get_theme_mod('slider_content_text', 'Your Content Here') . '"; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: ' . get_theme_mod('slider_content_color', '#ffffff') . '; font-size: 24px; }';
        }
        $output .= '</style>';
        $output .= '<script>';
        $output .= 'jQuery(document).ready(function($) {';
        $output .= '$(".slider-container").slick({';
        $output .= 'autoplay: ' . ($autoplay ? 'true' : 'false') . ',';
        $output .= 'autoplaySpeed: ' . $speed . ',';
        $output .= 'fade: ' . ($animation == 'fade' ? 'true' : 'false') . ',';
        $output .= 'dots: ' . ($dots ? 'true' : 'false') . ','; // Dots are always enabled
        $output .= 'speed: 600,';
        $output .= '});';
        $output .= '});';
        $output .= '</script>';

        return $output;
    } else {
        return 'No images found.';
    }
}
add_shortcode('custom_slider', 'custom_slider_shortcode');

