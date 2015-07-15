<?php
/**
 * Defines MenuCards_Custom_Post custom post. This class contains functions for customizing
 * the add/edit page and posts list page.
 *
 * @package Menu_Card
 * @author  Furqan Khanzada <furqan.khanzada@gmail.com>
 * @license   GPL-2.0+
 * @link      https://wordpress.org/plugins/menu-card/
 **/

class Menu_Card_MenuCards_Custom_Post
    extends Menu_Card_Custom_Post_Base {

    /* Default values of post meta data to be used in metaboxes */
    // TODO: Default values for variables must be initialized
    private static $default_values = array(
        "price" => 0,
        "link" => 'yes',
        "custom_url" => ''
    );

    public function Menu_Card_MenuCards_Custom_Post() {
        parent::__construct();
        $this->post_type = 'menu-cards';
        $this->texonomy = 'menu-card-category';
        $this->prefix = Menu_Card_Info::slug . "-" . $this->post_type;

        // constructor must be called from init
        $this->handle_init();
    }

    private function handle_init() {

        //register post type and taxonomy
        $this->register_post_type();
        $this->register_taxonomy();
        $this->register_script_and_style();

        //Add hook for admin admin style sheet and javascript.
        add_action('admin_enqueue_scripts', array($this, 'handle_admin_enqueue_scripts'));
        add_action( 'wp_ajax_get_posts_by_category', array($this, 'get_posts_by_category_callback'));
        add_action( 'wp_ajax_nopriv_get_posts_by_category', array($this, 'get_posts_by_category_callback'));

    }

    /**
     * Registers a new custom post type
     */
    private function register_post_type() {

        $labels = array(
            'name' => __('Menu Cards', 'menu-card'),
            'singular_name' => __('Menu Card', 'menu-card'),
            'add_new' => __('Add New', 'menu-card'),
            'add_new_item' => __('Add New Menu Card', 'menu-card'),
            'edit_item' => __('Edit Menu Card', 'menu-card'),
            'new_item' => __('New Menu Card', 'menu-card'),
            'view_item' => __('View Menu Card', 'menu-card'),
            'not_found' => __('No Menu Card found', 'menu-card'),
            'not_found_in_trash' => __('No Menu Card found in Trash', 'menu-card'),
            'all_items' => __('All Menu Cards', 'menu-card'),
            'search_items' => __('Search Menu Cards', 'menu-card'),
            'parent_item_colon' => false,
            'menu_name' => __('Menu Cards', 'menu-card')
        );

        $args = array(
            /* (array) (optional) labels - An array of labels for this post type. By default post labels are used
               for non-hierarchical types and page labels for hierarchical ones.
               Default: if empty, name is set to label value, and singular_name is set to name value*/
            'labels' => $labels,

            /* (boolean) (optional) Whether a post type is intended to be used publicly
               either via the admin interface or by front-end users.
               Default: false */
            'public' => false,

            /* (boolean) (optional) Whether queries can be performed on the front end as part of parse_request(). Default: value of public argument */
            'publicly_queryable' => true,

            /* (boolean) (optional) Whether to generate a default UI for managing this post type in the admin.
               Default: value of public argument */
            'show_ui' => true,

            /* (boolean or string) (optional) Where to show the post type in the admin menu. show_ui must be true.
                Default: value of show_ui argument */
            'show_in_menu' => true,

            /* (boolean or string) (optional) Sets the query_var key for this post type.
               Default: true - set to $post_type */
            'query_var' => true,

            // TODO: Kashif see rewrite, this should be same as wp settings
            /* (boolean or array) (optional) Triggers the handling of rewrites for this post type.
               To prevent rewrites, set to false.
               Default: true and use $post_type as slug */
            'rewrite' => false, // TODO: Kashif

            /* (string or array) (optional) The string to use to build the read, edit and delete
               capabilities. May be passed as an array to allow for alternative plurals when using this
               argument as a base to construct the capabilities.
               Default: "post" */
            'capability_type' => Menu_Card_Info::$capability_for_custom_post,

            /* (boolean or string) (optional) Enables post type archives.
            Will use $post_type as archive slug by default.
            Default: false */
            'has_archive' => true,

            /* (boolean) (optional) Whether the post type is hierarchical (e.g. page).
               Allows Parent to be specified. The 'supports' parameter should contain 'page-attributes' to
               show the parent select box on the editor page. Default: false */
            'hierarchical' => false,

            /* (integer) (optional) The position in the menu order the post type should appear.
               show_in_menu must be true. Default: null - defaults to below Comments */
            'menu_position' => null,

            /* (array/boolean) (optional) An alias for calling add_post_type_support() directly.
               As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behaviour.
               Default: title and editor */
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail')
        );

        //Register Post type
        register_post_type( $this->post_type, $args);

    }


    /**
     * Registers taxonomy for custom post type
     */
    private function register_taxonomy() {

        $labels = array(
            'name'              => __('Menu Card Categories', 'menu-card'),
            'singular_name'     => __('Menu Card Category', 'menu-card'),
            'search_items'      => __('Search Menu Card Categories', 'menu-card'),
            'all_items'         => __('All Menu Card Categories', 'menu-card'),
            'parent_item'       => __('Parent Menu Card Category', 'menu-card'),
            'parent_item_colon' => __('Parent Menu Card Category:', 'menu-card'),
            'edit_item'         => __('Edit Menu Card Category', 'menu-card'),
            'update_item'       => __('Update Menu Card Category', 'menu-card'),
            'add_new_item'      => __('Add New Menu Card Category', 'menu-card'),
            'new_item_name'     => __('New Menu Card Category Name', 'menu-card'),
            'menu_name'         => __('Menu Card Categories', 'menu-card'),
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => false //array('slug' => 'Menu Card Category') // TODO: Kashif see rewrite, this should be same as wp settings
        );

        //Register Taxonomy
        register_taxonomy($this->texonomy, array( $this->post_type ), $args);
    }


    /**
     * Handles post_updated_messages filter. This filter is added in base class.
     *
     */
    public function handle_post_updated_messages($messages) {

        global $post, $post_ID;

        $messages[$this->post_type] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(__('Menu Card updated. <a href="%s">View Menu Card</a>', 'menu-card'), esc_url(get_permalink($post_ID))),
            2 => __('Menu Card updated', 'menu-card'),
            3 => __('Menu Card deleted', 'menu-card'),
            4 => __('Menu Card updated', 'menu-card'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Menu Card restored to revision from %s', 'menu-card'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(__('Menu Card published. <a href="%s">View Menu Card</a>', 'menu-card'), esc_url(get_permalink($post_ID))),
            7 => __('Menu Card saved.', 'menu-card'),
            8 => sprintf(__('Menu Card submitted <a target="_blank" href="%s">Preview Menu Card</a>', 'menu-card'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            9 => sprintf(__('Menu Card scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Menu Card</a>', 'menu-card'),
                // translators: Publish box date format, see php.net/date
                date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
            10 => sprintf(__('Menu Card draft updated. <a target="_blank" href="%s">Preview Menu Card</a>', 'menu-card'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        );

        return $messages;

    }

    /**
     * Register script and stylesheet for custom post admin page.
     * These would be enqueued later only when necessary.
     *
     * @since    0.1.0
     */
    private function register_script_and_style() {

        parent::register_script_and_style_base();

        wp_register_script( $this->prefix . '-script',
            Menu_Card_Info::$plugin_url . '/assets/js/admin-edit-menu-cards.js',
            array( 'jquery' ),
            Menu_Card_Info::version );

        wp_register_style( $this->prefix . '-style',
            Menu_Card_Info::$plugin_url . '/assets/css/admin-edit-menu-cards.css',
            array(),
            Menu_Card_Info::version );
    }

    public function handle_admin_enqueue_scripts($screen_suffix) {
        //Access the global $wp_version variable to see which version of WordPress is installed.
        global $wp_version;
        global $post;

        // check that we are on post add/edit page
        if ( $screen_suffix != 'post-new.php' && $screen_suffix != 'post.php' ) {
            return;
        }

        // check that post being edited is our custom post type
        if ( $this->post_type !== $post->post_type ) {
            return;
        }

        /*
        //If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
        if (3.5 <= $wp_version) {
          //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
          wp_enqueue_style('wp-color-picker');
          wp_enqueue_script('wp-color-picker');

        } //If the WordPress version is less than 3.5 load the older farbtasic color picker.
        else {
          //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
          wp_enqueue_style('farbtastic');
          wp_enqueue_script('farbtastic');
        }
        */

        //Enqueue base css file
        foreach(parent::$styles as $style) {
            wp_enqueue_style($style);
        }

        //Enqueue our custom css file
        wp_enqueue_style($this->prefix . '-style');

        //Enqueue our custom javascript file
        wp_enqueue_script($this->prefix . '-script');
    }

    /***************************** Override Template Pattern Functions *********************************/

    protected function get_default_values() {
        return self::$default_values;
    }

    /**
     * Child classes must override filter_post_data_on_save to save and sanitize post meta data
     *
     * @param original Associative array of meta data values from database
     * @param changed Associative array of meta data values submitted by user
     *
     * @return void
     **/
    protected function filter_post_data_on_save($original, $changed) {
        // TODO: Implement custom sanitization logic here or leave default implementation
        return $changed;
    }


    /*
     * Called by add_meta_boxes handler of base class
     * */
    protected function add_meta_boxes() {
        // Add as many meta boxes as you need here
        $this->add_meta_box('details', __('Details', 'menu-card'), 'render_details_metabox');
    }

    /********************************** Metaboxes Related ******************************************/
    /*
     * Renders Meta box on custom post add/edit page
     **/
    public function render_details_metabox() {
        $this->render_input_field('price', __('Price', 'menu-card'));
        $this->render_radio_field('link', __('Link', 'menu-card'), array('yes' => 'Linked with post', 'no' => 'No Link', 'custom_url' => 'Custom Url (use below field for url)'));
        $this->render_input_field('custom_url', __('Custom Url', 'menu-card'));
    }

    public function get_posts_by_category_callback() {
        $category = $_POST['category'];
        wp_send_json($this->list_posts_by_term($this->post_type, $this->texonomy, $category));
    }

    /**
     * Get posts and group by taxonomy terms.
     * @param string $posts Post type to get.
     * @param string $terms Taxonomy to group by.
     * @param array $filter_terms Taxonomy to group by.
     * @param integer $count How many post to show per taxonomy term.
     * @return object $grouped_posts
     */
    public function list_posts_by_term( $posts, $terms, $filter_terms, $count = -1 ) {
        $grouped_posts = array();
        $filter = array(
            'orderby' => 'name'
        );
        if($filter_terms){
            $filter['slug'] = $filter_terms;
        }
        $tax_terms = get_terms( $terms, $filter );
        foreach ( $tax_terms as $term ) {
            $args = array(
                'posts_per_page' => $count,
                $terms => $term->slug,
                'post_type' => $posts,
            );
            $tax_terms_posts = get_posts( $args );
            foreach ( $tax_terms_posts as $post ) {
                $post_meta = get_post_meta($post->ID, $this->prefix);
                $post->post_meta = $post_meta[0];
                $post->post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
            }

            $grouped_posts[] = array(
                'name' => $term->name,
                'description' => $term->description,
                'posts' => $tax_terms_posts
            );

        }
        return $grouped_posts;
    }

}