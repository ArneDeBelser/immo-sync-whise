<?php

namespace ADB\ImmoSyncWhise\Integrations\Elementor\Widgets;

use ADB\ImmoSyncWhise\Database\IWS_DetailsTable;

class EstateDetails extends \Elementor\Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        add_action('wp_ajax_fetch_estate_detials', [$this, 'fetchEstateDetails']);
        add_action('wp_ajax_nopriv_fetch_custom_estate_details', [$this, 'fetchEstateDetails']);
    }

    public function get_name()
    {
        return 'estate-details-widget';
    }

    public function get_title()
    {
        return __('Estate Details', 'immo-sync-whise');
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
        $create_details_table = new IWS_DetailsTable();
        $detail_labels = $create_details_table->getDistinctDetailLabels();

        $this->start_controls_section(
            'section_select_custom_fields',
            [
                'label' => esc_html__('Select Fields', 'immo-sync-whise'),
            ]
        );

        $this->add_control(
            'panddetails',
            [
                'label' => __('Selecteer panddetails', 'immo-sync-whise'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'detail_label',
                        'label' => __('Kies detail', 'immo-sync-whise'),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'options' => array_combine($detail_labels, $detail_labels),
                        'default' => '',
                        'label_block' => true,
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

    protected function render()
    {
        if (is_singular('estate')) {
            global $post;

            $pandDetails = $this->get_settings('panddetails');
            $detailsTable = new IWS_DetailsTable();

            if (!empty($pandDetails)) {
                echo '<div class="information_wrapper">';
                foreach ($pandDetails as $detail) {
                    $detail_label = $detail['detail_label'];
                    $before_text = $detail['before_text'];
                    $after_text = $detail['after_text'];
                    $detail_value = $detailsTable->getDetailValueByLabel($post->ID, $detail_label);

                    echo '<div class="information_group">';
                    echo esc_html($before_text);
                    echo '<p><span id="' . esc_attr($detail_label) . '">' . esc_html($detail_value) . '</span></p>';
                    echo esc_html($after_text);
                    echo '</div>';
                }
                echo '</div>';
            }
        }
    }

    protected function getCustomDetailValue($post_id, $detail_label)
    {
        // Replace 'custom_table' with your actual table name
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_table';

        $query = $wpdb->prepare(
            "SELECT detail_value FROM $table_name WHERE post_id = %d AND detail_label = %s",
            $post_id,
            $detail_label
        );

        $detail_value = $wpdb->get_var($query);

        return $detail_value ? $detail_value : '';
    }

    protected function content_template()
    {
?>
        <div class="information_wrapper">
            <p>Custom Estate information will be shown here.</p>
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
                            action: 'fetch_custom_estate_information',
                            security: '<?php echo wp_create_nonce('fetch_custom_estate_information_nonce'); ?>',
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

    public function fetchEstateDetails()
    {
        check_ajax_referer('fetch_custom_estate_information_nonce', 'security');

        $post_id = $_POST['elementorPreviewID'];
        $custom_fields = $this->get_settings('custom_fields');
        $html = '';

        if (!empty($custom_fields)) {
            $html .= '<div class="information_wrapper">';
            foreach ($custom_fields as $field) {
                $detail_label = $field['detail_label'];
                $detail_type = $field['detail_type'];
                $detail_group = $field['detail_group'];

                $detail_value = $this->getCustomDetailValue($post_id, $detail_label);

                $html .= '<div class="information_group">';
                $html .= '<p><strong><span>' . esc_html($detail_label) . ':</span></strong> <span id="' . esc_attr($detail_label) . '">' . esc_html($detail_value) . '</span></p>';
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            $html .= '<div class="information_group">';
            $html .= '<p>Custom Estate information will be shown here.</p>';
            $html .= '</div>';
        }

        wp_send_json_success(array('html' => $html));
        wp_die();
    }
}
