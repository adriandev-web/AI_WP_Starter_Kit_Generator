<?php
/**
 * Plugin Name: Ballsquad External Auth
 * Plugin URI: https://ballsquad.pl
 * Description: Integracja zewnętrznego systemu uwierzytelniania Ballsquad z WordPress/WooCommerce
 * Version: 1.7.0
 * Author: Ballsquad
 * Text Domain: ballsquad-external-auth
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Zabezpieczenie przed bezpośrednim dostępem
if (!defined('ABSPATH')) {
    exit;
}

// Definicje stałych
define('BSEA_VERSION', '1.7.0');
define('BSEA_PLUGIN_FILE', __FILE__);
define('BSEA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BSEA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BSEA_COOKIE_NAME', 'ext_jwt');

/**
 * Główna klasa pluginu
 */
class BallsquadExternalAuth {
    
    /**
     * Instancja pluginu
     */
    private static $instance = null;
    
    /**
     * Flaga inicjalizacji
     */
    private $initialized = false;
    
    /**
     * Statyczna flaga do sprawdzenia czy hooki zostały zarejestrowane
     */
    private static $hooks_registered = false;
    
    /**
     * Komponenty pluginu
     */
    private $settings;
    private $auth_client;
    private $jwt_storage;
    private $user_sync;
    private $login_controller;
    private $logout_controller;
    private $rest_proxy;
    private $api_cache;
    private $user_data_provider;
    private $profile_editor;
    private $bidirectional_sync;
    private $frontend_display;
    
    /**
     * Pobierz instancję pluginu (Singleton)
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Konstruktor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inicjalizacja hooków
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // AJAX hooks
        add_action('wp_ajax_bsea_test_connection', array($this, 'ajax_test_connection'));
    }
    
    /**
     * Inicjalizacja pluginu
     */
    public function init() {
        // Sprawdź czy plugin już został zainicjalizowany
        if ($this->initialized) {
            return;
        }
        
        // Autoloader dla klas
        $this->autoload();
        
        // Inicjalizacja komponentów
        $this->init_components();
        
        // Oznacz jako zainicjalizowany
        $this->initialized = true;
    }
    
