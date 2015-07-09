<?php
/**
 * Plugin class.
 *
 * @package Menu_Card
 * @author  Furqan Khanzada <furqan.khanzada@gmail.com>
 * @license   GPL-2.0+
 * @link      https://wordpress.org/plugins/menu-card/
 */
class Menu_Card {

  /**
   * Instance of this class.
   *
   * @since    0.1.0
   * @var      object
   */
  protected static $instance = null;

  /**
   * Initialize the plugin by setting localization, filters, and administration functions.
   *
   * @since     0.1.0
   */
  private function Menu_Card() {
  	// Handle init
  	add_action( 'init', array( $this, 'handle_init' ) );
  }

  /**
   * Return an instance of this class.
   *
   * @since     0.1.0
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {

  	// If the single instance hasn't been set, set it now.
  	if ( null == self::$instance ) {
  		self::$instance = new self;
  	}

  	return self::$instance;
  }

  /**
   * Handles init action.
   *
   * @since     0.2.0
   * @return    void
   */
  public function handle_init() {
    $this->load_plugin_textdomain();

  
    $this->load_custom_post_types();
  

    $this->register_script_and_style();

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
  }
  
  
  private function load_custom_post_types() {
    require(plugin_dir_path(__FILE__) . 'custom-post-base.php');
    require( plugin_dir_path( __FILE__ ) . 'custom-post-menu-cards.php' );
    //Menu_Card_MenuCards_Custom_Post::init();
    $cp = new Menu_Card_MenuCards_Custom_Post();
  }
  
  

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.1.0
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
    $version_key = 'menu-card-version';
    $new_version = Menu_Card_Info::version;
    $old_version = get_option($version_key, "");
    
    if ($old_version != $new_version) {
      // Execute your upgrade logic here

      // Then update the version value
      update_option($old_version, $new_version);
    }
    
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.1.0
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	private function load_plugin_textdomain() {

		$domain = Menu_Card_Info::slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

    $plugin_rel_path = dirname(dirname(__FILE__)) . '/lang/';
    $plugin_rel_path = substr($plugin_rel_path, strlen(WP_PLUGIN_DIR)+1 );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, $plugin_rel_path );
	}

  /**
   * Register public-facing script and style sheet. These would be enqueued later only when necessary.
   *
   * @since    0.1.0
   */
  private function register_script_and_style() {
    wp_register_script( Menu_Card_Info::slug . '-plugin-script',
                        Menu_Card_Info::$plugin_url . '/assets/js/public.js',
                        array( 'jquery', 'underscore' ),
                        Menu_Card_Info::version );

    wp_register_script( Menu_Card_Info::slug . '-jquery.touchSwipe',
                        Menu_Card_Info::$plugin_url . '/assets/js/lib/jquery.touchSwipe.min.js',
                        array( 'jquery' ),
                        Menu_Card_Info::version );

    wp_register_style( Menu_Card_Info::slug . '-plugin-style',
                      Menu_Card_Info::$plugin_url . '/assets/css/public.css',
                      array(),
                      Menu_Card_Info::version );
  }

  /**
   * Register and enqueue public-facing style sheet.
   *
   * @since    0.1.0
   */
  public function enqueue_styles() {
    wp_enqueue_style(Menu_Card_Info::slug . '-plugin-style');
  }

  /**
   * Register and enqueues public-facing JavaScript files.
   *
   * @since    0.1.0
   */
  public function enqueue_scripts() {
      wp_enqueue_script(Menu_Card_Info::slug . '-jquery.touchSwipe');
      wp_enqueue_script(Menu_Card_Info::slug . '-plugin-script');
      // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
      wp_localize_script( Menu_Card_Info::slug . '-plugin-script', 'ajax_object',
          array(
              'ajax_url' => admin_url( 'admin-ajax.php' ),
              'template_url' => Menu_Card_Info::$plugin_url . '/assets/templates/menu-card.html',
              'we_value' => 1234
          ) );
  }

}