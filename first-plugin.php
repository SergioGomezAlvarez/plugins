<?php
/**
 * Plugin Name: Poké Inventory
 * Description: This is the Poké Inventory plugin for WordPress.
 * Version: 1.5.0
 * Author: Sergio
 * Author URI: https://sergiogomezalvarez.github.io/Portfolio/index.html
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue frontend styles
 */
function fp_enqueue_styles()
{
    wp_enqueue_style(
        'first-plugin-styles',
        plugin_dir_url(__FILE__) . 'style.css',
        [],
        '1.4.0'
    );
}
add_action('wp_enqueue_scripts', 'fp_enqueue_styles');


/**
 * Fetch Pokémon from the PokeAPI
 */
function fp_get_pokemon_list()
{
    $limit = get_option('pi_pokemon_limit', 10);
    $title = get_option('pi_pokemon_title', 'Pokémon Lijst');

    $response = wp_remote_get("https://pokeapi.co/api/v2/pokemon?limit={$limit}");

    if (is_wp_error($response)) {
        return "<p>Kon geen verbinding maken met de PokeAPI.</p>";
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!$body || empty($body['results'])) {
        return "<p>Geen Pokémon gevonden.</p>";
    }

    $html = "<h2>" . esc_html($title) . "</h2>";
    $html .= "<ul>";

    foreach ($body['results'] as $pokemon) {
        $html .= "<li>" . esc_html(ucfirst($pokemon['name'])) . "</li>";
    }

    $html .= "</ul>";

    return $html;
}

/**
 * SHORTCODE
 * Usage: [pokemon_list]
 */
add_shortcode('pokemon_list', 'fp_pokemon_shortcode');

function fp_pokemon_shortcode()
{
    $pokemon_html = fp_get_pokemon_list();

    return "<div class='fp-pokemon-container'>{$pokemon_html}</div>";
}

/**
 * Admin menu
 */
function pi_add_admin_menu()
{
    add_menu_page(
        'Poké Inventory Settings',
        'Poké Inventory',
        'manage_options',
        'pi_footer_message',
        'pi_settings_page_html'
    );
}
add_action('admin_menu', 'pi_add_admin_menu');

/**
 * Settings page HTML
 */
function pi_settings_page_html()
{
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

/**
 * Settings fields
 */
function pi_pokemon_limit_html()
{
    $value = get_option('pi_pokemon_limit', 10);
    echo "<input type='number' min='1' max='50' name='pi_pokemon_limit' value='" . esc_attr($value) . "'>";
}

function pi_pokemon_title_html()
{
    $value = get_option('pi_pokemon_title', 'Pokémon Lijst');
    echo "<input type='text' name='pi_pokemon_title' value='" . esc_attr($value) . "' class='regular-text'>";
}

function pi_show_footer_html()
{
    $checked = get_option('pi_show_footer', true);
    echo "<input type='checkbox' name='pi_show_footer' value='1' " . checked(1, $checked, false) . ">";
}

/**
 * Register settings
 */
function pi_register_settings()
{
    register_setting('pi_settings_group', 'pi_pokemon_limit', [
        'type' => 'integer',
        'default' => 10,
        'sanitize_callback' => 'absint'
    ]);

    register_setting('pi_settings_group', 'pi_pokemon_title', [
        'type' => 'string',
        'default' => 'Pokémon Lijst',
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    register_setting('pi_settings_group', 'pi_show_footer', [
        'type' => 'boolean',
        'default' => true
    ]);

    add_settings_section(
        'pi_main_section',
        'Algemene instellingen',
        null,
        'pi_footer_message'
    );

    add_settings_field(
        'pi_pokemon_limit',
        'Aantal Pokémon',
        'pi_pokemon_limit_html',
        'pi_footer_message',
        'pi_main_section'
    );

    add_settings_field(
        'pi_pokemon_title',
        'Titel van de lijst',
        'pi_pokemon_title_html',
        'pi_footer_message',
        'pi_main_section'
    );

    add_settings_field(
        'pi_show_footer',
        'Footer tekst tonen',
        'pi_show_footer_html',
        'pi_footer_message',
        'pi_main_section'
    );
}
add_action('admin_init', 'pi_register_settings');

