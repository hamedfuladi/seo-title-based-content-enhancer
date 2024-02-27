<?php
/**
 * Plugin Name: SEO Title-Based Content Enhancer
 * Description: Inserts a dynamic text based on the post title at the end of post content for SEO purposes.
 * Version: 1.0.0
 * Author: Hamed Fuladi
 * Author URI: https://hamedfuladi.github.io/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SEO_Title_Based_Content_Enhancer
{

    /**
     * Constructor function that sets up WordPress hooks.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('the_content', array($this, 'enhance_content'));
        add_filter('mce_buttons', array($this, 'add_mce_button'));
        add_filter('mce_external_plugins', array($this, 'add_mce_plugin'));
        add_action('admin_enqueue_scripts', array($this, 'seo_enhancer_scripts'));
    }

    /**
     * Creates an options page in the WordPress admin dashboard.
     */
    public function add_admin_menu() {
        add_options_page(
            __('SEO Title-Based Content Enhancer Settings', 'seo-title-based-content-enhancer'),
            __('SEO Enhancer', 'seo-title-based-content-enhancer'),
            'manage_options',
            'seo-enhancer-settings',
            array($this, 'settings_page')
        );
    }


    /**
     * Displays the contents of the created options page.
     */

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('SEO Title-Based Content Enhancer Settings', 'seo-title-based-content-enhancer'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('seo_title_based_content_enhancer_settings');
                do_settings_sections('seo-enhancer-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }


    /**
     * Registers the options for the plugin.
     */
    public function register_settings()
    {
        register_setting('seo_title_based_content_enhancer_settings', 'seo_title_based_content_enhancer_settings');

        add_settings_section(
            'seo_title_based_content_enhancer_main_section',
            'SEO Enhancer Settings',
            '',
            'seo-enhancer-settings'
        );

        add_settings_field(
            'seo_title_based_content_enhancer_sentence',
            'Dynamic Text Sentence',
            array($this, 'settings_field'),
            'seo-enhancer-settings',
            'seo_title_based_content_enhancer_main_section'
        );

        add_settings_field(
            'seo_title_based_content_enhancer_categories',
            'Exclude Categories',
            array($this, 'categories_field'),
            'seo-enhancer-settings',
            'seo_title_based_content_enhancer_main_section'
        );

        add_settings_field(
            'seo_title_based_content_enhancer_tags',
            'Exclude Tags',
            array($this, 'tags_field'),
            'seo-enhancer-settings',
            'seo_title_based_content_enhancer_main_section'
        );
    }

    /**
     * Displays a list of tags and allows the user to exclude certain tags from being enhanced by the plugin.
     */
    public function tags_field()
    {
        $options = get_option('seo_title_based_content_enhancer_settings');
        $selected_tags = isset($options['exclude_tags']) ? $options['exclude_tags'] : array();
        $tags = get_tags();

        if (count($tags) == 0) {
            echo 'No tags found.';
            return;
        }
        ?>
        <select name="seo_title_based_content_enhancer_settings[exclude_tags][]" multiple class="select2Tag"
                style="width:100%">
            <?php foreach ($tags as $tag) { ?>
                <option value="<?php echo esc_attr($tag->term_id); ?>" <?php selected(in_array($tag->term_id, $selected_tags)); ?>><?php echo esc_html($tag->name); ?></option>
            <?php } ?>
        </select>
        <script>
            jQuery(document).ready(function ($) {
                $('.select2Tag').select2({
                    placeholder: 'Select tags to exclude '
                });
            });
        </script>

        <?php
    }

    /**
     * Displays the text editor field to enter dynamic text sentence.
     */
    public function settings_field()
    {
        $options = get_option('seo_title_based_content_enhancer_settings');
        $sentence = isset($options['sentence']) ? $options['sentence'] : '';
        wp_editor(
            $sentence,
            'seo_title_based_content_enhancer_sentence',
            array(
                'textarea_name' => 'seo_title_based_content_enhancer_settings[sentence]',
                'media_buttons' => false,
                'textarea_rows' => 5,
                // 'teeny' => true
            )
        );
    }
    /**
     * Displays a list of categories and allows the user to exclude certain categories from being enhanced by the plugin.
     */
    public function categories_field()
    {
        $options = get_option('seo_title_based_content_enhancer_settings');
        $selected_categories = isset($options['exclude_categories']) ? $options['exclude_categories'] : array();
        $categories = get_categories();

        if (count($categories) == 0) {
            echo 'No categories found.';
            return;
        }
        ?>
        <select name="seo_title_based_content_enhancer_settings[exclude_categories][]" multiple class="select2Cat"
                style="width:100%">
            <?php foreach ($categories as $category) { ?>
                <option value="<?php echo esc_attr($category->cat_ID); ?>" <?php selected(in_array($category->cat_ID, $selected_categories)); ?>><?php echo esc_html($category->name); ?></option>
            <?php } ?>
        </select>
        <script>
            jQuery(document).ready(function ($) {
                $('.select2Cat').select2({
                    placeholder: 'Select categories to exclude '
                });
            });
        </script>

        <?php
    }

    /**
     * Enqueues the necessary scripts and styles for the options page.
     */
    public function seo_enhancer_scripts()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'seo-enhancer-settings') {
            // Get the plugin directory URL
            $plugin_dir_url = plugin_dir_url(__FILE__);

            // Enqueue select2 script
            wp_enqueue_script('select2-script', $plugin_dir_url . 'assets/js/select2.min.js', array('jquery'), '4.0.13', true);

            // Enqueue select2 style
            wp_enqueue_style('select2-style', $plugin_dir_url . 'assets/css/select2.min.css', array(), '4.0.13');
        }
    }


    /**
     * Adds a button to the visual editor toolbar.
     */
    public function add_mce_button($buttons)
    {
        $screen = get_current_screen();
        if ($_GET['page'] === 'seo-enhancer-settings') {
            array_push($buttons, '|', 'seo_title_based_content_enhancer_title_button');
        }
        return $buttons;
    }

    /**
     * Specifies a URL where the JavaScript file for the added button can be found.
     */
    public function add_mce_plugin($plugins)
    {
        $screen = get_current_screen();
        if ($_GET['page'] === 'seo-enhancer-settings') {
            $plugins['seo_title_based_content_enhancer_title_button'] = plugins_url('/title-button.js', __FILE__);
        }
        return $plugins;
    }

    /**
     * Modifies the content of the post to include dynamic text based on the post title.
     * It also checks if the post belongs to any excluded category or tag, and if so, does not enhance that post.
     */


    public function enhance_content($content) {
        global $post;

        if (!in_the_loop() || !is_singular() || !is_main_query() || $post->post_type !== 'post') {
            return $content;
        }

        $options = get_option('seo_title_based_content_enhancer_settings');
        $sentence = isset($options['sentence']) ? wp_kses_post($options['sentence']) : '';
        $exclude_categories = isset($options['exclude_categories']) ? $options['exclude_categories'] : array();
        $exclude_tags = isset($options['exclude_tags']) ? $options['exclude_tags'] : array();

        $categories = wp_get_post_categories($post->ID);
        foreach ($categories as $category) {
            if (in_array($category, $exclude_categories)) {
                return $content;
            }
        }

        $tags = wp_get_post_tags($post->ID);
        foreach ($tags as $tag) {
            if (in_array($tag->term_id, $exclude_tags)) {
                return $content;
            }
        }

        $title = get_the_title();
        $dynamic_text = str_replace('[title]', wp_kses_post($title), $sentence);
        $content .= '<p>' . $dynamic_text . '</p>';

        return $content;
    }
}

new SEO_Title_Based_Content_Enhancer();

