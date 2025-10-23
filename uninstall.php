<?php
/**
 * Uninstall script dla pluginu Ballsquad External Auth
 * 
 * Ten plik jest wykonywany automatycznie przy odinstalowaniu pluginu
 * i usuwa wszystkie opcje, ustawienia i dane związane z pluginem.
 */

// Zabezpieczenie przed bezpośrednim dostępem
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Sprawdź czy użytkownik ma uprawnienia do odinstalowania pluginów
if (!current_user_can('activate_plugins')) {
    return;
}

// Lista opcji do usunięcia
$options_to_delete = array(
    'bsea_api_base_url',
    'bsea_use_staging',
    'bsea_block_local_registration',
    'bsea_roles_map_json',
    'bsea_fetch_profile_on_login',
    'bsea_token_keys_json',
    'bsea_profile_keys_json',
);

// Usuń opcje
foreach ($options_to_delete as $option) {
    delete_option($option);
}

// Usuń user meta dla wszystkich użytkowników
global $wpdb;

$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN (%s, %s)",
        'external_user_id',
        'external_roles'
    )
);

// Usuń cookie JWT jeśli istnieje
if (isset($_COOKIE['ext_jwt'])) {
    setcookie(
        'ext_jwt',
        '',
        array(
            'expires' => time() - 3600,
            'path' => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax'
        )
    );
}

// Wyczyść cache jeśli używasz jakiegoś systemu cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

// Flush rewrite rules
flush_rewrite_rules();

// Log odinstalowania (opcjonalnie)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Ballsquad External Auth plugin został odinstalowany');
}
