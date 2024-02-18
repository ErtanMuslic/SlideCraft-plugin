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

// Shortcode function to display the slider
function custom_slider_shortcode($atts) {
    // Get the current page ID
    $page_id = get_queried_object_id();

    // Get the content of the current page
    $page_content = get_post_field('post_content', $page_id);

    // Extract images from the content
    preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/', $page_content, $matches);

    // Check if images are found
    if (!empty($matches[1])) {
        $output = '<div class="custom-slider">';
        $output .= '<div class="slider-container">';
        foreach ($matches[1] as $image_url) {
            // Wrap the image in a div with a custom class
            $output .= '<div class="slide">';
            $output .= '<img src="' . $image_url . '" alt="">';
            $output .= '</div>';
            // Add CSS class to hide the original image
            $page_content = str_replace('<img src="' . $image_url . '"', '<img class="custom-slider-image" src="' . $image_url . '"', $page_content);
        }
        $output .= '</div>';
        $output .= '<div class="slider-dots"></div>';
        $output .= '</div>';

        // Add inline CSS to hide original images
        $output .= '<style>.custom-slider-image { display: none; }</style>';

        // Update the post content to hide original images
        wp_update_post(array(
            'ID'           => $page_id,
            'post_content' => $page_content,
        ));

        return $output;
    } else {
        return 'No images found.';
    
    }
}
add_shortcode('custom_slider', 'custom_slider_shortcode');