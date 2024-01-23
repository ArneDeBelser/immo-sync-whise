<?php

namespace ADB\ImmoSyncWhise\Integrations\Elementor\Widgets;

use ADB\ImmoSyncWhise\Matchers\EstateMatcher;
use ADB\ImmoSyncWhise\Model\Estate;
use Illuminate\Container\Container;

class EstateInformation extends \Elementor\Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        add_action('wp_ajax_fetch_estate_information', [$this, 'fetchEstateInformation']);
        add_action('wp_ajax_nopriv_fetch_estate_information', [$this, 'fetchEstateInformation']);
    }

    public function get_name()
    {
        return 'estate-information-widget';
    }

    public function get_title()
    {
        return __('Estate Information', ' immo-sync-whise');
    }

    public function get_icon()
    {
        return 'eicon-wordpress';
    }

    public function get_categories()
    {
        return ['immo-sync-whise'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_select_meta_fields',
            [
                'label' => esc_html__('Select Fields', 'immo-sync-whise'),
            ]
        );

        $this->add_control(
            'fields',
            [
                'label' => __('Select and Order Fields', 'immo-sync-whise'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'selected_field',
                        'label' => __('Select Meta Field', 'immo-sync-whise'),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        //'options' => (array) $metaFields,
                        'options' => [
                            "_iws_id" => "_iws_id",
                            "_iws_address" => "_iws_address",
                            "_iws_area" => "_iws_area",
                            "_iws_bathRooms" => "_iws_bathRooms",
                            "_iws_city" => "_iws_city",
                            "_iws_client" => "_iws_client",
                            "_iws_clientId" => "_iws_clientId",
                            "_iws_currency" => "_iws_currency",
                            "_iws_displayStatusId" => "_iws_displayStatusId",
                            "_iws_energyClass" => "_iws_energyClass",
                            "_iws_energyValue" => "_iws_energyValue",
                            "_iws_fronts" => "_iws_fronts",
                            "_iws_furnished" => "_iws_furnished",
                            "_iws_garage" => "_iws_garage",
                            "_iws_garden" => "_iws_garden",
                            "_iws_gardenArea" => "_iws_gardenArea",
                            "_iws_groundArea" => "_iws_groundArea",
                            "_iws_maxArea" => "_iws_maxArea",
                            "_iws_minArea" => "_iws_minArea",
                            "_iws_name" => "_iws_name",
                            "_iws_number" => "_iws_number",
                            "_iws_office" => "_iws_office",
                            "_iws_officeId" => "_iws_officeId",
                            "_iws_parking" => "_iws_parking",
                            "_iws_price" => "_iws_price",
                            "_iws_publicationText" => "_iws_publicationText",
                            "_iws_referenceNumber" => "_iws_referenceNumber",
                            "_iws_rooms" => "_iws_rooms",
                            "_iws_shortDescription" => "_iws_shortDescription",
                            "_iws_sms" => "_iws_sms",
                            "_iws_terrace" => "_iws_terrace",
                            "_iws_zip" => "_iws_zip",
                            "_iws_category" => "_iws_category",
                            "_iws_subCategory" => "_iws_subCategory",
                            "_iws_country" => "_iws_country",
                            "_iws_status" => "_iws_status",
                            "_iws_purpose" => "_iws_purpose",
                            "_iws_purposeStatus" => "_iws_purposeStatus",
                            "_iws_state" => "_iws_state"
                        ],
                    ],
                    [
                        'name' => 'before_text',
                        'label' => __('Text Before Field', 'immo-sync-whise'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => '',
                        'label_block' => true,
                    ],
                    [
                        'name' => 'after_text',
                        'label' => __('Text After Field', 'immo-sync-whise'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => '',
                        'label_block' => true,
                    ],
                ],
                'default' => [],
            ]
        );

        $this->end_controls_section();
    }

    private function getMetaFields()
    {
        $customExcludes = ['_thumbnail_id', 'ekit_post_views_count', '_edit_lock'];
        $elementorEditPostId = \Elementor\Plugin::$instance->editor->get_post_id();

        if ($elementorEditPostId && metadata_exists('post', $elementorEditPostId, '_elementor_page_settings')) {
            $elementorPageSettings = get_post_meta($elementorEditPostId, '_elementor_page_settings', true);
            $elementorPageSettings = is_serialized($elementorPageSettings) ? unserialize($elementorPageSettings) : $elementorPageSettings;

            if (is_array($elementorPageSettings) && isset($elementorPageSettings['preview_id'])) {
                $postId = $elementorPageSettings['preview_id'];
                $meta_fields = get_post_meta($postId);
                $options = [];

                foreach ($meta_fields as $key => $value) {
                    if (strpos($key, 'show') === false && !in_array($key, $customExcludes)) {
                        $options[$key] = $key;
                    }
                }

                return $options;
            }
        }

        return [];
    }

    protected function render()
    {
        if (is_singular('estate')) {
            global $post;

            $estate = Container::getInstance()->make(Estate::class);
            $matcher = Container::getInstance()->make(EstateMatcher::class);
            $fields = $estate->getMetaFields($post->ID);

            if (!empty($fields)) {
                $field_order = $this->get_settings('fields');

                echo '<div class="information_wrapper">';
                foreach ($field_order as $field_item) {
                    $selected_field = $field_item['selected_field'];
                    $before_text = $field_item['before_text'];
                    $after_text = $field_item['after_text'];
                    $showField = get_post_meta($post->ID, '_show' . $selected_field, true);

                    if ($showField == 1) {
                        echo '<div class="information_group">';
                        echo '<p>';
                        echo esc_html($before_text)
                            . '<span id="' . esc_attr($selected_field)  . '">' . esc_html($matcher->getField($selected_field, $fields[$selected_field][0])) . '</span>'
                            . esc_html($after_text);
                        echo '</p>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }
    }


    protected function content_template()
    {
?>
        <div class="information_wrapper">
            <p>Estate information will be shown here.</p>
        </div>

        <script>
            (function($) {
                $(document).ready(function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const elementorPreviewID = urlParams.get('elementor-preview');
                    console.log(elementorPreviewID);

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'fetch_estate_information',
                            security: '<?php echo wp_create_nonce('fetch_estate_information_nonce'); ?>',
                            elementorPreviewID: elementorPreviewID
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.information_wrapper').html(response.data.html);
                                console.error('Success:', response.data);
                            } else {
                                console.error('Error:', response.data);
                            }
                        },
                        error: function(errorThrown) {
                            console.error('AJAX Error:', errorThrown);
                        }
                    });
                });
            })(jQuery);
        </script>
<?php
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
}