    /**
     * Autoloader dla klas pluginu
     */
    private function autoload() {
        $includes_dir = BSEA_PLUGIN_DIR . 'includes/';
        
        $classes = array(
            'BSEA_Settings' => 'class-settings.php',
            'BSEA_Auth_Client' => 'class-auth-client.php',
            'BSEA_JWT_Storage' => 'class-jwt-storage.php',
            'BSEA_User_Sync' => 'class-user-sync.php',
            'BSEA_Login_Controller' => 'class-login-controller.php',
            'BSEA_Logout_Controller' => 'class-logout-controller.php',
            'BSEA_Rest_Proxy' => 'class-rest-proxy.php',
            'BSEA_API_Cache' => 'class-api-cache.php',
            'BSEA_User_Data_Provider' => 'class-user-data-provider.php',
            'BSEA_Profile_Editor' => 'class-profile-editor.php',
            'BSEA_Bidirectional_Sync' => 'class-bidirectional-sync.php',
            'BSEA_Frontend_Display' => 'class-frontend-display.php',
        );
        
        foreach ($classes as $class => $file) {
            $file_path = $includes_dir . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // Helper functions
        $helpers_file = $includes_dir . 'helpers.php';
        if (file_exists($helpers_file)) {
            require_once $helpers_file;
        }
    }
    
    /**
     * Inicjalizacja komponentów pluginu
     */
    private function init_components() {
        // Sprawdź czy komponenty już zostały zainicjalizowane
        if ($this->settings && $this->auth_client && $this->jwt_storage) {
            return;
        }
        
        error_log('[BSEA] Initializing plugin components');
        
        // Inicjalizacja komponentów w odpowiedniej kolejności z dependency injection
        $this->jwt_storage = new BSEA_JWT_Storage();
        $this->settings = new BSEA_Settings();
        $this->auth_client = new BSEA_Auth_Client($this->settings);
        $this->user_sync = new BSEA_User_Sync($this->settings);
        $this->rest_proxy = new BSEA_Rest_Proxy($this->settings, $this->auth_client, $this->jwt_storage);
        $this->api_cache = new BSEA_API_Cache($this->rest_proxy, $this->jwt_storage);
        $this->user_data_provider = new BSEA_User_Data_Provider($this->api_cache, $this->jwt_storage);
        $this->profile_editor = new BSEA_Profile_Editor($this->user_sync, $this->settings);
        $this->bidirectional_sync = new BSEA_Bidirectional_Sync($this->rest_proxy, $this->user_sync, $this->settings);
        $this->frontend_display = new BSEA_Frontend_Display($this->user_data_provider, $this->settings);
        $this->login_controller = new BSEA_Login_Controller($this->settings, $this->auth_client, $this->jwt_storage, $this->user_sync);
        $this->logout_controller = new BSEA_Logout_Controller($this->auth_client, $this->jwt_storage);
        
        error_log('[BSEA] Plugin components initialized successfully');
    }
    
    /**
     * Ładowanie tłumaczeń
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'ballsquad-external-auth',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    /**
     * Aktywacja pluginu
     */
    public function activate() {
        // Domyślne ustawienia
                            $default_settings = array(
                        'api_base_url' => 'https://api.ballsquad.pl/api',
                        'use_staging' => false,
                        'block_local_registration' => false,
                        'roles_map_json' => '{"ROLE_USER":"customer","ROLE_ADMIN":"administrator"}',
                        'fetch_profile_on_login' => false,
                    );
        
        foreach ($default_settings as $key => $value) {
            if (get_option('bsea_' . $key) === false) {
                add_option('bsea_' . $key, $value);
            }
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Deaktywacja pluginu
     */
    public function deactivate() {
        // Wyczyść cron jobs (jeśli istnieją)
        wp_clear_scheduled_hook('bsea_cron_sync');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Pobierz komponent
     */
    public function get_component($name) {
        return isset($this->$name) ? $this->$name : null;
    }
    
    /**
     * AJAX handler dla testu połączenia
     */
    public function ajax_test_connection() {
        try {
            // Sprawdź nonce
            if (!wp_verify_nonce($_POST['nonce'], 'bsea_admin_nonce')) {
                wp_send_json_error(array(
                    'message' => __('Security check failed.', 'ballsquad-external-auth')
                ));
            }
            
            // Sprawdź uprawnienia
            if (!current_user_can('manage_options')) {
                wp_send_json_error(array(
                    'message' => __('Insufficient permissions.', 'ballsquad-external-auth')
                ));
            }
            
            // Sprawdź czy auth_client jest dostępny
            if (!$this->auth_client) {
                wp_send_json_error(array(
                    'message' => __('Auth client not available. Plugin may not be properly initialized.', 'ballsquad-external-auth')
                ));
            }
            
            // Wykonaj test połączenia
            $result = $this->auth_client->test_connection();
            
            // Zwróć odpowiedź JSON
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
            
        } catch (Exception $e) {
            // Log error
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[BSEA] AJAX Test Connection Error: ' . $e->getMessage());
            }
            
            wp_send_json_error(array(
                'message' => __('An unexpected error occurred: ', 'ballsquad-external-auth') . $e->getMessage()
            ));
        }
    }
    
    
    /**
     * Gettery dla komponentów
     */
    public function get_settings() {
        return $this->settings;
    }
    
    public function get_auth_client() {
        return $this->auth_client;
    }
    
    public function get_jwt_storage() {
        return $this->jwt_storage;
    }
    
    public function get_user_sync() {
        return $this->user_sync;
    }
    
    public function get_login_controller() {
        return $this->login_controller;
    }
    
    public function get_logout_controller() {
        return $this->logout_controller;
    }
    
    public function get_rest_proxy() {
        return $this->rest_proxy;
    }
    
    public function get_api_cache() {
        return $this->api_cache;
    }
    
    public function get_user_data_provider() {
        return $this->user_data_provider;
    }
    
    public function get_profile_editor() {
        return $this->profile_editor;
    }
    
    public function get_bidirectional_sync() {
        return $this->bidirectional_sync;
    }
    
    public function get_frontend_display() {
        return $this->frontend_display;
    }
    
    
    /**
     * Sprawdź czy plugin jest zainicjalizowany
     */
    public function is_initialized() {
        return $this->initialized;
    }
}

// Inicjalizacja pluginu
function bsea_init() {
    return BallsquadExternalAuth::get_instance();
}

// Start pluginu
add_action('plugins_loaded', 'bsea_init');
