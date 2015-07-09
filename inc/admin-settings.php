<?php
/**
 * Admin class for Menu Card
 *
 * @package Menu_Card
 * @author  Furqan Khanzada <furqan.khanzada@gmail.com>
 * @license   GPL-2.0+
 * @link      https://wordpress.org/plugins/menu-card/
 */

require "admin-settings-base.php";
 
class Menu_Card_Admin 
          extends Menu_Card_Admin_Base {

  /**
   * Instance of this class.
   *
   * @since    0.1.0
   * @var      object
   */
  protected static $instance = null;
  
  
  /**
   * Initialize the plugin settings page.
   *
   * @since     0.1.0
   */
  private function Menu_Card_Admin() {

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
   * Handles filter_action_links filter. Adds the settings link on plugins page
   *
   * @since    0.1.0
   */
  public static function filter_action_links( $links, $file ) {
    if ( $file != Menu_Card_Info::$plugin_basename )
      return $links;

    $settings_link = '<a href="' . menu_page_url( Menu_Card_Info::settings_page_slug, false ) . '">'
      . esc_html( __( 'Settings', 'menu-card' ) ) . '</a>';

    array_push( $links, $settings_link );

    return $links;
  }

  /**
   * Handles admin_menu action
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    0.1.0
   */
  public function handle_admin_menu() {
    add_action( 'admin_init', array( $this, 'handle_admin_init') );
    $this->screen_hook_suffix = add_options_page( 
            __('Menu Card Options', 'menu-card'),     /* The title of the page when the menu is selected */
            __('Menu Card', 'menu-card'),/* The text for the menu */
            Menu_Card_Info::$capability_for_settings, /* capability required for this menu to be displayed to user */
            Menu_Card_Info::settings_page_slug , /* menu slug that is used when adding setting sections */
            array($this, 'add_options_page')               /* callback to output the content for this page */
          );
    
    // Load admin style sheet and JavaScript.
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    
  }

  /**
   * Register and enqueue admin-specific style sheet.
   *
   * @since     0.1.0
   * @return    void
   */
  public function enqueue_admin_styles($screen_suffix) {
    // Return early if no settings page is registered
    if ( ! isset( $this->screen_hook_suffix ) ) {
      return;
    }

    if ( $screen_suffix == $this->screen_hook_suffix ) {
      wp_enqueue_style( Menu_Card_Info::slug .'-admin-styles',
                        Menu_Card_Info::$plugin_url . '/assets/css/admin.css', 
                        array(),
                        Menu_Card_Info::version );
    }
  }

  /**
   * Register and enqueue admin-specific JavaScript.
   *
   * @since     0.1.0
   *
   * @return    void
   */
  public function enqueue_admin_scripts($screen_suffix) {
    // Return early if no settings page is registered
    if ( ! isset( $this->screen_hook_suffix ) ) {
      return;
    }

    if ( $screen_suffix == $this->screen_hook_suffix ) {
      wp_enqueue_script( Menu_Card_Info::slug . '-admin-script', 
                         Menu_Card_Info::$plugin_url . '/assets/js/admin.js',
                         array( 'jquery' ),
                        Menu_Card_Info::version );
    }
  }

  public function add_options_page() {
    ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2><?php printf( __('Menu Card Options', 'menu-card') ) ?></h2>
      <form action="options.php" method="POST">
      <?php 
        settings_fields( 'menu-card-settings-group' ); 
        do_settings_sections( Menu_Card_Info::settings_page_slug ); 
        $this->render_submit_button(__( 'Save Changes' ), 'menu-card-submit');
      ?>
      </form>
    </div>
    <?php
  }
  
  /**
   * Handles admin_init action
   * Adds sections and fields on settings page using settings API
   *
   * @since    0.1.0
   */
  public function handle_admin_init() {
    add_filter( 'plugin_action_links', array('Menu_Card_Admin','filter_action_links'), 10, 2 );
    $this->settings = get_option( 'menu-card-settings' );

    register_setting( 'menu-card-settings-group', 'menu-card-settings', array($this, 'validate_plugin_options') );
   
    $page = Menu_Card_Info::settings_page_slug;


    // Make 1 Sections
    $sections = array(
                "menu-card-required"
        );

    /********************************************* 
     * Required section
     ********************************************/

    $this->add_section( $sections[0], __('Required', 'menu-card'), 'render_required_section' );

    add_settings_field( 'field-1', __('field 1', 'menu-card'),
                        array($this, 'render_input_field'),
                        $page, $sections[0],
                        array(
                            'field_name' => 'field-1',
                            'help_text'  => __('Help text for field 1', 'menu-card'),
                        	  'field_type' => 'text'
                        ));

    add_settings_field( 'field-2', __('field 2', 'menu-card'),
                        array($this, 'render_input_field'),
                        $page, $sections[0],
                        array(
                            'field_name' => 'field-2',
                            'help_text'  => __('Help text for field 2', 'menu-card'),
                        	  'field_type' => 'number'
                        ));



  }
  
  private function add_section($section, $localized_title, $function_name) {
    add_settings_section( $section, /* string for use in the 'id' attribute of tags */
                          $localized_title, /* title of the section */
                          array($this, $function_name), /* function that fills the section with the desired content */
                          Menu_Card_Info::settings_page_slug /* The menu slug on which to display this section. should be same as passed in add_options_page() */
                        );
  }

  /**
  * Displays the descriptive text for Required section.
  * Could be any html.
  */
  public function render_required_section() { 
    printf(__('description of Required section', 'menu-card'));
  }


  /********************** Validation for Options Form **********************/
  
  /**
   * Sanitize user input before it gets saved to database
   */
  public function validate_plugin_options($inputs) {

    // retrieve old settings
    $options = get_option('menu-card-settings');

    /*
    $val = trim($input['req-setting-1']);
    if(preg_match('/^[a-z0-9]{32}$/i', $val)) { // put validation logic for req-setting-1 here
      $options['req-setting-1'] = $val;    
    }
    else {
      $options['req-setting-1'] = '';
    }
    */
    
    // return the settings that you want to be saved.
    return $inputs;
  }
  
}