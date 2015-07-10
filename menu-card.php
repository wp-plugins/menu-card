<?php
/**
 * Restaurants Menu Cards
 * 
 * @package Menu_Card
 * @author  Furqan Khanzada <furqan.khanzada@gmail.com>
 * @license   GPL-2.0+
 * @link      https://wordpress.org/plugins/menu-card/
 *
 * @wordpress-plugin
 * Plugin Name: Menu Card
 * Plugin URI:  https://wordpress.org/plugins/menu-card/
 * Description: Menu Card Lets you build menu card of restaurants, with a shortcode.
 * Version:     0.7.0
 * Author:      Furqan Khanzada
 * Author URI:  furqankhanzada.com
 * Text Domain: menu-card-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Menu Card MetaData class.
 *
 * Defines useful constants
 */

class Menu_Card_Info {
	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    0.1.0
	 * @var      string
	 */
  	const slug = 'menu-card';

    
  	const settings_page_slug = 'menu-card-options';
    

	/**
	 * Version of the plugin.
	 *
	 * @since    0.1.0
	 * @var      string
	 */
  	const version = '0.7.0';

  	const required_wp_version = '3.0';
    
    public static $plugin_dir = '';
    
    public static $plugin_url = '';

    public static $plugin_basename = '';

    // For demo build, the values of capability variables are overridden in init()
    public static $capability_for_settings    = 'manage_options';
    public static $capability_for_custom_post = 'post';


    /**
     * This function is called once on plugin load. It will initialize the static variables and also sets up appropriate hooks .
     *
     * @since    0.1.0
     */
  	public static function init() {
      self::$plugin_dir = untrailingslashit( dirname( __FILE__ ) );
      self::$plugin_url = untrailingslashit( plugins_url( '', __FILE__ ) );
      
      $plugin_basename = plugin_basename( __FILE__ );
      $plugin_basename = explode("src/", $plugin_basename);
      self::$plugin_basename = implode( $plugin_basename );

      // Load admin only when required
      add_action( 'admin_menu', array('Menu_Card_Info','handle_admin_menu') );
      add_shortcode( 'menucard', array('Menu_Card_Info','handle_menucard_shortcode') );

    }
    
    
    public static function handle_admin_menu() {
      require(plugin_dir_path(__FILE__) . 'inc/admin-settings.php');
      $admin = Menu_Card_Admin::get_instance();
      $admin->handle_admin_menu();
    }
    
    public static function handle_menucard_shortcode($atts) {
        $a = shortcode_atts( array(
            'category' => ''
        ), $atts );

        return "<div id='menu-card' data-category='".$a['category']."'></div>";
    }
}

Menu_Card_Info::init();

// include plugin's class file
require( plugin_dir_path( __FILE__ ) . 'inc/class-menu-card.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Menu_Card', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Menu_Card', 'deactivate' ) );

Menu_Card::get_instance();