<?php

namespace ADB\ImmoSyncWhise\Admin\CPT;

use ADB\ImmoSyncWhise\Container;
use ADB\ImmoSyncWhise\Enum\PostType;
use ADB\ImmoSyncWhise\Model\Estate;

class EstateCPT
{
    public function __construct()
    {
        add_action('init',                              [$this, 'create']);
        add_action('admin_enqueue_scripts',             [$this, 'admin_scripts']);
        add_action('add_meta_boxes_' . PostType::Name,  [$this, 'estate_add_meta_boxes']);
        add_filter('manage_posts_columns',              [$this, 'featured_image_column']);
        add_action('manage_posts_custom_column',        [$this, 'featured_image_column_data'], 10, 2);
        add_action('admin_head',                        [$this, 'columns_css']);
        add_action('wp_ajax_toggle_show_field',         [$this, 'toggle_show_field_callback']);
        add_action('wp_ajax_nopriv_toggle_show_field',  [$this, 'toggle_show_field_callback']);
    }

    public function create()
    {
        $labels = array(
            'name'          => 'Estates',
            'singular_name' => 'Estate'
        );

        $supports = array(
            'title',
            'thumbnail',
            'revisions',
        );

        $capabilities = array(
            'create_posts' => false
        );

        $args = array(
            'labels'              => $labels,
            'description'         => 'Whise Estates',
            'supports'            => $supports,
            'taxonomies'          => array(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-admin-multisite',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'capabilities'        => $capabilities,
            'map_meta_cap'        => true,
        );

        register_post_type(PostType::Name, $args);
    }

    public function estate_add_meta_boxes($post)
    {
        add_meta_box(
            'estate_information_meta_box',
            __('Estate Information', 'wp_whise'),
            [
                $this,
                'information_build_meta_box'
            ],
            PostType::Name,
            'advanced',
            'low'
        );

        add_meta_box(
            'estate_details_meta_box',
            __('Estate Details', 'wp_whise'),
            [
                $this,
                'detail_build_meta_box'
            ],
            PostType::Name,
            'advanced',
            'low'
        );

        add_meta_box(
            'estate_gallery_meta_box',
            __('Estate Gallery', 'wp_whise'),
            [
                $this,
                'gallery_build_meta_box'
            ],
            PostType::Name,
            'advanced',
            'low'
        );
    }

    public function featured_image_column($columns)
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'featured_image' => 'Image',
            'title' => 'Title',
            'date' => 'Date'
        );

        return $columns;
    }

    public function featured_image_column_data($column, $post_id)
    {
        switch ($column) {
            case 'featured_image':
                the_post_thumbnail('thumbnail');
                break;
        }
    }

    public function columns_css()
    {
        echo '<style type="text/css">';
        echo '.column-featured_image { width: 160px !important; overflow:hidden }';
        echo '.column-featured_image img { width: 100px !important; height: 100px !important; }';
        echo '.column-date { width: 30% !important }';
        echo '</style>';
    }

    public function detail_build_meta_box($post)
    {
        include_once ISW_PATH . '/templates/estate/detail.php';
    }

    public function information_build_meta_box($post)
    {
        include_once ISW_PATH . '/templates/estate/information.php';
    }

    public function gallery_build_meta_box($post)
    {
        include_once ISW_PATH . '/templates/estate/gallery.php';
    }

    public function toggle_show_field_callback()
    {
        if (isset($_POST['post_id'], $_POST['meta_key'], $_POST['meta_value'])) {
            $post_id = intval($_POST['post_id']);
            $meta_key = sanitize_text_field($_POST['meta_key']);
            $meta_value = intval($_POST['meta_value']);

            update_post_meta($post_id, '_show' . $meta_key, $meta_value);

            echo 'success';
        } else {
            echo 'error';
        }

        wp_die();
    }

    public function admin_scripts()
    {
        global $post_type;

        if (PostType::Name == $post_type) {
            wp_enqueue_media();
            wp_enqueue_style('whise-jquery-css', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
            // wp_enqueue_script('whise-gallery-js', plugins_url('/immo-sync-whise/assets/admin/js/gallery.js'), ['media-models'], ISW_VERSION);
            // wp_enqueue_script('whise-document-js', plugins_url('/immo-sync-whise/assets/admin/js/document.js'), ['media-models'], ISW_VERSION);
            wp_enqueue_script('jquery-ui-accordion');
            wp_enqueue_script('whise-index-js', plugins_url('/immo-sync-whise/assets/admin/js/index.js'), ['jquery', 'jquery-ui-accordion'], ISW_VERSION);
            wp_enqueue_style('whise-estate-css', plugins_url('/immo-sync-whise/assets/admin/css/estate.css'), [], ISW_VERSION . time());

            if (is_admin()) {
                wp_enqueue_script('whise-estate-js', plugins_url('/immo-sync-whise/assets/admin/js/estate.js'), ['media-models'], ISW_VERSION);
                wp_localize_script('whise-estate-js', 'estateData', ['post_id' => get_the_ID()]);
            }
        }
    }
}
