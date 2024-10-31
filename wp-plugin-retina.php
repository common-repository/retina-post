<?php

if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

	
if (!class_exists('wp_plugin_retina')) {
    abstract class wp_plugin_retina {
        protected $environment; // what environment are we in
        protected $options_name; // the name of the options associated with this plugin
        
        protected $options;
        
        function wp_plugin_retina($options_name) {
            $args = func_get_args();
            call_user_func_array(array(&$this, "__construct"), $args);
        }
        
        function __construct($options_name) {
        }
        
        // sub-classes determine what actions and filters to hook
        abstract protected function register_actions_filters();
        
        // MultiSite checking
        static function is_multisite() {
            if (function_exists('is_multisite'))
                if (is_multisite())
                    return true;
            return false;
        }
        
        // path finding
        static function plugins_directory() {
            return WP_CONTENT_DIR . '/plugins';
        }
        
        static function plugins_url() {
            return get_option('siteurl') . '/wp-content/plugins';
        }
        
        static function path_to_plugin_directory() {
            $current_directory = basename(dirname(__FILE__));
            
            return wp_plugin_retina::plugins_directory() . "/${current_directory}";
        }
        
        static function url_to_plugin_directory() {
           $current_directory = basename(dirname(__FILE__));
           
           return wp_plugin_retina::plugins_url() . "/${current_directory}";
        }
        
        // options
        abstract protected function activation_load_options();
        
        // option retrieval
        static function retrieve_options($options_name) {
            return get_option($options_name);
        }
        static function retrieve_site_options($options_name) {
			if (function_exists('get_site_option'))
				return get_site_option($options_name);
			return false;
        }
        
        static function remove_options($options_name) {
            return delete_option($options_name);
        }
        static function remove_site_options($options_name) {
			if (function_exists('delete_site_option'))
				return delete_site_option($options_name);
			return false;
        }
        
        static function add_options($options_name, $options) {
            return add_option($options_name, $options);
        }
        static function add_site_options($options_name, $options) {
			if (function_exists('add_site_option'))
				return add_site_option($options_name, $options);
			return false;
        }     
		
        static function update_options($options_name, $options) {
            return update_option($options_name, $options);
        }
        static function update_site_options($options_name, $options) {
			if (function_exists('update_site_option'))
				return update_site_option($options_name, $options);
			return false;
        }
		
        
        // calls the appropriate 'authority' checking function depending on the environment
        protected function is_authority() {
            if (!$this->is_multisite)
                return is_admin();            
            if ($this->is_multisite)
                return is_super_admin();
        }
    }
}

?>