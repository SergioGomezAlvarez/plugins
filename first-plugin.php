<?php
/**
 * Plugin Name: First Plugin
 * Description: This is my first WordPress plugin.
 * Version: 1.0.0
 * Author: Sergio
 * Author URI: https://sergiogomezalvarez.github.io/Portfolio/index.html
 */

add_action('wp_footer', 'show_footer');

function show_footer()
{
    echo "<p style='text-align: center;'>This is my first plugin!</p>";
}

?>