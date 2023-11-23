<?php

namespace ADB\ImmoSyncWhise\Integrations\Elementor;

use ADB\ImmoSyncWhise\Integrations\Elementor\Widgets\EstateInformation;
use ADB\ImmoSyncWhise\Matchers\EstateMatcher;
use ADB\ImmoSyncWhise\Model\Estate;

class ElementorWidgetInstantior
{
    public $widgets = [];

    public function __construct()
    {
        add_action('elementor/widgets/register', [$this, 'registerWidget']);
        add_action('elementor/elements/categories_registered', [$this, 'registerCategory']);

        add_action('wp_ajax_fetch_estate_information', [$this, 'fetchEstateInformation']);
        add_action('wp_ajax_nopriv_fetch_estate_information', [$this, 'fetchEstateInformation']);
    }

    /**
     * This function is in an unhappy place for now. Normally this would live in the widget. But the widget constructor won't allow to me hook into wp_ajax
     * 
     * This function returns the HTML for the EstateInformation widget content_template function
     *
     * @return void
     */
    public function fetchEstateInformation()
    {
        check_ajax_referer('fetch_estate_information_nonce', 'security');

        $estate = new Estate();
        $matcher = new EstateMatcher();

        $elementorWidgetFields = get_post_meta($_POST['elementorPreviewID']);
        $previewObject = unserialize($elementorWidgetFields['_elementor_page_settings'][0]);
        $postPreviewID = $previewObject['preview_id'];

        $fields = $estate->getMetaFields($postPreviewID);

        ob_start();

        if (!empty($fields)) {
            echo '<div class="information_wrapper">';
            foreach ($fields as $key => $field) {
                $showField = get_post_meta($postPreviewID, '_show' . $key, true);
                if ($showField == 1) {
                    echo '<div class="information_group">';
                    echo '<p><strong><span>' . esc_html($matcher->getTitle($key)) . ':</span></strong> <span id="' . esc_attr($key) . '">' . esc_html($matcher->getField($key, $field[0])) . '</span></p>';
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="information_group">';
            echo '<p>Estate information will be shown here.</p>';
            echo '</div>';
        }

        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
        wp_die();
    }

    public function registerWidget($widgets_manager)
    {
        $widgets_manager->register(new EstateInformation());
    }

    public function registerCategory($elements_manager)
    {
        $elements_manager->add_category(
            'immo-sync-whise',
            [
                'title' => __('Immo Sync Whise', 'immo-sync-whise'),
                'icon'  => 'fa fa-plug',
            ]
        );
    }
}
