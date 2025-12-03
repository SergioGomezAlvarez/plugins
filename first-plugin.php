<?php
/**
 * Plugin Name: First Plugin
 * Description: This is my first WordPress plugin, now with PokeAPI support.
 * Version: 1.2.0
 * Author: Sergio
 * Author URI: https://sergiogomezalvarez.github.io/Portfolio/index.html
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', 'fp_show_footer');
function fp_show_footer() {
    echo "<p style='text-align: center;'>This is my first plugin!</p>";
}

/**
 * Fetches Pokémon from the PokeAPI and outputs them as HTML.
 * Usage: [pokemon_list]
 */
function fp_get_pokemon_list() {

    $response = wp_remote_get('https://pokeapi.co/api/v2/pokemon?limit=10');

    if (is_wp_error($response)) {
        return "<p>Kon geen verbinding maken met de PokeAPI.</p>";
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!$body || empty($body['results'])) {
        return "<p>Geen Pokémon gevonden.</p>";
    }

    $html = "<h2>Pokémon Lijst</h2><ul>";

    foreach ($body['results'] as $pokemon) {
        $html .= "<li>" . esc_html(ucfirst($pokemon['name'])) . "</li>";
    }

    $html .= "</ul>";

    return $html;
}

add_action('wp_footer', 'fp_show_pokemon_on_home');

function fp_show_pokemon_on_home() {

    if (!is_front_page()) {
        return;
    }

    $pokemon_html = fp_get_pokemon_list();

    echo "<div style='padding: 20px; max-width: 600px; margin: 40px auto; background: #f5f5f5; border-radius: 8px;'>";
    echo $pokemon_html;
    echo "</div>";
}

function pi_add_admin_menu() {
    add_menu_page(
        'Poké Inventory Settings',
        'Poké Inventory',
        'manage_options',
        'pi_footer_message',
        'pi_settings_page_html',
    );
}
add_action('admin_menu', 'pi_add_admin_menu');

function pi_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    ?>
    <div class="wrap">
        <h1>Poké Inventory Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('pi_settings_group');
            do_settings_sections('pi_footer_message');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}